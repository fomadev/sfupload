<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Bridge\UploadedFileAdapter;
use SfUpload\Utility\FileHelper;
use SfUpload\Exception\UploadException;

$uploadDir = __DIR__ . '/uploads';
$message = '';
$status = '';
$fileInfo = null;
$uploadedFiles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        // Configuration avec FileHelper pour afficher les infos
        $storage = new LocalStorage($uploadDir, true);
        $mimeConstraint = new MimeTypeConstraint(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
        $validator = new Validator(5 * 1024 * 1024, $mimeConstraint);
        $uploader = new Uploader($storage, $validator);

        $psr7File = UploadedFileAdapter::fromGlobal($_FILES['file']);
        $fileInfo = $uploader->upload($psr7File);

        $status = 'success';
        $message = "✅ Fichier uploadé avec succès !";

    } catch (UploadException $e) {
        $status = 'error';
        $message = "⚠️ " . $e->getMessage();
    } catch (\Exception $e) {
        $status = 'error';
        $message = "❌ Erreur : " . $e->getMessage();
    }
}

// Récupération des fichiers avec statistiques
if (is_dir($uploadDir)) {
    $files = array_filter(scandir($uploadDir), fn($f) => !in_array($f, ['.', '..']));
    usort($files, fn($a, $b) => filemtime($uploadDir . '/' . $b) <=> filemtime($uploadDir . '/' . $a));
    
    foreach (array_slice($files, 0, 15) as $file) {
        $uploadedFiles[] = [
            'name' => $file,
            'stats' => FileHelper::getFileStats($uploadDir . '/' . $file),
            'type' => FileHelper::getFileType($file)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SfUpload - Exemple Simple</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 700px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 8px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content { padding: 40px; }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            animation: slideIn 0.3s ease-out;
        }
        .alert.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .form-group { margin-bottom: 24px; }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            background: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #667eea;
            font-weight: 500;
        }
        .file-input-label:hover {
            background: #f0f2ff;
            border-color: #764ba2;
        }
        input[type="file"] { display: none; }
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: all 0.3s ease;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4); }
        .file-list { margin-top: 30px; padding-top: 30px; border-top: 2px solid #f0f0f0; }
        .file-list h3 { color: #333; margin-bottom: 16px; }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .file-name { color: #667eea; font-weight: 500; flex: 1; }
        .file-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #667eea;
            color: white;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 8px;
        }
        .file-size { color: #999; margin-left: 10px; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛡️ sfUpload</h1>
        <p>Téléchargez vos fichiers en sécurité (Images et PDF)</p>
    </div>

    <div class="content">
        <?php if ($message): ?>
            <div class="alert <?= $status ?>">
                <?= htmlspecialchars($message) ?>
                <?php if ($fileInfo && $status === 'success'): ?>
                    <br><small>📄 <?= htmlspecialchars($fileInfo->originalName) ?> (<?= $fileInfo->getFormattedSize() ?>)</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Sélectionner un fichier</label>
                <label class="file-input-label" for="file">
                    📁 Cliquez ou déposez votre fichier
                </label>
                <input type="file" id="file" name="file" required accept="image/*,.pdf">
            </div>
            <button type="submit" class="btn">Télécharger</button>
        </form>

        <?php if (!empty($uploadedFiles)): ?>
            <div class="file-list">
                <h3>📂 Fichiers récents (<?= count($uploadedFiles) ?>)</h3>
                <?php foreach ($uploadedFiles as $file): ?>
                    <div class="file-item">
                        <span class="file-name"><?= htmlspecialchars($file['name']) ?></span>
                        <span class="file-badge"><?= $file['type'] ?></span>
                        <span class="file-size"><?= $file['stats']['size_formatted'] ?? 'N/A' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const fileInput = document.getElementById('file');
    const fileLabel = document.querySelector('.file-input-label');
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            fileLabel.textContent = `✓ ${file.name}`;
            fileLabel.style.color = '#28a745';
        }
    });
    
    fileLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileLabel.style.background = '#e8f5e9';
    });
    fileLabel.addEventListener('dragleave', () => {
        fileLabel.style.background = '#f8f9ff';
    });
    fileLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        fileInput.files = e.dataTransfer.files;
        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        fileLabel.style.background = '#f8f9ff';
    });
</script>
</body>
</html>
