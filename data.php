<?php

    require_once("functions.php");

//получение параметра фильтра
    function get_sql_filter_value($filter) {

        if ($filter === "today") {
            $sql_filter = "AND deadline = CURDATE()";
        } elseif ($filter === "tomorrow") {
            $sql_filter = "AND deadline = DATE_ADD(CURDATE(), Interval 1 DAY)";
        } elseif ($filter === "expired") {
            $sql_filter = "AND deadline < CURDATE()";
        } else {
            $sql_filter = "";
        }

        return $sql_filter;
    }

//запрос на получение количества задач
    function get_tasks_count($link, $id_user, $id_project = false, $filter = false) {
        if ($filter) {

            $sql_filter = get_sql_filter_value($filter);

            $sql_tasks_count =  "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            .$sql_filter;

            if ($id_project) {

                $sql_filter = get_sql_filter_value($filter);

                $sql_tasks_count =  "SELECT COUNT(task) AS tasks_count "
                ."FROM tasks "
                ."WHERE id_user = ? "
                ."AND id_project = ? "
                .$sql_filter;
            }
        } else {

            $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ?";

            if ($id_project) {

                $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
                ."FROM tasks "
                ."WHERE id_user = ? "
                ."AND id_project = ?";
            }
        }

        if ($id_project) {
            $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, [$id_user, $id_project]);
        } else {
            $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, [$id_user]);
        }

        return $tasks_count_arr[0]["tasks_count"];
    }

//запрос на получение списка задач
    function get_tasks_with_limit_and_offset($link, $limit, $offset, $id_user, $id_project = false, $filter = false) {

        if ($filter) {

            $sql_filter = get_sql_filter_value($filter);

            $sql_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            .$sql_filter
            ."LIMIT " . $limit . " OFFSET " . $offset;

            if ($id_project) {

                $sql_filter = get_sql_filter_value($filter);

                $sql_tasks =  "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                ."FROM tasks AS t "
                ."JOIN projects AS p "
                ."ON t.id_project = p.id_project "
                ."WHERE t.id_user = ? "
                ."AND t.id_project = ? "
                .$sql_filter
                ."LIMIT " . $limit . " OFFSET " . $offset;
            }
        } else {

            $sql_tasks = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, p.project, t.status "
            ."FROM tasks AS t "
            ."JOIN projects AS p "
            ."ON p.id_project = t.id_project "
            ."WHERE t.id_user = ? "
            ."LIMIT " . $limit . " OFFSET " . $offset;

            if ($id_project) {

                $sql_tasks = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                ."FROM tasks AS t "
                ."JOIN projects AS p "
                ."ON t.id_project = p.id_project "
                ."WHERE t.id_user = ? "
                ."AND t.id_project = ? "
                ."LIMIT " . $limit . " OFFSET " . $offset;
            }
        }

        if ($id_project) {
            $tasks = db_fetch_data($link, $sql_tasks, [$id_user, $id_project]);
        } else {
            $tasks = db_fetch_data($link, $sql_tasks, [$id_user]);
        }

        return $tasks;
    }


/*--------------------------------- КОЛИЧЕСТВО ЗАДАЧ СТАТУС 0 -----------------------------------*/

//запрос на получение количества задач со статусом 0
    function get_tasks_count_with_status_notshow($link, $id_user, $id_project = false, $filter = false) {

        if ($filter) {

            $sql_filter = get_sql_filter_value($filter);

            $sql_tasks_count =  "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND status = 0 "
            .$sql_filter;

            if ($id_project) {

                $sql_filter = get_sql_filter_value($filter);

                $sql_tasks_count =  "SELECT COUNT(task) AS tasks_count "
                ."FROM tasks "
                ."WHERE id_user = ? "
                ."AND id_project = ? "
                ."AND status = 0 "
                .$sql_filter;
            }
        } else {

            $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND status = 0";

            if ($id_project) {

                $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
                ."FROM tasks "
                ."WHERE id_user = ? "
                ."AND id_project = ? "
                ."AND status = 0";
            }
        }

        if ($id_project) {
            $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, [$id_user, $id_project]);
        } else {
            $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, [$id_user]);
        }

        return $tasks_count_arr[0]["tasks_count"];
    }


/*--------------------------------- СПИСОК ЗАДАЧ СТАТУС 0 -----------------------------------*/

//запрос на получение списка задач со статусом 0
    function get_tasks_with_limit_and_offset_with_status_notshow($link, $limit, $offset, $id_user, $id_project = false, $filter = false) {

        if ($filter) {

            $sql_filter = get_sql_filter_value($filter);

            $sql_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND status = 0 "
            .$sql_filter
            ."LIMIT " . $limit . " OFFSET " . $offset;

            if ($id_project) {

                $sql_filter = get_sql_filter_value($filter);

                $sql_tasks =  "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                ."FROM tasks AS t "
                ."JOIN projects AS p "
                ."ON t.id_project = p.id_project "
                ."WHERE t.id_user = ? "
                ."AND t.id_project = ? "
                ."AND status = 0 "
                .$sql_filter
                ."LIMIT " . $limit . " OFFSET " . $offset;
            }
        } else {

            $sql_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND status = 0 "
            ."LIMIT " . $limit . " OFFSET " . $offset;

            if ($id_project) {

                $sql_tasks = "SELECT t.id_task, t.task, t.file, DATE_FORMAT(t.deadline, '%d.%m.%Y') AS deadline, t.status, p.project "
                ."FROM tasks AS t "
                ."JOIN projects AS p "
                ."ON t.id_project = p.id_project "
                ."WHERE t.id_user = ? "
                ."AND t.id_project = ? "
                ."AND status = 0 "
                ."LIMIT " . $limit . " OFFSET " . $offset;
            }
        }

        if ($id_project) {
            $tasks = db_fetch_data($link, $sql_tasks, [$id_user, $id_project]);
        } else {
            $tasks = db_fetch_data($link, $sql_tasks, [$id_user]);
        }

        return $tasks;
    }


/*-----------------------------------------------ПОИСК---------------------------------------------------*/

//запрос на получение количества задач для полнотектового поиска
    function get_tasks_count_for_search_fulltext($link, $data = []) {

        $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND MATCH(task) AGAINST(?)";

        $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, $data);

        foreach ($tasks_count_arr as $key => $value) {
            $tasks_count = $value["tasks_count"];
        }

        return $tasks_count;
    }

//запрос на получение количества задач для поиска по подстроке
    function get_tasks_count_for_search_like($link, $data = []) {

        $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND task LIKE ?";

        $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, $data);

        foreach ($tasks_count_arr as $key => $value) {
            $tasks_count = $value["tasks_count"];
        }

        return $tasks_count;
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


//------------------------------------------------------------------------------//


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

//запрос на получение проектов с количеством задач
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

//запрос на получение данных о пользователе по введенному email
    function get_user_data($link, $data) {
        $sql = "SELECT * "
        ."FROM users "
        ."WHERE email = ?";

        $user = db_fetch_data($link, $sql, [$data]);

        return $user;
    }

//запрос на получение списка пользователей и количества задач у них с истекающим сроком и статусом 0
    function get_users_with_count_tasks_deadline_curdate($link) {

        $sql_users = "SELECT u.id_user, name, email, COUNT(task) AS tasks_count "
        ."FROM users AS u "
        ."JOIN tasks AS t "
        ."ON u.id_user = t.id_user "
        ."WHERE STATUS = 0 "
        ."AND deadline = CURDATE() "
        ."GROUP BY name";

        $users = db_fetch_data($link, $sql_users, []);

        return $users;
    }

//запрос на получение списка задач с истекающим сроком и статусом 0
    function get_tasks_deadline_curdate($link) {

        $sql_tasks = "SELECT id_user, task, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline "
        ."FROM tasks "
        ."WHERE STATUS = 0 "
        ."AND deadline = CURDATE()";

        $tasks = db_fetch_data($link, $sql_tasks, []);

        return $tasks;
    }

//запрос на добавление проекта в БД
    function add_project_in_db($link, $id_user, $project_name) {
        $sql_project = "INSERT INTO projects (id_user, project) "
        ."VALUES (?, ?)";

        db_insert_data($link, $sql_project, [$id_user, $project_name]);
    }

//запрос на получение списка пользователей
    function get_users($link) {

        $sql = "SELECT * "
        ."FROM users";

        $users = db_fetch_data($link, $sql, []);

        return $users;
    }

//запрос на добавление данных пользователя в БД
    function add_users($link, $data = []) {
        $sql = "INSERT INTO users (email, password, name) "
        ."VALUES "
        ."(?, ?, ?)";

        db_insert_data($link, $sql, $data);
    }

?>
