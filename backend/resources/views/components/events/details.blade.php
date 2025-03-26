<div {{ $attributes->merge(['class' => 'flex flex-col mt-2 border-b border-gray-900/10 pb-6']) }}>
    <div class="row mb-2">
        <p x-text="selectedItem.name" class="font-semibold text-lg"></p>
    </div>

    <div class="row mb-2">
        <p x-text="selectedItem.date"></p>
    </div>

    <div class="row mb-2">
        <p x-text="selectedItem.description" class="text-gray-500"></p>
    </div>
</div>
