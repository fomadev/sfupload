<?php

declare(strict_types=1);

namespace SfUpload\Utility;

/**
 * Classe helper pour manipuler les fichiers uploadés
 */
class FileHelper
{
    /**
     * Convertit les bytes en format lisible (B, KB, MB, GB)
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Détermine le type de fichier basé sur l'extension
     */
    public static function getFileType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'pdf', 'doc', 'docx', 'xls', 'xlsx' => 'document',
            'mp3', 'wav', 'flac' => 'audio',
            'mp4', 'avi', 'mov' => 'video',
            'zip', 'rar', '7z' => 'archive',
            default => 'file'
        };
    }

    /**
     * Génère un chemin sécurisé sans traversée de répertoires
     */
    public static function sanitizePath(string $path): string
    {
        return str_replace(['..', '\\', "\0"], '', $path);
    }

    /**
     * Vérifie si un fichier existe (utile pour les statistiques)
     */
    public static function fileExists(string $filePath): bool
    {
        return is_file($filePath) && is_readable($filePath);
    }

    /**
     * Récupère les infos détaillées d'un fichier uploadé
     */
    public static function getFileStats(string $filePath): ?array
    {
        if (!self::fileExists($filePath)) {
            return null;
        }

        return [
            'size' => filesize($filePath),
            'size_formatted' => self::formatFileSize(filesize($filePath)),
            'modified' => filemtime($filePath),
            'type' => self::getFileType($filePath),
            'permission' => substr(sprintf('%o', fileperms($filePath)), -4),
        ];
    }
}
