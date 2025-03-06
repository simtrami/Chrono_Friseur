<!-- filepath: /C:/Users/bapti/Documents/PROJETS_CODE/Chrono_Friseur/backend/resources/views/event-form.blade.php -->
<form id="eventUpdater"></form>
    @csrf
    @method('PUT')
    <input type="hidden" id="event-id" name="event-id" value="{{ $id }}" />
    <label for="name">Nom:</label>
    <input type="text" id="name" name="name" value="{{ $name }}" /><br>
    <label for="description">Description:</label>
    <textarea id="description" name="description">{{ $description }}</textarea><br>
    <label for="date">Date:</label>
    <input type="text" id="date" name="date" value="{{ $date }}" /><br>
    <button type="submit" class="">Sauvegarder</button>
</form>

<script>
    console.log('vivant');
    document.getElementById('eventUpdater').addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Submitting form data:', e.target);
        const id = e.target['event-id'].value;
        const formData = {
            name: e.target.name.value,
            description: e.target.description.value,
            date: e.target.date.value
        };
        const response = await fetch('/events/' + id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(formData)
        });
        if (response.ok) {
            console.log('Event updated:', await response.json());
            // TODO: Add the new event to the timeline
        } else {
            console.error('Failed to update event:', await response.text());
        }
    });
</script>