<?php

    require_once("functions.php");

//получение параметра фильтра
    function get_sql_filter_value($filter)
    {
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

//сборка запроса количества задач
    function get_queries_tasks_count($id_project = false, $filter = false, $status = false)
    {
        //количество всех задач
        $sql_tasks_count =  "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ?";

        //количество задач с фильтром
        if ($filter) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks_count = $sql_tasks_count . " " . $sql_filter;
            //количество задач с фильтром и статусом 0
            if ($status) {
                $sql_tasks_count = $sql_tasks_count . " AND status = 0 " . $sql_filter;
            }
            //количество задач одного проекта с фильтром
            if ($id_project) {
                $sql_tasks_count = $sql_tasks_count . " AND id_project = ? " . $sql_filter;
                //количество задач одного проекта с фильтром и статусом 0
                if ($status) {
                    $sql_tasks_count = $sql_tasks_count . " AND id_project = ?" . " AND status = 0 " . $sql_filter;
                }
            }
        } else {
            //количество всех задач со статусом 0
            if ($status) {
                $sql_tasks_count = $sql_tasks_count . " AND status = 0";
            }
            //количество задач одного проекта
            if ($id_project) {
                $sql_tasks_count = $sql_tasks_count . " AND id_project = ?";
                //количество задач одного проекта со статусом 0
                if ($status) {
                    $sql_tasks_count = $sql_tasks_count . " AND id_project = ?" . " AND status = 0";
                }
            }
        }

        return $sql_tasks_count;
    }

//сборка запроса списка задач
    function get_queries_tasks($limit, $offset, $id_project = false, $filter = false, $status = false)
    {
        $sql_tasks_base =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ?";
        $sql_pagination = " LIMIT " . $limit . " OFFSET " . $offset;

        //список задач с фильтром
        if ($filter) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks = $sql_tasks_base . " " . $sql_filter . $sql_pagination;
            //список задач с фильтром и статусом 0
            if ($status) {
                $sql_tasks = $sql_tasks_base . " AND status = 0 " . $sql_filter . $sql_pagination;
            }
            //список задач одного проекта с фильтром
            if ($id_project) {
                $sql_tasks = $sql_tasks_base . " AND id_project = ? " . $sql_filter . $sql_pagination;
                //список задач одного проекта с фильтром и статусом 0
                if ($status) {
                    $sql_tasks = $sql_tasks_base . " AND id_project = ? AND status = 0 " . $sql_filter . $sql_pagination;
                }
            }
        } else {
            //список всех задач
            $sql_tasks = $sql_tasks_base . $sql_pagination;
            //список всех задач со статусом 0
            if ($status) {
                $sql_tasks = $sql_tasks_base . " AND status = 0" . $sql_pagination;
            }
            //список задач одного проекта
            if ($id_project) {
                $sql_tasks = $sql_tasks_base . " AND id_project = ?" . $sql_pagination;
                //список задач одного проекта со статусом 0
                if ($status) {
                    $sql_tasks = $sql_tasks_base . " AND id_project = ? AND status = 0" . $sql_pagination;
                }
            }
        }

        return $sql_tasks;
    }

//запрос на получение количества задач
    function get_tasks_count($link, $id_user, $id_project = false, $filter = false, $status = false)
    {
        $sql_tasks_count = get_queries_tasks_count($id_project, $filter, $status);

        if ($id_project) {
            $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, [$id_user, $id_project]);
        } else {
            $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, [$id_user]);
        }

        return $tasks_count_arr[0]["tasks_count"];
    }

//запрос на получение списка задач
    function get_tasks($link, $limit, $offset, $id_user, $id_project = false, $filter = false, $status = false)
    {
        $sql_tasks = get_queries_tasks($limit, $offset, $id_project, $filter, $status);

        if ($id_project) {
            $tasks = db_fetch_data($link, $sql_tasks, [$id_user, $id_project]);
        } else {
            $tasks = db_fetch_data($link, $sql_tasks, [$id_user]);
        }

        return $tasks;
    }

/*-----------------------------------------------ПОИСК---------------------------------------------------*/

//запрос на получение количества задач для полнотектового поиска
    function get_tasks_count_for_search_fulltext($link, $data = [])
    {
        $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND MATCH(task) AGAINST(?)";

        $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, $data);

        return $tasks_count_arr[0]["tasks_count"];
    }

//запрос на получение количества задач для поиска по подстроке
    function get_tasks_count_for_search_like($link, $data = [])
    {
        $sql_tasks_count = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND task LIKE ?";

        $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, $data);

        return $tasks_count_arr[0]["tasks_count"];
    }

//запрос на получение списка задач по введеной в строку поиска фразе - полнотекстовый поиск
    function get_tasks_for_search_fulltext($link, $limit, $offset, $data = [])
    {
        $sql_search_tasks =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ? "
            ."AND MATCH(task) AGAINST(?) "
            ."LIMIT " . $limit . " OFFSET " . $offset;

        $tasks = db_fetch_data($link, $sql_search_tasks, $data);

        return $tasks;
    }

//запрос на получение списка задач по введеной в строку поиска фразе - поиск по подстроке
    function get_tasks_for_search_like($link, $limit, $offset, $data = [])
    {
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
    function change_task_status($link, $check, $data)
    {
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
    function get_projects_with_tasks_count($link, $data = [])
    {
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
    function add_task_data_in_db($link, $data = [])
    {
        $sql_task = "INSERT INTO tasks (status, task, file, deadline, id_user, id_project) "
            ."VALUES "
            ."(?, ?, ?, ?, ?, ?)";

        db_insert_data($link, $sql_task, $data);
    }

//запрос на получение данных о пользователе по введенному email
    function get_user_data($link, $data)
    {
        $sql = "SELECT * "
        ."FROM users "
        ."WHERE email = ?";

        $user = db_fetch_data($link, $sql, [$data]);

        return $user;
    }

//запрос на получение списка пользователей и количества задач у них с истекающим сроком и статусом 0
    function get_users_with_count_tasks_deadline_curdate($link)
    {
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
    function get_tasks_deadline_curdate($link)
    {
        $sql_tasks = "SELECT id_user, task, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline "
        ."FROM tasks "
        ."WHERE STATUS = 0 "
        ."AND deadline = CURDATE()";

        $tasks = db_fetch_data($link, $sql_tasks, []);

        return $tasks;
    }

//запрос на добавление проекта в БД
    function add_project_in_db($link, $id_user, $project_name)
    {
        $sql_project = "INSERT INTO projects (id_user, project) "
        ."VALUES (?, ?)";

        db_insert_data($link, $sql_project, [$id_user, $project_name]);
    }

//запрос на получение списка пользователей
    function get_users($link)
    {
        $sql = "SELECT * "
        ."FROM users";

        $users = db_fetch_data($link, $sql, []);

        return $users;
    }

//запрос на добавление данных пользователя в БД
    function add_users($link, $data = [])
    {
        $sql = "INSERT INTO users (email, password, name) "
        ."VALUES "
        ."(?, ?, ?)";

        db_insert_data($link, $sql, $data);
    }
