<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

$target_user_id = isset($_GET['user_id'])
    ? (int) $_GET['user_id']
    : 0;

$conversation_id = isset($_GET['conversation_id'])
    ? (int) $_GET['conversation_id']
    : 0;

$current_user_id = (int) $_SESSION["user_id"];

$page = "chat";

if ($target_user_id <= 0 || $conversation_id <= 0) {
    die("User ID atau Conversation ID tidak valid.");
}

// cek apakah user login anggota cv atau bukan
$stmt = mysqli_prepare(
    $conn,
    "SELECT 
        c.id,
        u.username
     FROM conversations c
     JOIN users u ON u.id = ?
     WHERE c.id = ?
     AND (
        (c.user1_id = ? AND c.user2_id = ?)
        OR
        (c.user1_id = ? AND c.user2_id = ?)
     )
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "iiiiii",
    $target_user_id,
    $conversation_id,
    $current_user_id,
    $target_user_id,
    $target_user_id,
    $current_user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$targetUser = mysqli_fetch_assoc($result);

if (!$targetUser) {
    die("Akses conversation ditolak.");
}

$targetUsername = $targetUser["username"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CryptChat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<body>

    <div class="chat-wrapper">

        <?php include "partials/sidebar.php"; ?>

        <div class="chat-main">
            <div class="chat-header">
                <div id="fingerprintBox">
                    🔒 End-to-End Encrypted
                </div>
                <h5 class="mb-0">
                    <?php echo htmlspecialchars($targetUsername); ?>
                </h5>
                <span class="status">
                    AES-256-GCM Active
                </span>
            </div>


            <div id="chatBox" class="chat-body"></div>


            <div class="chat-input-area">
                <input
                    type="text"
                    id="messageInput"
                    class="form-control"
                    placeholder="Type your message...">
                <button
                    onclick="sendEncryptedMessage()"
                    class="btn btn-primary send-btn">
                    Send
                </button>
            </div>
        </div>
    </div>


    <script>
        const targetUserId =
            <?php echo json_encode($target_user_id); ?>;

        const conversationId =
            <?php echo json_encode($conversation_id); ?>;

        const currentUserId =
            <?php echo json_encode($_SESSION['user_id']); ?>;

        const currentUsername =
            <?php echo json_encode($_SESSION['username']); ?>;
    </script>


    <script src="../assets/js/crypto.js"></script>


    <script>
        (function() {
            var STACK_SIZE = 50;
            var SENTINEL = {
                locked: true,
                page: "chat",
                conversationId: <?php echo (int)$conversation_id; ?>
            };

            function floodStack() {
                for (var i = 0; i < STACK_SIZE; i++) {
                    history.pushState(SENTINEL, "", window.location.href);
                }
            }

            floodStack();

            window.addEventListener("popstate", function() {
                history.pushState(SENTINEL, "", window.location.href);
            });

            window.addEventListener("pageshow", function(event) {
                var navType = (
                    performance.getEntriesByType("navigation")[0] || {
                        type: ""
                    }
                ).type;

                if (event.persisted || navType === "back_forward") {
                    window.location.reload();
                }
            });
        })();


        async function forceLogoutAndRedirect(reason) {
            sessionStorage.clear();
            try {
                await fetch("../api/force_logout.php", {
                    method: "POST"
                });
            } catch (e) {
                console.warn("force_logout fetch gagal:", e);
            }
            window.location.replace("login.php");
        }


        (async function() {
            if (!sessionStorage.getItem("privateKey")) {
                await forceLogoutAndRedirect("privateKey missing on chat load");
            }
        })();
        

        // enter untuk kirim
        document
            .getElementById("messageInput")
            .addEventListener(
                "keypress",
                function(e) {
                    if (e.key === "Enter") {
                        sendEncryptedMessage();
                    }
                }
            );


        loadMessages();
        generateFingerprint(targetUserId)
            .then(fp => {
                document.getElementById("fingerprintBox").textContent =
                    `🔒 Fingerprint: ${fp}`;
            });
    </script>
</body>

</html>