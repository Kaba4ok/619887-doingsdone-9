<?php

    require_once("init.php");

    if (isset($_SESSION) && !empty($_SESSION)) {

        //запрос на показ списка проектов
        foreach ($_SESSION["user"] as $key => $value) {
            $db_id_user = $value["id_user"];
            $db_user_name = $value["name"];
        }

        $projects = get_projects_with_tasks_count($connect, [$db_id_user]);

        $content = include_template("project_add.php", ["projects" => $projects]);

        //отправка данных из формы
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $errors = [];

            // проверка на заполненность обязательных полей
            if (empty($_POST["name"])) {
                $errors["name"] = "Это поле надо заполнить";
            }

            if (mb_strlen($_POST["name"]) > 18) {
                $errors["name"] = "Значение данного поля не должно содержать более 18 символов";
            }

            foreach ($projects as $key => $value) {
                if ($value["project"] === $_POST["name"]) {
                    $errors["name"] = "Такой проект уже существует";
                }
            }

            //проверка на наличие ошибок
            if (count($errors)) {
                $content = include_template("project_add.php", ["errors" => $errors]);
            } else {
                //формирование запроса на добавление данных из формы в БД и редирект на главную страницу в случае отсутствия ошибок
                $project_name = $_POST["name"];
                $id_user = $db_id_user;

                add_project_in_db($connect, $id_user, $project_name);

                header("Location: /index.php");
            }
        }

        $page = include_template("layout.php", ["content" => $content, "projects" => $projects, "title" => $title, "db_user_name" => $db_user_name]);

        print($page);
    } else {
        header("Location: guest.php");
    }
