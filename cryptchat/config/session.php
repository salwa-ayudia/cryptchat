<?php
session_start();

// Strict no-cache headers for all authenticated pages
// This prevents browsers from serving stale cached pages via back/forward
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Prevent framing (defense in depth)
header("X-Frame-Options: DENY");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit;
}
