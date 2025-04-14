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
                    html += `<template x-for='tag in events.get(${item.id}).tags' :key='tag.id'>`;
                    html += `<li class='inline-flex size-2 h-2 w-2'>
                                 <span x-tooltip='tags.get(tag.id).name.fr'
                                       :style='\`background-color: \${tags.get(tag.id).color}\`'
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
        getEvents() {
            axios.get('/events')
                .then(response => {
                    this.events.clear();
                    response.data.forEach(e => {
                        this.events.add({
                            id: e.id,
                            content: e.name,
                            start: e.date,
                            name: e.name,
                            description: e.description,
                            date: e.date,
                            tags: e.tags.map(t => this.tags.get(t.id))
                        });
                    });
                    this.timeline.fit();
                }).catch(() => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les événements.'});
                });
        },
        tagRequestInProgress: { 0: false },
        tagPreventDelete: { 0: true },
        getData() {
            // Get tags then get events
            this.tags.clear();
            axios.get('/tags')
                .then(response => {
                    response.data.forEach(t => {
                        this.tags.add({
                            id: t.id,
                            color: t.color,
                            name: { fr: t.name.fr }
                        });
                        this.tagRequestInProgress[t.id] = false;
                        this.tagPreventDelete[t.id] = true;
                    })
                    this.getEvents();
                }).catch(() => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les tags.'});
                });
        },
        openEventFlyout: false,
        currentEvent: {
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
            if (this.currentEvent.id !== event.detail) {
                var e = this.events.get(event.detail);
                this.currentEvent = {
                    id: e.id,
                    name: e.name,
                    description: e.description,
                    date: e.date,
                    tags: e.tags
                };
                console.log(this.currentEvent)
                for (t in this.currentEvent.tags) {
                    console.log(t)
                }
            }
        },
        preventDelete: true,
        requestInProgress: false,
        deleteEvent() {
            if (this.preventDelete) {
                this.preventDelete = false;
                setTimeout(() => {
                    this.preventDelete = true;
                }, 3000)
            } else {
                this.preventDelete = true;
                this.requestInProgress = true;
                axios.delete('/events/' + this.currentEvent.id)
                    .then(() => {
                        this.events.remove(this.currentEvent.id);
                        this.currentEvent = { id: null, name: null, description: null, date: null, tags: [] };
                        this.openEventFlyout = false;
                        this.$dispatch('notify', { content: `L'événement a bien été supprimé.`, type: 'success' })
                    }).catch(error => {
                        if (error.status === 404) {
                            this.$dispatch('notify', { content: `L'événement est introuvable.`, type: 'error' })
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' })
                        }
                    }).finally(() => { this.requestInProgress = false; })
            }
        },
        formErrors: { name: [], description: [], date: [], tags: [] },
        updateEvent() {
            this.requestInProgress = true;
            this.formErrors = { name: [], description: [], date: [], tags: [] };
            axios.put('/events/' + this.currentEvent.id, this.currentEvent)
                .then(response => {
                    this.events.updateOnly({
                        id: response.data.id,
                        content: response.data.name,
                        start: response.data.date,
                        name: response.data.name,
                        description: response.data.description,
                        date: response.data.date,
                        tags: response.data.tags.map(t => this.tags.get(t.id))
                    });
                    this.currentEvent = this.events.get(response.data.id);
                    this.mode = 'showEvent';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.formErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                    }
                }).finally(() => { this.requestInProgress = false; })
        },
        showAddForm(e) {
            // Remove focus on the button because the focus must not be hidden from assistive technology users.
            document.activeElement.blur();
            this.mode = 'addEvent';
            this.openEventFlyout = true;
            if (e.detail.snappedTime) {
                date = e.detail.snappedTime.format('YYYY-MM-DD HH:mm');
                this.currentEvent = { id: null, name: null, description: null, date: date, tags: [] };
            } else {
                this.currentEvent = { id: null, name: null, description: null, date: null, tags: [] };
            }
            this.formErrors = { name: [], description: [], date: [], tags: [] };
        },
        addEvent() {
            this.requestInProgress = true;
            this.formErrors = { name: [], description: [], date: [], tags: [] };
            axios.post('/events', {
                    name: this.currentEvent.name,
                    description: this.currentEvent.description,
                    date: this.currentEvent.date,
                    tags: this.currentEvent.tags
                }).then(response => {
                    this.events.add({
                        id: response.data.id,
                        content: response.data.name,
                        start: response.data.date,
                        name: response.data.name,
                        description: response.data.description,
                        date: response.data.date,
                        tags: response.data.tags.map(t => this.tags.get(t.id))
                    });
                    this.openEventFlyout = false;
                    this.currentEvent = { id: null, name: null, description: null, date: null, tags: [] }
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.formErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de l'ajout.`, type: 'error' })
                    }
                }).finally(() => { this.requestInProgress = false; })
        },
        openTagFlyout: false,
        listTags(event) {
            // Remove focus on the button because the focus must not be hidden from assistive technology users.
            document.activeElement.blur();
            this.mode = 'listTag';
            this.openTagFlyout = true;
        },
        deleteTag(tag) {
            if (this.tagPreventDelete[tag.id]) {
                this.tagPreventDelete[tag.id] = false;
                setTimeout(() => {
                    this.tagPreventDelete[tag.id] = true;
                }, 3000);
            } else {
                this.tagPreventDelete[tag.id] = true;
                this.tagRequestInProgress[tag.id] = true;
                axios.delete('/tags/' + tag.id)
                    .then(() => {
                        this.tags.remove(tag.id);
                        this.$dispatch('notify', { content: `Le tag a bien été supprimé.`, type: 'success' });
                    }).catch(error => {
                        if (error.status === 404) {
                            this.$dispatch('notify', { content: `Le tag n'existe pas.`, type: 'error' });
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' });
                        }
                    }).finally(() => {
                        delete this.tagPreventDelete[tag.id];
                        delete this.tagRequestInProgress[tag.id];
                    })
            }
        },
        currentTag: {
            id: null,
            name: { fr: null },
            color: '#000000'
        },
        showAddTag() {
            this.mode = 'addTag';
            this.currentTag = { id: 0, name: { fr: '' }, color: '#000000' };
            this.tagFormErrors = { name: [], color: [] };
        },
        showEditTag(tag) {
            this.mode = 'editTag';
            this.currentTag = { id: tag.id, name: { fr: tag.name.fr }, color: tag.color };
            this.tagFormErrors = { name: [], color: [] };
        },
        tagFormErrors: { name: [], color: [] },
        updateTag() {
            this.tagRequestInProgress[this.currentTag.id] = true;
            this.tagFormErrors = { name: [], color: [] };
            axios.put('/tags/' + this.currentTag.id, this.currentTag)
                .then(response => {
                    this.tags.updateOnly({
                        id: response.data.id,
                        color: response.data.color,
                        name: { fr: response.data.name.fr }
                    });
                    this.mode = 'listTag';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.tagFormErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                    }
                }).finally(() => { this.tagRequestInProgress[this.currentTag.id] = false; })
        },
        addTag() {
            this.tagRequestInProgress[this.currentTag.id] = true;
            this.tagFormErrors = { name: [], color: [] };
            axios.post('/tags', this.currentTag)
                .then(response => {
                    this.tags.add({
                        id: response.data.id,
                        color: response.data.color,
                        name: { fr: response.data.name.fr }
                    });
                    this.tagRequestInProgress[response.data.id] = false;
                    this.tagPreventDelete[response.data.id] = true;
                    this.mode = 'listTag';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.tagFormErrors = error.response.data.errors;
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de l'ajout.`, type: 'error' });
                    }
                }).finally(() => { this.tagRequestInProgress[this.currentTag.id] = false; })
        }
    }"
    @timeline-select.window="showEvent($event)"
    @add-event.window="showAddForm($event)"
    @list-tags.window="listTags($event)"
    class="w-full h-full flex items-center bg-white"
>
    <!-- Loading overlay -->
    <x-loading-overlay x-show="loading"/>

    <div x-cloak x-ref='timeline' class="w-full relative"></div>

    <!-- Event flyout -->
    <x-flyout x-model="openEventFlyout">
        <x-events.show x-show="currentEvent.id && mode === 'showEvent'"/>
        <x-events.form x-show="(currentEvent.id && mode === 'editEvent') || mode === 'addEvent'"/>
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
