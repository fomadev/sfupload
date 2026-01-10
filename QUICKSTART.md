# Guide de démarrage rapide - sfUpload 1.2.0

## Installation en 2 secondes

```bash
composer require fomadev/sfupload
```

## Utilisation basique (copier-coller)

```php
<?php
require_once 'vendor/autoload.php';

use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Bridge\UploadedFileAdapter;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $storage = new LocalStorage(__DIR__ . '/uploads', true);
        $validator = new Validator(
            5 * 1024 * 1024,
            new MimeTypeConstraint(['image/jpeg', 'image/png'])
        );
        $uploader = new Uploader($storage, $validator);
        
        $fileInfo = $uploader->upload(UploadedFileAdapter::fromGlobal($_FILES['file']));
        
        echo "✅ Succès! Fichier: " . $fileInfo->savedName;
        echo " (" . $fileInfo->getFormattedSize() . ")";
        
    } catch (\Exception $e) {
        echo "❌ Erreur: " . $e->getMessage();
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Uploader</button>
</form>
```

## Configurations prédéfinies

### Images uniquement
```php
use SfUpload\Configuration\UploadConfig;

$config = UploadConfig::imageOnly(10 * 1024 * 1024);
$validator = new Validator($config->getMaxSize(), 
    new MimeTypeConstraint($config->getAllowedMimes()));
```

### Documents uniquement
```php
$config = UploadConfig::documentOnly(20 * 1024 * 1024);
```

### N'importe quel fichier
```php
$config = UploadConfig::any(50 * 1024 * 1024);
```

## Affichage d'infos sur le fichier

```php
echo $fileInfo->originalName;        // Nom d'origine
echo $fileInfo->savedName;           // Nom sécurisé
echo $fileInfo->getFormattedSize();  // "5.2 MB"
echo $fileInfo->getFileType();       // "image"
echo $fileInfo->getExtension();      // "jpg"
echo $fileInfo->mimeType;            // "image/jpeg"
```

## FileHelper - Utilitaires

```php
use SfUpload\Utility\FileHelper;

FileHelper::formatFileSize(5242880);      // "5 MB"
FileHelper::getFileType('photo.jpg');     // "image"
FileHelper::getFileStats('/path/file');   // Array avec stats
FileHelper::sanitizePath($userPath);      // Sécurise le chemin
```

## Gestion d'erreurs

```php
use SfUpload\Exception\UploadException;

try {
    $uploader->upload($file);
} catch (UploadException $e) {
    // Erreur de validation (taille, type MIME, etc.)
    echo "Validation échouée: " . $e->getMessage();
} catch (\Exception $e) {
    // Erreur système
    echo "Erreur système: " . $e->getMessage();
}
```

## Paramètres LocalStorage

```php
// Créer le dossier automatiquement
new LocalStorage($dir, true);

// Erreur si le dossier n'existe pas
new LocalStorage($dir, false);
```

## Exemples disponibles

- `examples/simple.php` - Exemple basique
- `examples/images-advanced.php` - Upload d'images
- `examples/api-endpoint.php` - API JSON
- `examples/multi-type.php` - Plusieurs types de fichiers

## Vérifications de sécurité importantes

✅ Type MIME détecté via Magic Bytes (finfo)  
✅ Noms de fichiers aléatoires (random_bytes)  
✅ Pas de traversée de répertoire  
✅ Support PSR-7 pour tous les frameworks  

**Recommandation** : Placez le dossier uploads en dehors du répertoire public !

## Questions ?

Voir le [README.md](../README.md) pour la documentation complète.
