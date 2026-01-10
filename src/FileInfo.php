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

    /**
     * Retourne le type de fichier (image, document, video, etc.)
     */
    public function getFileType(): string
    {
        return \SfUpload\Utility\FileHelper::getFileType($this->savedName);
    }

    /**
     * Retourne la taille formatée (B, KB, MB, etc.)
     */
    public function getFormattedSize(): string
    {
        return \SfUpload\Utility\FileHelper::formatFileSize($this->size);
    }

    /**
     * Vérifie si le fichier existe toujours
     */
    public function exists(): bool
    {
        return \SfUpload\Utility\FileHelper::fileExists($this->fullPath);
    }

    /**
     * Récupère les statistiques du fichier
     */
    public function getStats(): ?array
    {
        return \SfUpload\Utility\FileHelper::getFileStats($this->fullPath);
    }
}