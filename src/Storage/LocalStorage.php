<?php

declare(strict_types=1);

namespace SfUpload\Storage;

use Psr\Http\Message\UploadedFileInterface;
use SfUpload\Exception\UploadException;

class LocalStorage
{
    /**
     * @param string $uploadPath Le chemin absolu vers le dossier de stockage
     */
    public function __construct(
        private string $uploadPath
    ) {
        // 1. On vérifie si le dossier existe, sinon on tente de le créer (optionnel mais pratique)
        if (!is_dir($this->uploadPath)) {
            throw new UploadException("Le dossier de destination n'existe pas : {$this->uploadPath}");
        }

        // 2. On s'assure que le dossier est accessible en écriture
        if (!is_writable($this->uploadPath)) {
            throw new UploadException("Le dossier de destination n'a pas les permissions d'écriture.");
        }
        
        // 3. Nettoyage du chemin (retrait du slash final s'il existe)
        $this->uploadPath = rtrim($this->uploadPath, DIRECTORY_SEPARATOR);
    }

    /**
     * Retourne le chemin du dossier d'upload.
     * Cette méthode est utilisée par la classe Uploader.
     */
    public function getUploadPath(): string
    {
        return $this->uploadPath;
    }

    /**
     * Enregistre le fichier sur le disque avec un nom sécurisé.
     */
    public function store(UploadedFileInterface $file): string
    {
        $safeName = $this->generateSecureName($file);
        $targetPath = $this->uploadPath . DIRECTORY_SEPARATOR . $safeName;

        try {
            // Déplacement du fichier temporaire vers la destination finale
            $file->moveTo($targetPath);
        } catch (\Exception $e) {
            throw new UploadException("Erreur lors du déplacement du fichier : " . $e->getMessage());
        }

        return $safeName;
    }

    /**
     * Génère un nom unique, aléatoire et imprévisible.
     */
    private function generateSecureName(UploadedFileInterface $file): string
    {
        // On extrait l'extension à partir du nom d'origine
        $originalName = $file->getClientFilename() ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Génération d'un jeton aléatoire de 32 caractères (16 bytes)
        $randomHash = bin2hex(random_bytes(16));

        // Retourne : hash.extension ou juste le hash s'il n'y a pas d'extension
        return $randomHash . ($extension ? '.' . $extension : '');
    }
}