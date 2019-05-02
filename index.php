<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

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
    $sql_projects = "SELECT project, p.id_project, COUNT(*) AS tasks_count "
        ."FROM projects AS p "
        ."JOIN tasks AS t "
        ."ON t.id_project = p.id_project "
        ."JOIN users AS u "
        ."ON u.id_user = p.id_user "
        ."WHERE p.id_user = 1 "
        ."GROUP BY project";

    //запрос на показ списка задач для юзера с id = 1
    $sql_tasks = "SELECT t.task, t.deadline, p.project, t.status "
        ."FROM tasks AS t "
        ."JOIN users AS u "
        ."ON u.id_user = t.id_user "
        ."JOIN projects AS p "
        ."ON p.id_project = t.id_project "
        ."WHERE u.id_user = 1";

    //выполняем запрос и получаем ресурс результата
    $result_projects = mysqli_query($connect, $sql_projects);
    $result_tasks = mysqli_query($connect, $sql_tasks);

    //проверка запроса
    if ($result_projects) {
        //получаем двумерный массив с проектами
        $projects = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);
    } else {
        $error_query = mysqli_error($connect);
        echo($error_query);
    }

    //проверка запроса
    if ($result_tasks) {
        //получаем двумерный массив с задачами
        $tasks = mysqli_fetch_all($result_tasks, MYSQLI_ASSOC);
    } else {
        $error_query = mysqli_error($connect);
        echo($error_query);
    }
}

//получаем ссылку на проект
$params = $_GET;
$params["id_project"] = "";
/*$scriptname = pathinfo(__FILE__, PATHINFO_BASENAME);
$query = http_build_query($params);
$url = "/" . $scriptname . "?" . $query;*/



if (isset($_GET["id_project"])) {

    if ($_GET["id_project"] == "") {
        http_response_code(404);
        header("Location: pages/404.html");
        exit();
    }

    $id_project = $_GET["id_project"];
    $id_user = 1;

    $sql_id_project = "SELECT t.task, t.deadline, t.status, p.project "
        ."FROM tasks AS t "
        ."JOIN projects AS p "
        ."ON t.id_project = p.id_project "
        ."WHERE t.id_project = ? "
        ."AND t.id_user = ?";

    $tasks = db_fetch_data($connect, $sql_id_project, [$id_project, $id_user]);

    if (empty($tasks)) {
        http_response_code(404);
        header("Location: pages/404.html");
        exit();
    }
}


$content = include_template("index.php", ["show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks]);

$page = include_template("layout.php", ["content" => $content, "show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks, "title" => $title]);

print($page);

?>
