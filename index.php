<?php
include 'db.php';
$conn = $pdo;
echo "Connected Successfully";

echo "<script>
        setTimeout(function() {
            window.location.href = 'frontend/html/main.html';
        }, 3000); 
      </script>";
?>
