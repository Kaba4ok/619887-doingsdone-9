<?php
    session_start();
    // показывать или нет выполненные задачи
    /*$show_complete_tasks = rand(0, 1);*/

    //подключаем composer
    require_once("vendor/autoload.php");

    $title = "Дела в порядке";

    require_once("functions.php");

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
                $cur_page = $_GET["page"];
            } else {
                $cur_page = 1;
            }

            $page_items = 5;

            $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ?";

            $tasks_count_arr = db_fetch_data($connect, $sql_tasks_count, [$db_id_user]);

            $tasks_count = 0;

            foreach ($tasks_count_arr as $key => $value) {
                $tasks_count = $value;
            }

            $pages_count = ceil($tasks_count["tasks_count"] / $page_items);
            $offset = ($cur_page - 1) * $page_items;

            $pages = range(1, $pages_count);

        //запрос на показ списка проектов
            $sql_projects = "SELECT p.*, COUNT(t.id_task) AS tasks_count "
            ."FROM projects AS p "
            ."LEFT JOIN tasks AS t "
            ."ON p.id_project = t.id_project "
            ."WHERE p.id_user = ? "
            ."GROUP BY project";

        //запрос на показ списка задач
            $sql_tasks = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, p.project, t.status "
            ."FROM tasks AS t "
            ."JOIN projects AS p "
            ."ON p.id_project = t.id_project "
            ."WHERE t.id_user = ? "
            ."LIMIT " . $page_items . " OFFSET " . $offset;

            $projects = db_fetch_data($connect, $sql_projects, [$db_id_user]);
            $tasks = db_fetch_data($connect, $sql_tasks, [$db_id_user]);
        }

        //список задач для одного проекта
        if (isset($_GET["id_project"])) {

            if ($_GET["id_project"] === "") {
                http_response_code(404);
                header("Location: pages/404.html");
                exit();
            }

            $id_project = $_GET["id_project"];

            $sql_id_project = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
            ."FROM tasks AS t "
            ."JOIN projects AS p "
            ."ON t.id_project = p.id_project "
            ."WHERE t.id_project = ? "
            ."AND t.id_user = ? "
            ."LIMIT " . $page_items . " OFFSET " . $offset;

            $tasks = db_fetch_data($connect, $sql_id_project, [$id_project, $db_id_user]);

            if (empty($tasks)) {
                http_response_code(404);
                header("Location: pages/404.html");
                exit();
            }
        }

        //показывать/не показывать выполненные задачи
        $show_completed_status = 1;

        if (isset($_GET["show_completed"])) {
            $show_completed_checked = $_GET["show_completed"];

            if (!(int)$show_completed_checked) {
                $show_completed_status = 0;
            }
        }

        //смена состояния задачи (выполнена/не выполнена)
        $status = 0;

        if (isset($_GET["task_id"])) {
            $id_task = $_GET["task_id"];
            $check = $_GET["check"];

            if ((int)$check) {
                $status = 1;
            }

            $sql_task_status = "UPDATE tasks AS t "
                ."SET status = ? "
                ."WHERE t.id_task = ?";

            db_insert_data($connect, $sql_task_status, [$status, $id_task]);

            header("Location: $_SERVER[HTTP_REFERER]");
        }

        //поиск
        $error_search_message = false;

        if (isset($_GET["search"])) {

            $search_value = trim($_GET["search"]);

            if (mb_strlen($search_value) >= 3) {

                $sql_search_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
                    ."FROM tasks "
                    ."WHERE id_user = ? "
                    ."AND MATCH(task) AGAINST(?) "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                $tasks = db_fetch_data($connect, $sql_search_tasks, [$db_id_user, $search_value]);

                if (empty($tasks)) {
                    $error_search_message = true;
                }

            } elseif (mb_strlen($search_value) < 3 && mb_strlen($search_value) !== 0) {

                $search_value = "%" . $search_value . "%";

                $sql_search_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
                    ."FROM tasks "
                    ."WHERE id_user = ? "
                    ."AND task LIKE ? "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                $tasks = db_fetch_data($connect, $sql_search_tasks, [$db_id_user, $search_value]);

            } else {
                $error_search_message = true;
                $tasks = [];
            }
        }

        //фильтр задач
        if (isset($_GET["filter"])) {

            if ($_GET["filter"] === "today") {
                $sql_filtered_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
                    ."FROM tasks "
                    ."WHERE id_user = ? "
                    ."AND deadline = CURDATE() "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                $tasks = db_fetch_data($connect, $sql_filtered_tasks, [$db_id_user]);

                if ($_GET["filter"] === "today" && isset($_GET["id_project"])) {
                    $sql_filtered_tasks =  "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                    ."FROM tasks AS t "
                    ."JOIN projects AS p "
                    ."ON t.id_project = p.id_project "
                    ."WHERE t.id_project = ? "
                    ."AND t.id_user = ? "
                    ."AND deadline = CURDATE() "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                    $tasks = db_fetch_data($connect, $sql_filtered_tasks, [$id_project, $db_id_user]);
                }
            }

            if ($_GET["filter"] === "tomorrow") {
                $sql_filtered_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
                    ."FROM tasks "
                    ."WHERE id_user = ? "
                    ."AND deadline = DATE_ADD(CURDATE(), Interval 1 DAY) "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                $tasks = db_fetch_data($connect, $sql_filtered_tasks, [$db_id_user]);

                if ($_GET["filter"] === "tomorrow" && isset($_GET["id_project"])) {
                    $sql_filtered_tasks =  "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                    ."FROM tasks AS t "
                    ."JOIN projects AS p "
                    ."ON t.id_project = p.id_project "
                    ."WHERE t.id_project = ? "
                    ."AND t.id_user = ? "
                    ."AND deadline = DATE_ADD(CURDATE(), Interval 1 DAY) "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                    $tasks = db_fetch_data($connect, $sql_filtered_tasks, [$id_project, $db_id_user]);
                }
            }

            if ($_GET["filter"] === "expired") {
                $sql_filtered_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
                    ."FROM tasks "
                    ."WHERE id_user = ? "
                    ."AND deadline < CURDATE() "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                $tasks = db_fetch_data($connect, $sql_filtered_tasks, [$db_id_user]);

                if ($_GET["filter"] === "expired" && isset($_GET["id_project"])) {
                    $sql_filtered_tasks =  "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                    ."FROM tasks AS t "
                    ."JOIN projects AS p "
                    ."ON t.id_project = p.id_project "
                    ."WHERE t.id_project = ? "
                    ."AND t.id_user = ? "
                    ."AND deadline < CURDATE() "
                    ."LIMIT " . $page_items . " OFFSET " . $offset;

                    $tasks = db_fetch_data($connect, $sql_filtered_tasks, [$id_project, $db_id_user]);
                }
            }
        } else {
            $_GET["filter"] = "all_tasks";
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
