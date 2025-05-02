<form {{ $attributes }}
      x-data="{
        imagePreview: '',
        showPreview: false,
        init() {
            this.$watch('formData.name', (value) => {
                this.formData.slug = slugify(value, {lower: true});
            });
        },
        submit() {
            if (mode === 'edit') {
                this.update();
            } else if (mode === 'create') {
                this.create();
            }
        },
        makeFormData() {
            if (this.mode === 'create') {
                const formData = new FormData();
                formData.append('name', this.formData.name ?? '');
                formData.append('slug', this.formData.slug ?? '');
                formData.append('description', this.formData.description ?? '');
                if (this.formData.picture) {
                    formData.append('picture', this.formData.picture);
                }
                return formData;
            }
            return {
                name: this.formData.name ?? '',
                slug: this.formData.slug ?? '',
                description: this.formData.description ?? ''
            };
        },
        update() {
            this.requestInProgress = true;
            this.formErrors = { name: [], slug: [], description: [], picture: [] };
            axios.put('/timelines/' + this.formData.id, this.makeFormData()).then(response => {
                this.timelines.updateOnly(this.makeTimelineData(response.data.data));
                this.openFormFlyout = false;
            }).catch(error => {
                if (error.response.status === 422) {
                    this.formErrors = error.response.data.errors
                } else {
                    this.$dispatch('notify', { content: `Une erreur s'est produite lors de la modification.`, type: 'error' })
                }
            }).finally(() => { this.requestInProgress = false; })
        },
        create() {
            this.requestInProgress = true;
            this.formErrors = { name: [], slug: [], description: [], picture: [] };
            axios.post('/timelines', this.makeFormData(), {
                headers: { 'content-type': 'multipart/form-data' }
            }).then(response => {
                this.timelines.add(this.makeTimelineData(response.data.data));
                this.openFormFlyout = false;
                this.formData = { id: null, name: '', slug: null, description: null, picture: null }
            }).catch(error => {
                if (error.response.status === 422) {
                    this.formErrors = error.response.data.errors
                } else {
                    this.$dispatch('notify', { content: `Une erreur s'est produite lors de la création.`, type: 'error' })
                }
            }).finally(() => { this.requestInProgress = false; })
        },
        cancel() {
            this.formData = { id: null, name: '', slug: null, description: null, picture: null }
            this.openFormFlyout = false;
        }
      }"
      @submit.prevent="submit()"
      :id="`timeline-form-${formData.id ?? 'add'}`"
>
    <div class="flex flex-col my-2 space-y-3 max-w-2xl text-gray-500 border-b border-gray-900/10 pb-6">
        <!-- Name -->
        <div class="">
            <label for="name" class="block text-sm/6 font-medium">Nom</label>
            <div class="mt-2">
                <input x-model="formData.name" type="text" name="name" id="name"
                       class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                >
            </div>
            <template x-for="error in formErrors.name" :key="error">
            <x-form-error x-text="error"/>
            </template>
        </div>

        <!-- Slug -->
        <div class="">
            <label for="slug" class="block text-sm/6 font-medium">Slug (URL)</label>
            <div class="mt-2">
                <input x-model="formData.slug" type="text" name="slug" id="slug"
                       class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                >
            </div>
            <template x-for="error in formErrors.slug" :key="error">
            <x-form-error x-text="error"/>
            </template>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm/6 font-medium">Description</label>

            <div class="mt-2">
                <textarea x-model="formData.description" id="description" name="description"
                          class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                ></textarea>
            </div>

            <template x-for="error in formErrors.description" :key="error">
            <x-form-error x-text="error"/>
            </template>
        </div>

        <!-- Picture -->
        <div x-show="mode === 'create'">
            <label for="picture" class="block text-sm/6 font-medium">Illustration</label>

            <div class="mt-2">
                <div
                    class="flex flex-col justify-start items-left gap-y-2 sm:flex-row sm:items-center sm:justify-between sm:gap-y-0 sm:justify-between"
                >
                    <div class="flex flex-col items-left gap-y-2 sm:flex-row sm:items-center sm:gap-y-0 sm:gap-x-3">
                        <div class="flex items-center">
                            <svg x-show="! showPreview" class="size-12 text-gray-300" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24" fill="currentColor" data-slot="icon"
                            >
                                <path fill-rule="evenodd"
                                      d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z"
                                      clip-rule="evenodd"
                                />
                            </svg>

                            <div x-show="showPreview" class="size-12">
                                <img :src="imagePreview" alt="Preview" class="w-12 max-h-12 object-cover h-auto">
                            </div>
                        </div>


                        <div class="flex items-center">
                            <input name="picture" id="picture"
                                   x-on:change="formData.picture = $event.target.files[0]; imagePreview = URL.createObjectURL(formData.picture); showPreview = true"
                                   type="file" accept="image/png, image/jpeg, image/gif"
                                   class="file:rounded-md file:bg-white file:px-2.5 file:py-1.5 file:text-sm file:font-semibold file:text-gray-900 file:border-0 file:shadow-xs file:ring-1 file:ring-gray-300 file:ring-inset hover:file:bg-gray-50 file:transition"
                            >
                        </div>
                    </div>

                    <div class="flex items-center">
                        <button x-show="showPreview"
                                @click="showPreview = false; $refs.picture.value=''; formData.picture = null; imagePreview = ''"
                                type="button"
                                class="rounded-md bg-red-500 px-2.5 py-1.5 text-sm font-semibold text-white shadow-xs ring-1 ring-red-700 ring-inset hover:bg-red-400 transition"
                        >Retirer
                        </button>
                    </div>
                </div>
            </div>

            <template x-for="error in formErrors.picture" :key="error">
            <x-form-error x-text="error"/>
            </template>
        </div>
    </div>

    <!-- Actions for edit -->
    <div x-show="mode === 'edit'" class="mt-6 flex justify-end space-x-2">
        <button @click.prevent="cancel()" type="button"
                class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent bg-transparent px-3 py-2 font-semibold text-gray-800 hover:bg-gray-800/10 transition"
        ><span>Annuler</span></button>

        <button type="submit" :disabled="requestInProgress"
                class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-2 text-white font-semibold bg-indigo-600 outline-0 outline-transparent hover:bg-indigo-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
                :class="{'opacity-50 cursor-not-allowed': requestInProgress}"
        >
            <template x-if="!requestInProgress">
            <x-icons.pencil-square size="size-5"/>
            </template>

            <template x-if="requestInProgress">
            <x-icons.spinner size="size-5"/>
            </template>

            <span>Appliquer</span>
        </button>
    </div>

    <!-- Actions for create -->
    <div x-show="mode === 'create'" class="mt-6 flex justify-end space-x-2">
        <button @click.prevent="$dialog.close()" type="button"
                class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent bg-transparent px-3 py-2 font-semibold text-gray-800 hover:bg-gray-800/10 transition"
        ><span>Annuler</span></button>

        <button type="submit" :disabled="requestInProgress"
                class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-2 text-white font-semibold bg-indigo-600 outline-0 outline-transparent hover:bg-indigo-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
                :class="{'opacity-50 cursor-not-allowed': requestInProgress}"
        >
            <template x-if="!requestInProgress">
            <x-icons.plus size="size-5"/>
            </template>

            <template x-if="requestInProgress">
            <x-icons.spinner size="size-5"/>
            </template>

            <span>Créer</span>
        </button>
    </div>
</form>
