<?php
session_start();

require_once "../config/koneksi.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (
    !isset($_POST["username"]) ||
    !isset($_POST["password"]) ||
    !isset($_POST["public_key"]) ||
    !isset($_POST["encrypted_private_key"]) ||
    !isset($_POST["private_key_iv"]) ||
    !isset($_POST["private_key_salt"])
) {

    $_SESSION["error"] =
        "<div style='text-align:center;'>Invalid request</div>";

    header(
        "Location: ../views/register.php"
    );

    exit;
}

$username =
    trim($_POST["username"]);

$password =
    $_POST["password"];

$public_key =
    $_POST["public_key"];

$encrypted_private_key =
    $_POST["encrypted_private_key"];

$private_key_iv =
    $_POST["private_key_iv"];

$private_key_salt =
    $_POST["private_key_salt"];

$check = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE username=? LIMIT 1"
);

mysqli_stmt_bind_param(
    $check,
    "s",
    $username
);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {

    unset($password);

    $_POST["password"] = "";

    $_SESSION["error"] =
        "<div style='text-align:center;'>Username already taken, please choose another</div>";

    header(
        "Location: ../views/register.php"
    );

    exit;
}

$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

unset($password);

$_POST["password"] = "";

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users
    (
        username,
        password_hash,
        public_key,
        encrypted_private_key,
        private_key_iv,
        private_key_salt
    )
    VALUES (?,?,?,?,?,?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $username,
    $password_hash,
    $public_key,
    $encrypted_private_key,
    $private_key_iv,
    $private_key_salt
);

if (mysqli_stmt_execute($stmt)) {

    session_regenerate_id(true);

    $_SESSION["success"] =
        "<div style='text-align:center;'>Register successful, please login</div>";

    header(
        "Location: ../views/login.php"
    );

    exit;
} else {

    $_SESSION["error"] =
        "<div style='text-align:center;'>Register failed, please try again</div>";

    header(
        "Location: ../views/register.php"
    );

    exit;
}