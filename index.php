<?php
    session_start();
    // показывать или нет выполненные задачи
    /*$show_complete_tasks = rand(0, 1);*/

    //подключаем composer
    require_once("vendor/autoload.php");

    $title = "Дела в порядке";

    require_once("functions.php");
    require_once("data.php");

    if (isset($_SESSION) && !empty($_SESSION)) {

        foreach ($_SESSION["user"] as $key => $value) {
            $db_id_user = $value["id_user"];
            $db_user_name = $value["name"];
        }

        //подключение к БД
        $connect = mysqli_connect("localhost", "root", "", "dvp");

        //установка кодировки ресурса соединения
        mysqli_set_charset($connect, "utf8");

        //проверка подключения
        if (!$connect) {
            $error_connect = mysqli_connect_error(); //если подключение не удалось, показать текст ошибки
            echo($error_connect);
        } else {

        //пагинация
            if (isset($_GET["page"])) {
                $cur_page = (int)$_GET["page"];
            } else {
                $cur_page = 1;
            }

            $page_items = 5;
            $offset = ($cur_page - 1) * $page_items;

            $tasks_count = get_tasks_count($connect, $db_id_user);
            $pages_count = ceil($tasks_count / $page_items);
            $pages = range(1, $pages_count);

        //запрос на показ списка проектов
            $projects = get_projects_with_tasks_count($connect, [$db_id_user]);

        //запрос на показ списка задач
            $tasks = get_tasks_with_limit_and_offset($connect, $page_items, $offset, $db_id_user);
        }

        //список задач для одного проекта
        if (isset($_GET["id_project"])) {

            if ($_GET["id_project"] === "") {
                http_response_code(404);
                header("Location: pages/404.html");
                exit();
            }

            //получение id проекта
            $id_project = $_GET["id_project"];

            $tasks_count = get_tasks_count($connect, $db_id_user, $id_project);
            $pages_count = ceil($tasks_count / $page_items);
            $pages = range(1, $pages_count);

            //запрос на получение списка задач для одного проекта
            $tasks = get_tasks_with_limit_and_offset($connect, $page_items, $offset, $db_id_user, $id_project);

            if (empty($tasks)) {
                http_response_code(404);
                header("Location: pages/404.html");
                exit();
            }
        }

        //смена состояния задачи (выполнена/не выполнена)
        if (isset($_GET["task_id"])) {
            $id_task = $_GET["task_id"];
            $check = $_GET["check"];

            change_task_status($connect, $check, $id_task);

            header("Location: $_SERVER[HTTP_REFERER]");
        }

        //поиск
        $error_search_message = false;

        if (isset($_GET["search"])) {

            $search_value = trim($_GET["search"]);

            if (mb_strlen($search_value) >= 3) {

                $tasks_count = get_tasks_count_for_search_fulltext($connect, [$db_id_user, $search_value]);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                //запрос на получение списка задач по введеной в строку поиска фразе - полнотекстовый поиск
                $tasks = get_tasks_for_search_fulltext($connect, $page_items, $offset, [$db_id_user, $search_value]);

                if (empty($tasks)) {
                    $error_search_message = true;
                }

            } elseif (mb_strlen($search_value) < 3 && mb_strlen($search_value) !== 0) {

                $search_value = "%" . $search_value . "%";

                $tasks_count = get_tasks_count_for_search_like($connect, [$db_id_user, $search_value]);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                //запрос на получение списка задач по введеной в строку поиска фразе - поиск по подстроке
                $tasks = get_tasks_for_search_like($connect, $page_items, $offset, [$db_id_user, $search_value]);

            } else {
                $error_search_message = true;
                $tasks = [];
            }
        }

        //фильтр задач
        if (isset($_GET["filter"]) && ($_GET["filter"] === "today" || $_GET["filter"] === "tomorrow" || $_GET["filter"] === "expired")) {

            $tasks_count = get_tasks_count($connect, $db_id_user, false,  $_GET["filter"]);
            $pages_count = ceil($tasks_count / $page_items);
            $pages = range(1, $pages_count);

            $tasks = get_tasks_with_limit_and_offset($connect, $page_items, $offset, $db_id_user, false, $_GET["filter"]);

            if (isset($_GET["id_project"])) {

                $tasks_count = get_tasks_count($connect, $db_id_user, $id_project, $_GET["filter"]);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                $tasks = get_tasks_with_limit_and_offset($connect, $page_items, $offset, $db_id_user, $id_project, $_GET["filter"]);
            }
        } else {
            $_GET["filter"] = "all_tasks";
        }

        //показывать/не показывать выполненные задачи
        $show_completed_status = 1;

        if (isset($_GET["show_completed"])) {
            $show_completed_status = (int)($_GET["show_completed"]);

            if (!$show_completed_status) {

                $tasks_count = get_tasks_count_with_status_notshow($connect, $db_id_user);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                $tasks = get_tasks_with_limit_and_offset_with_status_notshow($connect, $page_items, $offset, $db_id_user);
            }

            if (!$show_completed_status && isset($_GET["id_project"])) {

                //запрос на подсчет количества задач для одного проекта и статусом 0
                $tasks_count = get_tasks_count_with_status_notshow($connect, $db_id_user, $id_project);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                // запрос на получение задач для одного проекта и статусом 0
                $tasks = get_tasks_with_limit_and_offset_with_status_notshow($connect, $page_items, $offset, $db_id_user, $id_project);

            }

            if (!$show_completed_status && isset($_GET["filter"])) {

                //запрос на подсчет количества задач для всех проектов с фильтром и статусом 0
                $tasks_count = get_tasks_count_with_status_notshow($connect, $db_id_user, false, $_GET["filter"]);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                // запрос на получение задач для всех проектов с фильтром и статусом 0
                $tasks = get_tasks_with_limit_and_offset_with_status_notshow($connect, $page_items, $offset, $db_id_user, false, $_GET["filter"]);

            }

            if (!$show_completed_status && (isset($_GET["id_project"]) && isset($_GET["filter"]))) {

                //запрос на подсчет количества задач для одного проекта с фильтром и статусом 0
                $tasks_count = get_tasks_count_with_status_notshow($connect, $db_id_user, $id_project, $_GET["filter"]);
                $pages_count = ceil($tasks_count / $page_items);
                $pages = range(1, $pages_count);

                // запрос на получение задач для одного проекта с фильтром и статусом 0
                $tasks = get_tasks_with_limit_and_offset_with_status_notshow($connect, $page_items, $offset, $db_id_user, $id_project, $_GET["filter"]);
            }
        }

        $content = include_template("index.php", [
            "show_completed_status" => $show_completed_status,
            "projects" => $projects,
            "tasks" => $tasks,
            "error_search_message" => $error_search_message,
            "cur_page" => $cur_page,
            "pages_count" => $pages_count,
            "pages" => $pages]);

        $page = include_template("layout.php", [
            "content" => $content,
            "projects" => $projects,
            "tasks" => $tasks,
            "title" => $title,
            "db_user_name" => $db_user_name]);

        print($page);
    } else {
        header("Location: guest.php");
    }
?>
