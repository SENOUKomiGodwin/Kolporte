<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryImageUploader implements ImageUploader
{
    public function envoyer(UploadedFile $fichier, string $dossier): string
    {
        $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

        $resultat = $cloudinary->uploadApi()->upload($fichier->getRealPath(), [
            'folder' => 'kolporte/'.$dossier,
        ]);

        return $resultat['secure_url'];
    }
}
