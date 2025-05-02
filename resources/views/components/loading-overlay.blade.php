<div {{ $attributes->merge(['class' => 'fixed inset-0 bg-indigo-600 z-50']) }}>
    <div class="flex justify-center items-center w-full h-full">
        <p class="inline-flex items-center space-x-2 uppercase text-2xl font-bold text-white animate-pulse">
            <x-icons.spinner size="size-8"/>

            <span>Chargement...</span>
        </p>
    </div>
</div>
