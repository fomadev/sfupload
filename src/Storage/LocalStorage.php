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
        // On s'assure que le dossier existe et est accessible en écriture dès le départ
        if (!is_dir($this->uploadPath) || !is_writable($this->uploadPath)) {
            throw new UploadException("Le dossier de destination n'est pas valide ou n'a pas les permissions d'écriture.");
        }
        
        // Nettoyage du chemin (retrait du slash final s'il existe)
        $this->uploadPath = rtrim($this->uploadPath, DIRECTORY_SEPARATOR);
    }

    /**
     * Enregistre le fichier sur le disque avec un nom sécurisé.
     */
    public function store(UploadedFileInterface $file): string
    {
        $safeName = $this->generateSecureName($file);
        $targetPath = $this->uploadPath . DIRECTORY_SEPARATOR . $safeName;

        try {
            // Méthode standard PSR-7 pour déplacer le fichier
            $file->moveTo($targetPath);
        } catch (\Exception $e) {
            throw new UploadException("Impossible de déplacer le fichier vers sa destination finale : " . $e->getMessage());
        }

        return $safeName;
    }

    /**
     * Génère un nom unique et imprévisible.
     */
    private function generateSecureName(UploadedFileInterface $file): string
    {
        // On récupère l'extension d'origine proprement
        $originalName = $file->getClientFilename() ?? 'file.bin';
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        // Génération d'un hash aléatoire (32 caractères)
        $randomHash = bin2hex(random_bytes(16));

        // On retourne le nom : hash.extension (ex: a1b2c3d4...jpg)
        return $randomHash . ($extension ? '.' . $extension : '');
    }
}