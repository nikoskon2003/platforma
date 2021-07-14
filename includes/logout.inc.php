<?php
session_start();
setcookie('autologin', '', time() - 3600, '/');
session_unset();
session_destroy();
header("Location: ../");
exit();