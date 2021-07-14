<?php

require 'config.php';
$conn = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);

//idk if this is necessary every time. Probably not. Too lazy to test tbh
mysqli_query($conn, 'SET character_set_results=utf8');
mysqli_query($conn, 'SET names=utf8');
mysqli_query($conn, 'SET character_set_client=utf8');
mysqli_query($conn, 'SET character_set_connection=utf8');
mysqli_query($conn, 'SET collation_connection=utf8mb4_general_ci');