<script src="{{ asset('js/webnu-pwa-install.js') }}"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' }).catch(function () {});
    });
}
</script>
