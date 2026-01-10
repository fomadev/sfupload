<?php

declare(strict_types=1);

namespace SfUpload\Configuration;

use SfUpload\Validation\MimeTypeConstraint;

/**
 * Configuration centralisée pour les uploads
 * Permet de configurer facilement les paramètres de sécurité
 */
class UploadConfig
{
    private int $maxSize;
    private array $allowedMimes = [];
    private bool $createMissingDir = true;

    public function __construct(
        int $maxSize = 5 * 1024 * 1024,
        array $allowedMimes = [],
        bool $createMissingDir = true
    ) {
        $this->maxSize = $maxSize;
        $this->allowedMimes = $allowedMimes;
        $this->createMissingDir = $createMissingDir;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    public function setMaxSize(int $maxSize): self
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function getAllowedMimes(): array
    {
        return $this->allowedMimes;
    }

    public function setAllowedMimes(array $mimes): self
    {
        $this->allowedMimes = $mimes;
        return $this;
    }

    public function shouldCreateMissingDir(): bool
    {
        return $this->createMissingDir;
    }

    public function setCreateMissingDir(bool $create): self
    {
        $this->createMissingDir = $create;
        return $this;
    }

    public static function imageOnly(int $maxSize = 10 * 1024 * 1024): self
    {
        return new self(
            $maxSize,
            ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
        );
    }

    public static function documentOnly(int $maxSize = 20 * 1024 * 1024): self
    {
        return new self(
            $maxSize,
            ['application/pdf', 'application/msword', 'application/vnd.ms-excel']
        );
    }

    public static function any(int $maxSize = 50 * 1024 * 1024): self
    {
        return new self($maxSize, []);
    }
}
