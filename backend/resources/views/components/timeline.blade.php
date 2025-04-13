<div
    x-data="{
        loading: true,
        events: [],
        tags: {},
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
                        h1: ['class', 'title'],
                        ul: ['class'],
                        li: ['class', 'style', 'title'],
                        span: ['class', 'style', 'title']
                    }
                }
            },
            template: function (item, element, data) {
                let html = `<h1 title='${moment(item.start).format('llll')}'>${item.content}</h1>`;
                if (item.tags.length > 0) {
                        html += `<ul class='absolute -top-1 left-0.5 flex space-x-1 items-start size-2 h-2 w-2 font-extrabold'>`;
                    item.tags.forEach(tag => {
                        html += `
                            <li class='inline-flex size-2 h-2 w-2'>
                                <span class='size-2 rounded-full shadow-sm hover:scale-150 transition'
                                      style='background-color: ${tag.color}' title='${tag.name}'></span>
                            </li>`;
                    })
                        html += '</ul>';
                }
                return html;
            }
        },
        init() {
            this.options.onInitialDrawComplete = () => { this.loading = false };
            this.events = new DataSet();
            this.timeline = new Timeline(this.$refs.timeline, this.events, this.options);
            this.getEvents();
            this.getTags();
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
                            tags: e.tags.map(t => {
                                return {
                                    id: t.id,
                                    name: t.name.fr,
                                    color: t.color
                                }
                            })
                        });
                    });
                    this.timeline.fit();
                }).catch(() => {
                    this.$dispatch('notify', {type: 'error', content: 'Impossible de charger les événements.'});
                });
        },
        tagRequestInProgress: [false],
        tagPreventDelete: [true],
        getTags() {
            axios.get('/tags')
                .then(response => {
                    response.data.forEach(t => {
                        this.tags[t.id] = t;
                        this.tagRequestInProgress[t.id] = false;
                        this.tagPreventDelete[t.id] = true;
                    })
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
                this.currentEvent = { id: null, name: null, description: null, date: null, tags: [] };
                axios.get('/events/' + event.detail)
                    .then(response => {
                        this.currentEvent = response.data;
                    }).catch(() => {
                        this.$dispatch('notify', {type: 'error', content: `Impossible de charger l'événements.`});
                    })
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
                        this.openEventFlyout = false;
                        this.currentEvent = { id: null, name: null, description: null, date: null, tags: [] };
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
                    this.events.update([{
                        id: response.data.id,
                        content: response.data.name,
                        start: response.data.date,
                        tags: response.data.tags.map(t => {
                            return {
                                id: t.id,
                                name: t.name.fr,
                                color: t.color
                            }
                        })
                    }]);
                    this.currentEvent = response.data;
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
                        start: response.data.date
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
            this.mode = 'listTag';
            this.openTagFlyout = true;
        },
        deleteTag(tag) {
            if (this.tagPreventDelete[tag.id]) {
                this.tagPreventDelete[tag.id] = false;
                setTimeout(() => {
                    this.tagPreventDelete[tag.id] = true;
                }, 3000)
            } else {
                this.tagPreventDelete[tag.id] = true;
                this.tagRequestInProgress[tag.id] = true;
                axios.delete('/tags/' + tag.id)
                    .then(() => {
                        delete this.tags[tag.id];
                        // Update tags in currentEvent if it has one or more
                        if (this.currentEvent.tags.length > 0) {
                            axios.get('/events/' + this.currentEvent.id)
                                .then(response => {
                                    this.currentEvent = response.data;
                                }).catch(() => {
                                    this.$dispatch('notify', { content: `Impossible de charger l'événements.`, type: 'error' });
                                })
                        }
                        this.$dispatch('notify', { content: `Le tag a bien été supprimé.`, type: 'success' })
                    }).catch(error => {
                        if (error.status === 404) {
                            this.$dispatch('notify', { content: `Le tag n'existe pas.`, type: 'error' })
                        } else {
                            this.$dispatch('notify', { content: `Une erreur s'est produite lors de la suppression.`, type: 'error' })
                        }
                    }).finally(() => { this.tagRequestInProgress[tag.id] = false; })
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
        updateTag(tag) {
            this.tagRequestInProgress[tag.id] = true;
            this.tagFormErrors = { name: [], color: [] };
            axios.put('/tags/' + tag.id, tag)
                .then(response => {
                    this.tags[tag.id] = response.data;
                    this.mode = 'listTag';
                    // Update tags in currentEvent if it has one or more
                    if (this.currentEvent.tags.length > 0) {
                        axios.get('/events/' + this.currentEvent.id)
                            .then(response => {
                                this.currentEvent = response.data;
                            }).catch(() => {
                                this.$dispatch('notify', { content: `Impossible de charger l'événements.`, type: 'error' });
                            })
                    }
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.tagFormErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                    }
                }).finally(() => { this.tagRequestInProgress[tag.id] = false; })
        },
        addTag(tag) {
            this.tagRequestInProgress[tag.id] = true;
            this.tagFormErrors = { name: [], color: [] };
            axios.post('/tags', tag)
                .then(response => {
                    this.tags[response.data.id] = response.data;
                    this.tagPreventDelete[response.data.id] = true;
                    this.tagRequestInProgress[response.data.id] = false;
                    this.mode = 'listTag';
                }).catch(error => {
                    if (error.response.status === 422) {
                        this.tagFormErrors = error.response.data.errors
                    } else {
                        this.$dispatch('notify', { content: `Une erreur s'est produite lors de l'ajout.`, type: 'error' })
                    }
                }).finally(() => { this.tagRequestInProgress[tag.id] = false; })
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
        <button
            @click="$dispatch('list-tags')"
            type="button"
            class="whitespace-nowrap h-min rounded-full bg-slate-100 p-3 text-base font-semibold text-indigo-500 shadow hover:shadow-xl hover:bg-slate-50 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.solid-tag size="size-6"/>

            <span class="sr-only">Gestion des tags</span>
        </button>

        <!-- Add event button -->
        <button @click="$dispatch('add-event')" type="button"
                class="flex items-center justify-center space-x-1 whitespace-nowrap h-min rounded-full bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow hover:shadow-xl hover:bg-indigo-500 outline-0 outline-transparent focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
        >
            <x-icons.plus size="size-6"/>

            <span class="sr-only">Ajouter un événement</span> <span aria-hidden="true">Événement</span>
        </button>
    </div>
</div>
