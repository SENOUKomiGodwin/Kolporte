<?php

namespace Tests\Fakes;

use App\Services\ImageUploader;
use Illuminate\Http\UploadedFile;

class FakeImageUploader implements ImageUploader
{
    public array $envoyes = [];

    public function envoyer(UploadedFile $fichier, string $dossier): string
    {
        $this->envoyes[] = ['nom' => $fichier->getClientOriginalName(), 'dossier' => $dossier];

        return 'https://exemple.test/'.$dossier.'/'.uniqid().'.jpg';
    }
}
