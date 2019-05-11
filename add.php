<?php

    session_start();

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

        $content = include_template("add.php", ["projects" => $projects]);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $required = ["name", "project"];

            $errors = [];

            // проверка на заполненность обязательных полей
            foreach ($required as $key) {
                if (empty($_POST[$key])) {
                    $errors[$key] = "Это поле надо заполнить";
                }
            }

            //проверка формата и значения даты
            if ($_POST["date"] !== "") {
                $current_time = strtotime("now");
                $deadline_time = $_POST["date"];

                if (strtotime($deadline_time) <= $current_time) {
                    $errors["date"] = "Дата выполнения должна быть больше или равна текущей дате";
                }

                if (!is_date_valid($deadline_time)) {
                    $errors["date"] = "Неправильный формат даты";
                }
            } else {
                $_POST["date"] = null;
            }

            //проверка на соответствие проекта
            $err_proj = true;

            foreach ($projects as $key => $value) {
                if ($value["id_project"] === (int)$_POST["project"]) {
                    $err_proj = false;
                }
            }

            if ($err_proj) {
                $errors["project"] = "Такого проекта не существует";
            }

            //загрузка файла
            $file = null;

            if (isset($_FILES["file"]) && !empty($_FILES["file"]["name"])) {
                $file_name = date("Y-m-d-H-i-s") . "___" . $_FILES["file"]["name"];
                $file_path = __DIR__ . "/uploads/";
                $file_url = "/uploads/" . $file_name;

                move_uploaded_file($_FILES["file"]["tmp_name"], $file_path . $file_name);

                $file = $file_url;

            }

            //проверка на наличие ошибок
            if (count($errors)) {
                $content = include_template("add.php", ["projects" => $projects, "errors" => $errors]);
            } else {
                //формирование запроса на добавление данных из формы в БД и редирект на главную страницу в случае отсутствия ошибок
                $status = 0;
                $task_name = $_POST["name"];
                $deadline = $_POST["date"];
                $id_project = $_POST["project"];

                $sql_task = "INSERT INTO tasks (status, task, file, deadline, id_user, id_project) "
                ."VALUES "
                ."(?, ?, ?, ?, ?, ?)";

                db_insert_data($connect, $sql_task, [$status, $task_name, $file, $deadline, $db_id_user, $id_project]);

                header("Location: /index.php");
            }
        }

        $page = include_template("layout.php", ["content" => $content, "projects" => $projects, "title" => $title, "db_user_name" => $db_user_name]);

        print($page);
    }
?>
