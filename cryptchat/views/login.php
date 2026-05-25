<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Login - CryptChat</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap"
        rel="stylesheet">
    <link
        rel="stylesheet"
        href="../assets/css/style.css">
</head>

<body class="auth-body">

    <div class="auth-card">
        <div class="text-center">
            <h1 class="logo-text">
                NSA Talks
            </h1>
            <p class="auth-subtitle">
                Secure End-to-End Encrypted Messaging
            </p>
        </div>

        <?php if (isset($_SESSION["success"])) : ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION["success"];
                unset($_SESSION["success"]);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])) : ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form
            action="../api/login.php"
            method="POST"
            id="loginForm"
            autocomplete="off">
            <div class="mb-3">
                <label class="form-label">
                    Username
                </label>
                <input
                    type="text"
                    name="login_user"
                    class="form-control"
                    placeholder="Enter username"
                    autocomplete="off"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                    required>
            </div>
            <div class="mb-4">
                <label class="form-label">
                    Password
                </label>
                <input
                    type="password"
                    name="login_pass"
                    id="login_pass"
                    class="form-control"
                    placeholder="Enter password"
                    autocomplete="new-password"
                    required>
                <div id="capslock-warning-login" style="display:none; margin-top:8px; padding:7px 12px; border-radius:10px; background:#fff8e1; color:#b45309; font-size:0.82rem; font-weight:500; letter-spacing:0.01em;">
                    ⇪ Caps Lock is ON
                </div>
            </div>
            <button
                type="submit"
                class="btn btn-primary auth-btn">
                Login Securely
            </button>
        </form>
        <div class="text-center mt-4">
            <small class="text-muted">
                Don't have an account?
            </small>
            <br>
            <a
                href="register.php"
                class="auth-link">
                Register here
            </a>
        </div>
    </div>

    <script>
        // capslock on
        (function () {
            var input   = document.getElementById("login_pass");
            var warning = document.getElementById("capslock-warning-login");

            function checkCaps(e) {
                if (typeof e.getModifierState === "function") {
                    warning.style.display = e.getModifierState("CapsLock") ? "block" : "none";
                }
            }

            input.addEventListener("keydown", checkCaps);
            input.addEventListener("keyup",   checkCaps);

            input.addEventListener("blur", function () {
                warning.style.display = "none";
            });
        })();

        document
            .getElementById("loginForm")
            .addEventListener(
                "submit",
                function () {
                    const password =
                        document.querySelector(
                            'input[name="login_pass"]'
                        ).value;

                    sessionStorage.clear();

                    sessionStorage.setItem(
                        "loginPassword",
                        password
                    );
                }
            );
    </script>
</body>

</html>