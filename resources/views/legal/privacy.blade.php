@extends('legal.layout')

@section('title', 'Política de privacidad')

@section('content')
    <h1>Política de privacidad</h1>
    <p class="text-gray-600">Última actualización: {{ date('d/m/Y') }}</p>

    <p>
        En Webnu.es tratamos tus datos personales conforme al Reglamento (UE) 2016/679 (RGPD)
        y la normativa española aplicable en materia de protección de datos.
    </p>

    <h2>Responsable del tratamiento</h2>
    <p>
        Webnu.es — contacto: <a href="mailto:hello@webnu.es">hello@webnu.es</a>
    </p>

    <h2>Datos que recogemos</h2>
    <ul>
        <li>Datos de registro: nombre, email, teléfono y datos de facturación si contratas un plan de pago.</li>
        <li>Datos de uso del panel: cartas, menús, imágenes y contenido que subes a la plataforma.</li>
        <li>Datos técnicos: logs de servidor, dirección IP y cookies estrictamente necesarias para el funcionamiento.</li>
        <li>Datos analíticos (solo si aceptas cookies en la landing): navegación agregada mediante Google Analytics u herramientas similares.</li>
    </ul>

    <h2>Finalidad</h2>
    <ul>
        <li>Prestar el servicio de carta digital y funcionalidades asociadas.</li>
        <li>Gestionar suscripciones y pagos a través de Stripe.</li>
        <li>Atender solicitudes de contacto y soporte.</li>
        <li>Mejorar el producto mediante analítica agregada (con consentimiento).</li>
    </ul>

    <h2>Conservación</h2>
    <p>
        Conservamos los datos mientras mantengas una cuenta activa y el tiempo necesario para cumplir obligaciones legales.
        Puedes solicitar la eliminación contactando con nosotros.
    </p>

    <h2>Derechos</h2>
    <p>
        Puedes ejercer los derechos de acceso, rectificación, supresión, oposición, limitación y portabilidad
        escribiendo a <a href="mailto:hello@webnu.es">hello@webnu.es</a>.
        También puedes reclamar ante la Agencia Española de Protección de Datos (AEPD).
    </p>

    <h2>Encargados y transferencias</h2>
    <p>
        Utilizamos proveedores como Stripe (pagos), servicios de email SMTP y, opcionalmente, Google (OAuth y analítica).
        Algunos proveedores pueden estar fuera del EEE con garantías adecuadas.
    </p>

    <h2>Cookies</h2>
    <p>
        Las cookies técnicas son necesarias para la sesión y seguridad. Las cookies de analítica solo se activan
        si las aceptas en el banner de la web pública.
    </p>
@endsection
