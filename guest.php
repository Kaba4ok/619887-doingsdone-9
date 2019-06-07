<?php
    //подключаем composer
    require_once("init.php");

    if (!empty($_SESSION)) {
        header("Location: index.php");
    }

    require_once("templates/guest.php");
