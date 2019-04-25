<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);


// $projects = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];

/*$tasks = [
    [
        "task" => "Собеседование в IT компании",
        "deadline" => "10.06.2019",
        "category" => "Работа",
        "status" => false
    ],
    [
        "task" => "Выполнить тестовое задание",
        "deadline" => "25.12.2018",
        "category" => "Работа",
        "status" => false
    ],
    [
        "task" => "Сделать задание первого раздела",
        "deadline" => "21.12.2018",
        "category" => "Учеба",
        "status" => true
    ],
    [
        "task" => "Встреча с другом",
        "deadline" => "22.12.2018",
        "category" => "Входящие",
        "status" => false
    ],
    [
        "task" => "Купить корм для кота",
        "deadline" => null,
        "category" => "Домашние дела",
        "status" => false
    ],
    [
        "task" => "Заказать пиццу",
        "deadline" => null,
        "category" => "Домашние дела",
        "status" => false
    ]
];*/




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
    $sql_projects = "SELECT p.project "
        ."FROM projects AS p "
        ."JOIN users AS u "
        ."ON p.id_user = u.id_user "
        ."WHERE p.id_user = 1";

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




$title = "Дела в порядке";

require_once("functions.php");

$content = include_template("index.php", ["show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks]);

$page = include_template("layout.php", ["content" => $content, "show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks, "title" => $title]);

print($page);

?>
