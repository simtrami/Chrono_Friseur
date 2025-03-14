<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frise Chronologique Interactive</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="styles.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div id="timeline"></div>
    </div>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="app.js"></script>
    <script src="datetime-format.js"></script>
</body>
</html>
