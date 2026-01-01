<?php

declare(strict_types=1);

namespace SfUpload;

use Psr\Http\Message\UploadedFileInterface;
use SfUpload\Exception\UploadException;
use SfUpload\Storage\LocalStorage;
use SfUpload\Validation\Validator;

class Uploader
{
    public function __construct(
        private LocalStorage $storage,
        private Validator $validator
    ) {}

    /**
     * La méthode principale pour uploader un fichier en toute sécurité.
     */
    public function upload(UploadedFileInterface $file): string
    {
        // 1. On vérifie d'abord les erreurs natives d'upload (PHP)
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new UploadException("Erreur d'upload native PHP code: " . $file->getError());
        }

        // 2. On passe le fichier au validateur (MIME, Taille, etc.)
        if (!$this->validator->validate($file)) {
            throw new UploadException("Validation échouée : " . implode(', ', $this->validator->getErrors()));
        }

        // 3. On demande au storage d'enregistrer le fichier
        // Le storage s'occupera du renommage sécurisé
        return $this->storage->store($file);
    }
}