<?php

    require_once("functions.php");

//получение количества задач
    function get_tasks_count($link, $data = []) {

        $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ?";

        $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, $data);

        return $tasks_count_arr;
    }

//запрос на показ задач
    function get_tasks_with_limit_and_offset($link, $limit, $offset, $data = []) {

        $sql_tasks = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, p.project, t.status "
            ."FROM tasks AS t "
            ."JOIN projects AS p "
            ."ON p.id_project = t.id_project "
            ."WHERE t.id_user = ? "
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_tasks, $data);

        return $tasks;
    }

//запрос на получение списка задач для одного проекта
    function get_tasks_with_limit_and_offset_from_project($link, $limit, $offset, $data = []) {

        $sql_id_project = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
            ."FROM tasks AS t "
            ."JOIN projects AS p "
            ."ON t.id_project = p.id_project "
            ."WHERE t.id_project = ? "
            ."AND t.id_user = ? "
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_id_project, $data);

        return $tasks;
    }

//смена состояния задачи (выполнена/не выполнена)
    function change_task_status($link, $check, $data) {

        $status = 0;

        if ((int)$check) {
            $status = 1;
        }

        $sql_task_status = "UPDATE tasks AS t "
            ."SET status = ? "
            ."WHERE t.id_task = ?";

        db_insert_data($link, $sql_task_status, [$status, $data]);
    }

//запрос на получение списка задач по введеной в строку поиска фразе - полнотекстовый поиск
    function get_tasks_for_search_fulltext($link, $limit, $offset, $data = []) {

        $sql_search_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND MATCH(task) AGAINST(?) "
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_search_tasks, $data);

        return $tasks;
    }

//запрос на получение списка задач по введеной в строку поиска фразе - поиск по подстроке
    function get_tasks_for_search_like($link, $limit, $offset, $data = []) {

        $sql_search_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND task LIKE ? "
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_search_tasks, $data);

        return $tasks;
    }

//получение списка всех задач в заисимости от значения фильтра
    function get_filtered_tasks($link, $limit, $offset, $filter, $data = []) {

        if ($filter === "today") {
            $sql_filter = "= CURDATE() ";
        } elseif ($filter === "tomorrow") {
            $sql_filter = "= DATE_ADD(CURDATE(), Interval 1 DAY) ";
        } elseif ($filter === "expired") {
            $sql_filter = "< CURDATE() ";
        }

        $sql_filtered_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND deadline " . $sql_filter
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_filtered_tasks, $data);

        return $tasks;
    }

//получение списка задач в заисимости от значения фильтра в одном проекте
    function get_filtered_tasks_from_project($link, $limit, $offset, $filter, $data = []) {

        if ($filter === "today") {
            $sql_filter = "= CURDATE() ";
        } elseif ($filter === "tomorrow") {
            $sql_filter = "= DATE_ADD(CURDATE(), Interval 1 DAY) ";
        } elseif ($filter === "expired") {
            $sql_filter = "< CURDATE() ";
        }

        $sql_filtered_tasks =  "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
            ."FROM tasks AS t "
            ."JOIN projects AS p "
            ."ON t.id_project = p.id_project "
            ."WHERE t.id_project = ? "
            ."AND t.id_user = ? "
            ."AND deadline " . $sql_filter
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_filtered_tasks, $data);

        return $tasks;
    }

//получение из БД проектов с количеством задач
    function get_projects_with_tasks_count($link, $data = []) {

        $sql_projects = "SELECT p.*, COUNT(t.id_task) AS tasks_count "
            ."FROM projects AS p "
            ."LEFT JOIN tasks AS t "
            ."ON p.id_project = t.id_project "
            ."WHERE p.id_user = ? "
            ."GROUP BY project";

        $projects = db_fetch_data($link, $sql_projects, $data);

        return $projects;
    }

//внесение данных задачи в БД
    function add_task_data_in_db($link, $data = []) {

        $sql_task = "INSERT INTO tasks (status, task, file, deadline, id_user, id_project) "
            ."VALUES "
            ."(?, ?, ?, ?, ?, ?)";

        db_insert_data($link, $sql_task, $data);
    }

?>
