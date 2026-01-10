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
 * Exemple API : Upload via requête AJAX
 * Retourne JSON pour intégration frontend
 */

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $uploadDir = __DIR__ . '/uploads/api';
        
        $config = UploadConfig::any(50 * 1024 * 1024);
        $storage = new LocalStorage($uploadDir, true);
        $validator = new Validator($config->getMaxSize(), new MimeTypeConstraint([]));
        $uploader = new Uploader($storage, $validator);

        $psr7File = UploadedFileAdapter::fromGlobal($_FILES['file']);
        $fileInfo = $uploader->upload($psr7File);

        $response['success'] = true;
        $response['message'] = 'Fichier uploadé avec succès';
        $response['data'] = [
            'original_name' => $fileInfo->originalName,
            'saved_name' => $fileInfo->savedName,
            'size' => $fileInfo->size,
            'size_formatted' => $fileInfo->getFormattedSize(),
            'type' => $fileInfo->getFileType(),
            'mime_type' => $fileInfo->mimeType,
            'extension' => $fileInfo->getExtension(),
        ];

    } catch (UploadException $e) {
        $response['message'] = 'Erreur de validation : ' . $e->getMessage();
    } catch (\Exception $e) {
        $response['message'] = 'Erreur système : ' . $e->getMessage();
    }
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
