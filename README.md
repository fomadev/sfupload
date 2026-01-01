# sfupload 🛡️

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-blue.svg)](https://php.net)

**sfupload** est une bibliothèque PHP 8.1+ légère, modulaire et ultra-sécurisée conçue pour gérer les téléchargements de fichiers. Elle repose sur les interfaces standards **PSR-7** pour garantir une compatibilité maximale avec les frameworks modernes (Slim, Symfony, Laravel, etc.).

## 🌟 Pourquoi utiliser sfupload ?

L'upload de fichiers est l'une des failles de sécurité les plus courantes. **sfupload** résout ce problème en appliquant les meilleures pratiques par défaut :

* **Vérification MIME Strict** : Utilise l'extension `finfo` (Magic Bytes) pour détecter le vrai type de fichier, ignorant les extensions trompeuses.
* **Renommage Cryptographique** : Génère des noms de fichiers imprévisibles via `random_bytes()` pour prévenir les attaques de type *Path Traversal*.
* **Architecture Découplée** : Séparez votre logique de validation, de stockage et de traitement grâce à une structure orientée objet.
* **Zéro Dépendance Externe** : Utilise uniquement les interfaces PSR et le cœur de PHP 8.

## 🚀 Installation

Installez la bibliothèque via [Composer](https://getcomposer.org/) :

```bash
composer require fomadev/sfupload
```

## 🛠️ Utilisation Rapide

Voici comment mettre en place un upload sécurisé en quelques lignes :
``` php
use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;

// 1. Définir le dossier de stockage (doit être accessible en écriture)
$storage = new LocalStorage(__DIR__ . '/uploads');

// 2. Configurer les contraintes de sécurité
$mimeConstraint = new MimeTypeConstraint(['image/jpeg', 'image/png', 'application/pdf']);
$validator = new Validator(
    maxSize: 5 * 1024 * 1024, // 5 Mo
    mimeConstraint: $mimeConstraint
);

// 3. Initialiser l'Uploader
$uploader = new Uploader($storage, $validator);

// 4. Exécuter l'upload (avec un objet PSR-7 UploadedFileInterface)
try {
    // Supposons que $file vienne de votre requête PSR-7
    $fileInfo = $uploader->upload($file);

    echo "Fichier téléchargé avec succès !";
    echo "Nouveau nom : " . $fileInfo->savedName;
    echo "Chemin complet : " . $fileInfo->fullPath;
} catch (\SfUpload\Exception\UploadException $e) {
    echo "Erreur : " . $e->getMessage();
}
```

## 📂 Structure du Projet

``` Plaintext
src/
├── Exception/          # Gestion des erreurs spécifiques
├── Storage/            # Logique de stockage (Local, futur S3...)
├── Validation/         # Moteur de validation et contraintes
├── FileInfo.php        # Objet de transfert de données (DTO) après upload
└── Uploader.php        # Façade principale (Chef d'orchestre)
```

## 🔒 Sécurité Recommandée

Bien que sfupload sécurise le processus de téléchargement, nous recommandons :

1. De placer votre dossier d'upload en dehors du répertoire public de votre serveur web.

2. De configurer votre serveur (Apache/Nginx) pour interdire l'exécution de scripts dans le dossier de destination.

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier <a href="LICENSE">LICENSE</a> pour plus de détails.

<hr>

Développé avec ❤️ par <b>fomadev<b>