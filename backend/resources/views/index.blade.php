<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frise Chronologique Interactive</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="flatpickr.min.css">
    <script src="flatpickr.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="m-0 p-0 flex justify-center items-center h-screen bg-gray-100 font-sans">
    <div class="flex flex-col items-center">
        <div id="timeline" class="border border-gray-300 bg-white relative"></div>
    </div>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="app.js"></script>
    <script src="datetime-format.js"></script>
</body>
</html>
