
<?php

session_start();

$_SESSION = [];

session_destroy();

header("Location: login_adm.php");
exit;

?>
