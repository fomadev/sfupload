<?php

declare(strict_types=1);

namespace SfUpload\Bridge;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Convertisseur simple de $_FILES en PSR-7 UploadedFileInterface
 * Élimine le besoin d'implémentation manuelle complexe
 */
class UploadedFileAdapter implements UploadedFileInterface
{
    private array $file;

    private function __construct(array $file)
    {
        $this->file = $file;
    }

    /**
     * Factory method pour créer un UploadedFileAdapter à partir de $_FILES
     */
    public static function fromGlobal(array $phpFile): self
    {
        return new self($phpFile);
    }

    public function getStream(): StreamInterface
    {
        return new class($this->file['tmp_name'] ?? '') implements StreamInterface {
            public function __construct(private string $path) {}

            public function getMetadata(?string $key = null): mixed
            {
                $metadata = ['uri' => $this->path];
                return $key === null ? $metadata : ($metadata[$key] ?? null);
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

    public function moveTo(string $targetPath): void
    {
        if (!move_uploaded_file($this->file['tmp_name'] ?? '', $targetPath)) {
            throw new \RuntimeException("Impossible de déplacer le fichier vers : $targetPath");
        }
    }

    public function getSize(): ?int
    {
        return isset($this->file['size']) ? (int)$this->file['size'] : null;
    }

    public function getError(): int
    {
        return isset($this->file['error']) ? (int)$this->file['error'] : UPLOAD_ERR_NO_FILE;
    }

    public function getClientFilename(): ?string
    {
        return $this->file['name'] ?? null;
    }

    public function getClientMediaType(): ?string
    {
        return $this->file['type'] ?? null;
    }
}
