<?php

namespace App\Http\Controllers\Promoteur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiensController extends Controller
{
    public function index(Request $request): View
    {
        $liens = $request->user()->liens()
            ->with(['article.boutique', 'promoteur'])
            ->latest()
            ->get()
            ->sortByDesc(fn ($lien) => $lien->gains())
            ->values();

        return view('promoteur.liens', ['liens' => $liens]);
    }
}
