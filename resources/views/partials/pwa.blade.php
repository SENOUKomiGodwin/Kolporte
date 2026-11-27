{{-- Balises PWA : manifeste, thème, icône Apple et enregistrement du service worker (production). --}}
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#0F5C45">
<link rel="apple-touch-icon" href="/icons/icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Kolporte">
<script>
    if ('serviceWorker' in navigator && {{ app()->environment('production') ? 'true' : 'false' }}) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>
