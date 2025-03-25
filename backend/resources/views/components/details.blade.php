<div
    {{ $attributes->merge(['class' => 'fixed inset-0 overflow-hidden z-10']) }}
    x-dialog
    x-cloak>
    <!-- Overlay -->
    <div x-dialog:overlay x-transition.opacity class="fixed inset-0 bg-black/25"></div>

    <!-- Panel -->
    <div class="fixed inset-y-0 right-0 max-w-lg w-full max-h-dvh min-h-dvh">
        <div
            x-dialog:panel
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="h-full w-full">
            <div class="h-full flex flex-col bg-white shadow-lg overflow-y-auto p-8">
                <!-- Close Button -->
                <div class="absolute right-0 top-0 mr-4 mt-4">
                    <button @click="$dialog.close()" type="button"
                            class="relative inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-transparent p-1.5 font-medium text-gray-400 hover:bg-gray-800/10 hover:text-gray-800"
                    >
                        <span class="sr-only">Fermer le panneau</span>
                        <x-icons.cross size="size-5"/>
                    </button>
                </div>

                <!-- Body -->
                <div class="space-y-6">
                    <!-- Title -->
                    <h2 x-dialog:title class="font-medium text-gray-800 text-xl">Détails de l'événement</h2>

                    <template x-if="!selectedItem">
                        <div class="space-y-3 animate-pulse">
                            <div class="rounded-md bg-gray-200/70 h-5 w-[300px]"></div>
                            <div class="rounded-md bg-gray-200/70 h-5 w-[250px]"></div>
                            <div class="rounded-md bg-gray-200/70 h-5 w-[200px]"></div>
                        </div>
                    </template>

                    <!-- Content -->
                    <div class="flex flex-col mt-2 border-b border-gray-900/10 pb-6">
                        <div class="row mb-2">
                            <p x-text="selectedItem?.content" class="font-semibold text-lg"></p>
                        </div>

                        <div class="row mb-2">
                            <p x-text="selectedItem?.start"></p>
                        </div>

                        <div class="row mb-2">
                            <p x-text="selectedItem?.description" class="text-gray-500"></p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end space-x-2">
                        <button
                            type="button"
                            class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent bg-transparent px-3 py-2 font-semibold text-gray-800 hover:bg-gray-800/10"
                        >
                            <x-icons.pencil-square/>
                            <span>Modifier</span>
                        </button>

                        <button
                            @click.prevent="deleteEvent()"
                            type="button"
                            class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-2 text-white font-semibold bg-red-600 hover:bg-red-500"
                            :class="{'opacity-50 cursor-not-allowed': deleteInProgress, 'animate-wiggle': !preventDelete}"
                            :disabled="deleteInProgress"
                        >
                            <template x-if="preventDelete && !deleteInProgress">
                                <x-icons.trash/>
                            </template>

                            <template x-if="deleteInProgress">
                                <x-icons.spinner/>
                            </template>

                            <span
                                x-show="!deleteInProgress"
                                x-text="preventDelete ? 'Supprimer' : 'Vraiment ? :('"
                            ></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
