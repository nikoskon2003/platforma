<?php

#MySQL Database login info
$dbHost = "hostname";
$dbUsername = "username";
$dbPassword = "password";
$dbName = "database";

#Encryption Keys
//MUST CHANGE THESE
$encIV = "encIV";
$encKey = "encKey";

#Website info
//admin's password
// $siteDomain/admin/genpass.php
// default is '12345' for the above $encIV and $encKey  ONLY!!!
$adminPasswordHash = "599447MnO7e3JVSj5qhkF2bwG5EA==1abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5";
//title prefix
$siteName = "Example";
$siteDomain = "example.com";
$contactEmail = "example@example.com";
$pubFileVer = "3.120"; //this is mainly used for resource updating... which will most likely never happen again. OOF
// **y** = year, **m** = mail, **b** = break, **s** = space
$footer = "Δημιουργήθηκε από τον μαθητή Νικόλαο Κωνσταντίνου του**b**Προτύπου Γενικού Λυκείου Αναβρύτων 2019-**y**"; // :)
$enableProgram = true; //tbh, most useless feature I've implemented

#Push Notifications Keys
//Create a new pair at:  $siteDomain/includes/notifications/v.php (must be logged-in as admin)
$publicKey = "BO7BP0HT-_wSU6OttC0K8BcUTRrZnfyC0mIhEIiD4J9cdzarbH2lgvbI5ueVNeLWzOpFse-z_TzZWv1agaZW0Yo";
$privateKey = "9O_4hI1wsIgyr5vXafP-p2utr-hkyC_InxYb5fX2dcw";
