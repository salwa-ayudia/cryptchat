<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
</head>

<body>
    <script>
        sessionStorage.clear();
        localStorage.clear();

        window.location.replace("login.php");
    </script>
</body>

</html>