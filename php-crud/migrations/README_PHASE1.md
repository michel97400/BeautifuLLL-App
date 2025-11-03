# Phase 1: Migration Base de Donnees - Guide d'Execution

## ⚠️ AVERTISSEMENTS IMPORTANTS

### AVANT DE COMMENCER:

1. **BACKUP OBLIGATOIRE**: Cette migration va SUPPRIMER tous les agents existants
2. **TEMPS D'ARRET**: Pendant la migration, le systeme de chat sera indisponible
3. **IRREVERSIBLE**: Une fois executee, cette migration ne peut pas etre annulee facilement
4. **VERIFICATION**: Assurez-vous d'avoir teste sur un environnement de developpement d'abord

---

## 📋 Pre-requis

- [ ] Acces a phpMyAdmin ou ligne de commande MySQL
- [ ] Droits d'administration sur la base de donnees `ia_educative`
- [ ] Backup complet de la base de donnees
- [ ] Temps estime: 5-10 minutes

---

## 🔄 Etape 0: Sauvegarde de la Base de Donnees

### Option A: Via phpMyAdmin (RECOMMANDE pour debutants)

1. Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Selectionner la base `ia_educative` dans la colonne de gauche
3. Cliquer sur l'onglet **"Exporter"** en haut
4. Choisir **"Rapide"** ou **"Personnalise"**
5. Format: **SQL**
6. Cliquer sur **"Executer"**
7. Sauvegarder le fichier: `ia_educative_backup_AVANT_PHASE1_2025-01-03.sql`

### Option B: Via Ligne de Commande

```bash
# Ouvrir le terminal
cd C:\wamp64\www\BeautifuLLL-App

# Creer le backup
mysqldump -u root -p ia_educative > backup_phase1_2025-01-03.sql

# Verifier que le fichier a ete cree
dir backup_phase1_2025-01-03.sql
```

---

## 🚀 Etape 1: Executer le Script de Migration

### Option A: Via phpMyAdmin (RECOMMANDE)

1. Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Selectionner la base `ia_educative`
3. Cliquer sur l'onglet **"SQL"** en haut
4. **Ouvrir le fichier** `php-crud/migrations/refactor_agent_by_subject.sql`
5. **Copier tout le contenu** du fichier
6. **Coller** dans la zone de texte SQL de phpMyAdmin
7. Cliquer sur **"Executer"**

#### Resultats Attendus:
Vous devriez voir plusieurs messages verts:
- `Backup cree: X agents sauvegardes`
- `Tous les agents ont ete supprimes`
- `Colonnes LLM ajoutees avec succes`
- `Colonne id_matieres rendue obligatoire`
- `Contrainte unique ajoutee sur id_matieres`
- `Colonne id_etudiant supprimee avec succes`
- `Colonne prompt_systeme rendue obligatoire`
- `MIGRATION TERMINEE AVEC SUCCES`

#### En cas d'Erreur:
1. **NE PAS PANIQUER**
2. Noter le message d'erreur exact
3. La transaction sera annulee automatiquement (ROLLBACK)
4. La table Agent reste dans son etat original
5. Contactez le support ou verifiez les logs

### Option B: Via Ligne de Commande

```bash
# Ouvrir le terminal
cd C:\wamp64\www\BeautifuLLL-App\php-crud\migrations

# Executer le script
mysql -u root -p ia_educative < refactor_agent_by_subject.sql

# Entrer le mot de passe MySQL quand demande
```

---

## 🌱 Etape 2: Executer le Script de Seed

### Option A: Via phpMyAdmin (RECOMMANDE)

1. Rester dans phpMyAdmin, base `ia_educative`
2. Onglet **"SQL"**
3. **Ouvrir le fichier** `php-crud/migrations/seed_default_agents.sql`
4. **Copier tout le contenu**
5. **Coller** dans la zone SQL
6. Cliquer sur **"Executer"**

#### Resultats Attendus:
Vous devriez voir:
- `Agent Francais cree avec succes`
- `Agent Anglais cree avec succes`
- `Agent Mathematiques cree avec succes`
- `Agent Histoire-Geo cree avec succes`
- `Agent Biologie cree avec succes`
- `Agent Physique cree avec succes`
- `Agent Chimie cree avec succes`
- `SEED TERMINE AVEC SUCCES`
- `7 agents ont ete crees`

Puis un tableau affichant les 7 agents avec leurs parametres.

### Option B: Via Ligne de Commande

```bash
# Executer le seed
mysql -u root -p ia_educative < seed_default_agents.sql
```

---

## ✅ Etape 3: Verification

### Verifier la Structure de la Table

```sql
-- Dans phpMyAdmin, onglet SQL, executer:
DESCRIBE Agent;
```

**Colonnes attendues**:
- id_agents (INT, PRIMARY KEY)
- nom_agent (VARCHAR)
- type_agent (VARCHAR)
- avatar_agent (VARCHAR)
- est_actif (BOOLEAN)
- description (TEXT)
- date_creation (DATETIME)
- prompt_systeme (TEXT, NOT NULL) ✅
- **model (VARCHAR)** ✅ NOUVEAU
- **temperature (DECIMAL)** ✅ NOUVEAU
- **max_tokens (INT)** ✅ NOUVEAU
- **top_p (DECIMAL)** ✅ NOUVEAU
- **reasoning_effort (ENUM)** ✅ NOUVEAU
- id_matieres (INT, NOT NULL) ✅ MODIFIE
- ~~id_etudiant~~ ❌ SUPPRIME

### Verifier les Contraintes

```sql
SELECT
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'ia_educative' AND TABLE_NAME = 'Agent';
```

**Contraintes attendues**:
- PRIMARY KEY sur id_agents
- UNIQUE sur nom_agent
- **UNIQUE sur id_matieres** ✅ NOUVEAU
- FOREIGN KEY sur id_matieres

### Verifier les Agents Crees

```sql
SELECT
    a.nom_agent,
    m.nom_matieres,
    a.temperature,
    a.reasoning_effort
FROM Agent a
LEFT JOIN Matieres m ON a.id_matieres = m.id_matieres;
```

**Resultats attendus**: 7 lignes (un agent par matiere)

---

## 🎯 Etape 4: Test Manuel

### Test 1: Verifier l'Unicite

Essayer de creer un 2e agent pour une matiere existante:

```sql
-- Ceci DOIT echouer avec erreur "Duplicate entry"
INSERT INTO Agent (nom_agent, type_agent, prompt_systeme, id_matieres, model)
VALUES ('Test Agent', 'Tuteur_Prive', 'Test', 1, 'openai/gpt-oss-20b');
```

**Resultat attendu**: Erreur `Duplicate entry '1' for key 'unique_agent_per_matiere'`

### Test 2: Verifier l'Obligation de Matiere

Essayer de creer un agent sans matiere:

```sql
-- Ceci DOIT echouer avec erreur "cannot be null"
INSERT INTO Agent (nom_agent, type_agent, prompt_systeme, model)
VALUES ('Test Agent 2', 'Tuteur_Prive', 'Test', 'openai/gpt-oss-20b');
```

**Resultat attendu**: Erreur `Column 'id_matieres' cannot be null`

---

## 📊 Comparaison Avant/Apres

### AVANT la Migration:
```
Agent
├─ id_etudiant (NOT NULL, FK vers Etudiants) ❌
├─ id_matieres (NULL autorise) ❌
├─ prompt_systeme (NULL autorise) ❌
└─ Pas de parametres LLM ❌
```

### APRES la Migration:
```
Agent
├─ id_etudiant SUPPRIME ✅
├─ id_matieres (NOT NULL, UNIQUE, FK) ✅
├─ prompt_systeme (NOT NULL) ✅
├─ model (VARCHAR) ✅ NOUVEAU
├─ temperature (DECIMAL) ✅ NOUVEAU
├─ max_tokens (INT) ✅ NOUVEAU
├─ top_p (DECIMAL) ✅ NOUVEAU
└─ reasoning_effort (ENUM) ✅ NOUVEAU
```

---

## 🔍 Troubleshooting

### Erreur: "Table 'Agent_backup_20250103' already exists"

**Cause**: Le script a deja ete execute partiellement

**Solution**:
```sql
DROP TABLE IF EXISTS Agent_backup_20250103;
-- Puis re-executer le script de migration
```

### Erreur: "Cannot add foreign key constraint"

**Cause**: Probleme avec les references aux matieres

**Solution**:
1. Verifier que la table Matieres existe
2. Verifier que toutes les matieres ont un id valide
```sql
SELECT * FROM Matieres;
```

### Erreur: "Column 'id_etudiant' cannot be dropped"

**Cause**: Contrainte de cle etrangere non trouvee

**Solution**:
```sql
-- Lister les contraintes
SHOW CREATE TABLE Agent;

-- Supprimer manuellement la contrainte FK
-- Remplacer 'nom_contrainte' par le nom reel
ALTER TABLE Agent DROP FOREIGN KEY nom_contrainte;
ALTER TABLE Agent DROP COLUMN id_etudiant;
```

### Les Agents ne S'affichent Pas dans le Seed

**Cause**: Noms de matieres ne correspondent pas exactement

**Solution**:
```sql
-- Verifier les noms exacts des matieres
SELECT id_matieres, nom_matieres FROM Matieres;

-- Modifier le script seed si necessaire avec les bons noms
```

---

## 🔙 Restauration en Cas de Probleme

Si quelque chose se passe mal et que vous devez revenir en arriere:

### Via phpMyAdmin:

1. Aller dans phpMyAdmin
2. Selectionner `ia_educative`
3. Onglet **"Importer"**
4. Choisir le fichier de backup: `ia_educative_backup_AVANT_PHASE1_2025-01-03.sql`
5. Cliquer sur **"Executer"**

### Via Ligne de Commande:

```bash
# Restaurer le backup
mysql -u root -p ia_educative < backup_phase1_2025-01-03.sql
```

---

## 📈 Prochaines Etapes

Une fois la Phase 1 terminee avec succes:

1. ✅ **Phase 1 completee**: Base de donnees migree
2. ⏭️ **Phase 2**: Modifier `php-crud/model/agent.php`
3. ⏭️ **Phase 3**: Modifier `php-crud/controllers/AgentController.php`
4. ⏭️ **Phase 4**: Modifier formulaire agent (admin)
5. ⏭️ **Phase 5**: Integrer chatModel avec les agents DB

Voir le fichier `docs/REFACTORING_PLAN.md` pour la suite.

---

## 📞 Support

En cas de probleme:
1. Verifier les logs MySQL
2. Consulter `REFACTORING_PLAN.md` section Troubleshooting
3. Restaurer le backup si necessaire
4. Ne pas hesiter a demander de l'aide

---

**Bonne migration! 🚀**
