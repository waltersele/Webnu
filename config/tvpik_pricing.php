<?php

return [

    /*
    | Tarifa TVPik Profesional (paridad con tvpik.es).
    | Precio unitario según el total de pantallas en la suscripción (2–20).
    | Ejemplos: 3×8 € = 24 € · 5×7 € = 35 € · 10×6 € = 60 €
    */
    'min_professional_screens' => 2,
    'max_screens' => 20,

  'tiers' => [
        ['min' => 2, 'max' => 3, 'rate_eur' => 8],
        ['min' => 4, 'max' => 5, 'rate_eur' => 7],
        ['min' => 6, 'max' => 20, 'rate_eur' => 6],
    ],

    /*
    | Descuento en la parte TVPik al facturar anual (misma regla que TVPik).
    */
    'annual_discount' => 0.20,

    /*
    | Más de max_screens → plan Franquicias (contacto).
    */
    'franchise_contact_url' => 'mailto:hola@webnu.es?subject=TVPik%20franquicias',

];
