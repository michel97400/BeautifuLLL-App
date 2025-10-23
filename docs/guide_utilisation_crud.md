# Guide d'utilisation du CRUD Étudiant

## 📚 Structure créée

Voici les fichiers qui ont été créés pour le CRUD Étudiant :

```
php-crud/
├── views/
│   ├── form_etudiant.php           ✅ Formulaire de création
│   ├── liste_etudiants.php         ✅ Liste de tous les étudiants
│   ├── details_etudiant.php        ✅ Détails d'un étudiant
│   └── form_edit_etudiant.php      ✅ Formulaire de modification
```

## 🔧 Modifications nécessaires dans index.php

Pour que le CRUD fonctionne, vous devez **ajouter ces actions** dans votre fichier `index.php` :

### Code à ajouter dans index.php (ligne 22-32)

Remplacez le bloc PHP actuel par celui-ci :

```php
<?php
$action = $_GET['action'] ?? 'accueil';

switch ($action) {
    // Créer un étudiant
    case 'creer_etudiant':
        include 'php-crud/views/form_etudiant.php';
        break;

    // Lister tous les étudiants
    case 'liste_etudiants':
        include 'php-crud/views/liste_etudiants.php';
        break;

    // Voir les détails d'un étudiant
    case 'details_etudiant':
        if (isset($_GET['id'])) {
            include 'php-crud/views/details_etudiant.php';
        } else {
            echo '<p class="error">ID manquant.</p>';
        }
        break;

    // Modifier un étudiant
    case 'modifier_etudiant':
        if (isset($_GET['id'])) {
            include 'php-crud/views/form_edit_etudiant.php';
        } else {
            echo '<p class="error">ID manquant.</p>';
        }
        break;

    // Supprimer un étudiant
    case 'supprimer_etudiant':
        if (isset($_GET['id'])) {
            require_once 'php-crud/controllers/EtudiantController.php';
            use Controllers\EtudiantController;

            $controller = new EtudiantController();
            $result = $controller->deleteEtudiant($_GET['id']);

            if ($result) {
                echo '<p class="success">Étudiant supprimé avec succès.</p>';
                echo '<a href="?action=liste_etudiants">Retour à la liste</a>';
            } else {
                echo '<p class="error">Erreur lors de la suppression.</p>';
            }
        } else {
            echo '<p class="error">ID manquant.</p>';
        }
        break;

    // Connexion
    case 'connect':
        include 'php-crud/views/form_connect.php';
        break;

    // Accueil par défaut
    default:
        echo '<p>Bienvenue sur BeautifuLLL AI. Sélectionnez une action dans le menu.</p>';
        break;
}
?>
```

### Ajout dans la navigation (ligne 13-19)

Ajoutez un lien vers la liste des étudiants dans le menu :

```php
<nav>
    <a href="index.php">Accueil</a>
    <a href="?action=liste_etudiants">📋 Liste des étudiants</a>
    <a href="?action=creer_etudiant">➕ Créer un étudiant</a>
    <a href="?action=connect">Connexion</a>
    <a href="#">Contact</a>
</nav>
```

## 🚀 Comment utiliser le CRUD

### 1. Lister tous les étudiants
**URL** : `index.php?action=liste_etudiants`

Affiche un tableau avec tous les étudiants enregistrés dans la base de données.

**Actions disponibles** :
- 👁️ Voir les détails
- ✏️ Modifier
- 🗑️ Supprimer

---

### 2. Créer un étudiant
**URL** : `index.php?action=creer_etudiant`

Affiche le formulaire de création avec tous les champs requis :
- Nom
- Prénom
- Email
- Avatar (upload de fichier)
- Mot de passe
- Date d'inscription
- Consentement RGPD
- Rôle
- Niveau

---

### 3. Voir les détails d'un étudiant
**URL** : `index.php?action=details_etudiant&id=5`

Affiche toutes les informations de l'étudiant avec :
- Une carte visuelle avec avatar
- Tous les champs en lecture seule
- Boutons d'action : Modifier / Supprimer / Retour

---

### 4. Modifier un étudiant
**URL** : `index.php?action=modifier_etudiant&id=5`

Affiche un formulaire pré-rempli avec les données actuelles de l'étudiant.

**Fonctionnalités** :
- Avatar actuel affiché
- Possibilité de changer l'avatar
- Mot de passe optionnel (laissez vide pour conserver l'actuel)
- Tous les champs modifiables

---

### 5. Supprimer un étudiant
**URL** : `index.php?action=supprimer_etudiant&id=5`

Supprime l'étudiant après confirmation JavaScript.

---

## 🎨 Styles

Chaque vue contient ses propres styles CSS intégrés pour :
- Un design moderne et responsive
- Des boutons colorés et clairs
- Des tableaux lisibles
- Des formulaires bien structurés
- Des cartes élégantes pour les détails

---

## 📂 Flux de navigation

```
index.php
    │
    ├─→ ?action=liste_etudiants (Liste)
    │       │
    │       ├─→ ?action=details_etudiant&id=X (Détails)
    │       │       │
    │       │       ├─→ ?action=modifier_etudiant&id=X (Modifier)
    │       │       └─→ ?action=supprimer_etudiant&id=X (Supprimer)
    │       │
    │       ├─→ ?action=modifier_etudiant&id=X (Modifier)
    │       └─→ ?action=supprimer_etudiant&id=X (Supprimer)
    │
    └─→ ?action=creer_etudiant (Créer)
```

---

## ⚠️ IMPORTANT : Corrections à faire avant utilisation

**Avant d'utiliser ce CRUD, vous DEVEZ corriger les erreurs listées dans le fichier :**

📄 **`docs/erreurs_a_corriger.md`**

Ce fichier contient :
- 4 erreurs dans `php-crud/model/etudiant.php`
- 2 erreurs dans `php-crud/controllers/EtudiantController.php`
- 2 problèmes dans `php-crud/views/form_etudiant.php`

**Total : 8 corrections à effectuer**

---

## 🔐 Sécurité

Les vues utilisent :
- `htmlspecialchars()` pour éviter les injections XSS
- Confirmation JavaScript pour les suppressions
- Validation des ID avec `intval()`

**⚠️ ATTENTION** : Le Controller doit encore être modifié pour :
- Gérer les uploads de fichiers (avatar)
- Hasher les mots de passe avec `password_hash()`
- Valider les données avant insertion

---

## 📊 Résumé des fichiers créés

| Fichier | Taille | Fonction |
|---------|--------|----------|
| `liste_etudiants.php` | ~4 Ko | Liste tous les étudiants dans un tableau |
| `details_etudiant.php` | ~6 Ko | Affiche les détails d'un étudiant |
| `form_edit_etudiant.php` | ~7 Ko | Formulaire de modification |
| `form_etudiant.php` | ~2 Ko | Formulaire de création (renommé) |
| `erreurs_a_corriger.md` | ~5 Ko | Liste toutes les erreurs à corriger |
| `guide_utilisation_crud.md` | Ce fichier | Guide complet d'utilisation |

---

## 🎯 Prochaines étapes

1. ✅ Lire `docs/erreurs_a_corriger.md`
2. ✅ Corriger les erreurs dans Model et Controller
3. ✅ Modifier `index.php` avec le code fourni ci-dessus
4. ✅ Tester la création d'un étudiant
5. ✅ Tester la liste, la modification et la suppression

---

**Bonne chance ! 🚀**
