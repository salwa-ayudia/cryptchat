<?php
session_start();

require_once "../config/koneksi.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "User belum login."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan."
    ]);
    exit;
}

if (
    !isset($_POST["conversation_id"]) ||
    !isset($_POST["ciphertext"]) ||
    !isset($_POST["iv"])
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Data pesan tidak lengkap."
    ]);
    exit;
}

$conversation_id = (int) $_POST["conversation_id"];
$ciphertext = trim($_POST["ciphertext"]);
$iv = trim($_POST["iv"]);
$sender_id = (int) $_SESSION["user_id"];

if ($conversation_id <= 0 || $ciphertext === "" || $iv === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Data pesan tidak valid."
    ]);
    exit;
}

// cek apakah pengirim adalah anggota conversation
$check = mysqli_prepare(
    $conn,
    "SELECT id
     FROM conversations
     WHERE id = ?
     AND (user1_id = ? OR user2_id = ?)
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check,
    "iii",
    $conversation_id,
    $sender_id,
    $sender_id
);

mysqli_stmt_execute($check);

$checkResult = mysqli_stmt_get_result($check);

if (mysqli_num_rows($checkResult) === 0) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Akses conversation ditolak."
    ]);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO messages
    (conversation_id, sender_id, ciphertext, iv)
    VALUES (?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "iiss",
    $conversation_id,
    $sender_id,
    $ciphertext,
    $iv
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Pesan berhasil dikirim."
    ]);
    exit;
}

http_response_code(500);

echo json_encode([
    "success" => false,
    "message" => "Pesan gagal disimpan."
]);

exit;