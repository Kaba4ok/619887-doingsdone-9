<?php

    $title = "Дела в порядке";

    require_once("functions.php");

    //подключение к БД
    $connect = mysqli_connect("localhost", "root", "", "dvp");

    //установка кодировки ресурса соединения
    mysqli_set_charset($connect, "utf8");

    //проверка подключения
    if (!$connect) {
        $error_connect = mysqli_connect_error(); //если подключение не удалось, показать текст ошибки
        echo($error_connect);
    } else {
        //запрос на показ списка проектов для юзера с id = 1
        $sql_projects = "SELECT p.*, COUNT(*) AS tasks_count "
            ."FROM projects AS p "
            ."JOIN tasks AS t "
            ."ON t.id_project = p.id_project "
            ."WHERE p.id_user = 1 "
            ."GROUP BY project";

        //выполняем запрос и получаем ресурс результата
        $result_projects = mysqli_query($connect, $sql_projects);

        //проверка запроса
        if ($result_projects) {
            //получаем двумерный массив с проектами
            $projects = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);
        } else {
            $error_query = mysqli_error($connect);
            echo($error_query);
        }
    }

    $content = include_template("add.php", ["projects" => $projects]);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {


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
        }

        //проверка на соответствие проекта
        $err_proj = true;

        foreach ($projects as $key => $value) {
            if ($value["id_project"] == $_POST["project"]) {
                $err_proj = false;
            }
        }

        if ($err_proj) {
            $errors["project"] = "Такого проекта не существует";
        }

        //загрузка файла
        if (isset($_FILES["file"])) {
            $file_name = $_FILES["file"]["name"];
            $file_path = __DIR__ . "/uploads/";
            $file_url = "/uploads/" . $file_name;

            move_uploaded_file($_FILES["file"]["tmp_name"], $file_path . $file_name);
        }

        //проверка на наличие ошибок
        if (count($errors)) {
            $content = include_template("add.php", ["projects" => $projects, "errors" => $errors]);
        } else {
            //формирование запроса с данными из формы и редирект на главную страницу в случае отсутствия ошибок
            $status = 0;
            $task_name = $_POST["name"];
            $file = $file_url;
            $deadline = $_POST["date"];
            $id_user = 1;
            $id_project = $_POST["project"];

            $sql_task = "INSERT INTO tasks (status, task, file, deadline, id_user, id_project) "
            ."VALUES "
            ."(?, ?, ?, ?, ?, ?)";

            db_insert_data($connect, $sql_task, [$status, $task_name, $file, $deadline, $id_user, $id_project]);

            header("Location: /index.php");
        }

    }

    $page = include_template("layout.php", ["content" => $content, "projects" => $projects, "title" => $title]);

    print($page);
?>
