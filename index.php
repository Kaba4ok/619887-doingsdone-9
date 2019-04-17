<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);


$projects = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];

$tasks = [
    [
        "task" => "Собеседование в IT компании",
        "deadline" => "01.12.2018",
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
];

$title = "Дела в порядке";

require_once("functions.php");

$content = include_template("index.php", ["show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks]);

$page = include_template("layout.php", ["content" => $content, "show_complete_tasks" => $show_complete_tasks, "projects" => $projects, "tasks" => $tasks, "title" => $title]);

print($page);

?>
