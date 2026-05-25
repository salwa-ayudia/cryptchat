<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Register - CryptChat</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

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
                Create your secure encrypted account
            </p>

        </div>

        <?php if (isset($_SESSION["error"])) : ?>

            <div class="alert alert-danger">

                <?php
                echo $_SESSION["error"];
                unset($_SESSION["error"]);
                ?>

            </div>

        <?php endif; ?>

        <form
            action="../api/register.php"
            method="POST"
            id="registerForm">

            <div class="mb-3">

                <label class="form-label">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    placeholder="3-15 characters (letters, numbers, underscore)"
                    pattern="^(?=.*[a-zA-Z])[a-zA-Z0-9_]{3,15}$"
                    title="Username harus 3-15 karakter, berisi minimal 1 huruf, hanya huruf/angka/underscore"
                    required>
                
                <div id="username-error" style="display:none; margin-top:8px; padding:7px 12px; border-radius:10px; background:#fff8e1; color:#b45309; font-size:0.82rem; font-weight:500; letter-spacing:0.01em;">
                    ⚠ Username must be 3-15 characters with at least 1 letter (letters, numbers, underscore only)
                </div>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="reg_password"
                    class="form-control"
                    placeholder="Minimum 6 characters, including 1 number"
                    pattern="^(?=.*[0-9]).{6,}$"
                    title="Password must have at least 6 characters and contain at least 1 number"
                    required>

                <div id="password-error" style="display:none; margin-top:8px; padding:7px 12px; border-radius:10px; background:#fff8e1; color:#b45309; font-size:0.82rem; font-weight:500; letter-spacing:0.01em;">
                    ⚠ Password must be at least 6 characters and contain at least 1 number
                </div>

                <div id="capslock-warning-reg" style="display:none; margin-top:8px; padding:7px 12px; border-radius:10px; background:#fff8e1; color:#b45309; font-size:0.82rem; font-weight:500; letter-spacing:0.01em;">
                    ⇪ Caps Lock is ON
                </div>

            </div>

            <input
                type="hidden"
                name="public_key"
                id="public_key">

            <input
                type="hidden"
                name="encrypted_private_key"
                id="encrypted_private_key">

            <input
                type="hidden"
                name="private_key_iv"
                id="private_key_iv">

            <input
                type="hidden"
                name="private_key_salt"
                id="private_key_salt">

            <button
                class="btn btn-primary auth-btn">
                Register Securely
            </button>

        </form>

        <div class="text-center mt-4">
            <small 
                class="text-muted">
                Already have an account?
            </small>
            <br>
            <a
                href="login.php"
                class="auth-link">
                Login here
            </a>
        </div>
    </div>

    <script src="../assets/js/crypto.js"></script>

    <script>
        // Deteksi Caps Lock
        (function () {
            var input   = document.getElementById("reg_password");
            var warning = document.getElementById("capslock-warning-reg");

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
        
        // Validasi Username real-time
        (function () {
            var usernameInput = document.getElementById("username");
            var usernameError = document.getElementById("username-error");
            var pattern = /^(?=.*[a-zA-Z])[a-zA-Z0-9_]{3,15}$/;

            function validateUsername() {
                var value = usernameInput.value.trim();
                
                if (value === "") {
                    usernameError.style.display = "none";
                    usernameInput.classList.remove("is-invalid");
                    return true;
                }

                if (pattern.test(value)) {
                    usernameError.style.display = "none";
                    usernameInput.classList.remove("is-invalid");
                    return true;
                } else {
                    usernameError.style.display = "block";
                    usernameInput.classList.add("is-invalid");
                    return false;
                }
            }

            usernameInput.addEventListener("input", validateUsername);
            usernameInput.addEventListener("blur", validateUsername);
        })();
        
        // Validasi Password real-time
        (function () {
            var passwordInput = document.getElementById("reg_password");
            var passwordError = document.getElementById("password-error");
            var pattern = /^(?=.*[0-9]).{6,}$/;

            function validatePassword() {
                var value = passwordInput.value;
                
                if (value === "") {
                    passwordError.style.display = "none";
                    passwordInput.classList.remove("is-invalid");
                    return true;
                }

                if (pattern.test(value)) {
                    passwordError.style.display = "none";
                    passwordInput.classList.remove("is-invalid");
                    return true;
                } else {
                    passwordError.style.display = "block";
                    passwordInput.classList.add("is-invalid");
                    return false;
                }
            }

            passwordInput.addEventListener("input", validatePassword);
            passwordInput.addEventListener("blur", validatePassword);
        })();
        
        // Hapus material kriptografi yang sudah usang saat mendarat di halaman register
        sessionStorage.clear();

        document
            .getElementById("registerForm")
            .addEventListener(
                "submit",
                async function (e) {

                    e.preventDefault();
                    
                    // Validasi final username
                    var usernameInput = document.getElementById("username");
                    var usernamePattern = /^(?=.*[a-zA-Z])[a-zA-Z0-9_]{3,15}$/;
                    
                    if (!usernamePattern.test(usernameInput.value.trim())) {
                        alert("Username tidak valid! Harus 3-15 karakter dengan minimal 1 huruf.");
                        usernameInput.focus();
                        return;
                    }
                    
                    // Validasi final password
                    var passwordInput = document.getElementById("reg_password");
                    var passwordPattern = /^(?=.*[0-9]).{6,}$/;
                    
                    if (!passwordPattern.test(passwordInput.value)) {
                        alert("Password must be at least 6 characters and contain at least 1 number!");
                        passwordInput.focus();
                        return;
                    }

                    const result =
                        await generateKeyPair();

                    const password =
                        document.querySelector(
                            'input[name="password"]'
                        ).value;

                    const encrypted =
                        await encryptPrivateKey(
                            result.privateKey,
                            password
                        );

                    document.getElementById(
                        "public_key"
                    ).value =
                        result.publicKey;

                    document.getElementById(
                        "encrypted_private_key"
                    ).value =
                        encrypted.ciphertext;

                    document.getElementById(
                        "private_key_iv"
                    ).value =
                        encrypted.iv;

                    document.getElementById(
                        "private_key_salt"
                    ).value =
                        encrypted.salt;

                    this.submit();
                }
            );
    </script>

</body>

</html>