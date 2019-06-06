<?php

    require_once("init.php");

    if (!empty($_SESSION)) {
        header("Location: index.php");
    }

    $content = include_template("auth.php", []);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $errors = [];

        //ищем в БД юзера с введенным email и получаем массив с его данными
        $email = $_POST["email"];

        $user = get_user_data($connect, $email);

        //создание переменной с хэшом пароля из БД
        if (!empty($user)) {
            foreach ($user as $key => $value) {
                $password = $value["password"];
            }
        }

        //проверка email/пароля и создание сессии для юзера
        if (!empty($user)) {
            //проверка на соответствие введенного пароля данным из БД
            if (password_verify($_POST["password"], $password)) {
                $_SESSION["user"] = $user;
            } else {
                $errors["password"] = "Неверный пароль";
                $errors["password_invalid"] = true;
            }
        }
        //проверка на заполненность поля email
        elseif (empty($_POST["email"])) {
            $errors["email"] = "Это поле надо заполнить";
        }
        //проверка на заполненность поля password
        elseif (empty($_POST["password"])) {
            $errors["password"] = "Это поле надо заполнить";
        }
        //проверка на соответствие формату поля email
        elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "E-mail введён некорректно";
        }
        //текст ошибки если email не соответствует данным из БД
        else {
            $errors["email"] = "Такой пользователь не найден";
            $errors["email_invalid"] = true;
        }

        //проверка на наличие ошибок
        if (count($errors)) {
            $content = include_template("auth.php", ["errors" => $errors]);
        } else {
            header("Location: /index.php");
            exit();
        }
    }

    $page = include_template("layout-unlogin.php", ["content" => $content, "title" => $title]);

    print($page);
