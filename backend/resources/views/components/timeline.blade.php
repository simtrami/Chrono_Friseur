<div
    x-data="{
        loading: true,
        events: [],
        timeline: null,
        options: {
            start: '1020-01-01',
            end: '2030-12-31',
            onInitialDrawComplete: null,
            margin: {
                item: 20
            },
            locale: 'fr'
        },
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
                            title: new Date(e.date).toLocaleDateString(),
                            start: e.date
                        });
                    });
                    this.timeline.setItems(this.events);
                    this.timeline.fit();
                }).catch(error => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les événements.'});
                    console.error('Erreur lors du chargement des événements:', error);
                });
        },
        openFlyout: false,
        selectedItem: {
            id: null,
            name: null,
            description: null,
            date: null
        },
        show(e) {
            this.editMode = false;
            this.openFlyout = true;
            axios.get('/events/' + e.detail).then(response => {
                this.selectedItem = response.data;
            }).catch(error => {
                this.$dispatch('notify', {type: 'error', content: `Impossible de charger l'événements.`});
                console.error('Erreur lors du chargement :', error);
            })
        },
        preventDelete: true,
        deleteInProgress: false,
        deleteEvent() {
            if (this.preventDelete) {
                this.preventDelete = false;
                setTimeout(() => {
                    this.preventDelete = true;
                }, 3000)
            } else {
                this.preventDelete = true;
                this.deleteInProgress = true;
                axios.delete('/events/' + this.selectedItem.id)
                    .then(() => {
                        this.deleteInProgress = false;
                        this.events.remove(this.selectedItem.id);
                        this.openFlyout = false;
                        this.selectedItem = null;
                        this.$dispatch('notify', { content: `L'événement a bien été supprimé.`, type: 'success' })
                    }).catch((error) => {
                        this.deleteInProgress = false;
                        console.log(error);
                        if (error.status === 404) {
                            this.$dispatch('notify', { content: `L'événement est introuvable.`, type: 'error' })
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' })
                        }
                    })
            }
        },
        editMode: false,
        formErrors: { name: [], description: [], date: [] },
        updateEvent() {
            this.formErrors = { name: [], description: [], date: [] };
            axios.put('/events/' + this.selectedItem.id, this.selectedItem)
            .then(response => {
                this.$dispatch('notify', { content: `${response.data.name} a bien été modifié.`, type: 'success' })
                this.events.update([{
                    id: response.data.id,
                    content: response.data.name,
                    title: new Date(response.data.date).toLocaleDateString(),
                    start: response.data.date
                }]);
                this.selectedItem = response.data;
                this.editMode = false;
            }).catch(error => {
                if (error.response.status === 422) {
                    this.formErrors = error.response.data.errors
                } else {
                    this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                }
            })
        }
    }"
    @timeline-select.window="show($event)"
    class="w-full h-full flex items-center bg-white"
>
    <!-- Loading overlay -->
    <x-loading-overlay x-show="loading"/>

    <!-- Add event -->
    <x-add-event/>

    <!-- Event details -->
    <x-flyout x-model="openFlyout">
        <x-events.details x-show="!editMode"/>
        <x-events.edit x-show="editMode"/>
    </x-flyout>

    <div x-cloak x-ref='timeline' class="w-full relative"></div>
</div>
