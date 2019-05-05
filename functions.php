<?php
    /**
     * Считает количество часов до окончания срока выполнения задачи
     * @param string $value Дата окончания срока выполнения задачи
     * @return float Количество часов до окончания срока выполнения задачи
     */
    function check_time($value) {
        if ($value) {
            $sec_in_hour = 3600;
            $cur_time = strtotime("now");
            $dead_line = strtotime($value);
            $hours_count = floor(($dead_line - $cur_time) / $sec_in_hour);

            return $hours_count;
        }
    }

    /**
     * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
     * @param string $name Путь к файлу шаблона относительно папки templates
     * @param array $data Ассоциативный массив с данными для шаблона
     * @return string Итоговый HTML
     */
    function include_template($name, array $data = []) {
        $name = 'templates/' . $name;
        $result = '';

        if (!is_readable($name)) {
            return $result;
        }

        ob_start();
        extract($data);
        require $name;

        $result = ob_get_clean();

        return $result;
    }

    /**
     * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
     *
     * @param $link mysqli Ресурс соединения
     * @param $sql string SQL запрос с плейсхолдерами вместо значений
     * @param array $data Данные для вставки на место плейсхолдеров
     *
     * @return mysqli_stmt Подготовленное выражение
     */
    function db_get_prepare_stmt($link, $sql, $data = []) {
        $stmt = mysqli_prepare($link, $sql);

        if ($stmt === false) {
            $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
            die($errorMsg);
        }

        if ($data) {
            $types = '';
            $stmt_data = [];

            foreach ($data as $value) {
                $type = 's';

                if (is_int($value)) {
                    $type = 'i';
                }
                else if (is_string($value)) {
                    $type = 's';
                }
                else if (is_double($value)) {
                    $type = 'd';
                }

                if ($type) {
                    $types .= $type;
                    $stmt_data[] = $value;
                }
            }

            $values = array_merge([$stmt, $types], $stmt_data);

            $func = 'mysqli_stmt_bind_param';
            $func(...$values);

            if (mysqli_errno($link) > 0) {
                $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
                die($errorMsg);
            }
        }

        return $stmt;
    }

    /**
     * Создает массив с данными на основе готового SQL запроса и переданных данных
     *
     * @param $link mysqli Ресурс соединения
     * @param $sql string SQL запрос с плейсхолдерами вместо значений
     * @param array $data Данные для вставки на место плейсхолдеров
     *
     * @return $result Массив с данными
     */
    function db_fetch_data($link, $sql, $data = []) {
        $result = [];
        $stmt = db_get_prepare_stmt($link, $sql, $data);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($res) {
            $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
        }

        return $result;
    }

    /**
     * Добавляет данные в БД на основе готового SQL запроса и переданных данных
     *
     * @param $link mysqli Ресурс соединения
     * @param $sql string SQL запрос с плейсхолдерами вместо значений
     * @param array $data Данные для вставки на место плейсхолдеров
     *
     * @return $result bool true при успешном добавлении данных в БД
     */
    function db_insert_data($link, $sql, $data = []) {
        $stmt = db_get_prepare_stmt($link, $sql, $data);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            $result = mysqli_insert_id($link);
        }

        return $result;
    }

    /**
     * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
     *
     * Примеры использования:
     * is_date_valid('2019-01-01'); // true
     * is_date_valid('2016-02-29'); // true
     * is_date_valid('2019-04-31'); // false
     * is_date_valid('10.10.2010'); // false
     * is_date_valid('10/10/2010'); // false
     *
     * @param string $date Дата в виде строки
     *
     * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
     */
    function is_date_valid(string $date) : bool {
        $format_to_check = 'Y-m-d';
        $dateTimeObj = date_create_from_format($format_to_check, $date);

        return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
    }

    /**
     * Получает имя файла на основе пути к этому файлу
     *
     * @param $path string Путь к файлу
     * @return $name string Имя файла
     */
    function get_file_name($path) {
        $separate_path = explode("___", $path);
        $result = array_pop($separate_path);
        $name = mb_substr($result, 0, 25);

        if (mb_strlen($name) >= 25) {
            $name = $name . "...";
        }

        return $name;
    }
?>
