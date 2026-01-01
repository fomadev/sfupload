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
     * Exécute le processus complet d'upload sécurisé.
     * * @param UploadedFileInterface $file Le fichier issu d'une requête PSR-7
     * @return FileInfo Un objet contenant les détails du fichier sauvegardé
     * @throws UploadException En cas d'erreur de validation ou de stockage
     */
    public function upload(UploadedFileInterface $file): FileInfo
    {
        // 1. Vérification des erreurs natives de PHP/Serveur
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new UploadException($this->getErrorMessage($file->getError()));
        }

        // 2. Validation stricte (Taille et Type MIME réel)
        if (!$this->validator->validate($file)) {
            $errors = implode(' ', $this->validator->getErrors());
            throw new UploadException("Validation échouée : " . $errors);
        }

        // 3. Sauvegarde physique du fichier
        // LocalStorage génère un nom aléatoire et déplace le fichier
        $savedName = $this->storage->store($file);

        // 4. Construction de l'objet de réponse FileInfo
        return new FileInfo(
            originalName: $file->getClientFilename() ?? 'unknown',
            savedName: $savedName,
            fullPath: $this->storage->getUploadPath() . DIRECTORY_SEPARATOR . $savedName,
            mimeType: $file->getClientMediaType() ?? 'application/octet-stream',
            size: $file->getSize() ?? 0
        );
    }

    /**
     * Traduction des codes d'erreur natifs de PHP.
     */
    private function getErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la limite 'upload_max_filesize' du serveur.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la limite définie dans le formulaire HTML.",
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé.",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé.",
            default => "Une erreur inconnue est survenue lors de l'upload."
        };
    }
}