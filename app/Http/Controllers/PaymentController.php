<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Endpoints de checkout legacy (alta+pago sin onboarding freemium).
 * Desactivados: usar /register + Ajustes → Plan / Stripe portal.
 */
class PaymentController extends Controller
{
    /**
     * @deprecated
     */
    public function pay_product(Request $request)
    {
        abort(410, 'Este endpoint de pago ya no está disponible. Regístrate en /register y elige plan en Ajustes.');
    }

    /**
     * @deprecated
     */
    public function process_subscription(Request $request)
    {
        abort(410, 'Este checkout legacy está desactivado. Regístrate gratis y activa Pro/Plus desde Ajustes → Plan.');
    }
}
