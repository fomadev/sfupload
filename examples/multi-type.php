<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SfUpload\Configuration\UploadConfig;
use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Bridge\UploadedFileAdapter;
use SfUpload\Utility\FileHelper;
use SfUpload\Exception\UploadException;

/**
 * Exemple complet : Gestion multi-type avec validation stricte
 */

$uploadDir = __DIR__ . '/uploads/multi';
$message = '';
$status = '';
$selectedType = $_GET['type'] ?? 'all';

// Configuration par type
$configs = [
    'all' => ['label' => '📦 Tous les fichiers', 'config' => UploadConfig::any(50 * 1024 * 1024)],
    'images' => ['label' => '🖼️ Images uniquement', 'config' => UploadConfig::imageOnly(10 * 1024 * 1024)],
    'documents' => ['label' => '📄 Documents uniquement', 'config' => UploadConfig::documentOnly(20 * 1024 * 1024)],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $type = $_POST['type'] ?? 'all';
        if (!isset($configs[$type])) {
            throw new Exception('Type de fichier invalide');
        }

        $config = $configs[$type]['config'];
        $storage = new LocalStorage($uploadDir, true);
        $mimeConstraint = new MimeTypeConstraint($config->getAllowedMimes());
        $validator = new Validator($config->getMaxSize(), $mimeConstraint);
        $uploader = new Uploader($storage, $validator);

        $psr7File = UploadedFileAdapter::fromGlobal($_FILES['file']);
        $fileInfo = $uploader->upload($psr7File);

        $status = 'success';
        $message = "✅ {$fileInfo->originalName} uploadé ({$fileInfo->getFormattedSize()})";

    } catch (UploadException $e) {
        $status = 'error';
        $message = "⚠️ " . $e->getMessage();
    } catch (\Exception $e) {
        $status = 'error';
        $message = "❌ " . $e->getMessage();
    }
}

// Liste des fichiers uploadés
$uploadedFiles = [];
if (is_dir($uploadDir)) {
    $files = array_filter(scandir($uploadDir), fn($f) => !in_array($f, ['.', '..']));
    usort($files, fn($a, $b) => filemtime($uploadDir . '/' . $b) <=> filemtime($uploadDir . '/' . $a));
    $uploadedFiles = array_slice($files, 0, 10);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Multi-Type</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            padding: 40px 20px;
        }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 8px; }
        .content { padding: 40px; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 2px solid #f0f0f0;
        }
        .tab {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .tab.active { color: #667eea; border-bottom-color: #667eea; }
        form { display: none; }
        form.active { display: block; }
        input[type="file"] { display: block; margin: 20px 0; padding: 10px; border: 2px solid #667eea; border-radius: 6px; width: 100%; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        button:hover { background: #764ba2; }
        .file-list { margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0; }
        .file-item { padding: 12px; background: #f8f9fa; border-radius: 6px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .file-name { font-weight: 500; color: #333; }
        .file-meta { font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📂 Upload Multi-Type</h1>
        <p>Choisissez le type de fichier à uploader</p>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="alert <?= $status ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="tabs">
            <?php foreach ($configs as $key => $config): ?>
                <button class="tab <?= $selectedType === $key ? 'active' : '' ?>" onclick="switchTab('<?= $key ?>')">
                    <?= $config['label'] ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($configs as $key => $config): ?>
            <form method="POST" enctype="multipart/form-data" class="<?= $selectedType === $key ? 'active' : '' ?>">
                <input type="hidden" name="type" value="<?= $key ?>">
                <label>Sélectionner un fichier</label>
                <input type="file" name="file" required>
                <button type="submit">Uploader</button>
            </form>
        <?php endforeach; ?>

        <?php if (!empty($uploadedFiles)): ?>
            <div class="file-list">
                <h3>📋 Derniers uploads</h3>
                <?php foreach ($uploadedFiles as $file): ?>
                    <div class="file-item">
                        <div>
                            <div class="file-name"><?= htmlspecialchars($file) ?></div>
                            <div class="file-meta">
                                Type: <?= FileHelper::getFileType($file) ?> | 
                                Taille: <?= FileHelper::formatFileSize(filesize($uploadDir . '/' . $file)) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('form').forEach(f => f.classList.remove('active'));
        event.target.classList.add('active');
        document.querySelector(`form[data-type="${tab}"]`)?.classList.add('active') || 
        Array.from(document.querySelectorAll('form')).find(f => 
            f.querySelector(`input[value="${tab}"]`)
        )?.classList.add('active');
        
        window.history.pushState({}, '', `?type=${tab}`);
    }
</script>
</body>
</html>
