<?php
require_once "../config/koneksi.php";

header("Content-Type: application/json");

if (!isset($_GET["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "User ID required"
    ]);
    exit;
}

$user_id = $_GET["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT public_key FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && !empty($user["public_key"])) {
    echo json_encode([
        "success" => true,
        "public_key" => $user["public_key"]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Public key tidak ditemukan"
    ]);
}