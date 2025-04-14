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
            template: function (item, element, data) {
                let html = `<h1 x-tooltip=&quot;'${moment(item.start).format('llll')}'&quot;>${item.content}</h1>`;
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
        tagRequestInProgress: { 0: false },
        preventTagDelete: { 0: true },
        makeTagData(tag) {
            return {
                id: tag.id,
                color: tag.color,
                name: { fr: tag.name.fr }
            }
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
        getData() {
            // Get tags then get events
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
        getEvents() {
            axios.get('/events')
                .then(response => {
                    this.events.clear();
                    response.data.forEach(e => {
                        this.events.add(this.makeEventData(e));
                    });
                    this.timeline.fit();
                }).catch(() => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les événements.'});
                });
        },
        openEventFlyout: false,
        selectedEvent: {
            id: null,
            name: null,
            description: null,
            date: null,
            tags: []
        },
        formEvent: {
            id: null,
            name: null,
            description: null,
            date: null,
            tags: []
        },
        mode: 'addEvent',
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
        showEditEvent() {
            this.mode = 'editEvent';
            if (this.formEvent.id !== this.selectedEvent.id) {
                var e = this.events.get(this.selectedEvent.id);
                this.formEvent = {
                    id: e.id,
                    name: e.name,
                    description: e.description,
                    date: e.date,
                    tags: e.tags
                };
            }
        },
        cancelEditEvent() {
            this.mode = 'showEvent';
            var e = this.events.get(this.selectedEvent.id);
            this.formEvent = {
                id: e.id,
                name: e.name,
                description: e.description,
                date: e.date,
                tags: e.tags
            };
        },
        preventEventDelete: true,
        eventRequestInProgress: false,
        deleteEvent() {
            // Must execute this action twice in 3s to effectively delete.
            if (this.preventEventDelete) {
                this.preventEventDelete = false;
                setTimeout(() => {
                    this.preventEventDelete = true;
                }, 3000)
            } else {
                this.preventEventDelete = true;
                this.eventRequestInProgress = true;
                axios.delete('/events/' + this.selectedEvent.id)
                    .then(() => {
                        this.events.remove(this.selectedEvent.id);
                        this.selectedEvent = { id: null, name: null, description: null, date: null, tags: [] };
                        this.openEventFlyout = false;
                        this.$dispatch('notify', { content: `L'événement a bien été supprimé.`, type: 'success' })
                    }).catch(error => {
                        if (error.status === 404) {
                            this.$dispatch('notify', { content: `L'événement est introuvable.`, type: 'error' })
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' })
                        }
                    }).finally(() => { this.eventRequestInProgress = false; })
            }
        },
        eventFormErrors: { name: [], description: [], date: [], tags: [] },
        updateEvent() {
            this.eventRequestInProgress = true;
            this.eventFormErrors = { name: [], description: [], date: [], tags: [] };
            axios.put('/events/' + this.formEvent.id, this.formEvent)
                .then(response => {
                    this.events.updateOnly(this.makeEventData(response.data));
                    this.selectedEvent = this.events.get(response.data.id);
                    this.mode = 'showEvent';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.eventFormErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                    }
                }).finally(() => { this.eventRequestInProgress = false; })
        },
        showAddForm(e) {
            // Remove focus on the button because the focus must not be hidden from assistive technology users.
            document.activeElement.blur();
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
        addEvent() {
            this.eventRequestInProgress = true;
            this.eventFormErrors = { name: [], description: [], date: [], tags: [] };
            axios.post('/events', this.formEvent)
                .then(response => {
                    this.events.add(this.makeEventData(response.data));
                    this.openEventFlyout = false;
                    this.formEvent = { id: null, name: null, description: null, date: null, tags: [] }
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.eventFormErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de l'ajout.`, type: 'error' })
                    }
                }).finally(() => { this.eventRequestInProgress = false; })
        },
        openTagFlyout: false,
        showListTags(event) {
            // Remove focus on the button because the focus must not be hidden from assistive technology users.
            document.activeElement.blur();
            this.mode = 'listTags';
            this.openTagFlyout = true;
        },
        deleteTag(tag) {
            // Must execute this action twice in 3s to effectively delete.
            if (this.preventTagDelete[tag.id]) {
                this.preventTagDelete[tag.id] = false;
                setTimeout(() => {
                    this.preventTagDelete[tag.id] = true;
                }, 3000);
            } else {
                this.preventTagDelete[tag.id] = true;
                this.tagRequestInProgress[tag.id] = true;
                axios.delete('/tags/' + tag.id)
                    .then(response => {
                        this.tags.remove(tag.id);
                        // Update events which had this tag
                        if (response.data.affected_events) {
                            response.data.affected_events.forEach(e => {
                                this.events.updateOnly(this.makeEventData(e));
                            })
                        }
                        // Reset possible copies of an event which had this tag
                        this.selectedEvent = { id: null, name: null, description: null, date: null, tags: [] };
                        this.formEvent = { id: null, name: null, description: null, date: null, tags: [] };

                        this.$dispatch('notify', { content: `Le tag a bien été supprimé.`, type: 'success' });
                    }).catch(error => {
                        if (error.status === 404) {
                            this.$dispatch('notify', { content: `Le tag n'existe pas.`, type: 'error' });
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' });
                        }
                    }).finally(() => {
                        delete this.preventTagDelete[tag.id];
                        delete this.tagRequestInProgress[tag.id];
                    })
            }
        },
        formTag: {
            id: null,
            name: { fr: null },
            color: '#000000'
        },
        tagFormErrors: { name: [], color: [] },
        showAddTag() {
            this.mode = 'addTag';
            this.formTag = { id: 0, name: { fr: '' }, color: '#000000' };
            this.tagFormErrors = { name: [], color: [] };
        },
        showEditTag(tag) {
            this.mode = 'editTag';
            this.formTag = { id: tag.id, name: { fr: tag.name.fr }, color: tag.color };
            this.tagFormErrors = { name: [], color: [] };
        },
        updateTag() {
            this.tagRequestInProgress[this.formTag.id] = true;
            this.tagFormErrors = { name: [], color: [] };
            axios.put('/tags/' + this.formTag.id, this.formTag)
                .then(response => {
                    this.tags.updateOnly(this.makeTagData(response.data));
                    this.mode = 'listTags';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.tagFormErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                    }
                }).finally(() => { this.tagRequestInProgress[this.formTag.id] = false; })
        },
        addTag() {
            this.tagRequestInProgress[this.formTag.id] = true;
            this.tagFormErrors = { name: [], color: [] };
            axios.post('/tags', this.formTag)
                .then(response => {
                    this.tags.add(this.makeTagData(response.data));
                    this.tagRequestInProgress[response.data.id] = false;
                    this.preventTagDelete[response.data.id] = true;
                    this.mode = 'listTags';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.tagFormErrors = error.response.data.errors;
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de l'ajout.`, type: 'error' });
                    }
                }).finally(() => { this.tagRequestInProgress[this.formTag.id] = false; })
        }
    }"
    @timeline-select.window="showEvent($event)"
    @add-event.window="showAddForm($event)"
    @list-tags.window="showListTags($event)"
    class="w-full h-full flex items-center bg-white"
>
    <!-- Loading overlay -->
    <x-loading-overlay x-show="loading"/>

    <div x-cloak x-ref='timeline' class="w-full relative"></div>

    <!-- Event flyout -->
    <x-flyout x-model="openEventFlyout">
        <x-events.show x-show="selectedEvent.id && mode === 'showEvent'"/>
        <x-events.form x-show="(formEvent.id && mode === 'editEvent') || mode === 'addEvent'"/>
    </x-flyout>

    <!-- Tag flyout -->
    <x-flyout x-model="openTagFlyout">
        <x-tags.list/>
    </x-flyout>

    <div class="fixed flex space-x-4 bottom-0 right-0 pr-8 pb-8 z-10 md:pr-12 md:pb-12">
        <!-- List tags button -->
        <button x-tooltip="'Gestion des tags'"
                @click="$dispatch('list-tags')" type="button"
                class="whitespace-nowrap h-min rounded-full bg-slate-100 p-3 text-base font-semibold text-indigo-500 shadow hover:shadow-xl hover:bg-slate-50 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.solid-tag size="size-6"/>
        </button>

        <!-- Add event button -->
        <button x-tooltip="'Ajouter un événement'"
                @click="$dispatch('add-event')" type="button"
                class="flex items-center justify-center space-x-1 whitespace-nowrap h-min rounded-full bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow hover:shadow-xl hover:bg-indigo-500 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.plus size="size-6"/>

            <span aria-hidden="true">Événement</span>
        </button>
    </div>
</div>
