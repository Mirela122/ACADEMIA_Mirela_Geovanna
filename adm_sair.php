
<?php

session_start();

$_SESSION = [];

session_destroy();

header("Location: adm_login.php");
exit;

?>
