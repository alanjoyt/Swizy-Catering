<?php 


//your project path goes here
define("BASE_URL","http://localhost/project/");
define("BASE_PATH","c:/wamp64/www/project/");

//set your timezone here
date_default_timezone_set('asia/kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(BASE_PATH.'config/database.php'); 
require_once(BASE_PATH.'classes/database.php'); 
require_once(BASE_PATH.'classes/FormAssist.class.php'); 
require_once(BASE_PATH.'classes/FormValidator.class.php'); 
require_once(BASE_PATH.'classes/DataAccess.class.php');
 
function swalRedirect($message, $url, $type='success', $title='Notification') {
    echo "<!DOCTYPE html><html><head>";
    echo "<link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap' rel='stylesheet'>";
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<style>body{background:#121212;font-family:'Poppins',sans-serif;} .swal2-popup{background:#1e1e1e!important;color:#fff!important;border:1px solid #d4af37!important;} .swal2-confirm{background:linear-gradient(45deg,#b8860b,#d4af37)!important;color:#000!important;border:none!important;}</style>";
    echo "</head><body><script>";
    echo "document.addEventListener('DOMContentLoaded', function() {";
    echo "  Swal.fire({";
    echo "    title: '".addslashes($title)."',";
    echo "    text: '".addslashes($message)."',";
    echo "    icon: '$type',";
    echo "    confirmButtonText: 'OK'";
    echo "  }).then(function() {";
    echo "    window.location.href = '$url';";
    echo "  });";
    echo "});";
    echo "</script></body></html>";
    exit();
}

?>    
