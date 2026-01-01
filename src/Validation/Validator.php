<?php

declare(strict_types=1);

namespace SfUpload\Validation;

use Psr\Http\Message\UploadedFileInterface;

class Validator
{
    private array $errors = [];

    public function __construct(
        private int $maxSize, // Taille en octets
        private array $allowedMimeTypes = [] // Ex: ['image/jpeg', 'image/png']
    ) {}

    /**
     * Valide le fichier selon les règles définies.
     */
    public function validate(UploadedFileInterface $file): bool
    {
        $this->errors = []; // Reset des erreurs

        $this->validateSize($file);
        $this->validateMimeType($file);

        return empty($this->errors);
    }

    /**
     * Vérifie la taille du fichier.
     */
    private function validateSize(UploadedFileInterface $file): void
    {
        if ($file->getSize() > $this->maxSize) {
            $this->errors[] = "Le fichier est trop volumineux (max: " . ($this->maxSize / 1024 / 1024) . " Mo).";
        }
    }

    /**
     * Vérifie le contenu réel du fichier (MIME Type).
     */
    private function validateMimeType(UploadedFileInterface $file): void
    {
        if (empty($this->allowedMimeTypes)) {
            return;
        }

        // Récupération sécurisée du type MIME réel
        $realMimeType = $this->detectRealMimeType($file);

        if (!in_array($realMimeType, $this->allowedMimeTypes, true)) {
            $this->errors[] = "Type de fichier non autorisé : $realMimeType.";
        }
    }

    /**
     * Utilise finfo pour lire le contenu réel du fichier.
     */
    private function detectRealMimeType(UploadedFileInterface $file): string
    {
        // Pour PSR-7, on récupère le flux temporaire
        $stream = $file->getStream();
        $uri = $stream->getMetadata('uri');

        if (empty($uri)) {
            // Si l'URI n'est pas disponible, on se rabat sur l'info fournie par le client
            // (moins sécurisé, mais nécessaire pour certains environnements de test)
            return $file->getClientMediaType() ?? 'application/octet-stream';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($uri) ?: 'application/octet-stream';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}