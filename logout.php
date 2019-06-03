<?php
    session_start();

    require_once("vendor/autoload.php");

    unset($_SESSION["user"]);

    header("Location: /index.php");
