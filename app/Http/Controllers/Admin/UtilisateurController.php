<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));

        $utilisateurs = User::query()
            ->with('portefeuille')
            ->when($recherche !== '', fn ($q) => $q->where(function ($s) use ($recherche) {
                $s->where('name', 'like', "%{$recherche}%")
                    ->orWhere('email', 'like', "%{$recherche}%")
                    ->orWhere('telephone', 'like', "%{$recherche}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.utilisateurs', [
            'utilisateurs' => $utilisateurs,
            'recherche' => $recherche,
        ]);
    }
}
