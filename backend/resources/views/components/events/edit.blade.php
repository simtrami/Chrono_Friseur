<form
    {{ $attributes }}
    x-data="{
        init() {
            let picker = flatpickr(this.$refs.picker, {
                dateFormat: 'Y-m-d H:i',
                altInput: true,
                altFormat: 'd/m/Y H:i',
                allowInput: true,
                enableTime: true,
                onChange: (date, dateString) => {
                    this.value = dateString
                }
            })
            this.$watch('selectedItem', (item) => picker.setDate(item.date))
        }
    }"
    @submit.prevent="submit()"
>
    <div class="flex flex-col mt-2 max-w-2xl text-gray-500 border-b border-gray-900/10 pb-6">
        <!-- Name -->
        <div class="row mb-2">
            <label for="name" class="block text-sm/6 font-medium">Nom</label>
            <div class="mt-2">
                <input x-model="selectedItem.name" type="text" name="name" id="name"
                       class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
            <template x-for="error in formErrors.name" :key="error">
                <p x-text="error" class="mt-2 text-sm text-red-600" id="name-error"></p>
            </template>
        </div>

        <!-- Description -->
        <div class="row mb-2">
            <label for="description" class="block text-sm/6 font-medium">Description</label>
            <div class="mt-2">
                <textarea x-model="selectedItem.description" id="description" name="description"
                          class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                ></textarea>
            </div>
            <template x-for="error in formErrors.description" :key="error">
                <p x-text="error" class="mt-2 text-sm text-red-600" id="description-error"></p>
            </template>
        </div>

        <!-- Date -->
        <div class="row">
            <label for="picker" class="text-sm font-medium select-none">Date</label>
            <div class="mt-2">
                <input
                    x-ref="picker" x-model="selectedItem.date" id="picker" type="text" name="date"
                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
            <template x-for="error in formErrors.date" :key="error">
                <p x-text="error" class="mt-2 text-sm text-red-600" id="date-error"></p>
            </template>
        </div>
    </div>
</form>
