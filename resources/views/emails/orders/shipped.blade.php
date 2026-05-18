@component('mail::message')
# Suscripción a Webnu

Hola, ¡bienvenido a **Webnu** !
Ya puedes acceder con tu correo electrónico y contraseña.

@component('mail::button', ['url' => config('app.url').'/login'])
Configurar mi carta
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
