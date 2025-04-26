<x-layout>
    <x-slot:title>Connexion - Chrono Friseur</x-slot:title>


    <div class="relative bg-gradient-to-bl from-slate-50 via-indigo-50 to-indigo-200 bg-cover">
        <div
            class="min-h-screen flex flex-col align-middle sm:justify-center items-center pt-6 sm:pt-0 bg-dots"
        >
            <div class="motion-safe:animate-bounce drop-shadow-lg">
                <h1 class="text-4xl font-light text-gray-900">
                    <span class="font-extrabold">Chrono</span> Friseur
                </h1>
            </div>

            <a href="{{ route('auth.github') }}"
               class="flex items-center justify-center w-full mt-6 px-6 py-4 bg-indigo-100/20 backdrop-blur-sm shadow-lg overflow-hidden rounded-md hover:bg-indigo-50/20 hover:shadow-2xl focus:outline-2 sm:max-w-md transition"
            >
                <div class="flex items-center space-x-2 mx-2 p-2">
                    <x-icons.github size="size-8"/>
                    <span class="font-bold">Connexion avec GitHub</span>
                </div>
            </a>
        </div>
    </div>
</x-layout>
