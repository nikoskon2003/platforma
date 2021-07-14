<?php
session_start();
if(!isset($_SESSION['type'])){ 
	include '../../error.php';
	exit();
}
if($_SESSION['type'] !== 'ADMIN'){ 
	include '../../error.php';
	exit();
}


require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;
var_dump(VAPID::createVapidKeys());