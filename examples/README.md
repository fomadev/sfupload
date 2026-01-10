# Exemples sfUpload

Ce dossier contient des exemples complets pour utiliser sfUpload.

## 📋 Liste des exemples

### 1. 🎨 simple.php
**Exemple basique avec interface visuelle**

- Upload simple d'images et PDF
- Galerie des fichiers récents
- Affichage des infos (taille, type)
- Drag & drop supporté
- Responsive design

**Fichiers acceptés** : Images (JPEG, PNG, WebP), PDF  
**Limite** : 5 Mo

```
http://localhost/sfupload/examples/simple.php
```

---

### 2. 🖼️ images-advanced.php
**Upload d'images avec configuration prédéfinie**

Démontre :
- Utilisation de `UploadConfig::imageOnly()`
- Configuration facile via presets
- Moins de code à écrire
- Interface minimaliste

**Fichiers acceptés** : JPEG, PNG, WebP, GIF  
**Limite** : 10 Mo

```
http://localhost/sfupload/examples/images-advanced.php
```

---

### 3. 🔌 api-endpoint.php
**Endpoint API JSON pour uploads AJAX**

Démontre :
- Réponse JSON structurée
- Intégration avec JavaScript/Fetch
- Utilisable dans les applications modernes
- Pas d'interface HTML (API pure)

**Réponse JSON** :
```json
{
  "success": true,
  "message": "Fichier uploadé avec succès",
  "data": {
    "original_name": "photo.jpg",
    "saved_name": "a1b2c3d4e5f6.jpg",
    "size": 2097152,
    "size_formatted": "2 MB",
    "type": "image",
    "mime_type": "image/jpeg",
    "extension": "jpg"
  }
}
```

**Utilisation cURL** :
```bash
curl -F "file=@photo.jpg" http://localhost/sfupload/examples/api-endpoint.php
```

---

### 4. 📂 multi-type.php
**Gestion multi-type avec onglets**

Démontre :
- Plusieurs types de fichiers (images, documents, tous)
- Interface avec onglets
- Configurations différentes par type
- Liste des fichiers récents

**Onglets disponibles** :
- 📦 Tous les fichiers (50 Mo)
- 🖼️ Images uniquement (10 Mo)
- 📄 Documents uniquement (20 Mo)

```
http://localhost/sfupload/examples/multi-type.php
```

---

## 🚀 Démarrage rapide

### Prérequis
- PHP 8.1+
- Dossier `uploads/` avec permissions d'écriture
- Composer installé

### Installation

```bash
cd sfupload
composer install
```

### Lancer les exemples

Avec PHP built-in :
```bash
php -S localhost:8000
```

Puis accédez à :
- http://localhost:8000/examples/simple.php
- http://localhost:8000/examples/images-advanced.php
- http://localhost:8000/examples/api-endpoint.php
- http://localhost:8000/examples/multi-type.php

---

## 📝 Cas d'usage

| Besoin | Exemple | Pourquoi |
|--------|---------|---------|
| Démarrer rapidement | `simple.php` | Interface complète, prête à l'emploi |
| Upload d'images | `images-advanced.php` | Configuration prédéfinie, moins de code |
| API AJAX | `api-endpoint.php` | JSON, intégration JavaScript |
| Multi-type | `multi-type.php` | Gestion flexible de plusieurs types |

---

## 🔧 Personnalisation

Chaque exemple peut être adapté :

### Changer la limite de taille
```php
new UploadConfig(maxSize: 100 * 1024 * 1024)
```

### Ajouter des types MIME
```php
new MimeTypeConstraint(['image/jpeg', 'image/png', 'application/pdf'])
```

### Changer le dossier de destination
```php
new LocalStorage(__DIR__ . '/uploads/custom', true)
```

---

## ❌ Erreurs courantes

**Erreur** : "Le dossier de destination n'existe pas"  
**Solution** : Passer `true` au deuxième paramètre de `LocalStorage`
```php
new LocalStorage($dir, true) // Crée le dossier
```

**Erreur** : "Le type de fichier n'est pas autorisé"  
**Solution** : Vérifier la liste des `MimeTypeConstraint`
```php
new MimeTypeConstraint(['image/jpeg']) // Accepte seulement JPEG
```

**Erreur** : "Le fichier est trop volumineux"  
**Solution** : Augmenter la limite de `Validator`
```php
new Validator(50 * 1024 * 1024, $mimeConstraint) // 50 Mo
```

---

## 📚 Documentation

Pour plus d'infos :
- [README.md](../README.md) - Documentation complète
- [QUICKSTART.md](../QUICKSTART.md) - Guide rapide
- [src/](../src/) - Code source documenté

---

## 🎓 Apprendre à partir des exemples

Chaque exemple montre :

1. **Initialisation** - Créer storage, validator, uploader
2. **Gestion du formulaire** - Traiter la requête POST
3. **Affichage** - Présenter les infos du fichier
4. **Listing** - Afficher les fichiers précédents
5. **Design** - CSS et UX modernes

Modifiez-les pour apprendre !

---

Développé avec ❤️ par **fomadev**
