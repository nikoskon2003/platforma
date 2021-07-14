<?php

$length = 6;
if(isset($_GET['l']))
if(is_numeric($_GET['l']))
if($_GET['l'] > 0) 
if($_GET['l'] < 1001) 
$length = $_GET['l'];

$out = "";
$a = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789";

for($i = 0; $i < $length; $i++) $out .= substr($a, random_int(0, strlen($a)-1), 1);

echo $out;