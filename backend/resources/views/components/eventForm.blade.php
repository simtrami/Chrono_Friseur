<!-- filepath: /c:/Users/bapti/Documents/PROJETS_CODE/Chrono_Friseur/backend/resources/views/components/event-form.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Form</title>
    
    <link rel="stylesheet" href="flatpickr.min.css">
    <script src="flatpickr.js"></script>
</head>

<body>
<form id="eventForm">
    @csrf
    <input type="hidden" name="event-id" value="{{ $id ?? '' }}">
    <div class="m-5 p-5" style="display: flex; flex-direction: column; align-items: center;">
        <div class="space-y-12">
            <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="mt-2">
                            <input type="text" name="name" id="name" value="{{ $name ?? '' }}"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                    </div>
                    <div class="sm:col-span-4">
                        <div class="mt-2">
                            <textarea id="description" name="description"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">{{ $description ?? '' }}</textarea>
                        </div>
                    </div>
                    <div x-data="{
                        value: '{{ $date ?? '' }}',
                        init() {
                            let picker = flatpickr(this.$refs.picker, {
                                dateFormat: 'd/m/Y h:i',
                                defaultDate: this.value,
                                onChange: (date, dateString) => {
                                    this.value = dateString
                                }
                            })
                            this.$watch('value', () => picker.setDate(this.value))
                        },
                    }" class="sm:col-span-3">
                        <div class="mt-2">
                            <input class="mt-3 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm"
                                x-ref="picker" id="picker" type="text" name="date" value="{{ $date ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="button" class="text-sm/6 font-semibold text-gray-900">Annuler</button>
            <button type="submit"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sauvegarder</button>
            <button type="button" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600", id="deleteEventButton">Suprimer</button>
        </div>
    </div>
</form>
</body>
</html>

