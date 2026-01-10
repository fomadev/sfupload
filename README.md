# sfupload 🛡️

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-blue.svg)](https://php.net)
![Version](https://img.shields.io/badge/version-1.2.0-green.svg)

**sfupload** est une bibliothèque PHP 8.1+ légère, modulaire et ultra-sécurisée pour gérer les téléchargements de fichiers. Elle repose sur l'interface standard **PSR-7** pour garantir une compatibilité maximale avec tous les frameworks modernes (Symfony, Laravel, Slim, etc.).

## 🌟 Pourquoi choisir sfupload ?

L'upload de fichiers est l'une des plus grandes failles de sécurité en développement web. **sfupload** applique les meilleures pratiques par défaut :

✅ **Vérification MIME stricte** : Détecte le vrai type de fichier via Magic Bytes (`finfo`), pas l'extension  
✅ **Renommage cryptographique** : Génère des noms imprévisibles avec `random_bytes()`  
✅ **Architecture modulaire** : Séparez validation, stockage et logique métier  
✅ **Configuration flexible** : Configurations prédéfinies (images, documents) ou personnalisées  
✅ **Zéro dépendance externe** : Utilise uniquement PSR-7 et le cœur PHP  
✅ **API simple** : 5 lignes pour un upload sécurisé  

## 🚀 Installation

```bash
composer require fomadev/sfupload
```

## 📖 Usage rapide

### Exemple basique (5 lignes)

```php
use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Bridge\UploadedFileAdapter;

// Configuration simple
$storage = new LocalStorage(__DIR__ . '/uploads', true);
$mimeConstraint = new MimeTypeConstraint(['image/jpeg', 'image/png']);
$validator = new Validator(5 * 1024 * 1024, $mimeConstraint); // 5 Mo max
$uploader = new Uploader($storage, $validator);

// Upload sécurisé
try {
    $fileInfo = $uploader->upload(UploadedFileAdapter::fromGlobal($_FILES['file']));
    echo "Succès ! Fichier: " . $fileInfo->savedName;
    echo "Taille: " . $fileInfo->getFormattedSize(); // Utilise FileHelper
} catch (\SfUpload\Exception\UploadException $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Avec configuration prédéfinie

```php
use SfUpload\Configuration\UploadConfig;

// Configuration prédéfinie pour images uniquement
$config = UploadConfig::imageOnly(10 * 1024 * 1024); // 10 Mo

$storage = new LocalStorage($uploadDir, true);
$mimeConstraint = new MimeTypeConstraint($config->getAllowedMimes());
$validator = new Validator($config->getMaxSize(), $mimeConstraint);
$uploader = new Uploader($storage, $validator);
```

### Configuration personnalisée

```php
$config = new UploadConfig(
    maxSize: 20 * 1024 * 1024, // 20 Mo
    allowedMimes: ['application/pdf', 'application/msword'],
    createMissingDir: true
);
```

## 🎯 Configurations prédéfinies

| Preset | Types | Taille max |
|--------|-------|-----------|
| `UploadConfig::imageOnly()` | JPEG, PNG, WebP, GIF | 10 Mo |
| `UploadConfig::documentOnly()` | PDF, Word, Excel | 20 Mo |
| `UploadConfig::any()` | Tous | 50 Mo |

## 📚 API Complète

### Classe `Uploader`

```php
$uploader->upload(UploadedFileInterface $file): FileInfo
```

### Objet `FileInfo` retourné

```php
$fileInfo->originalName;        // Nom original du fichier
$fileInfo->savedName;           // Nom sécurisé généré
$fileInfo->fullPath;            // Chemin complet du fichier
$fileInfo->mimeType;            // Type MIME détecté
$fileInfo->size;                // Taille en bytes

// Méthodes utiles
$fileInfo->getExtension();      // Extension du fichier
$fileInfo->getFileType();       // Type: image, document, etc.
$fileInfo->getFormattedSize();  // Taille lisible: "5.2 MB"
$fileInfo->exists();            // Vérifie l'existence du fichier
$fileInfo->getStats();          // Retourne les stats du fichier
```

### Classe `UploadConfig`

```php
$config = new UploadConfig($maxSize, $mimes, $createDir);

$config->getMaxSize();
$config->getAllowedMimes();
$config->shouldCreateMissingDir();

// Fluent interface
$config->setMaxSize(10 * 1024 * 1024)->setAllowedMimes([...]);
```

### Classe `FileHelper`

```php
FileHelper::formatFileSize(5242880);              // "5 MB"
FileHelper::getFileType('image.jpg');             // "image"
FileHelper::sanitizePath($userPath);              // Sécurise le chemin
FileHelper::fileExists($filePath);                // Vérifie l'existence
FileHelper::getFileStats($filePath);              // Retourne les stats
```

## 📂 Structure du projet

```
src/
├── Bridge/                      # Adaptateurs PSR-7
│   └── UploadedFileAdapter.php  # Convertisseur $_FILES → PSR-7
├── Configuration/               # Gestion de la configuration
│   └── UploadConfig.php         # Configuration centralisée
├── Exception/                   # Exceptions personnalisées
│   └── UploadException.php
├── Storage/                     # Moteur de stockage
│   └── LocalStorage.php
├── Utility/                     # Utilitaires
│   └── FileHelper.php           # Helpers pour les fichiers
├── Validation/                  # Moteur de validation
│   ├── Validator.php
│   └── MimeTypeConstraint.php
├── FileInfo.php                 # DTO pour les infos du fichier
└── Uploader.php                 # Façade principale
```

## 🔐 Sécurité

### Recommandations

1. **Placez le dossier d'upload en dehors du public**
   ```php
   // ❌ Mauvais
   $storage = new LocalStorage(__DIR__ . '/public/uploads');
   
   // ✅ Bon
   $storage = new LocalStorage(__DIR__ . '/../private/uploads', true);
   ```

2. **Configurez votre serveur pour interdire l'exécution de scripts**
   ```apache
   # .htaccess
   <FilesMatch "\.php$">
       Deny from all
   </FilesMatch>
   ```

3. **Validez strictement les types MIME**
   ```php
   $mimeConstraint = new MimeTypeConstraint(['image/jpeg']);
   // Détecte les vrais types, pas les extensions trompeuses
   ```

## 📋 Exemples inclus

### 1. Simple (`examples/simple.php`)
Exemple basique avec galerie des fichiers récents

### 2. Images avancées (`examples/images-advanced.php`)
Upload d'images avec configuration prédéfinie

### 3. API Endpoint (`examples/api-endpoint.php`)
Endpoint AJAX qui retourne JSON

### 4. Multi-type (`examples/multi-type.php`)
Gestion de plusieurs types de fichiers avec onglets

## 🔄 Changelog

### 1.2.0 (Janvier 2026) ✨ Nouvelle version
- ✨ Nouvelle classe `UploadConfig` pour configurations prédéfinies
- ✨ Nouvelle classe `FileHelper` avec utilitaires
- ✨ Nouvelle classe `UploadedFileAdapter` pour PSR-7
- 🎨 Méthodes utiles dans `FileInfo` (getFormattedSize, getFileType, etc.)
- 📚 4 exemples complets et documentés
- 🔧 Support de création automatique des dossiers

### 1.0.0
- Version initiale de la bibliothèque

## 🧪 Tests

```bash
composer test
```

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont bienvenues ! N'hésitez pas à :
- Signaler des bugs
- Proposer des améliorations
- Soumettre des pull requests

## 📞 Support

Pour toute question, créez une [GitHub issue](https://github.com/fomadev/sfupload/issues).

---

Développé avec ❤️ par **fomadev**