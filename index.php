<?php
session_start();

if(isset($_SESSION['user'])){
    header("Location: dashboard/index.php");
} else {
    header("Location: auth/login.php");
}
exit;
?>