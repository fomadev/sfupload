# Meilleures pratiques sfUpload 1.2.0

## 🔐 Sécurité

### ✅ À faire

**1. Mettre le dossier uploads HORS du public**
```php
// Structure recommandée
project/
├── public/         (accessible au web)
├── private/        (NON accessible au web)
│   └── uploads/    (stocker ici)
└── src/

// Code
new LocalStorage(__DIR__ . '/../../private/uploads', true);
```

**2. Configurer le serveur pour interdire l'exécution de PHP**
```apache
# .htaccess dans le dossier uploads
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>

# Nginx
location /uploads/ {
    location ~ \.php$ {
        deny all;
    }
}
```

**3. Valider strictement les types MIME**
```php
// ❌ Mauvais - Accepte tout
new MimeTypeConstraint([]);

// ✅ Bon - Spécifier les types acceptés
new MimeTypeConstraint([
    'image/jpeg',
    'image/png',
    'application/pdf'
]);
```

**4. Limiter la taille des fichiers**
```php
// ❌ Mauvais - Pas de limite
new Validator(PHP_INT_MAX, $constraint);

// ✅ Bon - Limite raisonnable
new Validator(10 * 1024 * 1024, $constraint); // 10 Mo
```

**5. Gérer les erreurs silencieusement**
```php
// ❌ Mauvais
echo "Fichier uploadé: " . $_FILES['file']['name'];

// ✅ Bon - Utiliser les données sécurisées
echo "Fichier uploadé: " . $fileInfo->originalName;
```

### ❌ À éviter

**Ne jamais faire confiance à l'extension du fichier**
```php
// ❌ DANGER
$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
if ($ext === 'jpg') { /* Accepter */ }  // Un attaquant peut changer l'extension

// ✅ Utiliser la détection MIME
new MimeTypeConstraint(['image/jpeg']);  // Détecte le type réel
```

**Ne jamais utiliser le nom d'origine directement**
```php
// ❌ DANGER - Path Traversal possible
move_uploaded_file($_FILES['file']['tmp_name'], 
                  __DIR__ . '/uploads/' . $_FILES['file']['name']);
                  // Un attaquant peut envoyer: "../../malware.php"

// ✅ sfUpload génère un nom sécurisé
$fileInfo->savedName;  // "a1b2c3d4e5f6.jpg"
```

**Ne jamais exécuter des fichiers uploadés**
```php
// ❌ DANGER
include('/uploads/' . $fileInfo->savedName);

// ✅ Servir les fichiers comme ressources
header('Content-Type: ' . $fileInfo->mimeType);
readfile($fileInfo->fullPath);
```

---

## 🎯 Performance

### ✅ À faire

**1. Mettre en cache les configurations**
```php
// ❌ Créer à chaque requête
if ($_POST) {
    $config = UploadConfig::imageOnly();
    $validator = new Validator(...);
    $uploader = new Uploader(...);
}

// ✅ Créer une fois
class UploadService {
    private static $uploader;
    
    public static function getInstance() {
        if (!self::$uploader) {
            self::$uploader = new Uploader(...);
        }
        return self::$uploader;
    }
}
```

**2. Nettoyer les fichiers obsolètes**
```php
// Supprimer les fichiers de plus de 30 jours
$uploadDir = __DIR__ . '/uploads';
foreach (scandir($uploadDir) as $file) {
    if ($file !== '.' && $file !== '..') {
        $path = $uploadDir . '/' . $file;
        if (time() - filemtime($path) > 30 * 24 * 3600) {
            unlink($path);
        }
    }
}
```

**3. Limiter la taille des demandes POST**
```apache
# .htaccess
php_value post_max_size 50M
php_value upload_max_filesize 50M
```

---

## 🛠️ Utilisation

### ✅ À faire

**1. Gérer les erreurs complètement**
```php
try {
    $fileInfo = $uploader->upload($psr7File);
    // Succès - Log et redirection
    error_log("Upload réussi: " . $fileInfo->savedName);
    header('Location: /success');
} catch (\SfUpload\Exception\UploadException $e) {
    // Erreur de validation - Message utilisateur
    $error = "Erreur de validation: " . $e->getMessage();
    $_SESSION['error'] = $error;
} catch (\Exception $e) {
    // Erreur système - Log + message générique
    error_log("Erreur upload: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur système s'est produite";
}
```

**2. Valider côté client ET serveur**
```php
// HTML - Validation client (UX)
<input type="file" accept="image/jpeg,image/png" max-size="5242880">

// PHP - Validation serveur (sécurité) - OBLIGATOIRE
new MimeTypeConstraint(['image/jpeg', 'image/png']);
new Validator(5 * 1024 * 1024, $constraint);
```

**3. Afficher les informations de manière lisible**
```php
// Utiliser les helpers FileInfo
echo "Fichier: " . htmlspecialchars($fileInfo->originalName);
echo "Taille: " . $fileInfo->getFormattedSize();  // "5.2 MB"
echo "Type: " . $fileInfo->getFileType();         // "image"
```

**4. Utiliser les configurations prédéfinies quand possible**
```php
// ✅ Mieux - Prédefini
$config = UploadConfig::imageOnly();

// ✅ Bon aussi - Personnalisé
$config = new UploadConfig(10 * 1024 * 1024, ['image/jpeg']);

// ✅ Mais jamais - Vague
$config = new UploadConfig(0, []);
```

### ❌ À éviter

**Ne pas ignorer les exceptions**
```php
// ❌ MAUVAIS
try {
    $uploader->upload($file);
} catch (\Exception $e) {
    // Silencieux - L'utilisateur ne sait pas ce qui s'est passé
}

// ✅ Gérer proprement
catch (\Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}
```

**Ne pas mélanger types de fichiers sans raison**
```php
// ❌ Confus
new MimeTypeConstraint([
    'image/jpeg', 'image/png',
    'application/pdf', 'application/msword',
    'video/mp4'
]);

// ✅ Clair - Utiliser des présets ou commenter
// Images pour les profils utilisateur
new MimeTypeConstraint([
    'image/jpeg', 'image/png'
]);
```

---

## 📋 Checklist de déploiement

- [ ] Dossier uploads en dehors du public
- [ ] Permissions de dossier vérifiées (755 ou 775)
- [ ] Configuration MIME stricte appliquée
- [ ] Limites de taille raisonnables
- [ ] Gestion d'erreurs complète
- [ ] Fichiers réels stockés (pas exécutés)
- [ ] HTTPS activé
- [ ] Logs d'upload configurés
- [ ] Nettoyage des anciens fichiers programmé
- [ ] Tests de sécurité effectués

---

## 🚀 Optimisations avancées

### Stockage en base de données

```php
// Enregistrer les infos d'upload en base
$fileInfo = $uploader->upload($psr7File);

$db->insert('uploads', [
    'original_name' => $fileInfo->originalName,
    'saved_name' => $fileInfo->savedName,
    'file_path' => $fileInfo->fullPath,
    'mime_type' => $fileInfo->mimeType,
    'file_size' => $fileInfo->size,
    'uploaded_by' => $_SESSION['user_id'],
    'uploaded_at' => date('Y-m-d H:i:s'),
]);
```

### Stockage en cloud (futur)

```php
// sfUpload sera compatible avec d'autres Storage backends
// à l'avenir (S3, GCS, etc.)

use SfUpload\Storage\S3Storage;

$storage = new S3Storage('bucket-name', [
    'key' => 'AWS_KEY',
    'secret' => 'AWS_SECRET',
]);

$uploader = new Uploader($storage, $validator);
```

### Traitement d'image

```php
// Après upload
$fileInfo = $uploader->upload($psr7File);

// Traiter l'image (redimensionner, etc.)
if ($fileInfo->getFileType() === 'image') {
    $image = new ImageProcessor($fileInfo->fullPath);
    $image->resize(800, 600)->save();
    $image->createThumbnail(200, 200)->save();
}
```

---

## 📚 Ressources

- [README.md](README.md) - Documentation complète
- [QUICKSTART.md](QUICKSTART.md) - Guide rapide
- [ARCHITECTURE.md](ARCHITECTURE.md) - Architecture détaillée
- [examples/](examples/) - Exemples complets

---

## ✉️ Questions ?

Créez une issue sur GitHub pour toute question de sécurité ou de best practice.

---

Développé avec ❤️ par **fomadev**
