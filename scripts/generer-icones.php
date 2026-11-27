<?php

/**
 * Génère les icônes PWA Kolporte (carré vert #0F5C45 + lien blanc).
 * Usage : php scripts/generer-icones.php
 */
$vert = [0x0F, 0x5C, 0x45];
$blanc = [0xFF, 0xFF, 0xFF];
$sortie = __DIR__.'/../public/icons';

if (! is_dir($sortie)) {
    mkdir($sortie, 0755, true);
}

/** Dessine le glyphe « lien » : deux anneaux blancs + barre diagonale. */
function dessinerLien($img, int $x0, int $y0, int $cote, int $epaisseur, $couleurBlanc, $couleurVert): void
{
    $rayonLien = (int) ($cote * 0.16);
    $centre1x = $x0 + $cote * 0.34;
    $centre1y = $y0 + $cote * 0.34;
    $centre2x = $x0 + $cote * 0.66;
    $centre2y = $y0 + $cote * 0.66;

    imageline($img, (int) $centre1x, (int) $centre1y, (int) $centre2x, (int) $centre2y, $couleurBlanc);
    imageline($img, (int) $centre1x + $epaisseur, (int) $centre1y, (int) $centre2x + $epaisseur, (int) $centre2y, $couleurBlanc);

    foreach ([[$centre1x, $centre1y], [$centre2x, $centre2y]] as [$cx, $cy]) {
        imagefilledellipse($img, (int) $cx, (int) $cy, 2 * $rayonLien, 2 * $rayonLien, $couleurBlanc);
        imagefilledellipse($img, (int) $cx, (int) $cy, 2 * ($rayonLien - $epaisseur), 2 * ($rayonLien - $epaisseur), $couleurVert);
    }
}

/** Icône classique : carré vert arrondi sur fond transparent. */
function genererIcone(int $taille, string $fichier, array $vert, array $blanc): void
{
    $img = imagecreatetruecolor($taille, $taille);
    imagealphablending($img, true);
    imagesavealpha($img, true);

    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    $couleurVert = imagecolorallocate($img, $vert[0], $vert[1], $vert[2]);
    $couleurBlanc = imagecolorallocate($img, $blanc[0], $blanc[1], $blanc[2]);

    // Le glyphe est dessiné dans la couleur du fond : anneaux = vert, trou = transparent
    $trou = imagecolorallocatealpha($img, 0, 0, 0, 127);

    $cote = (int) ($taille * 0.82);
    $x0 = (int) (($taille - $cote) / 2);
    $y0 = $x0;
    $rayon = (int) ($cote * 0.18);

    // Carré arrondi vert
    imagefilledrectangle($img, $x0 + $rayon, $y0, $x0 + $cote - $rayon, $y0 + $cote, $couleurVert);
    imagefilledrectangle($img, $x0, $y0 + $rayon, $x0 + $cote, $y0 + $cote - $rayon, $couleurVert);
    imagefilledellipse($img, $x0 + $rayon, $y0 + $rayon, 2 * $rayon, 2 * $rayon, $couleurVert);
    imagefilledellipse($img, $x0 + $cote - $rayon, $y0 + $rayon, 2 * $rayon, 2 * $rayon, $couleurVert);
    imagefilledellipse($img, $x0 + $rayon, $y0 + $cote - $rayon, 2 * $rayon, 2 * $rayon, $couleurVert);
    imagefilledellipse($img, $x0 + $cote - $rayon, $y0 + $cote - $rayon, 2 * $rayon, 2 * $rayon, $couleurVert);

    $epaisseur = max(4, (int) ($cote * 0.11));
    $rayonLien = (int) ($cote * 0.16);
    $centre1x = $x0 + $cote * 0.34;
    $centre1y = $y0 + $cote * 0.34;
    $centre2x = $x0 + $cote * 0.66;
    $centre2y = $y0 + $cote * 0.66;

    imageline($img, (int) $centre1x, (int) $centre1y, (int) $centre2x, (int) $centre2y, $couleurBlanc);
    imageline($img, (int) $centre1x + $epaisseur, (int) $centre1y, (int) $centre2x + $epaisseur, (int) $centre2y, $couleurBlanc);

    foreach ([[$centre1x, $centre1y], [$centre2x, $centre2y]] as [$cx, $cy]) {
        imagefilledellipse($img, (int) $cx, (int) $cy, 2 * $rayonLien, 2 * $rayonLien, $couleurBlanc);
        imagefilledellipse($img, (int) $cx, (int) $cy, 2 * ($rayonLien - $epaisseur), 2 * ($rayonLien - $epaisseur), $trou);
    }

    imagepng($img, $fichier, 9);
    imagedestroy($img);

    echo "Générée : {$fichier}\n";
}

/** Icône maskable : fond vert plein, glyphe dans la zone sûre (~80 %). */
function genererMaskable(int $taille, string $fichier, array $vert, array $blanc): void
{
    $img = imagecreatetruecolor($taille, $taille);
    imagealphablending($img, true);

    $couleurVert = imagecolorallocate($img, $vert[0], $vert[1], $vert[2]);
    $couleurBlanc = imagecolorallocate($img, $blanc[0], $blanc[1], $blanc[2]);
    imagefill($img, 0, 0, $couleurVert);

    // Zone sûre : 80 % du canvas, glyphe « lien » blanc
    $cote = (int) ($taille * 0.8);
    $x0 = (int) (($taille - $cote) / 2);
    $y0 = $x0;
    $epaisseur = max(4, (int) ($cote * 0.11));
    $rayonLien = (int) ($cote * 0.16);
    $centre1x = $x0 + $cote * 0.34;
    $centre1y = $y0 + $cote * 0.34;
    $centre2x = $x0 + $cote * 0.66;
    $centre2y = $y0 + $cote * 0.66;

    // Barre + anneaux en vert sur fond vert : on dessine le glyphe en « creux »
    // → anneaux verts cerclés de blanc fin pour rester visible
    foreach ([[$centre1x, $centre1y], [$centre2x, $centre2y]] as [$cx, $cy]) {
        imagefilledellipse($img, (int) $cx, (int) $cy, 2 * $rayonLien, 2 * $rayonLien, $couleurBlanc);
        imagefilledellipse($img, (int) $cx, (int) $cy, 2 * ($rayonLien - $epaisseur), 2 * ($rayonLien - $epaisseur), $couleurVert);
    }
    imageline($img, (int) $centre1x, (int) $centre1y, (int) $centre2x, (int) $centre2y, $couleurBlanc);
    imageline($img, (int) $centre1x + $epaisseur, (int) $centre1y, (int) $centre2x + $epaisseur, (int) $centre2y, $couleurBlanc);

    imagepng($img, $fichier, 9);
    imagedestroy($img);

    echo "Générée : {$fichier}\n";
}

genererIcone(192, $sortie.'/icon-192.png', $vert, $blanc);
genererIcone(512, $sortie.'/icon-512.png', $vert, $blanc);
genererMaskable(512, $sortie.'/icon-maskable-512.png', $vert, $blanc);
