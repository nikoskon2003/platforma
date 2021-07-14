<?php

//comment from here
session_start();
if(!isset($_SESSION['type'])){ 
	include '../error.php';
	exit();
}
if($_SESSION['type'] !== 'ADMIN'){ 
	include '../error.php';
	exit();
}
//to here, to generate a new admin password to use in includes/config.php, in $adminPasswordHash

include '../includes/enc.inc.php';
if(!isset($_GET['p'])){
	echo '?p=&lt;password&gt;';
	exit();
}
$s = $_GET['p'];

$h = hash('sha256', $s);
$e = encrypt($s);
$f = (int)base_convert($h[0], 36, 10) + 1;
$o = substr_replace($h, $e, $f, 0); 

echo $o;
