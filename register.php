<?php

    require_once("init.php");

    if (!empty($_SESSION)) {
        header("Location: index.php");
    }

    //запрос на показ списка юзеров
    $users = get_users($connect);

    $content = include_template("register.php", []);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $required = ["email", "password", "name"];

        $errors = [];

        // проверка на заполненность обязательных полей
        foreach ($required as $key) {
            if (empty($_POST[$key])) {
                $errors[$key] = "Это поле надо заполнить";
            }
        }

        //проверка на соответствие формату и заполненность поля email, а так же ограничения длины имени пользователя
        foreach ($_POST as $key => $value) {
            if ($key === "email") {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = "E-mail введён некорректно";
                }
                if (empty($_POST["email"])) {
                    $errors[$key] = "Это поле надо заполнить";
                }
            }

            if ($key === "name") {
                if (mb_strlen($value) > 70) {
                    $errors[$key] = "Значение данного поля не должно содержать более 70 символов";
                }
            }
        }

        //проверка уникальности email
        foreach ($users as $key) {
            if ($_POST["email"] === $key["email"]) {
                $errors["email"] = "Пользователь с таким e-mail уже зарегистрирован";
            }
        }

        //хэширование пароля
        $hash_password = password_hash($_POST["password"], PASSWORD_DEFAULT);

        //проверка на наличие ошибок
        if (count($errors)) {
            $content = include_template("register.php", ["errors" => $errors]);
        } else {
            //формирование запроса на добавление данных из формы в БД и редирект на главную страницу в случае отсутствия ошибок
            $user_email = $_POST["email"];
            $user_password = $hash_password;
            $user_name = htmlspecialchars($_POST["name"]);

            add_users($connect, [$user_email, $user_password, $user_name]);

            $user = get_user_data($connect, $user_email);

            $_SESSION["user"] = $user;

            header("Location: /index.php");
        }
    }

    $page = include_template("layout-unlogin.php", ["content" => $content, "title" => $title]);

    print($page);
