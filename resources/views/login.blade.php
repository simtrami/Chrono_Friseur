<x-layout>
    <x-slot:title>Connexion - Chrono Friseur</x-slot:title>

    <div class="relative bg-white bg-cover">
        <div
            class="absolute bg-gradient-to-bl from-slate-50 via-indigo-100 to-indigo-300 animate-[pulse_5s_ease-in-out_infinite] w-full h-full"
        ></div>

        <div class="absolute bg-dots animate-pulse w-full h-full"></div>

        <div class="flex flex-col items-center justify-center align-middle mx-4 min-h-screen">
            <div class="drop-shadow-lg">
                <h1 class="text-4xl font-light text-gray-900">
                    <span class="font-extrabold">Chrono</span> Friseur
                </h1>
            </div>

            <a href="{{ route('auth.github') }}"
               class="flex items-center justify-center w-full mt-6 px-6 py-4 bg-white/40 backdrop-blur-xs shadow-lg overflow-hidden rounded-md hover:bg-white/60 hover:shadow-2xl focus:outline-2 sm:max-w-md transition"
            >
                <div class="flex items-center space-x-2 mx-2 p-2">
                    <x-icons.github size="size-8"/>
                    <span class="font-bold">Connexion avec GitHub</span>
                </div>
            </a>
        </div>
    </div>
</x-layout>
