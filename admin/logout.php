<?php
session_start();
session_unset();
session_destroy();
header('Location: /Projet%20Automobile/public/index.php');
exit();
