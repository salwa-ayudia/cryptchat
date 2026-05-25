<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - CryptChat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<body>

    <div class="dashboard-wrapper">

        <?php include "partials/sidebar.php"; ?>

        <div class="dashboard-main">

            <div class="dashboard-header">

                <div>
                    <h3>Secure Conversation</h3>
                    <p>
                        Start encrypted communication safely with other users.
                    </p>
                </div>
            </div>

            <div class="empty-state">

                <h1>CryptChat</h1>

                <p>
                    Select a user from the sidebar and start your secure
                    end-to-end encrypted conversation.
                </p>

            </div>

        </div>

    </div>

    <script src="../assets/js/crypto.js"></script>

    <script>
    async function forceLogoutAndRedirect(reason) {
        sessionStorage.clear();
        try {
            await fetch("../api/force_logout.php", { method: "POST" });
        } catch (e) {
            // Tetap redirect meski fetch gagal (offline, dsb)
            console.warn("force_logout fetch gagal:", e);
        }
        window.location.replace("login.php");
    }
    

    (async function () {
        try {
            const existingPrivateKey =
                sessionStorage.getItem("privateKey");

            if (existingPrivateKey) {
                return;
            }

            const encryptedPrivateKey =
                <?php echo json_encode($_SESSION['encrypted_private_key']); ?>;

            const privateKeyIv =
                <?php echo json_encode($_SESSION['private_key_iv']); ?>;

            const privateKeySalt =
                <?php echo json_encode($_SESSION['private_key_salt']); ?>;

            const password =
                sessionStorage.getItem("loginPassword");

            if (!password) {
                await forceLogoutAndRedirect("loginPassword missing");
                return;
            }

            const privateKey = await decryptPrivateKey(
                encryptedPrivateKey,
                privateKeyIv,
                password,
                privateKeySalt
            );

            if (!privateKey) {
                await forceLogoutAndRedirect("decryptPrivateKey failed");
                return;
            }

            sessionStorage.setItem("privateKey", privateKey);
            sessionStorage.removeItem("loginPassword");

        } catch (error) {
            console.error("Private key decryption failed:", error);
            await forceLogoutAndRedirect("exception: " + error.message);
        }
    })();
    </script>

</body>

</html>
