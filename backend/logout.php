<?php
session_start();

session_unset();
session_destroy();

// Add error handling
if (!headers_sent()) {
    header('Location: ../Frontend/html/login.html?logout=1');
    exit;
} else {
    echo '<script>window.location.href="../Frontend/html/login.html?logout=1";</script>';
    exit;
}
?>
