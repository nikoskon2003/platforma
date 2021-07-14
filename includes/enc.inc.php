<?php
function encryptMain($string, $key) {
    require 'config.php';

    $output = false;

    $encrypt_method = "AES-256-CBC";
    $iv = $encIV;

    $Ukey = hash('sha256', $key);
    $Uiv = substr(hash('sha256', $iv), 0, 16);

    $output = openssl_encrypt($string, $encrypt_method, $Ukey, 0, $Uiv);
    return $output;
}
function decryptMain($string, $key) {
    require 'config.php';

    $output = false;

    $encrypt_method = "AES-256-CBC";
    $iv = $encIV;

    $Ukey = hash('sha256', $key);
    $Uiv = substr(hash('sha256', $iv), 0, 16);

    $output = openssl_decrypt($string, $encrypt_method, $Ukey, 0, $Uiv);
    return $output;
}

function encrypt($string)
{
    require 'config.php';
    return encryptMain($string, $encKey);
}
function decrypt($string)
{
    require 'config.php';
    return decryptMain($string, $encKey);
}

function randomString($length = 6){
    $out = "";
    $a = "aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789";
    for($i = 0; $i < $length; $i++) $out .= substr($a, random_int(0, strlen($a)-1), 1);
    return $out;
}