<?php
    session_start();

    //подключаем composer
    require_once("vendor/autoload.php");

    unset($_SESSION["user"]);

    header("Location: /index.php");
