<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div id="install-card" class="hidden p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="font-display font-bold text-lg mb-1">Installer Kolporte</h3>
                    <p class="text-sm text-gray-600 mb-4">Ajoutez Kolporte à votre écran d'accueil pour l'utiliser comme une application.</p>
                    <button type="button" id="btn-install" class="inline-flex items-center justify-center min-h-[48px] px-6 rounded-xl bg-vert text-white font-semibold">
                        Installer l'application
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var carte = document.getElementById('install-card');
            var dejaInstallee = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
            var invite = null;

            if (dejaInstallee) {
                return;
            }

            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                invite = e;
                carte.classList.remove('hidden');
            });

            window.addEventListener('appinstalled', function () {
                carte.classList.add('hidden');
            });

            document.getElementById('btn-install').addEventListener('click', function () {
                if (!invite) {
                    return;
                }
                invite.prompt();
                invite.userChoice.then(function (choix) {
                    if (choix.outcome === 'accepted') {
                        carte.classList.add('hidden');
                    }
                    invite = null;
                });
            });
        })();
    </script>
</x-app-layout>
