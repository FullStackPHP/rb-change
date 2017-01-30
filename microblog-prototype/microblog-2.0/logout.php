<?php
include('./lib.php');

$userid = $_COOKIE['userid'];
setcookie('username','',time() - 3600);
setcookie('userid','',time() - 3600);
setcookie('authsecret','',time() - 3600);

$r = connredis();
$r->set('user:userid:'.$userid.':authsecret','');
header('Location:index.php');

?>
