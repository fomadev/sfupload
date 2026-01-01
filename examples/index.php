<?php

declare(strict_types=1);

// Chargement de l'autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use SfUpload\Uploader;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;
use SfUpload\Validation\MimeTypeConstraint;
use SfUpload\Exception\UploadException;

// --- CONFIGURATION ---
$uploadDir = __DIR__ . '/uploads';

// Création du dossier s'il n'existe pas
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$message = '';
$status = '';

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['my_file'])) {
    try {
        // 1. Initialisation des composants
        $storage = new LocalStorage($uploadDir);
        $mimeConstraint = new MimeTypeConstraint(['image/jpeg', 'image/png', 'application/pdf']);
        $validator = new Validator(maxSize: 2 * 1024 * 1024, mimeConstraint: $mimeConstraint);
        $uploader = new Uploader($storage, $validator);

        // 2. BRIDGE PSR-7 (Strictement compatible PHP 8.1 / PSR-7 v2.0)
        $psr7File = new class($_FILES['my_file']) implements \Psr\Http\Message\UploadedFileInterface {
            public function __construct(private array $file) {}
            
            public function getStream(): \Psr\Http\Message\StreamInterface {
                return new class($this->file['tmp_name']) implements \Psr\Http\Message\StreamInterface {
                    public function __construct(private string $tmpName) {}
                    public function getMetadata(?string $key = null): mixed { 
                        $data = ['uri' => $this->tmpName];
                        return ($key === null) ? $data : ($data[$key] ?? null); 
                    }
                    public function close(): void {}
                    public function detach() { return null; }
                    public function getSize(): ?int { return null; }
                    public function tell(): int { return 0; }
                    public function eof(): bool { return true; }
                    public function isSeekable(): bool { return false; }
                    public function seek(int $offset, int $whence = SEEK_SET): void {}
                    public function rewind(): void {}
                    public function isWritable(): bool { return false; }
                    public function write(string $string): int { return 0; }
                    public function isReadable(): bool { return true; }
                    public function read(int $length): string { return ""; }
                    public function getContents(): string { return ""; }
                    public function __toString(): string { return ""; }
                };
            }

            public function moveTo(string $targetPath): void {
                if (!move_uploaded_file($this->file['tmp_name'], $targetPath)) {
                    throw new \RuntimeException("Échec du déplacement du fichier.");
                }
            }

            public function getSize(): ?int { return (int)$this->file['size']; }
            public function getError(): int { return (int)$this->file['error']; }
            public function getClientFilename(): ?string { return $this->file['name']; }
            public function getClientMediaType(): ?string { return $this->file['type']; }
        };

        // 3. L'UPLOAD
        $fileInfo = $uploader->upload($psr7File);

        $status = 'success';
        $message = "✅ <strong>Succès !</strong><br>Fichier : <code>{$fileInfo->savedName}</code>";

    } catch (UploadException $e) {
        $status = 'error';
        $message = "❌ <strong>Validation :</strong> " . $e->getMessage();
    } catch (\Exception $e) {
        $status = 'error';
        $message = "❌ <strong>Système :</strong> " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SfUpload Test</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; padding: 50px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        h1 { font-size: 22px; color: #1a73e8; margin-bottom: 20px; text-align: center; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success { background: #e6f4ea; color: #1e7e34; border: 1px solid #ceead6; }
        .error { background: #fce8e6; color: #d93025; border: 1px solid #fad2cf; }
        input[type="file"] { border: 2px dashed #dadce0; padding: 20px; width: 100%; box-sizing: border-box; border-radius: 8px; margin-bottom: 20px; }
        button { width: 100%; background: #1a73e8; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        button:hover { background: #1557b0; }
        code { font-weight: bold; color: #c2185b; }
    </style>
</head>
<body>

<div class="card">
    <h1>🛡️ sfupload v1.0.0</h1>
    
    <?php if ($message): ?>
        <div class="alert <?= $status ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Choisir un fichier (PNG, JPG, PDF - Max 2Mo)</label>
        <input type="file" name="my_file" required>
        <button type="submit">Uploader maintenant</button>
    </form>
</div>

</body>
</html>