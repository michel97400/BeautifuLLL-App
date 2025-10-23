# Erreurs à corriger dans le CRUD Etudiant

## 📁 Fichier : `php-crud/model/etudiant.php`

### ❌ Erreur 1 : Méthode `create()` - ligne 17-33
**Problème** : Le paramètre `$id_niveau` est utilisé dans la requête SQL mais n'est pas déclaré dans les paramètres de la fonction.

**Code actuel :**
```php
public function create($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
```

**À corriger :**
```php
public function create($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
```

**Ligne SQL à vérifier :**
- Ligne 19 : Il y a 9 placeholders dans VALUES mais seulement 8 colonnes dans la liste
- Corrigez : `INSERT INTO Etudiants (nom, prenom, email, avatar, passwordhash, date_inscription, consentement_rgpd, id_role, id_niveau)`

---

### ❌ Erreur 2 : Méthode `readSingle()` - ligne 47
**Problème** : PDO n'a pas le backslash pour le namespace.

**Code actuel :**
```php
$stmt->bindParam(':id_etudiant', $id_etudiant, PDO::PARAM_INT);
return $stmt->fetch(PDO::FETCH_ASSOC);
```

**À corriger :**
```php
$stmt->bindParam(':id_etudiant', $id_etudiant, \PDO::PARAM_INT);
return $stmt->fetch(\PDO::FETCH_ASSOC);
```

---

### ❌ Erreur 3 : Méthode `update()` - ligne 51-59
**Problème** : Les paramètres ne correspondent pas aux champs de la table Etudiants.

**Code actuel :**
```php
public function update($id_etudiant, $name, $email)
{
    $sql = "UPDATE Etudiants SET name = :name, email = :email WHERE id_etudiant = :id_etudiant";
```

**À corriger :**
```php
public function update($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
{
    $sql = "UPDATE Etudiants SET nom = :nom, prenom = :prenom, email = :email, avatar = :avatar, passwordhash = :passwordhash, date_inscription = :date_inscription, consentement_rgpd = :consentement_rgpd, id_role = :id_role, id_niveau = :id_niveau WHERE id_etudiant = :id_etudiant";
```

**Ajouter les bindParam manquants :**
```php
$stmt->bindParam(':nom', $nom);
$stmt->bindParam(':prenom', $prenom);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':avatar', $avatar);
$stmt->bindParam(':passwordhash', $passwordhash);
$stmt->bindParam(':date_inscription', $date_inscription);
$stmt->bindParam(':consentement_rgpd', $consentement_rgpd);
$stmt->bindParam(':id_role', $id_role);
$stmt->bindParam(':id_niveau', $id_niveau);
$stmt->bindParam(':id_etudiant', $id_etudiant);
```

---

### ❌ Erreur 4 : Méthode `delete()` - ligne 65
**Problème** : Variable incorrecte dans le bindParam.

**Code actuel :**
```php
$stmt->bindParam(':id_etudiant', $id);
```

**À corriger :**
```php
$stmt->bindParam(':id_etudiant', $id_etudiant);
```

---

## 📁 Fichier : `php-crud/controllers/EtudiantController.php`

### ❌ Erreur 5 : Méthode `createEtudiant()` - ligne 9-13
**Problème** : Le paramètre `$id_niveau` est manquant.

**Code actuel :**
```php
public function createEtudiant($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
{
    $Etudiant = new Etudiants();
    return $Etudiant->create(nom: $nom, prenom: $prenom, email: $email, avatar: $avatar, passwordhash: $passwordhash, date_inscription: $date_inscription, consentement_rgpd: $consentement_rgpd, id_role: $id_role);
}
```

**À corriger :**
```php
public function createEtudiant($nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
{
    $Etudiant = new Etudiants();
    return $Etudiant->create(nom: $nom, prenom: $prenom, email: $email, avatar: $avatar, passwordhash: $passwordhash, date_inscription: $date_inscription, consentement_rgpd: $consentement_rgpd, id_role: $id_role, id_niveau: $id_niveau);
}
```

---

### ❌ Erreur 6 : Méthode `updateEtudiant()` - ligne 27-31
**Problème** : Les paramètres ne correspondent pas à la signature du Model.

**Code actuel :**
```php
public function updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role)
{
    $Etudiant = new Etudiants();
    return $Etudiant->update(id: $id_etudiant,nom: $nom, prenom: $prenom, email: $email, avatar: $avatar, passwordhash: $passwordhash, date_inscription: $date_inscription, consentement_rgpd: $consentement_rgpd, id_role: $id_role);
}
```

**À corriger :**
```php
public function updateEtudiant($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau)
{
    $Etudiant = new Etudiants();
    return $Etudiant->update($id_etudiant, $nom, $prenom, $email, $avatar, $passwordhash, $date_inscription, $consentement_rgpd, $id_role, $id_niveau);
}
```

**Note** : Il y a aussi une erreur de syntaxe avec `id:` au lieu de simplement passer les paramètres.

---

## 📁 Fichier : `php-crud/views/form.php`

### ⚠️ Attention : Ligne 43
**Problème** : Ligne de code PHP qui ne devrait pas être là.

**Code actuel (ligne 43) :**
```php
('6 eme'), ('5 eme'), ('4 eme'), ('3 eme'), ('Second'), ('Premiere'), ('Terminale')
```

**Action** : Supprimer cette ligne complètement.

---

### ⚠️ Attention : Valeurs dupliquées dans le select niveau
**Problème** : Plusieurs options ont la même valeur `value="3"`.

**Code actuel (lignes 36-42) :**
```php
<option value="1">6 ème</option>
<option value="2">5 ème</option>
<option value="3">4 ème</option>
<option value="3">3 ème</option>    <!-- Doublon -->
<option value="3">Second</option>   <!-- Doublon -->
<option value="3">Premiere</option> <!-- Doublon -->
<option value="3">Terminale</option><!-- Doublon -->
```

**À corriger :**
```php
<option value="1">6 ème</option>
<option value="2">5 ème</option>
<option value="3">4 ème</option>
<option value="4">3 ème</option>
<option value="5">Second</option>
<option value="6">Premiere</option>
<option value="7">Terminale</option>
```

---

## 📝 Résumé des corrections nécessaires

| Fichier | Nombre d'erreurs | Priorité |
|---------|------------------|----------|
| `php-crud/model/etudiant.php` | 4 erreurs | 🔴 HAUTE |
| `php-crud/controllers/EtudiantController.php` | 2 erreurs | 🔴 HAUTE |
| `php-crud/views/form.php` | 2 problèmes | 🟡 MOYENNE |

**Total : 8 corrections à effectuer**

---

## 🎯 Actions recommandées

1. **Commencer par le Model** : Corriger toutes les erreurs dans `etudiant.php`
2. **Ensuite le Controller** : Ajuster `EtudiantController.php` pour qu'il corresponde au Model
3. **Enfin les vues** : Nettoyer `form.php`

---

## 📚 Fichiers manquants à créer

Les vues suivantes doivent être créées pour avoir un CRUD complet :

- ✅ `php-crud/views/form_etudiant.php` (renommer `form.php`)
- ❌ `php-crud/views/liste_etudiants.php`
- ❌ `php-crud/views/details_etudiant.php`
- ❌ `php-crud/views/form_edit_etudiant.php`

Ces fichiers seront créés automatiquement par Claude.
