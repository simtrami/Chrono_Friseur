<div
    x-data="{
        timeline: null,
        events: [],
        options: {
            start: '1020-01-01',
            end: '2030-12-31',
            onInitialDrawComplete: null,
            margin: {
                item: 20
            },
            locale: 'fr'
        },
        loading: true,
        openDetails: false,
        selectedItem: null,
        editMode: false,
        init() {
            this.options.onInitialDrawComplete = () => this.loading = false;
            this.events = new vis.DataSet();
            this.timeline = new vis.Timeline(this.$refs.timeline, this.events, this.options);
            this.getEvents();
            this.timeline.on('select', function (properties) {
                window.dispatchEvent(new CustomEvent('timeline-select', { detail: properties.items[0] }));
            });
        },
        getEvents() {
            axios.get('/events')
                .then(response => {
                    response.data.forEach(e => {
                        this.events.add({
                            id: e.id,
                            content: e.name,
                            description: e.description,
                            title: new Date(e.date).toLocaleDateString(),
                            start: e.date,
                        });
                    });
                    this.timeline.setItems(this.events);
                    this.timeline.fit();
                }).catch(error => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les événements.'});
                    console.error('Erreur lors du chargement des événements:', error);
                });
        },
        show(e) {
            this.openDetails = true;
            this.selectedItem = this.events.get(e.detail);
        }
    }"
    @timeline-select.window="show($event)"
    class="w-full h-full flex items-center bg-white"
>
    <div x-show="loading" class="fixed inset-0 bg-indigo-600 z-10">
        <div class="w-full h-full flex justify-center items-center">
            <p class="text-white animate-pulse font-bold inline-flex items-center space-x-2">
                <svg class="size-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0-3-3m3 3 3-3m-8.25 6a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>
                <span class="uppercase text-2xl">Chargement...</span>
            </p>
        </div>
    </div>

    <div x-cloak x-ref='timeline' class="w-full relative"></div>

    <!-- Flyout -->
    <div
        x-dialog
        x-model="openDetails"
        x-cloak
        class="fixed inset-0 overflow-hidden z-10">
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
                        <button type="button" @click="$dialog.close()" class="relative inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-transparent p-1.5 font-medium text-gray-400 hover:bg-gray-800/10 hover:text-gray-800">
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
                            <button type="button"
                                    class="relative flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-transparent bg-transparent px-3 py-2 font-semibold text-sm text-gray-800 hover:bg-gray-800/10"
                            >Modifier
                            </button>

                            <button type="button"
                                    class="relative flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-transparent px-3 py-2 text-white font-semibold text-sm bg-red-600 hover:bg-red-500"
                            >Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
