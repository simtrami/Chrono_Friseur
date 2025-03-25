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
                            description: e.description,
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
        openDetails: false,
        selectedItem: null,
        show(e) {
            this.openDetails = true;
            this.selectedItem = this.events.get(e.detail);
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
                        this.openDetails = false;
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
    <x-details x-model="openDetails"/>

    <div x-cloak x-ref='timeline' class="w-full relative"></div>
</div>
