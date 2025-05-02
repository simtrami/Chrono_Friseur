<x-layout>
    <x-slot:title>Frises - Chrono Friseur</x-slot:title>

    <div
        x-data="{
            loading: true,
            timelines: new DataSet(),
            mode: 'create',
            requestInProgress: false,
            openFormFlyout: false,
            formData: { id: null, name: '', slug: null, description: null, picture: null },
            formErrors: { name: [], slug: [], description: [], picture: [] },
            openDeleteModal: false,
            timelineToDelete: { id: null, name: '' },
            init() {
                this.getData()
            },
            getData() {
                this.timelines.clear();
                axios.get('/timelines').then(response => {
                    if (response.data.data.length !== 0) {
                        this.timelines.add(response.data.data.map(timeline => this.makeTimelineData(timeline)));
                    }
                    this.loading = false;
                }).catch(() => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les frises.'});
                });
            },
            makeTimelineData(timeline) {
                return {
                    id: timeline.id,
                    name: timeline.name,
                    slug: timeline.slug,
                    description: timeline.description,
                    picture: timeline.picture,
                    //events_count: timeline.events_count
                    created_at: timeline.created_at,
                    created_by: timeline.created_by
                }
            },
            showForm(timeline_id = null) {
                this.openFormFlyout = true;
                if (timeline_id) {
                    if (this.formData.id !== timeline_id) {
                        const timeline = this.timelines.get(timeline_id);
                        this.formData = {
                            id: timeline.id,
                            name: timeline.name,
                            slug: timeline.slug,
                            description: timeline.description,
                            picture: timeline.picture
                        };
                    }
                    this.mode = 'edit';
                } else {
                    this.formData = { id: null, name: '', slug: null, description: null, picture: null };
                    this.mode = 'create';
                }
            },
            deleteTimeline(id) {
                timeline = this.timelines.get(id);
                this.timelineToDelete.id = timeline.id;
                this.timelineToDelete.name = timeline.name;
                this.openDeleteModal = true;
            },
            confirmDelete(id) {
                this.requestInProgress = true;
                axios.delete('/timelines/' + id)
                    .then(response => {
                        this.timelines.remove(id);
                        this.timelineToDelete = { id: null, name: '' };
                        this.openDeleteModal = false;
                        this.$dispatch('notify', { content: response.data.message, type: 'success' })
                    }).catch(error => {
                        if (error.status === 404) {
                            this.timelineToDelete = { id: null, name: '' };
                            this.openDeleteModal = false;
                            this.$dispatch('notify', { content: `La frise est introuvable.`, type: 'error' })
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' })
                        }
                    }).finally(() => { this.requestInProgress = false; })
            }
        }"
        class="min-h-dvh m-0 bg-slate-100 sm:p-2"
    >
        <div class="bg-white sm:shadow-sm sm:rounded-md sm:max-w-7xl sm:mx-auto">
            <div class="border-b border-gray-200 px-4 py-5">
                <div class="-mt-2 -ml-4 flex flex-wrap items-center justify-between sm:flex-nowrap">
                    <div class="mt-2 ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">Mes Frises</h1>
                    </div>

                    <div class="mt-2 ml-4 shrink-0">
                        <button x-tooltip="'Créer une frise'" @click="$el.blur(); showForm()" type="button"
                                class="relative flex items-center justify-center whitespace-nowrap rounded-lg border border-transparent px-2.5 py-1.5 text-sm text-white font-semibold bg-indigo-600 outline-0 outline-transparent hover:bg-indigo-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 sm:text-base transition"
                        >
                            <x-icons.plus size="size-4 sm:size-5"/>

                            <span class="inline" aria-hidden="true">&nbsp;Créer</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 px-4">
                <ul role="list" class="divide-y divide-gray-100">
                    <!-- Loading data -->
                    <template x-if="loading">
                    <li class="flex justify-center items-center w-full py-4 ">
                        <p class="inline-flex items-center space-x-2 uppercase text-lg font-bold text-indigo-300 animate-pulse ">
                            <x-icons.spinner size="size-5"/>

                            <span>Chargement...</span>
                        </p>
                    </li>
                    </template>

                    <!-- No timelines yet -->
                    <template x-if="timelines.length === 0 && !loading">
                    <li class="flex justify-center items-center w-full py-4 ">
                        <div class="w-6 shrink-0">
                            <x-icons.face-frown size="size-5" class="shrink-0"/>
                        </div>

                        <span class="text-lg">Pas de frise</span>
                    </li>
                    </template>

                    <!-- Timelines list -->
                    <template
                        x-for="timeline of timelines.get({fields: ['id', 'name', 'slug', 'description', 'picture', 'created_at', 'created_by']})"
                        :key="timeline.id"
                    >
                    <x-timelines.item/>
                    </template>
                </ul>
            </div>
        </div>

        <x-flyout x-model="openFormFlyout">
            <!-- Title -->
            <x-slot:title>
                <span x-show="mode === 'edit'">Modifier une frise</span>
                <span x-show="mode === 'create'">Créer une frise</span>
            </x-slot>

            <x-timelines.form/>
        </x-flyout>

        <div x-dialog x-model="openDeleteModal" x-cloak class="fixed inset-0 z-10 overflow-y-auto">
            <!-- Overlay -->
            <div x-dialog:overlay x-transition.opacity class="fixed inset-0 bg-black/25"></div>

            <!-- Panel -->
            <div class="relative flex min-h-screen items-center justify-center p-4">
                <div x-dialog:panel x-transition class="relative min-w-96 max-w-xl rounded-xl bg-white p-6 shadow-lg">
                    <!-- Close Button -->
                    <div class="absolute right-0 top-0 mr-4 mt-4">
                        <button type="button" @click="$dialog.close()"
                                class="relative inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-transparent p-1.5 font-medium text-gray-400 hover:bg-gray-800/10 hover:text-gray-800"
                        >
                            <span class="sr-only">Fermer</span>
                            <x-icons.cross size="size-5"/>
                        </button>
                    </div>

                    <!-- Body -->
                    <div>
                        <!-- Title -->
                        <h2 x-dialog:title class="font-medium text-gray-800">Confirmer la suppression</h2>

                        <!-- Content -->
                        <div class="mt-2 text-gray-500 max-w-xs">
                            <p x-text="`Supprimer la frise &quot;${timelineToDelete.name}&quot; définitivement&nbsp;?`"></p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end space-x-2">
                        <button type="button" x-on:click="$dialog.close()"
                                class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent bg-transparent px-3 py-2 font-semibold text-gray-800 hover:bg-gray-800/10 transition"
                        >Annuler
                        </button>

                        <button type="button" x-on:click="confirmDelete(timelineToDelete.id)"
                                class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-2 text-white font-semibold bg-red-600 outline-0 outline-transparent hover:bg-red-500 focus:outline-2 focus:outline-offset-2 focus:outline-red-700 transition"
                                :class="{'opacity-50 cursor-not-allowed': requestInProgress}"
                                :disabled="requestInProgress"
                        >
                            <template x-if="requestInProgress">
                            <x-icons.spinner size="size-5"/>
                            </template>

                            <span x-show="!requestInProgress">Confirmer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
