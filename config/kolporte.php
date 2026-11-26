<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paramètres métier Kolporte
    |--------------------------------------------------------------------------
    |
    | Minimum de retrait en FCFA, nombre de jours de validité d'un lien
    | d'affiliation et commission prélevée par la plateforme (0 = aucune).
    |
    */

    'retrait_minimum' => (int) env('KOLPORTE_RETRAIT_MINIMUM', 5000),

    'validite_lien_jours' => (int) env('KOLPORTE_VALIDITE_LIEN_JOURS', 7),

    'commission_plateforme' => (float) env('KOLPORTE_COMMISSION_PLATEFORME', 0),

    /*
    | Mode de paiement : « simule » (sandbox locale) ou « fedapay ».
    */
    'paiement' => env('KOLPORTE_PAIEMENT', 'simule'),

    /*
    | Clé API et environnement FedaPay (sandbox|live). Jamais en dur.
    */
    'fedapay' => [
        'secret' => env('FEDAPAY_SECRET_KEY'),
        'env' => env('FEDAPAY_ENV', 'sandbox'),
    ],

];
