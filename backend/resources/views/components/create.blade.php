<form name="createEvent">
    @csrf
    <input type="text" name="name" placeholder="Name">
    <textarea name="description" placeholder="Description"></textarea>
    <input type="text" name="date" placeholder="YYYY-MM-DD hh:mm:ss">
    <button type="submit" class="">Submit</button>
</form>

<script>
    document.querySelector('form[name="createEvent"]').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const response = await fetch('/events', {
            method: 'POST',
            body: formData
        });
        if (response.ok) {
            // window.location.reload();
            console.log('Event created:', await response.json());
            // TODO: Add the new event to the timeline
        }
    });
</script>