<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

interface ImageUploader
{
    /** Envoie un fichier vers le stockage d'images et retourne son URL publique. */
    public function envoyer(UploadedFile $fichier, string $dossier): string;
}
