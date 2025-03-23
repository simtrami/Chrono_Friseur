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
        init() {
            this.options.onInitialDrawComplete = () => this.loading = false;
            this.events = new vis.DataSet();
            this.timeline = new vis.Timeline(this.$refs.timeline, this.events, this.options);
            this.getEvents();
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
</div>


