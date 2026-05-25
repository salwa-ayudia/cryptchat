<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

$current_user = $_SESSION["user_id"];

$query = mysqli_query(
    $conn,
    "
    SELECT 
        users.id,
        users.username,
        (
            SELECT ciphertext
            FROM messages
            INNER JOIN conversations
            ON messages.conversation_id = conversations.id
            WHERE
                (
                    conversations.user1_id = $current_user
                    AND conversations.user2_id = users.id
                )
                OR
                (
                    conversations.user1_id = users.id
                    AND conversations.user2_id = $current_user
                )
            ORDER BY messages.id DESC
            LIMIT 1
        ) AS last_message,

        (
            SELECT messages.created_at
            FROM messages
            INNER JOIN conversations
            ON messages.conversation_id = conversations.id
            WHERE
                (
                    conversations.user1_id = $current_user
                    AND conversations.user2_id = users.id
                )
                OR
                (
                    conversations.user1_id = users.id
                    AND conversations.user2_id = $current_user
                )
            ORDER BY messages.id DESC
            LIMIT 1
        ) AS last_time
    FROM users
    WHERE users.id != $current_user
    ORDER BY
        last_time DESC,
        username ASC
    "
);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-top">
            <h4 class="logo-text">
                NSA Talks
            </h4>
            <div class="dropdown">
                <button
                    class="profile-btn dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a
                            class="dropdown-item text-danger"
                            href="#"
                            onclick="logout(); return false;">
                            Logout
                        </a>

                        <script>
                            function logout() {
                                sessionStorage.clear();
                                window.location.href = "../api/logout.php";
                            }
                        </script>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="search-box">
        <input
            type="text"
            id="searchUser"
            placeholder="Search user...">
    </div>

    <div
        id="notFoundMessage"
        class="not-found-message"
        style="display:none;">
        User not found
    </div>

    <div class="chat-list">
        <?php while ($user = mysqli_fetch_assoc($query)) : ?>
            <a
                href="../api/create_conversation.php?target_user_id=<?php echo $user['id']; ?>"
                class="chat-user"
                data-username="<?php echo htmlspecialchars(strtolower($user['username']), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="user-info">
                    <h5>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h5>
                    <small>
                        <?php
                        if ($user['last_message']) {
                            echo "Encrypted message";
                        } else {
                            echo "No messages yet";
                        }
                        ?>
                    </small>
                </div>
            </a>
        <?php endwhile; ?>
        <div class="sidebar-footer">
            <small>
                🔒 Your messages are protected with
                End-to-End Encryption
            </small>
        </div>
    </div>

    <script>
        const searchInput =
            document.getElementById("searchUser");

        const users =
            document.querySelectorAll(".chat-user");

        const notFound =
            document.getElementById(
                "notFoundMessage"
            );

        searchInput.addEventListener(
            "input",
            function() {
                const keyword =
                    this.value.toLowerCase();
                let found = false;
                users.forEach(user => {
                    const username =
                        user.dataset.username;

                    if (
                        username.includes(keyword)
                    ) {
                        user.style.display =
                            "flex";
                        found = true;
                    } else {
                        user.style.display =
                            "none";
                    }
                });

                if (!found) {
                    notFound.style.display =
                        "block";
                } else {
                    notFound.style.display =
                        "none";
                }
            }
        );
    </script>
</div>