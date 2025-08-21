<div {{ $attributes }}
     x-data="{
        importRequestInProgress: false,
        csvFile: null,
        csvValidationState: null, // 'success', 'error', or null
        csvValidationMessage: '',
        csvFileInfo: null,
        resetCsvImport() {
            this.csvFile = null;
            this.csvValidationState = null;
            this.csvValidationMessage = '';
            this.csvFileInfo = null;
        },
        importCsvFile() {
            if (!this.csvFile || this.csvValidationState !== 'success') return;

            this.importRequestInProgress = true;

            const formData = new FormData();
            formData.append('csv_file', this.csvFile);

            axios.post('/events/import', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                // Show success notification
                this.$dispatch('notify', { type: 'success', content: response.data.message });

                // Show warnings if any
                if (response.data.warnings && response.data.warnings.length > 0) {
                    setTimeout(() => {
                        this.$dispatch('notify', { type: 'info', content: response.data.warnings.join(', ') });
                    }, 2000);
                }

                // Close the flyout and reset state
                this.openCsvImportFlyout = false;
                this.resetCsvImport();

                // Refresh the timeline data
                this.getData();
            }).catch(error => {
                let errorMessage = 'Une erreur est survenue lors de l\'importation.';

                if (error.response && error.response.data.message) {
                    errorMessage = error.response.data.message;
                }

                this.$dispatch('notify', { type: 'error', content: errorMessage });
            }).finally(() => { this.importRequestInProgress = false; });
        }
    }"
>
    <form @submit.prevent="importCsvFile()" id="csv-import-form"
          x-data="{
            isDragOver: false,
            fileDropped(event) {
                isDragOver = false;
                let files = event.dataTransfer.files;
                if (files.length > 0) {
                    csvFile = files[0];
                    csvValidationState = null;
                    csvValidationMessage = '';
                    csvFileInfo = null;
                }
            },
            validateCsvFile() {
                if (!this.csvFile) return;

                // Reset validation state
                this.csvValidationState = null;
                this.csvValidationMessage = '';
                this.csvFileInfo = null;

                const formData = new FormData();
                formData.append('csv_file', this.csvFile);

                axios.post('/events/import/validate', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    // Set success state
                    this.csvValidationState = 'success';
                    this.csvValidationMessage = response.data.message;
                    this.csvFileInfo = response.data.file_info;
                }).catch(error => {
                    // Set error state
                    this.csvValidationState = 'error';

                    if (error.response && error.response.data.errors) {
                        const errors = error.response.data.errors;
                        this.csvValidationMessage = Object.values(errors).flat().join(' ');
                    } else {
                        this.csvValidationMessage = 'Une erreur est survenue lors de la validation du fichier.';
                    }
                });
            }
        }"
    >
        <div class="flex flex-col my-2 space-y-3 max-w-2xl text-gray-500 border-b border-gray-900/10 mb-4 pb-6">
            <!-- File Upload Area -->
            <div @drop.prevent="fileDropped($event)"
                 @dragover.prevent="isDragOver = true"
                 @dragleave.prevent="isDragOver = false"
                 class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
                 :class="{ 'border-indigo-400 bg-indigo-50': isDragOver }"
            >
                <div x-show="!csvFile">
                    <x-icons.document-arrow-up class="mx-auto h-12 w-12 text-gray-400"/>

                    <div class="mt-4">
                        <label for="csv-file" class="cursor-pointer">
                        <span class="mt-2 block text-sm font-medium text-gray-900">
                            Glissez-déposez votre fichier CSV ici, ou
                            <span class="text-indigo-600 hover:text-indigo-500">cliquez pour parcourir</span>
                        </span>
                        </label>

                        <input id="csv-file" name="csv-file" type="file" accept=".csv" class="sr-only"
                               @change="csvFile = $event.target.files[0]; csvValidationState = null; csvValidationMessage = ''; csvFileInfo = null;"
                        >
                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Fichiers CSV uniquement, 10 Mo max
                    </p>
                </div>

                <!-- File Selected -->
                <div x-show="csvFile" class="space-y-4">
                    <div class="flex items-center justify-center space-x-2">
                        <x-icons.document-arrow-up class="h-8 w-8 text-green-500"/>

                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900" x-text="csvFile?.name"></p>

                            <p class="text-xs text-gray-500"
                               x-text="csvFile ? (csvFile.size / 1024 / 1024).toFixed(2) + ' Mo' : ''"
                            ></p>
                        </div>
                    </div>

                    <div class="flex items-center justify-center space-x-2">
                        <button @click="resetCsvImport(); document.getElementById('csv-file').value = ''" type="button"
                                class="inline-flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-1.5 font-medium text-xs text-gray-800 bg-transparent hover:bg-gray-800/10 focus:outline-2 focus:outline-gray-600 transition"
                        >
                            <x-icons.x-mark size="size-3" class="mr-1"/>
                            Annuler
                        </button>

                        <!-- Validation Button -->
                        <button @click="validateCsvFile()" type="button"
                                class="inline-flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-1.5 font-medium text-xs text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 transition"
                        >
                            Valider le fichier
                        </button>
                    </div>
                </div>
            </div>

            <!-- Validation Results -->
            <div x-show="csvValidationState" class="space-y-3">
                <!-- Success State -->
                <div x-show="csvValidationState === 'success'"
                     class="bg-green-50 border border-green-200 rounded-md p-4"
                >
                    <div class="flex">
                        <x-icons.check-circle size="size-5" class="text-green-400"/>

                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Fichier valide !</h3>

                            <div class="mt-2 text-sm text-green-700">
                                <p x-text="csvValidationMessage"></p>

                                <div x-show="csvFileInfo" class="mt-2">
                                    <p><strong>Détails du fichier :</strong></p>

                                    <ul class="list-disc list-inside">
                                        <li x-text="`Lignes&nbsp;: ${csvFileInfo?.lines || 0}`"></li>

                                        <li x-text="`Taille&nbsp;: ${csvFileInfo?.size ? (csvFileInfo.size / 1024 / 1024).toFixed(2) + ' Mo' : 'N/A'}`"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div x-show="csvValidationState === 'error'" class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <x-icons.x-circle size="size-5" class="text-red-400"/>

                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Fichier invalide</h3>

                            <div class="mt-2 text-sm text-red-700">
                                <p x-text="csvValidationMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSV Format Info -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-md p-4">
                <div class="flex">
                    <x-icons.information-circle size="size-5" class="text-indigo-400"/>

                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-indigo-800">Format CSV requis</h3>

                        <div class="mt-2 text-sm text-indigo-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Séparateur : point-virgule (;)</li>

                                <li>Colonnes : Nom;Description;Date;Étiquettes</li>

                                <li>Date : format YYYY-MM-DD</li>

                                <li>Étiquettes : séparées par des virgules</li>

                                <li>Maximum : 1000 lignes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end space-x-2">
            <button @click.prevent="$dialog.close(); resetCsvImport()" type="button"
                    class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent bg-transparent px-3 py-2 font-semibold text-gray-800 hover:bg-gray-800/10 focus:outline-2 focus:outline-gray-600 transition"
            >
                Annuler
            </button>

            <button @click.prevent="importCsvFile()" type="submit"
                    class="relative flex items-center justify-center space-x-1 whitespace-nowrap rounded-lg border border-transparent px-3 py-2 text-white font-semibold bg-indigo-600 outline-0 outline-transparent hover:bg-indigo-500 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-700 disabled:bg-indigo-100 disabled:cursor-not-allowed transition"
                    :class="{'opacity-50 cursor-not-allowed': importRequestInProgress}"
                    :disabled="!csvFile || csvValidationState !== 'success' || importRequestInProgress"
            >
                <span x-show="!importRequestInProgress">
                    <x-icons.document-arrow-up size="size-5"/>
                </span>

                <template x-if="importRequestInProgress">
                <x-icons.spinner size="size-5"/>
                </template>

                <span>Importer</span>
            </button>
        </div>
    </form>
</div>
