<?php

    require_once("functions.php");

    /**
     * Возвращает SQL-код в зависимости от значения фильтра
     * @param string $filter Значение фильтра
     * @return string $sql_filter SQL-код со значением фильтра
     */
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

    /**
     * Собирает SQL-запрос на получение количества задач в зависимости от переданных параметров
     * @param string $id_project Id проекта
     * @param string $filter Значение фильтра
     * @param bool $status Статус задачи: выполненная/невыполненная
     * @return string $sql_tasks_count SQL-код запроса на получение количества задач
     */
    function get_queries_tasks_count($id_project = false, $filter = false, $status = false)
    {
        $sql_tasks_count =  "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ?";

        if ($id_project && $filter && $status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks_count =  $sql_tasks_count . " AND id_project = ? AND status = 0 " . $sql_filter;
        } elseif ($id_project && $filter && !$status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks_count = $sql_tasks_count . " AND id_project = ? " . $sql_filter;
        } elseif ($id_project && !$filter && !$status) {
            $sql_tasks_count = $sql_tasks_count . " AND id_project = ?";
        } elseif (!$id_project && !$filter && $status) {
            $sql_tasks_count = $sql_tasks_count . " AND status = 0";
        } elseif (!$id_project && $filter && $status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks_count =  $sql_tasks_count . " AND status = 0 " . $sql_filter;
        } elseif ($id_project && !$filter && $status) {
            $sql_tasks_count = $sql_tasks_count . " AND id_project = ? AND status = 0";
        } elseif (!$id_project && $filter && !$status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks_count = $sql_tasks_count . " " . $sql_filter;
        } else {
            $sql_tasks_count = $sql_tasks_count;
        }

        return $sql_tasks_count;
    }

    /**
     * Собирает SQL-запрос на получение списка задач в зависимости от переданных параметров
     * @param integer $limit Количество задач на одной странице
     * @param integer $offset Смещение списка получаемых задач
     * @param string $id_project Id проекта
     * @param string $filter Значение фильтра
     * @param bool $status Статус задачи: выполненная/невыполненная
     * @return string $sql_tasks SQL-код запроса на получение списка задач
     */
    function get_queries_tasks($limit, $offset, $id_project = false, $filter = false, $status = false)
    {
        $sql_tasks_base =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ?";
        $sql_pagination = " LIMIT " . $limit . " OFFSET " . $offset;

        if ($id_project && $filter && $status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks = $sql_tasks_base . " AND id_project = ? AND status = 0 " . $sql_filter . $sql_pagination;
        } elseif ($id_project && $filter && !$status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks = $sql_tasks_base . " AND id_project = ? " . $sql_filter . $sql_pagination;
        } elseif ($id_project && !$filter && !$status) {
            $sql_tasks = $sql_tasks_base . " AND id_project = ?" . $sql_pagination;
        } elseif (!$id_project && !$filter && $status) {
            $sql_tasks = $sql_tasks_base . " AND status = 0" . $sql_pagination;
        } elseif (!$id_project && $filter && $status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks = $sql_tasks_base . " AND status = 0 " . $sql_filter . $sql_pagination;
        } elseif ($id_project && !$filter && $status) {
            $sql_tasks = $sql_tasks_base . " AND id_project = ? AND status = 0" . $sql_pagination;
        } elseif (!$id_project && $filter && !$status) {
            $sql_filter = get_sql_filter_value($filter);
            $sql_tasks = $sql_tasks_base . " " . $sql_filter . $sql_pagination;
        } else {
            $sql_tasks = $sql_tasks_base . $sql_pagination;
        }

        return $sql_tasks;
    }

    /**
     * Выполняет SQL-запрос на получение количества задач в зависимости от переданных параметров
     * @param integer $limit Количество задач на одной странице
     * @param integer $offset Смещение списка получаемых задач
     * @param string $id_project Id проекта
     * @param string $filter Значение фильтра
     * @param bool $status Статус задачи: выполненная/невыполненная
     * @return array $tasks_count_arr Количество задач
     */
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

    /**
     * Выполняет SQL-запрос на получение списка задач в зависимости от переданных параметров
     * @param integer $limit Количество задач на одной странице
     * @param integer $offset Смещение списка получаемых задач
     * @param string $id_project Id проекта
     * @param string $filter Значение фильтра
     * @param bool $status Статус задачи: выполненная/невыполненная
     * @return array $tasks Список задач
     */
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

    /**
     * Выполняет SQL-запрос на получение количества задач соответствующих результатам поиска
     * @param string $link Ресурс соединения
     * @param array $data Данные для вставки на место плейсхолдеров
     * @param bool $fulltext Способ поиска
     * @return array $tasks_count_arr Количество задач соответствующих результатам поиска
     */
    function get_tasks_count_for_search($link, $data = [], $fulltext = false)
    {
        $sql_tasks_count_base = "SELECT COUNT(task) AS tasks_count "
            ."FROM tasks "
            ."WHERE id_user = ?";

        if ($fulltext) {
            $sql_tasks_count = $sql_tasks_count_base . " AND MATCH(task) AGAINST(?)";
        } else {
            $sql_tasks_count = $sql_tasks_count_base . " AND task LIKE ?";
        }

        $tasks_count_arr = db_fetch_data($link, $sql_tasks_count, $data);

        return $tasks_count_arr[0]["tasks_count"];
    }

    /**
     * Выполняет SQL-запрос на получение списка задач соответствующих результатам поиска
     * @param string $link Ресурс соединения
     * @param integer $limit Количество задач на одной странице
     * @param integer $offset Смещение списка получаемых задач
     * @param array $data Данные для вставки на место плейсхолдеров
     * @param bool $fulltext Способ поиска
     * @return array $tasks Список задач соответствующих результатам поиска
     */
    function get_tasks_for_search($link, $limit, $offset, $data = [], $fulltext = false)
    {
        $sql_tasks_base =  "SELECT id_task, task, file, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline, status "
            ."FROM tasks "
            ."WHERE id_user = ?";
        $sql_pagination = "LIMIT " . $limit . " OFFSET " . $offset;

        if ($fulltext) {
            $sql_tasks =  $sql_tasks_base . " AND MATCH(task) AGAINST(?) " . $sql_pagination;
        } else {
            $sql_tasks =  $sql_tasks_base . " AND task LIKE ? " . $sql_pagination;
        }

        $tasks = db_fetch_data($link, $sql_tasks, $data);

        return $tasks;
    }

    /**
     * Выполняет SQL-запрос на обновление статуса задачи в БД
     * @param string $link Ресурс соединения
     * @param string $check Статус задачи: выполненная/невыполненная
     * @param string $data Данные для вставки на место плейсхолдеров
     */
    function change_task_status($link, $check, $data)
    {
        $status = 0;

        if ((int)$check) {
            $status = 1;
        }

        $sql_task_status = "UPDATE tasks "
            ."SET status = ? "
            ."WHERE id_task = ?";

        db_insert_data($link, $sql_task_status, [$status, $data]);
    }

    /**
     * Выполняет SQL-запрос на получение списка с количеством задач для каждого проекта
     * @param string $link Ресурс соединения
     * @param array $data Данные для вставки на место плейсхолдеров
     * @return array $projects Список проектов с количеством задач для каждого проекта
     */
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

    /**
     * Выполняет SQL-запрос на добавление данных задачи в БД
     * @param string $link Ресурс соединения
     * @param string $data Данные для вставки на место плейсхолдеров
     */
    function add_task_data_in_db($link, $data = [])
    {
        $sql_task = "INSERT INTO tasks (status, task, file, deadline, id_user, id_project) "
            ."VALUES "
            ."(?, ?, ?, ?, ?, ?)";

        db_insert_data($link, $sql_task, $data);
    }

    /**
     * Выполняет SQL-запрос на получение данных о пользователе по введенному email
     * @param string $link Ресурс соединения
     * @param string $data Данные для вставки на место плейсхолдера
     * @return array $user Данные пользователя
     */
    function get_user_data($link, $data)
    {
        $sql = "SELECT * "
        ."FROM users "
        ."WHERE email = ?";

        $user = db_fetch_data($link, $sql, [$data]);

        return $user;
    }

    /**
     * Выполняет SQL-запрос на получение списка пользователей и количества невыполненных задач у них с истекающим сроком
     * @param string $link Ресурс соединения
     * @return array $users Список пользователей и количества невыполненных задач у них с истекающим сроком
     */

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

    /**
     * Выполняет SQL-запрос на получение списка невыполненных задач с истекающим сроком
     * @param string $link Ресурс соединения
     * @return array $tasks Список задач
     */
    function get_tasks_deadline_curdate($link)
    {
        $sql_tasks = "SELECT id_user, task, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline "
        ."FROM tasks "
        ."WHERE STATUS = 0 "
        ."AND deadline = CURDATE()";

        $tasks = db_fetch_data($link, $sql_tasks, []);

        return $tasks;
    }

    /**
     * Выполняет SQL-запрос на добавление данных проекта в БД
     * @param string $link Ресурс соединения
     * @param string $id_user Id пользователя
     * @param string $project_name Назавание проекта
     */
    function add_project_in_db($link, $id_user, $project_name)
    {
        $sql_project = "INSERT INTO projects (id_user, project) "
        ."VALUES (?, ?)";

        db_insert_data($link, $sql_project, [$id_user, $project_name]);
    }

    /**
     * Выполняет SQL-запрос на получение списка пользователей
     * @param string $link Ресурс соединения
     * @return array $users Список пользователей
     */
    function get_users($link)
    {
        $sql = "SELECT * "
        ."FROM users";

        $users = db_fetch_data($link, $sql, []);

        return $users;
    }

    /**
     * Выполняет SQL-запрос на добавление данных пользователя в БД
     * @param string $link Ресурс соединения
     * @param array $data Данные для вставки на место плейсхолдеров
     */
    function add_users($link, $data = [])
    {
        $sql = "INSERT INTO users (email, password, name) "
        ."VALUES "
        ."(?, ?, ?)";

        db_insert_data($link, $sql, $data);
    }
