<?php


require ('../includes/session.php');

session_start();
vj_session_kill();
header('Location: /');
exit();

