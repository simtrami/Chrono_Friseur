<div {{ $attributes->merge(['class' => 'flex flex-col my-2 space-y-2 border-b border-gray-900/10 pb-6']) }}>
    <div class="row">
        <p x-text="currentEvent.name" class="font-semibold text-lg"></p>
    </div>

    <template x-if="currentEvent.tags.length > 0">
        <div class="row space-x-1">
            <template x-for="tag in currentEvent.tags" :key="tag.id">
                <x-tags.badge>
                    <span x-text="tag.name?.fr"></span>
                </x-tags.badge>
            </template>
        </div>
    </template>

    <div class="row">
        <p x-text="moment(currentEvent.date).format('llll') + ' (' + moment(currentEvent.date).fromNow() + ')'"></p>
    </div>

    <div class="row">
        <p x-text="currentEvent.description" class="text-gray-500"></p>
    </div>
</div>
