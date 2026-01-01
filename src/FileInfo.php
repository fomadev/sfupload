<?php

declare(strict_types=1);

namespace SfUpload;

/**
 * Cet objet représente le résultat d'un upload réussi.
 */
readonly class FileInfo
{
    public function __construct(
        public string $originalName,
        public string $savedName,
        public string $fullPath,
        public string $mimeType,
        public int $size
    ) {}

    public function getExtension(): string
    {
        return pathinfo($this->savedName, PATHINFO_EXTENSION);
    }
}