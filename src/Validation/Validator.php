<?php

declare(strict_types=1);

namespace SfUpload\Validation;

use Psr\Http\Message\UploadedFileInterface;

class Validator
{
    private array $errors = [];

    /**
     * @param int $maxSize Taille maximum en octets.
     * @param MimeTypeConstraint $mimeConstraint L'objet gérant la vérification du type MIME.
     */
    public function __construct(
        private int $maxSize,
        private MimeTypeConstraint $mimeConstraint
    ) {}

    /**
     * Valide le fichier selon les règles.
     */
    public function validate(UploadedFileInterface $file): bool
    {
        $this->errors = []; // Reset pour chaque validation

        // 1. Validation de la taille
        $this->validateSize($file);

        // 2. Validation du type MIME via la contrainte dédiée
        if (!$this->mimeConstraint->isValid($file)) {
            $this->errors[] = "Le type de fichier n'est pas autorisé ou est malveillant.";
        }

        return empty($this->errors);
    }

    /**
     * Vérifie la taille du fichier.
     */
    private function validateSize(UploadedFileInterface $file): void
    {
        if ($file->getSize() > $this->maxSize) {
            $maxMo = round($this->maxSize / 1024 / 1024, 2);
            $this->errors[] = "Le fichier est trop volumineux (max: {$maxMo} Mo).";
        }
    }

    /**
     * Récupère la liste des erreurs.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}