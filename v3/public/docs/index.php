<!DOCTYPE html>
<html>

<head>
    <title>LinkSkool API Docs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist/swagger-ui.css">
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: "./swagger.json",
            dom_id: "#swagger-ui",
            deepLinking: true,
            layout: "BaseLayout",
            docExpansion: "none",
            tagsSorter: "alpha",
            operationsSorter: "alpha"
        });
    </script>
</body>

</html>