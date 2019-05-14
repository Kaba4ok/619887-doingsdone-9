<?php

    session_start();

    //подключаем composer
    require_once("vendor/autoload.php");

    $title = "Дела в порядке";

    require_once("functions.php");

    if (isset($_SESSION) && !empty($_SESSION)) {
        //подключение к БД
        $connect = mysqli_connect("localhost", "root", "", "dvp");

        //установка кодировки ресурса соединения
        mysqli_set_charset($connect, "utf8");

        //проверка подключения
        if (!$connect) {
            $error_connect = mysqli_connect_error(); //если подключение не удалось, показать текст ошибки
            echo($error_connect);
        } else {
            //запрос на показ списка проектов
            $sql_projects = "SELECT p.*, COUNT(t.id_task) AS tasks_count "
                ."FROM projects AS p "
                ."LEFT JOIN tasks AS t "
                ."ON p.id_project = t.id_project "
                ."WHERE p.id_user = ? "
                ."GROUP BY project";

            foreach ($_SESSION["user"] as $key => $value) {
                $db_id_user = $value["id_user"];
                $db_user_name = $value["name"];
            }

            $projects = db_fetch_data($connect, $sql_projects, [$db_id_user]);
        }

        $content = include_template("project_add.php", ["projects" => $projects]);

        //отправка данных из формы
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $errors = [];

            // проверка на заполненность обязательных полей
            if (empty($_POST["name"])) {
                $errors["name"] = "Это поле надо заполнить";
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

                $sql_project = "INSERT INTO projects (id_user, project) "
                ."VALUES "
                ."(?, ?)";

                db_insert_data($connect, $sql_project, [$id_user, $project_name]);

                header("Location: /index.php");
            }
        }

        $page = include_template("layout.php", ["content" => $content, "projects" => $projects, "title" => $title, "db_user_name" => $db_user_name]);

        print($page);
    } else {
        header("Location: guest.php");
    }
?>
