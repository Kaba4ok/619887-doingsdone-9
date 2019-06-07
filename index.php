<?php

    require_once("init.php");

    if (isset($_SESSION) && !empty($_SESSION)) {

        //получение из массива SESSION["user"] пользовательских данных
        foreach ($_SESSION["user"] as $key => $value) {
            $db_id_user = $value["id_user"];
            $db_user_name = $value["name"];
        }

        //запрос на получение списка всех проектов пользователя
        $projects = get_projects_with_tasks_count($connect, [$db_id_user]);

        //показывать/не показывать выполненные задачи
        if (isset($_GET["show_completed"])) {
            $_SESSION["show_completed"] = $_GET["show_completed"];
            header("Location: $_SERVER[HTTP_REFERER]");
        }

        //смена состояния задачи (выполнена/не выполнена)
        if (isset($_GET["task_id"])) {
            $id_task = $_GET["task_id"];
            $check = $_GET["check"];

            change_task_status($connect, $check, $id_task);

            header("Location: $_SERVER[HTTP_REFERER]");
        }

        //пагинация
        if (isset($_GET["page"])) {
            $cur_page = (int)$_GET["page"];
        } else {
            $cur_page = 1;
        }

        $page_items = 5;
        $offset = ($cur_page - 1) * $page_items;

        //получение id проекта и проверка на совпадение id проекта с данными из БД и с пустой строкой
        if (isset($_GET["id_project"])) {
            $id_project = $_GET["id_project"];

            $projects_ids = get_projects_ids($projects);

            if ($_GET["id_project"] === "" || !in_array($id_project, $projects_ids)) {
                http_response_code(404);
                header("Location: pages/404.html");
                exit();
            }
        }

        //статус сообщения о наличии в проекте задач
        $project_message = false;

        //количество и список задач всех проектов пользователя
        $tasks_count = get_tasks_count($connect, $db_id_user);
        $tasks = get_tasks($connect, $page_items, $offset, $db_id_user);

        //количество и список задач для одного проекта пользователя
        if (isset($_GET["id_project"])) {
            $tasks_count = get_tasks_count($connect, $db_id_user, $id_project);
            $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, $id_project);
            // если в проекте нет задач
            if (empty($tasks)) {
                $project_message = true;
            }
        }

        //фильтр задач для всех проектов
        if (isset($_GET["filter"])) {
            $tasks_count = get_tasks_count($connect, $db_id_user, false, $_GET["filter"]);
            $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, false, $_GET["filter"]);

            //фильтр задач для одного проекта
            if (isset($_GET["id_project"])) {
                $tasks_count = get_tasks_count($connect, $db_id_user, $id_project, $_GET["filter"]);
                $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, $id_project, $_GET["filter"]);
            }
        } else {
            $_GET["filter"] = "all_tasks";
        }

        //невыполненные задачи всех проектов
        if (isset($_SESSION["show_completed"]) && (int)$_SESSION["show_completed"] === 0) {
            $tasks_count = get_tasks_count($connect, $db_id_user, false, false, true);
            $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, false, false, true);

            //невыполненные задачи одного проекта
            if (isset($_GET["id_project"])) {
                $tasks_count = get_tasks_count($connect, $db_id_user, $id_project, false, true);
                $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, $id_project, false, true);
            }
        }

        //невыполненные задачи всех проектов с фильтром
        if (isset($_SESSION["show_completed"]) && (int)$_SESSION["show_completed"] === 0 && isset($_GET["filter"])) {
            $tasks_count = get_tasks_count($connect, $db_id_user, false, $_GET["filter"], true);
            $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, false, $_GET["filter"], true);

            //невыполненные задачи одного проекта с фильтром
            if (isset($_GET["id_project"])) {
                $tasks_count = get_tasks_count($connect, $db_id_user, $id_project, $_GET["filter"], true);
                $tasks = get_tasks($connect, $page_items, $offset, $db_id_user, $id_project, $_GET["filter"], true);
            }
        }

        //поиск
        $error_search_message = false;

        if (isset($_GET["search"])) {
            $search_value = trim($_GET["search"]);
            //полнотекстовый поиск
            if (mb_strlen($search_value) >= 3) {
                $tasks_count = get_tasks_count_for_search($connect, [$db_id_user, $search_value], true);
                $tasks = get_tasks_for_search($connect, $page_items, $offset, [$db_id_user, $search_value], true);

                if (empty($tasks)) {
                    $error_search_message = true;
                }
                //поиск по подстроке
            } elseif (mb_strlen($search_value) < 3 && mb_strlen($search_value) !== 0) {
                $search_value = "%" . $search_value . "%";
                $tasks_count = get_tasks_count_for_search($connect, [$db_id_user, $search_value]);
                $tasks = get_tasks_for_search($connect, $page_items, $offset, [$db_id_user, $search_value]);
            } else {
                $error_search_message = true;
                $tasks_count = 0;
                $tasks = [];
            }
        }

        $pages_count = ceil($tasks_count / $page_items);
        $pages = range(1, $pages_count);

        $content = include_template("index.php", [
            "tasks" => $tasks,
            "project_message" => $project_message,
            "error_search_message" => $error_search_message,
            "cur_page" => $cur_page,
            "pages_count" => $pages_count,
            "pages" => $pages]);

        $page = include_template("layout.php", [
            "content" => $content,
            "projects" => $projects,
            "title" => $title,
            "db_user_name" => $db_user_name]);

        print($page);
    } else {
        header("Location: guest.php");
    }
