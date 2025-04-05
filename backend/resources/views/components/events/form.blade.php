<form
    {{ $attributes }}
    :id="mode + '-event-form'"
    x-data="{
        picker: null,
        init() {
            this.picker = flatpickr(this.$refs.picker, {
                dateFormat: 'Y-m-d H:i',
                altInput: true,
                altFormat: 'd/m/Y H:i',
                allowInput: true,
                enableTime: true,
                inline: true,
                locale: 'fr',
                onChange: (date, dateString) => {
                    this.currentEvent.date = dateString
                }
            })
            this.$watch('currentEvent', (event) => this.picker.setDate(event.date))
        },
        get label(){
            if(this.currentEvent.tags.length === 0){
                return 'Choisir des tags...';
            }
            return this.currentEvent.tags.length === 1 ? this.currentEvent.tags[0].name.fr : `${this.currentEvent.tags.length} sélectionnés`;
        }
    }"
    @submit.prevent="mode === 'edit' ? updateEvent() : addEvent()"
>
    <div class="flex flex-col my-2 space-y-2 max-w-2xl text-gray-500 border-b border-gray-900/10 pb-6">
        <!-- Name -->
        <div class="">
            <label for="name" class="block text-sm/6 font-medium">Nom</label>
            <div class="mt-2">
                <input x-model="currentEvent.name" type="text" name="name" id="name"
                       class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                >
            </div>
            <template x-for="error in formErrors.name" :key="error">
                <x-form-error x-text="error"/>
            </template>
        </div>

        <!-- Tags -->
        <div>
            <label for="tags" class="text-sm font-medium select-none">Tags</label>

            <div class="mt-2">
                <div x-listbox x-model="currentEvent.tags" class="relative p-0 bg-transparent border-0" multiple
                     by="id"
                >
                    <!-- Listbox Label -->
                    <label x-listbox:label class="sr-only">Tags</label>

                    <!-- Listbox Button -->
                    <button x-listbox:button
                            class="group flex w-full items-center justify-between gap-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600"
                    >
                        <span x-text="label" class="truncate text-base text-gray-900 sm:text-sm/6"
                              :class="{ '!text-gray-400': $listbox.value.length === 0 }"
                        ></span>

                        <!-- Heroicons up/down -->
                        <svg class="size-5 shrink-0 text-gray-300 group-hover:text-gray-800"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                             aria-hidden="true"
                        >
                            <path fill-rule="evenodd"
                                  d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                                  clip-rule="evenodd"
                            ></path>
                        </svg>
                    </button>

                    <!-- Listbox Options -->
                    <ul x-listbox:options x-cloak
                        class="absolute right-0 z-10 mt-2 max-h-80 w-full overflow-y-scroll overscroll-contain rounded-md border border-gray-300 bg-white p-1.5 shadow-sm outline-none"
                    >
                        <template x-if="tags.length === 0">
                            <li class="text-gray-400 cursor-not-allowed group flex w-full items-center rounded-md px-2 py-1.5 transition-colors">
                                <div class="w-6 shrink-0">
                                    <!-- Face frown -->
                                    <x-icons.face-frown size="size-5" class="shrink-0"/>
                                </div>

                                <span>Pas de tags</span>
                            </li>
                        </template>

                        <template x-for="tag in tags" :key="tag.id">
                            <!-- Listbox Option -->
                            <li
                                x-listbox:option
                                :value="tag"
                                :disabled="false"
                                :class="{
                            'bg-gray-100': $listboxOption.isActive,
                            'text-gray-900': ! $listboxOption.isActive && ! $listboxOption.isDisabled,
                            'text-gray-400 cursor-not-allowed': $listboxOption.isDisabled,
                        }"
                                class="group flex w-full cursor-default items-center rounded-md px-2 py-1.5 transition-colors"
                            >
                                <div class="w-6 shrink-0">
                                    <svg class="size-5 shrink-0" x-show="$listboxOption.isSelected"
                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                         aria-hidden="true"
                                    >
                                        <path fill-rule="evenodd"
                                              d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                              clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                </div>

                                <span x-text="tag?.name.fr"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
            <template x-for="error in formErrors.tags" :key="error">
                <x-form-error x-text="error"/>
            </template>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm/6 font-medium">Description</label>
            <div class="mt-2">
                <textarea x-model="currentEvent.description" id="description" name="description"
                          class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                ></textarea>
            </div>
            <template x-for="error in formErrors.description" :key="error">
                <x-form-error x-text="error"/>
            </template>
        </div>

        <!-- Date -->
        <div>
            <label for="picker" class="text-sm font-medium select-none">Date</label>
            <div class="mt-2">
                <input x-ref="picker" x-model="currentEvent.date" id="picker" type="text" name="date"
                       placeholder="01/01/2001 16:20"
                       class="block w-full rounded-md bg-white px-3 py-1.5 mb-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                >
            </div>
            <template x-for="error in formErrors.date" :key="error">
                <x-form-error x-text="error"/>
            </template>
        </div>
    </div>
</form>
