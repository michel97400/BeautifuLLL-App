# 📚 Guide de Refactoring MVC - CRUD Étudiant

## 🎯 Objectif

Déplacer toute la logique de validation et de traitement depuis `etudiant_form.php` (la Vue) vers `EtudiantController.php` (le Controller), pour respecter le pattern MVC.

**Principe MVC :**
- **Model** (etudiant.php) = Accès aux données (SQL uniquement) ✅ Déjà bon
- **Controller** (EtudiantController.php) = Logique métier, validation, orchestration
- **View** (etudiant_form.php) = Affichage HTML uniquement

---

## 📊 Avant / Après

### Avant (actuel)
```
etudiant_form.php = 296 lignes
├─ Validation (80 lignes)         ❌ Devrait être dans le Controller
├─ Upload fichiers (45 lignes)    ❌ Devrait être dans le Controller
├─ Logique métier (30 lignes)     ❌ Devrait être dans le Controller
└─ HTML (141 lignes)               ✅ OK dans la Vue
```

### Après (refactoré)
```
EtudiantController.php
└─ handleFormSubmit() (+200 lignes) ✅ Toute la logique

etudiant_form.php = ~85 lignes
├─ Appel controller (5 lignes)      ✅
└─ HTML (80 lignes)                  ✅
```

---

## 📋 ÉTAPE 1 : Modifier EtudiantController.php

### Localisation
**Fichier :** `php-crud/controllers/EtudiantController.php`

### Action
Ajouter une nouvelle méthode publique `handleFormSubmit()` **à la fin de la classe** (avant le `}` final).

### Signature de la méthode
```
Nom : handleFormSubmit
Paramètres :
  - $post (array) : Les données POST
  - $files (array) : Les fichiers uploadés
  - $isEditMode (bool) : Mode création ou modification
  - $etudiant (array|null) : Données de l'étudiant existant (si modification)

Retourne : array avec les clés suivantes
  - 'success' (bool) : true si succès, false sinon
  - 'errors' (array) : Liste des erreurs de validation
  - 'message' (string) : Message de succès ou vide
  - 'etudiant' (array|null) : Données de l'étudiant mis à jour
  - 'input' (array) : Données saisies (pour repeupler le formulaire)
```

### Ce qu'il faut mettre dans cette méthode

#### 1. Récupération des données (depuis etudiant_form.php lignes 30-38)
Déplacer tout le code qui récupère les données depuis `$_POST` :
- `$nom = trim($post['nom'] ?? '')`
- `$prenom = trim($post['prenom'] ?? '')`
- `$email = trim($post['email'] ?? '')`
- `$password = $post['password'] ?? ''`
- `$date_inscription`
- `$consentement_rgpd`
- `$id_role`
- `$id_niveau`

#### 2. Validation du nom (depuis etudiant_form.php lignes 42-49)
Copier tout le bloc de validation du nom :
- Vérifier si vide
- Vérifier longueur max 50
- Vérifier regex caractères autorisés

#### 3. Validation du prénom (depuis etudiant_form.php lignes 51-58)
Copier tout le bloc de validation du prénom (identique au nom)

#### 4. Validation de l'email (depuis etudiant_form.php lignes 60-78)
Copier tout le bloc de validation de l'email :
- Vérifier si vide
- Vérifier format avec `filter_var()`
- Vérifier unicité en appelant `$this->getEtudiant()` (et non `$controller->getEtudiant()`)

⚠️ **Important :** Remplacer `$controller = new EtudiantController()` par `$this->` car on est déjà dans le controller

#### 5. Validation du mot de passe (depuis etudiant_form.php lignes 80-93)
Copier tout le bloc de validation du mot de passe :
- En mode création : obligatoire, min 8 caractères
- En mode modification : optionnel, mais min 8 si fourni

#### 6. Validation RGPD (depuis etudiant_form.php lignes 95-98)
Copier la validation du consentement RGPD

#### 7. Validation des relations (depuis etudiant_form.php lignes 100-126)
Copier tout le bloc de validation des relations :
- Instancier RoleController et NiveauController
- Récupérer les rôles et niveaux
- Vérifier que `id_role` existe
- Vérifier que `id_niveau` existe

⚠️ **Important :** Ajouter en haut du fichier :
```
require_once __DIR__ . '/RoleController.php';
require_once __DIR__ . '/NiveauController.php';
```

#### 8. Gestion de l'upload avatar (depuis etudiant_form.php lignes 128-173)
Copier tout le bloc de gestion d'upload :
- Initialiser `$avatar`
- Vérifier si fichier uploadé
- Validation type MIME avec `finfo_open()`
- Validation taille (max 2MB)
- Si pas d'erreur : créer nom unique et déplacer le fichier
- Gérer les erreurs d'upload

⚠️ **Important :** Adapter le chemin d'upload car on n'est plus dans `views/` :
- Remplacer `__DIR__ . '/../../uploads/'` par `__DIR__ . '/../views/../../uploads/'`

#### 9. Enregistrement en base (depuis etudiant_form.php lignes 175-207)
Copier toute la logique d'enregistrement :
- Si pas d'erreurs (`if (empty($errors))`)
- Si mode modification : appeler `$this->updateEtudiant()`
- Si mode création : appeler `$this->createEtudiant()`
- Gérer les résultats et messages

#### 10. Retourner le résultat
À la fin de la méthode, retourner un tableau avec :
```
return [
    'success' => (pas d'erreurs ET message non vide),
    'errors' => tableau des erreurs,
    'message' => message de succès,
    'etudiant' => données de l'étudiant (si modification),
    'input' => compact('nom', 'prenom', 'email', 'id_role', 'id_niveau')
];
```

Le `compact()` permet de repeupler le formulaire en cas d'erreur.

---

## 📋 ÉTAPE 2 : Simplifier etudiant_form.php

### Localisation
**Fichier :** `php-crud/views/etudiant_form.php`

### Ce qu'il faut GARDER

#### En haut du fichier (lignes 1-26)
- Les `require_once` (lignes 2-5)
- Les `use` (lignes 7-9)
- L'initialisation des variables (lignes 11-14)
- La détection du mode édition avec `$_GET['id']` (lignes 16-26)

⚠️ **Ajouter :** Une nouvelle variable `$inputData = []` pour stocker les valeurs saisies

#### Le bloc de traitement POST (À REMPLACER)
Remplacer tout le bloc actuel (lignes 28-208) par un code simple qui :
1. Vérifie si `$_SERVER['REQUEST_METHOD'] === 'POST'`
2. Instancie le controller
3. Appelle `handleFormSubmit()` avec les bons paramètres
4. Récupère les résultats dans des variables :
   - `$errors = $result['errors']`
   - `$message = $result['message']`
   - `$etudiant = $result['etudiant']` (si modification)
   - `$inputData = $result['input']`

#### Récupération rôles/niveaux (lignes 210-214)
Garder tel quel :
- Instanciation RoleController et NiveauController
- Récupération des listes de rôles et niveaux

#### Tout le HTML (lignes 217-295)
Garder tout le HTML tel quel, juste adapter :
- Dans les `value=""` des inputs : utiliser `$inputData['nom']` en priorité, puis `$etudiant['nom']`
- Exemple : `value="<?= htmlspecialchars($inputData['nom'] ?? $etudiant['nom'] ?? '') ?>"`

### Ce qu'il faut SUPPRIMER COMPLÈTEMENT

#### Bloc 1 : Récupération des données (lignes 30-38)
Supprimer tout le code qui fait :
```
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
...
```
→ Maintenant dans le controller

#### Bloc 2 : Validation du nom (lignes 42-49)
Supprimer tout le code de validation du nom
→ Maintenant dans le controller

#### Bloc 3 : Validation du prénom (lignes 51-58)
Supprimer tout le code de validation du prénom
→ Maintenant dans le controller

#### Bloc 4 : Validation de l'email (lignes 60-78)
Supprimer tout le code de validation de l'email et vérification unicité
→ Maintenant dans le controller

#### Bloc 5 : Validation du mot de passe (lignes 80-93)
Supprimer tout le code de validation du mot de passe
→ Maintenant dans le controller

#### Bloc 6 : Validation RGPD (lignes 95-98)
Supprimer la validation du consentement RGPD
→ Maintenant dans le controller

#### Bloc 7 : Validation relations (lignes 100-126)
Supprimer toute la validation des id_role et id_niveau
→ Maintenant dans le controller

⚠️ **NE PAS supprimer** les lignes 210-214 qui récupèrent les listes (on en a besoin pour les selects)

#### Bloc 8 : Gestion upload (lignes 128-173)
Supprimer toute la gestion de l'upload de fichier
→ Maintenant dans le controller

#### Bloc 9 : Enregistrement (lignes 175-207)
Supprimer toute la logique d'enregistrement en base
→ Maintenant dans le controller

### Résultat final
Le fichier `etudiant_form.php` devrait faire environ **85 lignes** au lieu de 296.

---

## ✅ ORDRE D'EXÉCUTION

### 1. D'abord : EtudiantController.php
- Ouvrir le fichier
- Aller à la fin de la classe (avant le `}` final)
- Ajouter la méthode `handleFormSubmit()`
- Copier-coller tout le code depuis etudiant_form.php (lignes 30-207)
- Adapter les appels : remplacer `$controller->` par `$this->`
- Adapter le chemin d'upload
- Ajouter les `require_once` pour RoleController et NiveauController en haut du fichier
- Retourner le tableau de résultat à la fin

### 2. Ensuite : etudiant_form.php
- Ouvrir le fichier
- Ajouter `$inputData = []` avec les autres variables (ligne ~14)
- **SUPPRIMER** toutes les lignes 30-207 (validation + upload + enregistrement)
- **AJOUTER** le nouvel appel au controller dans le bloc POST (5 lignes environ)
- **ADAPTER** les `value=""` dans le HTML pour utiliser `$inputData` en priorité

### 3. Tester
- Tester création d'un étudiant
- Tester modification d'un étudiant
- Tester les validations (soumettre avec des erreurs)
- Vérifier l'upload d'avatar
- Vérifier que les valeurs sont conservées en cas d'erreur

### 4. Commit
Créer un commit avec le message :
```
Refactor: Apply MVC pattern to student form

- Move validation logic from view to controller
- Move file upload logic from view to controller
- Move database operations from view to controller
- Simplify etudiant_form.php (296 → 85 lines)
- Add handleFormSubmit() method in EtudiantController

MVC compliance improved:
- Model: Data access only ✅
- Controller: Business logic + validation ✅
- View: HTML display only ✅
```

---

## 🎓 Principes MVC appliqués

### ❌ Avant (Violation MVC)
```
Vue (etudiant_form.php)
├─ Récupère $_POST           ❌ Devrait être dans le Controller
├─ Valide les données        ❌ Devrait être dans le Controller
├─ Upload fichiers           ❌ Devrait être dans le Controller
├─ Appelle le Model          ❌ Devrait passer par le Controller
└─ Affiche HTML              ✅ OK
```

### ✅ Après (MVC respecté)
```
Vue (etudiant_form.php)
├─ Appelle le Controller     ✅
└─ Affiche HTML              ✅

Controller (EtudiantController.php)
├─ Reçoit la requête         ✅
├─ Valide les données        ✅
├─ Upload fichiers           ✅
├─ Appelle le Model          ✅
└─ Retourne le résultat      ✅

Model (etudiant.php)
└─ Accès aux données (SQL)   ✅
```

---

## 💡 Bénéfices de ce refactoring

1. **Séparation des responsabilités**
   - Chaque couche a un rôle clair
   - Plus facile à comprendre

2. **Réutilisabilité**
   - La validation peut être réutilisée ailleurs (API, import CSV)
   - Pas de duplication de code

3. **Testabilité**
   - On peut tester le controller indépendamment de la vue
   - On peut mocker les dépendances

4. **Maintenabilité**
   - Modifications plus faciles
   - Moins de risque de bugs

5. **Évolutivité**
   - Facile d'ajouter une API REST (réutiliser le même controller)
   - Facile de changer de vue (JSON, XML, etc.)

---

## 📌 Points d'attention

### Chemins de fichiers
- Dans le controller, adapter le chemin d'upload car on n'est plus dans `views/`
- Chemin : `__DIR__ . '/../views/../../uploads/'` au lieu de `__DIR__ . '/../../uploads/'`

### Appels aux méthodes
- Remplacer `$controller->getEtudiant()` par `$this->getEtudiant()`
- On est déjà dans le controller, donc utiliser `$this`

### Require des dépendances
- Ajouter les `require_once` pour RoleController et NiveauController en haut de EtudiantController.php

### Variables dans la vue
- Utiliser `$inputData` pour conserver les valeurs en cas d'erreur
- Utiliser `$etudiant` pour les valeurs par défaut en mode édition

---

## 🚀 Après le refactoring

Une fois ce refactoring terminé, vous pourriez facilement :

1. **Créer une API REST**
   ```
   Route : POST /api/etudiants
   Controller : Réutiliser handleFormSubmit()
   Retour : JSON au lieu de HTML
   ```

2. **Ajouter un import CSV**
   ```
   Lire le CSV
   Pour chaque ligne : appeler handleFormSubmit()
   Même validation appliquée !
   ```

3. **Ajouter des tests unitaires**
   ```
   Tester handleFormSubmit() avec différents cas
   Pas besoin de simuler le HTML
   ```

4. **Créer une classe Validator** (optionnel, pour aller plus loin)
   ```
   Extraire toute la validation dans EtudiantValidator
   Le controller devient encore plus simple
   ```

---

## ✅ Checklist finale

- [ ] EtudiantController.php : Méthode `handleFormSubmit()` créée
- [ ] EtudiantController.php : `require_once` ajoutés pour RoleController et NiveauController
- [ ] EtudiantController.php : Chemin d'upload adapté
- [ ] EtudiantController.php : Utilisation de `$this->` au lieu de `$controller->`
- [ ] etudiant_form.php : Variable `$inputData` ajoutée
- [ ] etudiant_form.php : Lignes 30-207 supprimées
- [ ] etudiant_form.php : Nouvel appel au controller ajouté
- [ ] etudiant_form.php : `value=""` adaptés pour utiliser `$inputData`
- [ ] Tests : Création d'étudiant fonctionne
- [ ] Tests : Modification d'étudiant fonctionne
- [ ] Tests : Validations fonctionnent (erreurs affichées)
- [ ] Tests : Upload avatar fonctionne
- [ ] Tests : Valeurs conservées en cas d'erreur
- [ ] Git : Commit créé avec message descriptif

---

**Bon refactoring ! 🎉**
