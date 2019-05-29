<?php

    session_start();

    $title = "Дела в порядке";

    require_once("vendor/autoload.php");
    require_once("functions.php");
    require_once("data.php");

    $connect = mysqli_connect("localhost", "root", "", "dvp");

    mysqli_set_charset($connect, "utf8");

    if (!$connect) {
        $error_connect = mysqli_connect_error();
        echo($error_connect);
    }
