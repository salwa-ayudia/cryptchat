<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

$current_user = (int) $_SESSION["user_id"];
$target_user = isset($_GET["target_user_id"]) ? (int) $_GET["target_user_id"] : 0;

if ($target_user <= 0 || $target_user === $current_user) {
    die("Target user tidak valid.");
}

// cek apakah conversation sudah ada
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM conversations
    WHERE (user1_id = ? AND user2_id = ?)
    OR (user1_id = ? AND user2_id = ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "iiii",
    $current_user,
    $target_user,
    $target_user,
    $current_user
);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $conversation_id = $row["id"];
} else {
    // buat conversation baru
    $fingerprint = hash(
        "sha256",
        $current_user . $target_user . time()
    );

    $insert = mysqli_prepare(
        $conn,
        "INSERT INTO conversations
        (user1_id, user2_id, fingerprint)
        VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insert,
        "iis",
        $current_user,
        $target_user,
        $fingerprint
    );

    mysqli_stmt_execute($insert);

    $conversation_id = mysqli_insert_id($conn);
}

header(
    "Location: ../views/chat.php?user_id=$target_user&conversation_id=$conversation_id"
);
exit;