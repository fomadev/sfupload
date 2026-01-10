# 📦 Déploiement sfUpload v1.2.0

## ✅ Checklist complète

### Avant de déployer

- [ ] PHP 8.1+ installé
- [ ] Composer installé
- [ ] `composer install` exécuté
- [ ] Permissions de dossiers vérifiées

### Configuration sécurité

- [ ] Dossier `uploads/` créé
- [ ] Dossier uploads en dehors du public
- [ ] .htaccess dans uploads (interdit l'exécution PHP)
- [ ] Permissions 755 sur dossier uploads
- [ ] HTTPS activé en production

### Tests

- [ ] Tester `examples/simple.php`
- [ ] Tester drag & drop
- [ ] Tester fichiers trop gros
- [ ] Tester mauvais types MIME
- [ ] Vérifier les noms de fichiers (hash)

### Documentation

- [ ] Lire QUICKSTART.md
- [ ] Lire ARCHITECTURE.md
- [ ] Lire BEST-PRACTICES.md
- [ ] Adapter exemples à cas d'usage

---

## 🚀 Installation rapide

```bash
# 1. Cloner/télécharger le repo
git clone https://github.com/fomadev/sfupload.git

# 2. Installer les dépendances
cd sfupload
composer install

# 3. Créer le dossier uploads
mkdir -p uploads
chmod 755 uploads

# 4. Lancer le serveur de développement
php -S localhost:8000

# 5. Tester
# Visiter http://localhost:8000/examples/simple.php
```

---

## 🎯 Structure de fichiers

```
sfupload/
├── src/
│   ├── Bridge/
│   │   └── UploadedFileAdapter.php      ← Nouveau
│   ├── Configuration/
│   │   └── UploadConfig.php             ← Nouveau
│   ├── Exception/
│   │   └── UploadException.php
│   ├── Storage/
│   │   └── LocalStorage.php
│   ├── Utility/
│   │   └── FileHelper.php               ← Nouveau
│   ├── Validation/
│   │   ├── MimeTypeConstraint.php
│   │   └── Validator.php
│   ├── FileInfo.php                     ← Amélioré
│   └── Uploader.php
│
├── examples/
│   ├── README.md                        ← Nouveau
│   ├── index.php                        ← Amélioration
│   ├── simple.php                       ← Nouveau
│   ├── images-advanced.php              ← Nouveau
│   ├── api-endpoint.php                 ← Nouveau
│   ├── multi-type.php                   ← Nouveau
│   └── uploads/                         (créé par app)
│
├── vendor/                              (composer)
├── composer.json                        ← Version 1.2.0
│
├── README.md                            ← Réécrit
├── QUICKSTART.md                        ← Nouveau
├── ARCHITECTURE.md                      ← Nouveau
├── BEST-PRACTICES.md                    ← Nouveau
├── CHANGELOG.md                         ← Nouveau
│
└── LICENSE                              (MIT)
```

---

## 🔐 Configuration de sécurité recommandée

### Apache (.htaccess dans uploads/)

```apache
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>

# Interdire les répertoires
Options -Indexes

# Redirige vers l'accueil
ErrorDocument 403 /index.php
```

### Nginx

```nginx
location /uploads/ {
    location ~ \.php$ {
        return 403;
    }
}

# Interdire accès direct
location /uploads {
    autoindex off;
}
```

### Permissions Linux

```bash
# Dossier uploads
chmod 755 uploads

# Fichiers uploadés
find uploads -type f -exec chmod 644 {} \;
```

---

## 📝 Intégration dans un projet existant

### 1. Installation

```bash
composer require fomadev/sfupload
```

### 2. Créer un service

```php
<?php
// Services/UploadService.php

use SfUpload\Configuration\UploadConfig;
use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Bridge\UploadedFileAdapter;

class UploadService
{
    private $uploader;
    
    public function __construct(string $uploadDir)
    {
        $config = UploadConfig::imageOnly();
        $storage = new LocalStorage($uploadDir, true);
        $constraint = new MimeTypeConstraint($config->getAllowedMimes());
        $validator = new Validator($config->getMaxSize(), $constraint);
        $this->uploader = new Uploader($storage, $validator);
    }
    
    public function uploadImage($file)
    {
        return $this->uploader->upload(
            UploadedFileAdapter::fromGlobal($file)
        );
    }
}
```

### 3. Utiliser dans contrôleur

```php
<?php
// Controller/ProfileController.php

$uploadService = new UploadService('/path/private/uploads');

if ($_FILES) {
    try {
        $fileInfo = $uploadService->uploadImage($_FILES['avatar']);
        // Enregistrer en DB
        User::updateAvatar($fileInfo->savedName);
    } catch (Exception $e) {
        // Afficher erreur
    }
}
```

---

## 🧪 Tests unitaires

### Exemple de test

```php
<?php

use PHPUnit\Framework\TestCase;
use SfUpload\Configuration\UploadConfig;

class UploadConfigTest extends TestCase
{
    public function testImageOnly()
    {
        $config = UploadConfig::imageOnly();
        
        $this->assertContains('image/jpeg', 
            $config->getAllowedMimes());
        $this->assertEquals(10 * 1024 * 1024, 
            $config->getMaxSize());
    }
    
    public function testFluentInterface()
    {
        $config = UploadConfig::any()
            ->setMaxSize(20 * 1024 * 1024)
            ->setAllowedMimes(['image/jpeg']);
        
        $this->assertEquals(20 * 1024 * 1024, 
            $config->getMaxSize());
    }
}
```

### Lancer les tests

```bash
composer test
# ou
./vendor/bin/phpunit
```

---

## 🐛 Troubleshooting

### Problème : "Le dossier de destination n'existe pas"

**Solution** :
```php
// Ajouter true pour créer le dossier
new LocalStorage($dir, true);
```

### Problème : "Le type de fichier n'est pas autorisé"

**Solution** :
1. Vérifier le type MIME accepté
2. Tester avec `php -r "echo mime_content_type('file.jpg');"`
3. Ajouter le type à la liste

```php
new MimeTypeConstraint(['image/jpeg', 'image/png']);
```

### Problème : "Le fichier est trop volumineux"

**Solution** :
```php
// Augmenter la limite
new Validator(50 * 1024 * 1024, $constraint);

// Vérifier aussi php.ini
// post_max_size >= upload_max_filesize
```

### Problème : Erreur "Impossible de déplacer le fichier"

**Solution** :
1. Vérifier que le dossier est accessible en écriture
2. Vérifier les permissions
3. Vérifier que tmp_upload_dir est accessible

---

## 📊 Monitoring

### Logs recommandés

```php
// Dans votre application
try {
    $fileInfo = $uploader->upload($file);
    error_log("Upload OK: " . $fileInfo->savedName);
} catch (Exception $e) {
    error_log("Upload FAIL: " . $e->getMessage());
}
```

### Statistiques

```php
// Taille total des uploads
$totalSize = 0;
foreach (glob($uploadDir . '/*') as $file) {
    $totalSize += filesize($file);
}

echo "Taille totale : " . FileHelper::formatFileSize($totalSize);
```

### Nettoyage automatique

```php
// Supprimer les fichiers de plus de 30 jours
$cutoff = time() - (30 * 24 * 3600);
foreach (glob($uploadDir . '/*') as $file) {
    if (filemtime($file) < $cutoff) {
        unlink($file);
    }
}
```

---

## 🚨 Production

### Checklist finale

- [ ] Base de données : Enregistrer métadonnées uploads
- [ ] Notifications : Alerter en cas d'erreur
- [ ] Backups : Sauvegarder les uploads
- [ ] Monitoring : Vérifier espace disque
- [ ] Logs : Activer et rotationner les logs
- [ ] Antivirus : Scanner les uploads
- [ ] CDN : Servir via CDN si possible

---

## 📞 Support

- **Issues** : GitHub Issues
- **Email** : fordimalanda7@gmail.com
- **Docs** : Voir README.md, QUICKSTART.md

---

Développé avec ❤️ par **fomadev**
