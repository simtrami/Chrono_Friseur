<form name="updateEvent">
    @csrf
    @method('PUT')
    <input type="text" name="name" placeholder="Name">
    <textarea name="description" placeholder="Description"></textarea>
    <input type="text" name="date" placeholder="YYYY-MM-DD hh:mm:ss">
    <button type="submit" class="">Submit</button>
</form>

<script>
    id = 1;
    // Fetch selected event data
    fetch('/events/' + id)
        .then(response => response.json())
        .then(data => {
            const eventSQL = data;
            console.log(eventSQL);
            // Populate the form fields with the fetched data
            document.querySelector('form[name="updateEvent"]>input[name="name"]').value = eventSQL.name;
            document.querySelector('form[name="updateEvent"]>textarea[name="description"]').value = eventSQL.description;
            document.querySelector('form[name="updateEvent"]>input[name="date"]').value = eventSQL.date;
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des événements:', error);
        });

    // Handle form submission
    document.querySelector('form[name="updateEvent"]').addEventListener('submit', async (e) => {
        e.preventDefault();
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
        }
    });
</script>