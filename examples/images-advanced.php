<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SfUpload\Configuration\UploadConfig;
use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Bridge\UploadedFileAdapter;
use SfUpload\Exception\UploadException;

/**
 * Exemple avancé : Configuration prédéfinie pour images
 */

$uploadDir = __DIR__ . '/uploads/images';
$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        // Utilisation de la configuration prédéfinie pour images
        $config = UploadConfig::imageOnly(10 * 1024 * 1024);
        
        $storage = new LocalStorage($uploadDir, $config->shouldCreateMissingDir());
        $mimeConstraint = new MimeTypeConstraint($config->getAllowedMimes());
        $validator = new Validator($config->getMaxSize(), $mimeConstraint);
        $uploader = new Uploader($storage, $validator);

        $psr7File = UploadedFileAdapter::fromGlobal($_FILES['image']);
        $fileInfo = $uploader->upload($psr7File);

        $status = 'success';
        $message = "✅ Image uploadée avec succès ! ({$fileInfo->getFormattedSize()})";

    } catch (UploadException $e) {
        $status = 'error';
        $message = "⚠️ " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload d'Images - Configuration Avancée</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 30px; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        input[type="file"] { display: block; margin: 20px 0; padding: 10px; border: 2px solid #667eea; border-radius: 6px; width: 100%; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        button:hover { background: #764ba2; }
        .info { background: #f8f9ff; padding: 16px; border-radius: 6px; margin-top: 20px; font-size: 14px; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <h1>🖼️ Upload d'Images</h1>
    <p class="subtitle">Configuration prédéfinie avec limite 10 Mo</p>

    <?php if ($message): ?>
        <div class="alert <?= $status ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Sélectionner une image (JPEG, PNG, WebP, GIF)</label>
        <input type="file" name="image" required accept="image/*">
        <button type="submit">Uploader l'image</button>
    </form>

    <div class="info">
        <strong>Configuration :</strong><br>
        • Formats : JPEG, PNG, WebP, GIF<br>
        • Taille max : 10 Mo<br>
        • Dossier : uploads/images
    </div>
</div>
</body>
</html>
