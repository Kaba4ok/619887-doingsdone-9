<?php
session_start();
// показывать или нет выполненные задачи
    $show_complete_tasks = rand(0, 1);

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

    //запрос на показ списка проектов для юзера с id = 1
        $sql_projects = "SELECT project, p.id_project, COUNT(*) AS tasks_count "
        ."FROM projects AS p "
        ."JOIN tasks AS t "
        ."ON t.id_project = p.id_project "
        ."JOIN users AS u "
        ."ON u.id_user = p.id_user "
        ."WHERE p.id_user = ? "
        ."GROUP BY project";

    //запрос на показ списка задач для юзера с id = 1
        $sql_tasks = "SELECT t.task, t.file, t.deadline, p.project, t.status "
        ."FROM tasks AS t "
        ."JOIN users AS u "
        ."ON u.id_user = t.id_user "
        ."JOIN projects AS p "
        ."ON p.id_project = t.id_project "
        ."WHERE u.id_user = ?";

        foreach ($_SESSION["user"] as $key => $value) {
            $db_id_user = $value["id_user"];
            $db_user_name = $value["name"];
        }

        $projects = db_fetch_data($connect, $sql_projects, [$db_id_user]);
        $tasks = db_fetch_data($connect, $sql_tasks, [$db_id_user]);
    }

    if (isset($_GET["id_project"])) {

        if ($_GET["id_project"] === "") {
            http_response_code(404);
            header("Location: pages/404.html");
            exit();
        }

        $id_project = $_GET["id_project"];

        $sql_id_project = "SELECT t.task, t.file, t.deadline, t.status, p.project "
        ."FROM tasks AS t "
        ."JOIN projects AS p "
        ."ON t.id_project = p.id_project "
        ."WHERE t.id_project = ? "
        ."AND t.id_user = ?";

        $tasks = db_fetch_data($connect, $sql_id_project, [$id_project, $db_id_user]);

        if (empty($tasks)) {
            http_response_code(404);
            header("Location: pages/404.html");
            exit();
        }
    }


    $content = include_template("index.php", ["show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks]);

    $page = include_template("layout.php", ["content" => $content, "show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks, "title" => $title, "db_user_name" => $db_user_name]);

    print($page);
} else {
    header("Location: guest.php");
}

?>
