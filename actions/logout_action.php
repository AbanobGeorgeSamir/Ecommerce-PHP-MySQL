<?php
require_once '../includes/app.php';

session_unset();
session_destroy();
session_start();
set_flash('You have been logged out successfully.', 'info');

header('Location: ../index.php');
exit();
