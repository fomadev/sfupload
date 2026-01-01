<?php

declare(strict_types=1);

namespace SfUpload\Validation;

use Psr\Http\Message\UploadedFileInterface;

class MimeTypeConstraint
{
    public function __construct(
        private array $allowedTypes
    ) {}

    /**
     * Vérifie si le type MIME du fichier est dans la liste autorisée.
     */
    public function isValid(UploadedFileInterface $file): bool
    {
        if (empty($this->allowedTypes)) {
            return true;
        }

        $realMime = $this->detectMimeType($file);
        return in_array($realMime, $this->allowedTypes, true);
    }

    private function detectMimeType(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();
        $uri = $stream->getMetadata('uri');

        // Si on ne peut pas accéder au fichier temporaire, on utilise l'info client
        if (!$uri || !file_exists($uri)) {
            return $file->getClientMediaType() ?? 'application/octet-stream';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($uri) ?: 'application/octet-stream';
    }

    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }
}