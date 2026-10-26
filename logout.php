<?php
require_once('config/autoload.php');
session_destroy();
header("Location: index.php");
exit();
?>
