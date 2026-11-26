<?php

namespace App\Http\Controllers\Marchand;

use App\Http\Controllers\Controller;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoutiqueController extends Controller
{
    public function edit(Request $request): View
    {
        return view('marchand.boutique.form', [
            'boutique' => $request->user()->boutiques()->first(),
        ]);
    }

    public function update(Request $request, ImageUploader $uploader): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $boutique = $user->boutiques()->firstOrNew();
        $boutique->marchand_id = $user->id;
        $boutique->nom = $donnees['nom'];
        $boutique->description = $donnees['description'] ?? null;

        if ($request->hasFile('logo')) {
            $boutique->logo_url = $uploader->envoyer($request->file('logo'), 'logos');
        }

        $boutique->save();

        return redirect()->route('marchand')->with('statut', 'Boutique enregistrée.');
    }
}
