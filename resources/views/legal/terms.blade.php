@extends('legal.layout')

@section('title', 'Términos y condiciones')

@section('content')
    <h1>Términos y condiciones de uso</h1>
    <p class="text-gray-600">Última actualización: {{ date('d/m/Y') }}</p>

    <p>
        Al registrarte o utilizar Webnu.es aceptas estos términos. Si no estás de acuerdo, no uses el servicio.
    </p>

    <h2>Descripción del servicio</h2>
    <p>
        Webnu es una plataforma SaaS que permite crear y publicar cartas digitales, menús, códigos QR
        y contenido para pantallas (TVPik/reproductor), según el plan contratado.
    </p>

    <h2>Cuenta y contenido</h2>
    <ul>
        <li>Eres responsable de la veracidad de los datos de registro y de mantener tus credenciales seguras.</li>
        <li>Eres responsable del contenido (textos, imágenes, precios) que publicas en tus cartas.</li>
        <li>No debes usar Webnu para contenido ilegal, engañoso o que infrinja derechos de terceros.</li>
    </ul>

    <h2>Planes y facturación</h2>
    <p>
        Los precios, periodos de prueba y límites de cada plan se describen en la landing y en el panel.
        Los pagos recurrentes se procesan mediante Stripe. Puedes gestionar tu suscripción desde el panel de cuenta.
    </p>

    <h2>Disponibilidad</h2>
    <p>
        Nos esforzamos por mantener el servicio disponible, pero pueden producirse interrupciones por mantenimiento
        o causas fuera de nuestro control. No garantizamos disponibilidad ininterrumpida.
    </p>

    <h2>Propiedad intelectual</h2>
    <p>
        Webnu y su software son propiedad de sus titulares. Conservas la propiedad del contenido que subes.
        Nos concedes una licencia limitada para alojar y mostrar ese contenido en el marco del servicio.
    </p>

    <h2>Limitación de responsabilidad</h2>
    <p>
        Webnu se ofrece «tal cual». En la medida permitida por la ley, no seremos responsables de daños indirectos
        derivados del uso del servicio. Nuestra responsabilidad máxima se limita a las cuotas pagadas en los últimos 12 meses.
    </p>

    <h2>Modificaciones</h2>
    <p>
        Podemos actualizar estos términos. Publicaremos la versión vigente en esta página.
        El uso continuado del servicio tras un cambio implica la aceptación de los nuevos términos.
    </p>

    <h2>Contacto</h2>
    <p>
        Para cualquier consulta: <a href="mailto:hello@webnu.es">hello@webnu.es</a>
    </p>
@endsection
