<?php

return [
    /*
    | Seuils réglementaires / opérationnels par grandeur [min, max].
    | Utilisés pour les indicateurs de santé, la détection d'anomalies et le score de risque.
    */
    'seuils' => [
        'debit' => [60, 260],
        'pression' => [2.0, 6.0],
        'niveau' => [20, 95],
        'turbidite' => [0, 4],
        'ph' => [6.5, 8.5],
        'temperature' => [5, 25],
    ],

    'anomalies' => [
        'methode' => env('ANOMALIES_METHODE', 'zscore'), // zscore | moyenne_mobile
        'seuil_z' => (float) env('ANOMALIES_SEUIL_Z', 3.0),
        'fenetre_jours' => (int) env('ANOMALIES_FENETRE_JOURS', 30),
        'min_points' => 20,
        'fenetre_mobile' => 12, // nb de relevés pour la moyenne mobile
        'ecart_mobile' => 0.35, // écart relatif toléré vs moyenne mobile
    ],

    'maintenance' => [
        'rappel_jours' => (int) env('MAINTENANCE_RAPPEL_JOURS', 3),
    ],
];
