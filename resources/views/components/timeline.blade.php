<div
    x-data="{
        loading: true,
        events: new DataSet(),
        tags: new DataSet(),
        timeline: null,
        options: {
            start: '1020-01-01',
            end: '2030-12-31',
            onInitialDrawComplete: null,
            margin: {
                item: 20
            },
            locale: 'fr',
            xss: {
                filterOptions: {
                    whiteList: {
                        h1: ['class', 'x-tooltip'],
                        ul: ['class'],
                        template: ['x-for', ':key'],
                        li: ['class', 'x-data'],
                        span: ['class', ':style', 'x-tooltip']
                    }
                }
            },
            template: function (item) {
                let html = `<h1 x-tooltip=&quot;'${moment(item.start).format('LL')}'&quot;>${item.content}</h1>`;
                if (item.tags.length > 0) {
                    html += `<ul class='absolute -top-1 left-0.5 flex space-x-1 items-start size-2 h-2 w-2 font-extrabold'>`;
                    html += `<template x-for='tag in events.get(${item.id})?.tags' :key='tag?.id'>`;
                    html += `<li class='inline-flex size-2 h-2 w-2'>
                                 <span x-tooltip='tags.get(tag.id)?.name.fr'
                                       :style='\`background-color: \${tags.get(tag.id)?.color}\`'
                                       class='size-2 rounded-full shadow-sm hover:scale-150 transition'
                                 ></span>
                             </li>`;
                    html += '</template></ul>';
                }
                return html;
            }
        },
        init() {
            this.options.onInitialDrawComplete = () => { this.loading = false };
            this.timeline = new Timeline(this.$refs.timeline, this.events, this.options);
            this.getData();
            this.timeline.on('select', function (selected) {
                // If the selection is empty, we don't want to do anything.
                if (selected.items.length > 0) {
                    // Equivalent to `this.$dispatch('timeline-select', selected.items[0]);`
                    // but `this.$dispatch` is not accessible here.
                    window.dispatchEvent(new CustomEvent('timeline-select', { detail: selected.items[0] }));
                }
            });
            this.timeline.on('contextmenu', function (props) {
                props.event.preventDefault();
                window.dispatchEvent(new CustomEvent('add-event', { detail: props }));
            });
        },
        makeTagData(tag) {
            return {
                id: tag.id,
                color: tag.color,
                name: { fr: tag.name.fr }
            }
        },
        tagRequestInProgress: { 0: false },
        preventTagDelete: { 0: true },
        getData() {
            // Get tags, then get events
            this.tags.clear();
            axios.get('/tags')
                .then(response => {
                    response.data.forEach(t => {
                        this.tags.add(this.makeTagData(t));
                        this.tagRequestInProgress[t.id] = false;
                        this.preventTagDelete[t.id] = true;
                    })
                    this.getEvents();
                }).catch(() => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les tags.'});
                });
        },
        makeEventData(event) {
            return {
                id: event.id,
                content: event.name,
                start: event.date,
                name: event.name,
                description: event.description,
                date: event.date,
                tags: event.tags.map(t => this.tags.get(t.id))
            };
        },
        eventRequestInProgress: false,
        getEvents(filters = null) {
            axios.get('/events', { params: filters })
                .then(response => {
                    this.$dispatch('events-loaded');
                    this.events.clear();
                    response.data.forEach(e => {
                        this.events.add(this.makeEventData(e));
                    });
                    this.timeline.fit();
                }).catch(error => {
                    if (error.status === 422) {
                        this.$dispatch('events-errored', {errors: error.response.data.errors})
                    } else {
                        this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les événements.'});
                    }
                }).finally(() => { this.eventRequestInProgress = false; });
        },
        mode: '',
        openEventFlyout: false,
        formEvent: {
            id: null,
            name: null,
            description: null,
            date: null,
            tags: []
        },
        eventFormErrors: { name: [], description: [], date: [], tags: [] },
        showAddForm(e) {
            this.mode = 'addEvent';
            this.openEventFlyout = true;
            if (e.detail.snappedTime) {
                date = e.detail.snappedTime.format('YYYY-MM-DD HH:mm');
                this.formEvent = { id: null, name: null, description: null, date: date, tags: [] };
            } else {
                this.formEvent = { id: null, name: null, description: null, date: null, tags: [] };
            }
            this.eventFormErrors = { name: [], description: [], date: [], tags: [] };
        },
        selectedEvent: {
            id: null,
            name: null,
            description: null,
            date: null,
            tags: []
        },
        showEvent(event) {
            this.mode = 'showEvent';
            this.openEventFlyout = true;
            if (this.selectedEvent.id !== event.detail) {
                var e = this.events.get(event.detail);
                this.selectedEvent = {
                    id: e.id,
                    name: e.name,
                    description: e.description,
                    date: e.date,
                    tags: e.tags
                };
            }
        },
        openTagFlyout: false,
        showListTags() {
            this.mode = 'listTags';
            this.openTagFlyout = true;
        },
        openSearchFlyout: false,
        showSearch() {
            this.openSearchFlyout = true;
            this.mode = 'search';
        }
    }"
    @timeline-select.window="showEvent($event)"
    @add-event.window="showAddForm($event)"
    @list-tags.window="showListTags()"
    @open-search.window="showSearch()"
    class="w-full h-full flex items-center bg-white"
>
    <!-- Loading overlay -->
    <x-loading-overlay x-show="loading"/>

    <div x-cloak x-ref='timeline' class="w-full relative"></div>

    <!-- Event flyout -->
    <x-flyout x-model="openEventFlyout">
        <!-- Title -->
        <x-slot:title>
            <span x-show="mode === 'showEvent'">Détails de l'événement</span>
            <span x-show="mode === 'editEvent'">Modifier l'événement</span>
            <span x-show="mode === 'addEvent'">Ajouter un événement</span>
        </x-slot:title>

        <!-- Animation when loading event data -->
        <template x-if="(!selectedEvent.id && mode === 'showEvent') || (!formEvent.id && mode === 'editEvent')">
        <div class="space-y-3 animate-pulse">
            <div class="rounded-md bg-gray-200/70 h-5 w-[300px]"></div>
            <div class="rounded-md bg-gray-200/70 h-5 w-[250px]"></div>
            <div class="rounded-md bg-gray-200/70 h-5 w-[200px]"></div>
        </div>
        </template>

        <!-- showEvent -->
        <x-events.show x-show="selectedEvent.id && mode === 'showEvent'"/>

        <!-- editEvent || addEvent -->
        <x-events.form x-show="(formEvent.id && mode === 'editEvent') || mode === 'addEvent'"/>
    </x-flyout>

    <!-- Tag flyout -->
    <x-flyout x-model="openTagFlyout">
        <!-- Title -->
        <x-slot:title>
            <span x-show="mode === 'listTags' || mode === 'editTag' || mode === 'addTag'">Gestion des tags</span>
        </x-slot>

        <x-tags.list/>
    </x-flyout>

    <!-- Search flyout -->
    <x-flyout x-model="openSearchFlyout">
        <!-- Title -->
        <x-slot:title>
            <span x-show="mode === 'search'">Rechercher & Filtrer par&mldr;</span>
        </x-slot>

        <x-search/>
    </x-flyout>

    <!-- Top right elements -->
    <div class="fixed flex space-x-4 top-0 right-0 pr-8 pt-8 z-10 md:pr-12 md:pt-12">
        <!-- Reset view button -->
        <button x-tooltip="'Ajuster la vue'"
                @click="timeline.fit();" type="button"
                class="whitespace-nowrap h-min rounded-full bg-slate-100 p-3 text-base font-semibold text-indigo-500 hover:bg-slate-50 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.back size="size-5"/>
        </button>

    </div>

    <!-- Bottom right elements -->
    <div class="fixed flex space-x-4 bottom-0 right-0 pr-8 pb-8 z-10 md:pr-12 md:pb-12">
        <!-- Open search button -->
        <button x-tooltip="'Filtrer les événements'"
                @click="$el.blur(); $dispatch('open-search')" type="button"
                class="whitespace-nowrap h-min rounded-full bg-slate-100 p-3 text-base font-semibold text-indigo-500 shadow hover:shadow-xl hover:bg-slate-50 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.solid-funnel size="size-6"/>
        </button>

        <!-- List tags button -->
        <button x-tooltip="'Gérer les tags'"
                @click="$el.blur(); $dispatch('list-tags')" type="button"
                class="whitespace-nowrap h-min rounded-full bg-slate-100 p-3 text-base font-semibold text-indigo-500 shadow hover:shadow-xl hover:bg-slate-50 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.solid-tag size="size-6"/>
        </button>

        <!-- Add event button -->
        <button x-tooltip="'Ajouter un événement'"
                @click="$el.blur(); $dispatch('add-event')" type="button"
                class="flex items-center justify-center space-x-1 whitespace-nowrap h-min rounded-full bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow hover:shadow-xl hover:bg-indigo-500 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.plus size="size-6"/>

            <span aria-hidden="true">Événement</span>
        </button>
    </div>
</div>
