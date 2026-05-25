<?php
session_start();

require_once "../config/koneksi.php";

// cegah cache
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (
    !isset($_POST["login_user"]) ||
    !isset($_POST["login_pass"])
) {
    header("Location: ../views/login.php");
    exit;
}

$username = trim($_POST["login_user"]);
$password = $_POST["login_pass"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users WHERE username=? LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {

    if (
        password_verify(
            $password,
            $user["password_hash"]
        )
    ) {

        // hapus password dari memori
        unset($password);
        $_POST["login_pass"] = "";

        // generate session lagi
        session_regenerate_id(true);

        $_SESSION["user_id"] =
            $user["id"];

        $_SESSION["username"] =
            $user["username"];

        $_SESSION["encrypted_private_key"] =
            $user["encrypted_private_key"];

        $_SESSION["private_key_iv"] =
            $user["private_key_iv"];

        $_SESSION["private_key_salt"] =
            $user["private_key_salt"];

        header(
            "Location: ../views/dashboard.php"
        );
        exit;
    } else {

        unset($password);

        $_SESSION["error"] =
            "<div style='text-align:center;'>Password incorrect</div>";

        header(
            "Location: ../views/login.php"
        );
        exit;
    }
} else {

    unset($password);

    $_SESSION["error"] =
        "<div style='text-align:center;'>User not found</div>";

    header(
        "Location: ../views/login.php"
    );
    exit;
}