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

if (!isset($_GET["conversation_id"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Conversation ID tidak ditemukan."
    ]);
    exit;
}

$current_user_id = (int) $_SESSION["user_id"];
$conversation_id = (int) $_GET["conversation_id"];

if ($conversation_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Conversation ID tidak valid."
    ]);
    exit;
}


// cek apakah user login adalah anggota conversation.
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
    $current_user_id,
    $current_user_id
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
    "SELECT sender_id, ciphertext, iv
     FROM messages
     WHERE conversation_id = ?
     ORDER BY created_at ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $conversation_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        "sender_id" => (int) $row["sender_id"],
        "ciphertext" => $row["ciphertext"],
        "iv" => $row["iv"]
    ];
}

echo json_encode([
    "success" => true,
    "messages" => $messages
]);
exit;