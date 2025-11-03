# Plan de Refactorisation: Architecture Agent par Matiere

**Projet**: BeautifuLLL AI
**Date**: 2025-01-03
**Objectif**: Transformer l'architecture de "etudiants creent leurs agents" vers "un agent par matiere avec parametres LLM personnalisables + conversations adaptees au niveau etudiant"

---

## Table des Matieres

1. [Analyse de l'Existant](#1-analyse-de-lexistant)
2. [Architecture Cible](#2-architecture-cible)
3. [Plan de Migration](#3-plan-de-migration)
4. [Ordre d'Implementation](#4-ordre-dimplementation)
5. [Tests et Validation](#5-tests-et-validation)

---

## 1. Analyse de l'Existant

### 1.1 Schema de Base de Donnees Actuel

#### Table `Agent` (PROBLEMATIQUE)
```sql
CREATE TABLE Agent(
   id_agents INT AUTO_INCREMENT,
   nom_agent VARCHAR(50) NOT NULL,
   type_agent VARCHAR(50) NOT NULL,
   avatar_agent VARCHAR(255),
   est_actif BOOLEAN DEFAULT TRUE,
   description TEXT,
   date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
   prompt_systeme TEXT,
   id_matieres INT,                    -- OPTIONNEL (peut etre NULL)
   id_etudiant INT NOT NULL,           -- PROBLEME: Agent appartient a un etudiant
   PRIMARY KEY(id_agents),
   UNIQUE(nom_agent),
   FOREIGN KEY(id_matieres) REFERENCES Matieres(id_matieres),
   FOREIGN KEY(id_etudiant) REFERENCES Etudiants(id_etudiant)
)
```

**Problemes identifies**:
- Agent appartient a un etudiant specifique (`id_etudiant NOT NULL`)
- Matiere est optionnelle (`id_matieres` peut etre NULL)
- Pas de contrainte unique sur `id_matieres` (plusieurs agents pour une matiere)
- Parametres LLM non stockes en base (hardcodes dans le code)

#### Table `Etudiants`
```sql
CREATE TABLE Etudiants(
   id_etudiant INT AUTO_INCREMENT,
   nom VARCHAR(50) NOT NULL,
   prenom VARCHAR(50) NOT NULL,
   email VARCHAR(100) NOT NULL,
   id_niveau INT NOT NULL,             -- IMPORTANT: Niveau de l'etudiant
   id_role INT NOT NULL,
   PRIMARY KEY(id_etudiant),
   FOREIGN KEY(id_niveau) REFERENCES Niveau(id_niveau)
)
```

#### Table `Session_conversation`
```sql
CREATE TABLE Session_conversation(
   id_session INT AUTO_INCREMENT,
   date_heure_debut DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   duree_session TIME,
   date_heure_fin DATETIME,
   id_agents INT NOT NULL,
   id_etudiant INT NOT NULL,
   PRIMARY KEY(id_session),
   FOREIGN KEY(id_agents) REFERENCES Agent(id_agents),
   FOREIGN KEY(id_etudiant) REFERENCES Etudiants(id_etudiant)
)
```

#### Table `Message`
```sql
CREATE TABLE Message(
   id_message INT AUTO_INCREMENT,
   role ENUM('user', 'assistant') NOT NULL,
   contenu TEXT NOT NULL,
   date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
   id_session INT NOT NULL,
   PRIMARY KEY(id_message),
   FOREIGN KEY(id_session) REFERENCES Session_conversation(id_session)
)
```

### 1.2 Problemes de l'Architecture Actuelle

#### A. Systeme de Chat Fragmente
Il existe **DEUX implementations separees** non connectees:

**Implementation 1: Chat Session (Utilise actuellement)**
- Fichiers: `chatModel.php`, `chat_card.php`, `agent_matiere_form.php`
- Stockage: `$_SESSION['chat_messages']` (session PHP seulement)
- Agent: N'utilise PAS la table Agent
- Parametres LLM: Hardcodes dans `chatModel.php`
- Persistence: AUCUNE - perdu a la deconnexion

**Implementation 2: CRUD Agent/Session/Message (Non utilise)**
- Fichiers: Controllers et Models pour Agent, Session, Message
- Stockage: Base de donnees MySQL
- Fonctionnalite: CRUD basique
- Probleme: Jamais integre au chat reel

#### B. Configuration LLM Hardcodee
```php
// Dans chatModel.php ligne 109-118
$data = [
    'messages' => $groqMessages,
    'model' => 'openai/gpt-oss-20b',      // HARDCODE
    'temperature' => 1,                    // HARDCODE
    'max_completion_tokens' => 8192,       // HARDCODE
    'top_p' => 1,                          // HARDCODE
    'reasoning_effort' => 'medium',        // HARDCODE
    'stream' => false,
    'stop' => null
];
```

**Probleme**: Impossible de personnaliser par matiere ou agent.

#### C. Prompt Systeme Non Utilise
```php
// chatModel.php genere son propre prompt au lieu d'utiliser agent.prompt_systeme
private static function getSystemPrompt() {
    $user = $_SESSION['user'] ?? null;
    $isAdmin = $user && isset($user['role']) && $user['role'] === 'Administrateur';
    $matiere = $_SESSION['agent_ia_matiere'] ?? null;

    if ($isAdmin) {
        return "Tu es un assistant IA pour BeautifuLLL AI...";
    } elseif ($matiere) {
        return "Tu es un agent IA expert dans la matiere '$matiere'...";
    }
    // Agent.prompt_systeme jamais utilise!
}
```

#### D. Niveau Etudiant Mal Integre
```php
// Dans chat_card.php ligne 16 - BUG
$nom_niveau = $niveau['nom_matieres'] ?? 'Non defini';  // ERREUR: devrait etre 'libelle_niveau'
```

Le niveau existe en base mais n'est pas correctement recupere ni utilise.

---

## 2. Architecture Cible

### 2.1 Principes de la Nouvelle Architecture

1. **Un Agent par Matiere**: Chaque matiere a UN SEUL agent configure par l'admin
2. **Parametres LLM Configurables**: Chaque agent a ses propres parametres (model, temperature, etc.)
3. **Adaptation au Niveau**: Le prompt systeme s'adapte automatiquement au niveau de l'etudiant
4. **Persistence Complete**: Toutes les conversations sont sauvegardees en base de donnees
5. **Integration Totale**: Le chat utilise les agents en base de donnees

### 2.2 Nouveau Schema de Base de Donnees

#### Table `Agent` (MODIFIEE)
```sql
CREATE TABLE Agent(
   id_agents INT AUTO_INCREMENT,
   nom_agent VARCHAR(50) NOT NULL,
   type_agent VARCHAR(50) NOT NULL,
   avatar_agent VARCHAR(255),
   est_actif BOOLEAN DEFAULT TRUE,
   description TEXT,
   date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
   prompt_systeme TEXT NOT NULL,                    -- OBLIGATOIRE

   -- NOUVEAU: Parametres LLM
   model VARCHAR(100) DEFAULT 'openai/gpt-oss-20b',
   temperature DECIMAL(3,2) DEFAULT 0.7,
   max_tokens INT DEFAULT 8192,
   top_p DECIMAL(3,2) DEFAULT 1.0,
   reasoning_effort ENUM('low', 'medium', 'high') DEFAULT 'medium',

   -- MODIFIE: Matiere obligatoire, etudiant supprime
   id_matieres INT NOT NULL,                        -- OBLIGATOIRE

   PRIMARY KEY(id_agents),
   UNIQUE(nom_agent),
   UNIQUE(id_matieres),                             -- NOUVEAU: Un seul agent par matiere
   FOREIGN KEY(id_matieres) REFERENCES Matieres(id_matieres) ON DELETE CASCADE
);
```

**Changements cles**:
- ❌ Supprime: `id_etudiant` (agents ne sont plus possedes par etudiants)
- ✅ Modifie: `id_matieres NOT NULL` (matiere obligatoire)
- ✅ Ajoute: `UNIQUE(id_matieres)` (un agent par matiere)
- ✅ Ajoute: Colonnes parametres LLM (model, temperature, max_tokens, top_p, reasoning_effort)

#### Tables Existantes (Inchangees)
- `Etudiants`: Garde `id_niveau` pour adapter les conversations
- `Session_conversation`: Continue de lier agent + etudiant
- `Message`: Continue de stocker les messages

### 2.3 Flux de Conversation Cible

```
1. Etudiant selectionne une MATIERE
   └─> Systeme charge automatiquement l'AGENT associe a cette matiere

2. Systeme recupere:
   - Agent.prompt_systeme (prompt de base de la matiere)
   - Agent.model, temperature, etc. (parametres LLM)
   - Etudiant.id_niveau (niveau scolaire de l'etudiant)

3. Construction du prompt final:
   Prompt_Final = Agent.prompt_systeme + "Adapte ton discours au niveau {niveau}"

4. Creation Session_conversation:
   - id_agents: Agent de la matiere
   - id_etudiant: Etudiant connecte
   - date_heure_debut: NOW()

5. Pour chaque message:
   - Envoi API avec parametres de l'agent
   - Sauvegarde dans table Message
   - Cache dans $_SESSION pour performance

6. Fin de conversation:
   - Mise a jour duree_session
   - date_heure_fin
```

---

## 3. Plan de Migration

### PHASE 1: Migration Base de Donnees

#### Etape 1.1: Script de Migration
**Fichier**: `php-crud/migrations/refactor_agent_by_subject.sql`

```sql
-- ================================================================
-- Migration: Refactorisation Agent par Matiere
-- Date: 2025-01-03
-- Description: Transformer agents possedes par etudiants en agents par matiere
-- ================================================================

START TRANSACTION;

-- 1. Sauvegarder les anciens agents (backup)
CREATE TABLE IF NOT EXISTS Agent_backup AS SELECT * FROM Agent;

-- 2. Supprimer les anciens agents (architecture incompatible)
DELETE FROM Agent;

-- 3. Ajouter les nouvelles colonnes LLM
ALTER TABLE Agent
ADD COLUMN model VARCHAR(100) DEFAULT 'openai/gpt-oss-20b' AFTER prompt_systeme,
ADD COLUMN temperature DECIMAL(3,2) DEFAULT 0.7 AFTER model,
ADD COLUMN max_tokens INT DEFAULT 8192 AFTER temperature,
ADD COLUMN top_p DECIMAL(3,2) DEFAULT 1.0 AFTER max_tokens,
ADD COLUMN reasoning_effort ENUM('low', 'medium', 'high') DEFAULT 'medium' AFTER top_p;

-- 4. Modifier la colonne id_matieres (obligatoire)
ALTER TABLE Agent
MODIFY COLUMN id_matieres INT NOT NULL;

-- 5. Ajouter contrainte unique sur id_matieres
ALTER TABLE Agent
ADD CONSTRAINT unique_agent_per_matiere UNIQUE(id_matieres);

-- 6. Supprimer la colonne id_etudiant
ALTER TABLE Agent
DROP FOREIGN KEY Agent_ibfk_2;  -- Nom peut varier, a verifier

ALTER TABLE Agent
DROP COLUMN id_etudiant;

-- 7. Modifier prompt_systeme (obligatoire)
ALTER TABLE Agent
MODIFY COLUMN prompt_systeme TEXT NOT NULL;

COMMIT;
```

#### Etape 1.2: Script de Seed Agents par Defaut
**Fichier**: `php-crud/migrations/seed_default_agents.sql`

```sql
-- ================================================================
-- Seed: Agents par Defaut pour chaque Matiere
-- Date: 2025-01-03
-- ================================================================

START TRANSACTION;

-- Agent pour Francais
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Francais',
    'Assistant_Pedagogique',
    'Assistant specialise en langue francaise et litterature',
    'Tu es un professeur de francais expert et pedagogique. Tu aides les eleves a comprendre la grammaire, l\'orthographe, la conjugaison et la litterature francaise. Tu adaptes ton niveau de langage et tes explications en fonction du niveau scolaire de l\'eleve. Tu encourages toujours l\'eleve et rends l\'apprentissage agreable.',
    'openai/gpt-oss-20b',
    0.7,
    8192,
    1.0,
    'medium',
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Francais'),
    TRUE
);

-- Agent pour Anglais
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Anglais',
    'Assistant_Pedagogique',
    'Assistant specialise en langue anglaise',
    'You are an English teacher specialized in helping French-speaking students. Tu peux alterner entre francais et anglais pour expliquer. Tu enseignes le vocabulaire, la grammaire, la prononciation et la culture anglophone. Adapte ton niveau selon la classe de l\'eleve (6eme a Terminale).',
    'openai/gpt-oss-20b',
    0.7,
    8192,
    1.0,
    'medium',
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Anglais'),
    TRUE
);

-- Agent pour Mathematiques
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Maths',
    'Tuteur_Prive',
    'Assistant specialise en mathematiques',
    'Tu es un professeur de mathematiques patient et pedagogique. Tu expliques les concepts mathematiques de maniere claire et progressive. Tu utilises des exemples concrets et des schemas quand c\'est necessaire. Tu decomposes les problemes complexes en etapes simples. Tu adaptes tes explications au niveau scolaire de l\'eleve.',
    'openai/gpt-oss-20b',
    0.5,  -- Plus bas pour plus de precision
    8192,
    1.0,
    'high',  -- Plus haut pour raisonnement mathematique
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Mathematique'),
    TRUE
);

-- Agent pour Histoire-Geo
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Histoire-Geo',
    'Assistant_Pedagogique',
    'Assistant specialise en histoire et geographie',
    'Tu es un professeur d\'histoire-geographie passionne. Tu rends l\'histoire vivante avec des anecdotes et des recits captivants. Tu expliques les liens entre les evenements historiques et la geographie. Tu contextualises toujours les informations pour aider a la comprehension. Tu adaptes la complexite selon le niveau de l\'eleve.',
    'openai/gpt-oss-20b',
    0.8,  -- Plus creatif pour recits historiques
    8192,
    1.0,
    'medium',
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Histoire-geo'),
    TRUE
);

-- Agent pour Biologie
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Biologie',
    'Assistant_Pedagogique',
    'Assistant specialise en sciences de la vie',
    'Tu es un professeur de biologie enthousiaste. Tu expliques le fonctionnement du vivant de maniere claire et accessible. Tu utilises des exemples de la vie quotidienne pour illustrer les concepts biologiques. Tu encourages la curiosite scientifique et l\'observation. Tu adaptes tes explications au niveau de l\'eleve.',
    'openai/gpt-oss-20b',
    0.7,
    8192,
    1.0,
    'medium',
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Biologie'),
    TRUE
);

-- Agent pour Physique
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Physique',
    'Tuteur_Prive',
    'Assistant specialise en physique',
    'Tu es un professeur de physique pedagogique. Tu expliques les lois de la physique avec des experiences concretes et des exemples du quotidien. Tu lies theorie et pratique. Tu utilises des schemas et des calculs adaptes. Tu decomposes les problemes de physique etape par etape. Tu adaptes au niveau scolaire.',
    'openai/gpt-oss-20b',
    0.6,  -- Precision pour calculs
    8192,
    1.0,
    'high',
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Phisique'),  -- Respecte l'orthographe actuelle en base
    TRUE
);

-- Agent pour Chimie
INSERT INTO Agent (
    nom_agent,
    type_agent,
    description,
    prompt_systeme,
    model,
    temperature,
    max_tokens,
    top_p,
    reasoning_effort,
    id_matieres,
    est_actif
) VALUES (
    'Prof Chimie',
    'Assistant_Pedagogique',
    'Assistant specialise en chimie',
    'Tu es un professeur de chimie passionnant. Tu expliques les reactions chimiques, les molecules et les elements de maniere claire et securisee. Tu fais le lien entre la chimie et la vie quotidienne. Tu insistes sur la securite en chimie. Tu adaptes tes explications au niveau de l\'eleve.',
    'openai/gpt-oss-20b',
    0.7,
    8192,
    1.0,
    'medium',
    (SELECT id_matieres FROM Matieres WHERE nom_matieres = 'Chimie'),
    TRUE
);

COMMIT;
```

---

### PHASE 2: Mise a Jour Modele Agent

#### Etape 2.1: Modifier `php-crud/model/agent.php`

**Ajouts de methodes**:

```php
<?php
// Dans la classe Agent

/**
 * Recuperer l'agent associe a une matiere
 * @param int $id_matieres ID de la matiere
 * @return array|false Agent ou false si non trouve
 */
public function getAgentByMatiere($id_matieres) {
    $query = "SELECT * FROM " . $this->table . "
              WHERE id_matieres = :id_matieres
              AND est_actif = TRUE
              LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Recuperer les parametres LLM d'un agent
 * @param int $id_agents ID de l'agent
 * @return array Parametres LLM
 */
public function getLLMParameters($id_agents) {
    $query = "SELECT model, temperature, max_tokens, top_p, reasoning_effort, prompt_systeme
              FROM " . $this->table . "
              WHERE id_agents = :id_agents";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Mettre a jour les parametres LLM d'un agent
 */
public function updateLLMParameters($id_agents, $model, $temperature, $max_tokens, $top_p, $reasoning_effort) {
    $query = "UPDATE " . $this->table . "
              SET model = :model,
                  temperature = :temperature,
                  max_tokens = :max_tokens,
                  top_p = :top_p,
                  reasoning_effort = :reasoning_effort
              WHERE id_agents = :id_agents";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':id_agents', $id_agents, PDO::PARAM_INT);
    $stmt->bindParam(':model', $model);
    $stmt->bindParam(':temperature', $temperature);
    $stmt->bindParam(':max_tokens', $max_tokens, PDO::PARAM_INT);
    $stmt->bindParam(':top_p', $top_p);
    $stmt->bindParam(':reasoning_effort', $reasoning_effort);

    return $stmt->execute();
}

/**
 * Modifier la methode create() pour inclure les parametres LLM
 */
public function create($nom_agent, $type_agent, $description, $prompt_systeme, $id_matieres,
                       $model = 'openai/gpt-oss-20b', $temperature = 0.7,
                       $max_tokens = 8192, $top_p = 1.0, $reasoning_effort = 'medium') {

    $query = "INSERT INTO " . $this->table . "
              (nom_agent, type_agent, description, prompt_systeme, id_matieres,
               model, temperature, max_tokens, top_p, reasoning_effort, est_actif)
              VALUES
              (:nom_agent, :type_agent, :description, :prompt_systeme, :id_matieres,
               :model, :temperature, :max_tokens, :top_p, :reasoning_effort, TRUE)";

    $stmt = $this->conn->prepare($query);

    // Binding...
    $stmt->bindParam(':nom_agent', $nom_agent);
    $stmt->bindParam(':type_agent', $type_agent);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':prompt_systeme', $prompt_systeme);
    $stmt->bindParam(':id_matieres', $id_matieres, PDO::PARAM_INT);
    $stmt->bindParam(':model', $model);
    $stmt->bindParam(':temperature', $temperature);
    $stmt->bindParam(':max_tokens', $max_tokens, PDO::PARAM_INT);
    $stmt->bindParam(':top_p', $top_p);
    $stmt->bindParam(':reasoning_effort', $reasoning_effort);

    return $stmt->execute();
}
?>
```

---

### PHASE 3: Mise a Jour Controleur Agent

#### Etape 3.1: Modifier `php-crud/controllers/AgentController.php`

**Changements**:

1. **Retirer le champ `id_etudiant`** de la creation
2. **Rendre `id_matieres` obligatoire**
3. **Ajouter validation des parametres LLM**
4. **Verifier unicite matiere** (un seul agent par matiere)

```php
<?php
// Dans handleCreate() ou handleUpdate()

// Validation matiere obligatoire
if (empty($_POST['id_matieres'])) {
    $errors[] = "La matiere est obligatoire";
}

// Verifier qu'il n'existe pas deja un agent pour cette matiere
if (!empty($_POST['id_matieres'])) {
    $existingAgent = $agentModel->getAgentByMatiere($_POST['id_matieres']);
    if ($existingAgent && $existingAgent['id_agents'] != $agent_id) {
        $errors[] = "Un agent existe deja pour cette matiere";
    }
}

// Validation temperature (0 a 2)
if (isset($_POST['temperature'])) {
    $temp = floatval($_POST['temperature']);
    if ($temp < 0 || $temp > 2) {
        $errors[] = "Temperature doit etre entre 0 et 2";
    }
}

// Validation top_p (0 a 1)
if (isset($_POST['top_p'])) {
    $top_p = floatval($_POST['top_p']);
    if ($top_p < 0 || $top_p > 1) {
        $errors[] = "Top_p doit etre entre 0 et 1";
    }
}

// Validation max_tokens (positif)
if (isset($_POST['max_tokens'])) {
    $max_tokens = intval($_POST['max_tokens']);
    if ($max_tokens < 1) {
        $errors[] = "Max tokens doit etre positif";
    }
}

// Si pas d'erreurs, creer l'agent
if (empty($errors)) {
    $result = $agentModel->create(
        $_POST['nom_agent'],
        $_POST['type_agent'],
        $_POST['description'],
        $_POST['prompt_systeme'],
        $_POST['id_matieres'],
        $_POST['model'] ?? 'openai/gpt-oss-20b',
        $_POST['temperature'] ?? 0.7,
        $_POST['max_tokens'] ?? 8192,
        $_POST['top_p'] ?? 1.0,
        $_POST['reasoning_effort'] ?? 'medium'
    );
}
?>
```

---

### PHASE 4: Mise a Jour Formulaire Agent

#### Etape 4.1: Modifier `php-crud/views/agents/form.php`

**Retirer**:
```html
<!-- SUPPRIMER ce champ -->
<label for="id_etudiant">Etudiant :</label>
<select id="id_etudiant" name="id_etudiant" required>
    <!-- ... -->
</select>
```

**Modifier**:
```html
<!-- Rendre obligatoire -->
<label for="id_matieres">Matiere * :</label>
<select id="id_matieres" name="id_matieres" required>
    <option value="">-- Selectionner une matiere --</option>
    <?php foreach ($matieres as $matiere): ?>
        <option value="<?= $matiere['id_matieres'] ?>"
                <?= (isset($agent) && $agent['id_matieres'] == $matiere['id_matieres']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($matiere['nom_matieres']) ?>
        </option>
    <?php endforeach; ?>
</select>
```

**Ajouter champs LLM**:
```html
<h3>Parametres LLM</h3>

<label for="model">Modele LLM :</label>
<input type="text" id="model" name="model"
       value="<?= htmlspecialchars($agent['model'] ?? 'openai/gpt-oss-20b') ?>">

<label for="temperature">Temperature (0-2) :</label>
<input type="range" id="temperature" name="temperature"
       min="0" max="2" step="0.1"
       value="<?= htmlspecialchars($agent['temperature'] ?? '0.7') ?>">
<span id="temp-value">0.7</span>

<label for="max_tokens">Max Tokens :</label>
<input type="number" id="max_tokens" name="max_tokens"
       min="1" max="32000"
       value="<?= htmlspecialchars($agent['max_tokens'] ?? '8192') ?>">

<label for="top_p">Top P (0-1) :</label>
<input type="range" id="top_p" name="top_p"
       min="0" max="1" step="0.05"
       value="<?= htmlspecialchars($agent['top_p'] ?? '1.0') ?>">
<span id="topp-value">1.0</span>

<label for="reasoning_effort">Reasoning Effort :</label>
<select id="reasoning_effort" name="reasoning_effort">
    <option value="low" <?= (isset($agent) && $agent['reasoning_effort'] == 'low') ? 'selected' : '' ?>>Low</option>
    <option value="medium" <?= (isset($agent) && $agent['reasoning_effort'] == 'medium') ? 'selected' : '' ?>>Medium</option>
    <option value="high" <?= (isset($agent) && $agent['reasoning_effort'] == 'high') ? 'selected' : '' ?>>High</option>
</select>

<script>
// Afficher valeur temperature en temps reel
document.getElementById('temperature').addEventListener('input', function() {
    document.getElementById('temp-value').textContent = this.value;
});

// Afficher valeur top_p en temps reel
document.getElementById('top_p').addEventListener('input', function() {
    document.getElementById('topp-value').textContent = this.value;
});
</script>
```

---

### PHASE 5: Integration Chat avec Agents DB

#### Etape 5.1: Refactoriser `php-crud/model/chatModel.php`

**Changements majeurs**:

```php
<?php
namespace Model;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/agent.php';
require_once __DIR__ . '/SessionConversation.php';
require_once __DIR__ . '/message.php';

use Config\Database;
use Model\Agent;
use Model\SessionConversation;
use Model\Message;

class ChatModel {

    /**
     * Charger l'agent pour une matiere donnee
     */
    private static function getAgentForSubject($id_matieres) {
        $database = new Database();
        $db = $database->getConnection();

        $agentModel = new Agent($db);
        return $agentModel->getAgentByMatiere($id_matieres);
    }

    /**
     * Construire le prompt systeme adapte au niveau
     */
    private static function buildSystemPrompt($agent, $niveau_etudiant) {
        $base_prompt = $agent['prompt_systeme'];

        // Adaptation au niveau
        $niveau_adaptation = "\n\nIMPORTANT: L'eleve est actuellement en " . $niveau_etudiant .
                            ". Adapte ton vocabulaire, tes exemples et la complexite de tes explications " .
                            "a ce niveau scolaire. Sois pedagogique et encourageant.";

        return $base_prompt . $niveau_adaptation;
    }

    /**
     * Demarrer ou recuperer une session de conversation
     */
    private static function getOrCreateSession($id_agents, $id_etudiant) {
        // Verifier s'il existe une session active (non terminee)
        if (isset($_SESSION['current_session_id'])) {
            return $_SESSION['current_session_id'];
        }

        // Creer nouvelle session en base
        $database = new Database();
        $db = $database->getConnection();
        $sessionModel = new SessionConversation($db);

        $session_id = $sessionModel->create($id_agents, $id_etudiant);
        $_SESSION['current_session_id'] = $session_id;

        return $session_id;
    }

    /**
     * Envoyer un message (VERSION INTEGREE)
     */
    public static function sendMessage($userMessage) {
        try {
            $user = $_SESSION['user'] ?? null;
            if (!$user) {
                return ['error' => 'Utilisateur non connecte'];
            }

            $id_etudiant = $user['id_users'];
            $id_matieres = $_SESSION['agent_ia_id_matieres'] ?? null;
            $niveau_etudiant = $_SESSION['agent_ia_niveau'] ?? 'Non defini';

            if (!$id_matieres) {
                return ['error' => 'Aucune matiere selectionnee'];
            }

            // 1. CHARGER L'AGENT DE LA MATIERE
            $agent = self::getAgentForSubject($id_matieres);
            if (!$agent) {
                return ['error' => 'Aucun agent disponible pour cette matiere'];
            }

            // 2. CONSTRUIRE LE PROMPT SYSTEME
            $systemPrompt = self::buildSystemPrompt($agent, $niveau_etudiant);

            // 3. RECUPERER OU CREER SESSION
            $session_id = self::getOrCreateSession($agent['id_agents'], $id_etudiant);

            // 4. PREPARER L'HISTORIQUE
            if (!isset($_SESSION['chat_messages'])) {
                $_SESSION['chat_messages'] = [];
            }

            // Ajouter message utilisateur
            $_SESSION['chat_messages'][] = [
                'role' => 'user',
                'content' => $userMessage
            ];

            // 5. CONSTRUIRE MESSAGES POUR API
            $groqMessages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            foreach ($_SESSION['chat_messages'] as $msg) {
                $groqMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }

            // Limiter historique
            if (count($groqMessages) > 21) {  // 1 system + 20 messages
                $groqMessages = array_merge(
                    [$groqMessages[0]],  // Garder system prompt
                    array_slice($groqMessages, -20)  // Garder 20 derniers
                );
            }

            // 6. PREPARER REQUETE API AVEC PARAMETRES DE L'AGENT
            $apiKey = getenv('GROQ_API_KEY');

            $data = [
                'messages' => $groqMessages,
                'model' => $agent['model'],                     // DEPUIS DB
                'temperature' => floatval($agent['temperature']),   // DEPUIS DB
                'max_completion_tokens' => intval($agent['max_tokens']),  // DEPUIS DB
                'top_p' => floatval($agent['top_p']),          // DEPUIS DB
                'reasoning_effort' => $agent['reasoning_effort'],  // DEPUIS DB
                'stream' => false,
                'stop' => null
            ];

            // 7. APPEL API (code CURL existant)
            $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // TODO: Activer en production

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return ['error' => 'Erreur API: ' . $httpCode];
            }

            $result = json_decode($response, true);
            $assistantMessage = $result['choices'][0]['message']['content'] ?? 'Erreur';

            // 8. SAUVEGARDER EN BASE DE DONNEES
            $database = new Database();
            $db = $database->getConnection();
            $messageModel = new Message($db);

            // Sauvegarder message utilisateur
            $messageModel->create($session_id, 'user', $userMessage);

            // Sauvegarder reponse assistant
            $messageModel->create($session_id, 'assistant', $assistantMessage);

            // 9. AJOUTER A LA SESSION PHP
            $_SESSION['chat_messages'][] = [
                'role' => 'assistant',
                'content' => $assistantMessage
            ];

            return [
                'success' => true,
                'message' => $assistantMessage,
                'agent_info' => [
                    'nom' => $agent['nom_agent'],
                    'matiere' => $_SESSION['agent_ia_matiere'],
                    'niveau' => $niveau_etudiant
                ]
            ];

        } catch (\Exception $e) {
            error_log('Erreur ChatModel: ' . $e->getMessage());
            return ['error' => 'Une erreur est survenue'];
        }
    }

    /**
     * Terminer la session active
     */
    public static function endSession() {
        if (isset($_SESSION['current_session_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            $sessionModel = new SessionConversation($db);

            $sessionModel->endSession($_SESSION['current_session_id']);
            unset($_SESSION['current_session_id']);
        }

        unset($_SESSION['chat_messages']);
        unset($_SESSION['agent_ia_id_matieres']);
        unset($_SESSION['agent_ia_matiere']);
        unset($_SESSION['agent_ia_niveau']);
    }
}
?>
```

#### Etape 5.2: Corriger `php-crud/views/ai_assistant/chat_card.php`

**Correction bug niveau**:
```php
<?php
// AVANT (LIGNE 16 - BUGGE)
$nom_niveau = $niveau['nom_matieres'] ?? 'Non defini';

// APRES (CORRIGE)
$nom_niveau = $niveau['libelle_niveau'] ?? 'Non defini';

// Stocker aussi en session pour chatModel
$_SESSION['agent_ia_niveau'] = $nom_niveau;
?>
```

---

### PHASE 6: Ameliorer Selection Matiere

#### Etape 6.1: Modifier `php-crud/views/ai_assistant/agent_matiere_form.php`

```php
<?php
require_once __DIR__ . '/../../controllers/MatiereController.php';
require_once __DIR__ . '/../../model/agent.php';

use Controllers\MatiereController;
use Model\Agent;
use Config\Database;

$matiereController = new MatiereController();
$matieres = $matiereController->getMatiere();

// Charger agents pour verifier disponibilite
$database = new Database();
$db = $database->getConnection();
$agentModel = new Agent($db);
?>

<div class="etudiant-form">
    <h2>Choisir une Matiere</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">
        Selectionnez la matiere pour laquelle vous souhaitez discuter avec l'assistant IA
    </p>

    <form action="" method="POST">
        <label for="matiere">Matiere * :</label>
        <select id="matiere" name="matiere" required>
            <option value="">-- Selectionnez une matiere --</option>
            <?php foreach ($matieres as $matiere): ?>
                <?php
                // Verifier si agent existe
                $agent = $agentModel->getAgentByMatiere($matiere['id_matieres']);
                $hasAgent = $agent !== false;
                $agentName = $hasAgent ? $agent['nom_agent'] : 'Aucun agent';
                ?>
                <option value="<?= htmlspecialchars($matiere['id_matieres']) ?>"
                        <?= !$hasAgent ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($matiere['nom_matieres']) ?>
                    <?= $hasAgent ? ' (' . htmlspecialchars($agentName) . ')' : ' (Indisponible)' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Demarrer la conversation</button>
    </form>
</div>

<?php
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['matiere'])) {
    $id_matieres = intval($_POST['matiere']);

    // Charger infos matiere
    $matiere = null;
    foreach ($matieres as $m) {
        if ($m['id_matieres'] == $id_matieres) {
            $matiere = $m;
            break;
        }
    }

    if ($matiere) {
        $_SESSION['agent_ia_id_matieres'] = $id_matieres;
        $_SESSION['agent_ia_matiere'] = $matiere['nom_matieres'];

        // Rediriger vers chat
        header('Location: index.php?action=agent-ia');
        exit;
    }
}
?>
```

---

### PHASE 7: Gestion Sessions/Messages

#### Etape 7.1: Ajouter methodes dans `php-crud/model/SessionConversation.php`

```php
<?php
/**
 * Terminer une session
 */
public function endSession($id_session) {
    $query = "UPDATE " . $this->table . "
              SET date_heure_fin = NOW(),
                  duree_session = TIMEDIFF(NOW(), date_heure_debut)
              WHERE id_session = :id_session";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Recuperer sessions d'un etudiant
 */
public function getSessionsByStudent($id_etudiant, $limit = 10) {
    $query = "SELECT s.*, a.nom_agent, m.nom_matieres
              FROM " . $this->table . " s
              LEFT JOIN Agent a ON s.id_agents = a.id_agents
              LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres
              WHERE s.id_etudiant = :id_etudiant
              ORDER BY s.date_heure_debut DESC
              LIMIT :limit";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Recuperer session active d'un etudiant
 */
public function getActiveSession($id_etudiant) {
    $query = "SELECT * FROM " . $this->table . "
              WHERE id_etudiant = :id_etudiant
              AND date_heure_fin IS NULL
              ORDER BY date_heure_debut DESC
              LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
```

#### Etape 7.2: Ajouter methodes dans `php-crud/model/message.php`

```php
<?php
/**
 * Creer un message
 */
public function create($id_session, $role, $contenu) {
    $query = "INSERT INTO " . $this->table . "
              (id_session, role, contenu, date_envoi)
              VALUES
              (:id_session, :role, :contenu, NOW())";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':contenu', $contenu);

    if ($stmt->execute()) {
        return $this->conn->lastInsertId();
    }
    return false;
}

/**
 * Recuperer tous les messages d'une session
 */
public function getMessagesBySession($id_session) {
    $query = "SELECT * FROM " . $this->table . "
              WHERE id_session = :id_session
              ORDER BY date_envoi ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id_session', $id_session, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
```

---

### PHASE 8: Interface Historique Conversations

#### Etape 8.1: Creer `php-crud/views/ai_assistant/conversation_history.php`

```php
<?php
require_once __DIR__ . '/../../model/SessionConversation.php';
require_once __DIR__ . '/../../config/Database.php';

use Model\SessionConversation;
use Config\Database;

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.php?action=connect');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$sessionModel = new SessionConversation($db);

$sessions = $sessionModel->getSessionsByStudent($user['id_users'], 20);
?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Historique de vos Conversations</h1>
        <a href="index.php?action=agent-ia" class="btn btn-primary">+ Nouvelle conversation</a>
    </div>

    <?php if (empty($sessions)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>Vous n'avez pas encore de conversations.</p>
            <a href="index.php?action=agent-ia" class="btn btn-primary">Demarrer votre premiere conversation</a>
        </div>
    <?php else: ?>
        <table class="crud-table">
            <thead>
                <tr>
                    <th>Matiere</th>
                    <th>Agent</th>
                    <th>Date debut</th>
                    <th>Duree</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?= htmlspecialchars($session['nom_matieres'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($session['nom_agent'] ?? 'N/A') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($session['date_heure_debut'])) ?></td>
                        <td>
                            <?php
                            if ($session['duree_session']) {
                                echo htmlspecialchars($session['duree_session']);
                            } else {
                                echo '<span style="color: green;">En cours</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($session['date_heure_fin']): ?>
                                <span style="color: #666;">Terminee</span>
                            <?php else: ?>
                                <span style="color: green; font-weight: bold;">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?action=view_conversation&id=<?= $session['id_session'] ?>"
                               class="btn btn-primary btn-sm">Voir</a>
                            <?php if (!$session['date_heure_fin']): ?>
                                <a href="index.php?action=continue_conversation&id=<?= $session['id_session'] ?>"
                                   class="btn btn-primary btn-sm">Continuer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
```

#### Etape 8.2: Creer `php-crud/views/ai_assistant/view_conversation.php`

```php
<?php
require_once __DIR__ . '/../../model/SessionConversation.php';
require_once __DIR__ . '/../../model/message.php';
require_once __DIR__ . '/../../config/Database.php';

use Model\SessionConversation;
use Model\Message;
use Config\Database;

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.php?action=connect');
    exit;
}

$session_id = $_GET['id'] ?? null;
if (!$session_id) {
    header('Location: index.php?action=conversation_history');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$sessionModel = new SessionConversation($db);
$messageModel = new Message($db);

$session = $sessionModel->readSingle($session_id);
$messages = $messageModel->getMessagesBySession($session_id);

// Verifier que la session appartient a l'utilisateur
if ($session['id_etudiant'] != $user['id_users']) {
    header('Location: index.php?action=acces_refuse');
    exit;
}
?>

<div class="crud-container">
    <div class="crud-header">
        <h1 class="page-title">Conversation du <?= date('d/m/Y H:i', strtotime($session['date_heure_debut'])) ?></h1>
        <a href="index.php?action=conversation_history" class="btn btn-secondary">Retour</a>
    </div>

    <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <p><strong>Duree:</strong> <?= $session['duree_session'] ?? 'En cours' ?></p>
        <p><strong>Statut:</strong>
            <?= $session['date_heure_fin'] ? 'Terminee' : '<span style="color: green;">Active</span>' ?>
        </p>
    </div>

    <div style="background: white; border-radius: 12px; padding: 20px; max-height: 600px; overflow-y: auto;">
        <?php foreach ($messages as $msg): ?>
            <div style="margin-bottom: 20px; padding: 15px; border-radius: 8px;
                        background: <?= $msg['role'] === 'user' ? '#e7f3ff' : '#f8f9fa' ?>;">
                <div style="font-weight: bold; margin-bottom: 8px; color: <?= $msg['role'] === 'user' ? '#0078d7' : '#28a745' ?>;">
                    <?= $msg['role'] === 'user' ? 'Vous' : 'Assistant IA' ?>
                    <span style="font-weight: normal; font-size: 0.9em; color: #888; margin-left: 10px;">
                        <?= date('H:i', strtotime($msg['date_envoi'])) ?>
                    </span>
                </div>
                <div style="white-space: pre-wrap;"><?= htmlspecialchars($msg['contenu']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

---

## 4. Ordre d'Implementation

### Sprint 1: Base de Donnees (Semaine 1)
**Objectif**: Preparer la nouvelle structure

- [ ] Etape 1.1: Executer script de migration DB
- [ ] Etape 1.2: Executer script de seed agents
- [ ] Verifier integrite des donnees
- [ ] Tester requetes de base

**Livrables**:
- Table Agent modifiee avec nouveaux champs
- 7 agents crees (un par matiere)

---

### Sprint 2: Backend Models (Semaine 1-2)
**Objectif**: Adapter les modeles aux nouvelles contraintes

- [ ] Etape 2.1: Modifier model Agent
  - [ ] Ajouter `getAgentByMatiere()`
  - [ ] Ajouter `getLLMParameters()`
  - [ ] Modifier `create()` pour LLM params
  - [ ] Modifier `update()` pour LLM params
- [ ] Ajouter methodes SessionConversation
- [ ] Ajouter methodes Message
- [ ] Tester chaque methode individuellement

**Livrables**:
- Models Agent, SessionConversation, Message mis a jour
- Tests unitaires basiques

---

### Sprint 3: Backend Controllers (Semaine 2)
**Objectif**: Adapter la logique metier

- [ ] Etape 3.1: Modifier AgentController
  - [ ] Retirer logique id_etudiant
  - [ ] Ajouter validation LLM params
  - [ ] Ajouter validation unicite matiere
- [ ] Tester creation/modification agents via CRUD

**Livrables**:
- AgentController mis a jour
- Creation d'agents fonctionnelle

---

### Sprint 4: Integration Chat (Semaine 2-3)
**Objectif**: Connecter le chat aux agents DB

- [ ] Etape 5.1: Refactoriser ChatModel
  - [ ] Ajouter `getAgentForSubject()`
  - [ ] Modifier `buildSystemPrompt()`
  - [ ] Implementer persistance DB
  - [ ] Charger parametres LLM depuis agent
- [ ] Etape 5.2: Corriger bug niveau dans chat_card.php
- [ ] Tester envoi message avec agent DB
- [ ] Verifier sauvegarde messages en DB

**Livrables**:
- ChatModel integre avec DB
- Messages sauvegardes correctement
- Parametres LLM charges depuis agent

---

### Sprint 5: Interface Admin (Semaine 3)
**Objectif**: Permettre gestion agents par admin

- [ ] Etape 4.1: Modifier formulaire agent
  - [ ] Retirer champ etudiant
  - [ ] Ajouter champs LLM
  - [ ] Ajouter sliders/selects
  - [ ] Ajouter JavaScript pour feedback temps reel
- [ ] Tester creation agent complet
- [ ] Tester modification parametres LLM
- [ ] Ameliorer liste agents (afficher matiere, parametres)

**Livrables**:
- Formulaire agent complet et fonctionnel
- Admin peut creer/modifier agents avec LLM params

---

### Sprint 6: Interface Etudiant (Semaine 3-4)
**Objectif**: Ameliorer experience etudiant

- [ ] Etape 6.1: Ameliorer selection matiere
  - [ ] Afficher disponibilite agents
  - [ ] Afficher nom agent
  - [ ] Desactiver matieres sans agent
- [ ] Tester selection matiere → chat
- [ ] Verifier affichage niveau correct
- [ ] Tester conversation complete (selection a reponse)

**Livrables**:
- Selection matiere intuitive
- Chat fonctionnel avec agent correct
- Niveau affiche correctement

---

### Sprint 7: Historique (Semaine 4)
**Objectif**: Permettre consultation historique

- [ ] Etape 8.1: Creer page historique conversations
- [ ] Etape 8.2: Creer page visualisation conversation
- [ ] Ajouter route dans index.php
- [ ] Ajouter lien dans navigation
- [ ] Tester affichage historique
- [ ] Tester visualisation conversation passee

**Livrables**:
- Page historique fonctionnelle
- Visualisation conversations passees
- Navigation fluide

---

### Sprint 8: Tests & Corrections (Semaine 4-5)
**Objectif**: Stabiliser et valider

- [ ] Tester tous les parcours utilisateur
- [ ] Tester tous les cas limites
- [ ] Corriger bugs identifies
- [ ] Optimiser performances
- [ ] Valider securite

**Livrables**:
- Application stable
- Tous les bugs corriges
- Documentation a jour

---

## 5. Tests et Validation

### 5.1 Scenarios de Test

#### Test 1: Creation Agent par Admin
**Pre-requis**: Compte admin

1. Se connecter en tant qu'admin
2. Aller dans Agents > Creer
3. Remplir formulaire (nom, type, matiere, prompt, params LLM)
4. Soumettre
5. **Verification**: Agent cree en DB avec tous les parametres
6. **Verification**: Agent visible dans liste

#### Test 2: Unicite Agent par Matiere
**Pre-requis**: Agent existe pour Francais

1. Tenter creer 2e agent pour Francais
2. **Verification**: Erreur affichee "Un agent existe deja pour cette matiere"
3. Agent non cree

#### Test 3: Selection Matiere par Etudiant
**Pre-requis**: Compte etudiant, agents existent

1. Se connecter en tant qu'etudiant
2. Aller dans Agent IA
3. Selectionner matiere dans dropdown
4. **Verification**: Matieres sans agent sont desactivees
5. **Verification**: Nom agent affiche a cote de la matiere
6. Soumettre

#### Test 4: Conversation Complete
**Pre-requis**: Matiere selectionnee

1. Interface chat s'ouvre
2. **Verification**: Matiere affichee
3. **Verification**: Niveau etudiant affiche correctement
4. Envoyer message "Explique-moi les fractions"
5. **Verification**: Reponse recue adaptee au niveau
6. **Verification**: Message utilisateur sauvegarde en DB
7. **Verification**: Reponse assistant sauvegardee en DB
8. **Verification**: Session creee en DB
9. Continuer conversation (3-4 messages)
10. **Verification**: Contexte maintenu

#### Test 5: Parametres LLM Differents
**Pre-requis**: 2 agents avec temperatures differentes

1. Agent Maths: temperature 0.5
2. Agent Histoire: temperature 0.8
3. Conversation avec Maths: reponses precises, factuelles
4. Conversation avec Histoire: reponses plus elaborees, narratives
5. **Verification**: Difference de style observable

#### Test 6: Adaptation au Niveau
**Pre-requis**: 2 comptes etudiants, niveaux differents

1. Etudiant A: Niveau 6eme
2. Etudiant B: Niveau Terminale
3. Meme question: "Qu'est-ce que la photosynthese?"
4. **Verification**: Reponse A simple et accessible
5. **Verification**: Reponse B plus technique et detaillee

#### Test 7: Historique Conversations
**Pre-requis**: Etudiant avec conversations passees

1. Aller dans Historique
2. **Verification**: Liste des conversations affichee
3. **Verification**: Matiere, date, duree affichees
4. Cliquer sur "Voir"
5. **Verification**: Tous les messages affiches
6. **Verification**: Ordre chronologique respecte

#### Test 8: Session Active
**Pre-requis**: Conversation en cours

1. Demarrer conversation
2. Envoyer 2 messages
3. Fermer navigateur
4. Se reconnecter
5. Retourner sur Agent IA
6. **Verification**: Session active proposee
7. **Verification**: Historique charge
8. Continuer conversation
9. **Verification**: Contexte maintenu

#### Test 9: Modification Parametres Agent
**Pre-requis**: Agent existe

1. Admin modifie temperature de 0.7 a 1.5
2. Etudiant demarre nouvelle conversation
3. **Verification**: Nouveaux parametres utilises
4. **Verification**: Style de reponse change

#### Test 10: Securite et Autorisations
1. Etudiant tente acceder formulaire creation agent
2. **Verification**: Acces refuse ou redirection
3. Etudiant tente voir conversation d'un autre
4. **Verification**: Acces refuse

---

### 5.2 Checklist de Validation Finale

#### Fonctionnalites
- [ ] Admin peut creer agents avec tous parametres
- [ ] Admin peut modifier agents
- [ ] Admin peut supprimer agents
- [ ] Un seul agent par matiere (contrainte respectee)
- [ ] Etudiant peut selectionner matiere
- [ ] Chat utilise agent correct
- [ ] Parametres LLM charges depuis DB
- [ ] Prompt systeme charge depuis DB
- [ ] Niveau etudiant integre au prompt
- [ ] Messages sauvegardes en DB
- [ ] Sessions creees et gerees
- [ ] Historique accessible
- [ ] Visualisation conversations passees

#### Securite
- [ ] Champs valides (LLM params entre bornes)
- [ ] Autorisations respectees (admin/etudiant)
- [ ] Pas d'injection SQL (prepared statements)
- [ ] XSS prevenu (htmlspecialchars)
- [ ] Sessions securisees
- [ ] Acces conversation limite au proprietaire

#### Performance
- [ ] Cache session pour historique recent
- [ ] Pagination historique si nombreuses conversations
- [ ] Requetes optimisees (index sur FK)
- [ ] Temps reponse API acceptable (<5s)

#### UX
- [ ] Interface intuitive
- [ ] Messages d'erreur clairs
- [ ] Feedback utilisateur (loading, success, error)
- [ ] Navigation logique
- [ ] Design coherent

---

## 6. Fichiers a Creer/Modifier

### Fichiers a CREER
1. `php-crud/migrations/refactor_agent_by_subject.sql`
2. `php-crud/migrations/seed_default_agents.sql`
3. `php-crud/views/ai_assistant/conversation_history.php`
4. `php-crud/views/ai_assistant/view_conversation.php`

### Fichiers a MODIFIER
1. `php-crud/model/agent.php`
2. `php-crud/model/SessionConversation.php`
3. `php-crud/model/message.php`
4. `php-crud/model/chatModel.php`
5. `php-crud/controllers/AgentController.php`
6. `php-crud/views/agents/form.php`
7. `php-crud/views/agents/list.php` (optionnel, afficher params)
8. `php-crud/views/ai_assistant/agent_matiere_form.php`
9. `php-crud/views/ai_assistant/chat_card.php`
10. `index.php` (ajouter routes conversation_history, view_conversation, continue_conversation)

---

## 7. Estimation Temps

### Par Niveau d'Experience

#### Developpeur Debutant (apprend en meme temps)
- **Sprint 1-2 (DB + Models)**: 1 semaine
- **Sprint 3-4 (Controllers + Chat)**: 1.5 semaines
- **Sprint 5-6 (Interfaces)**: 1 semaine
- **Sprint 7-8 (Historique + Tests)**: 0.5 semaine
- **TOTAL**: **4 semaines** (temps plein)

#### Developpeur Intermediaire
- **Sprint 1-4**: 1 semaine
- **Sprint 5-8**: 1 semaine
- **TOTAL**: **2 semaines**

#### Developpeur Experimente
- **Tout**: **3-5 jours**

---

## 8. Points d'Attention

### Critiques
1. **Backup avant migration**: Sauvegarder DB complete avant refactoring
2. **Migration progressive**: Tester chaque etape avant de continuer
3. **Gestion erreurs API**: Prevoir fallback si Groq API en panne
4. **Limitation tokens**: Surveiller couts API avec max_tokens

### Optimisations Futures
1. **Cache Redis**: Pour historique conversations recentes
2. **Pagination**: Ajouter si >50 messages dans conversation
3. **Export PDF**: Permettre telecharger conversations
4. **Statistiques**: Dashboard avec stats utilisation par matiere
5. **Multi-agents**: Permettre plusieurs agents par matiere (variantes)
6. **Streaming**: API responses en streaming pour meilleure UX

---

## 9. Glossaire

- **Agent**: Entite IA configuree pour une matiere specifique
- **Session**: Conversation entre un etudiant et un agent
- **LLM**: Large Language Model (modele de langage)
- **Temperature**: Parametre de creativite (0=precis, 2=creatif)
- **Top_p**: Parametre de diversite des reponses
- **Reasoning effort**: Niveau de reflexion du modele
- **Prompt systeme**: Instructions de base pour definir comportement IA

---

**Fin du Document**

_Document cree le 2025-01-03 pour le projet BeautifuLLL AI_
