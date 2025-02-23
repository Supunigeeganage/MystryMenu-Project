<?php
session_start();
require 'authMiddleware.php';
checkUserType(['admin']);
?>