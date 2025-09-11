<?php
session_start();
unset($_SESSION['student_logged_in']);
unset($_SESSION['student_username']);
session_destroy();
header('Location: login.php');
exit;
