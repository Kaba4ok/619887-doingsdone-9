<?php
    /**
     * Считает количество часов до окончания срока выполнения задачи
     * @param string $value Дата окончания срока выполнения задачи
     * @return float Количество часов до окончания срока выполнения задачи
     */
    function check_time ($value) {
        if ($value) {
            $sec_in_hour = 3600;
            $cur_time = strtotime("now");
            $dead_line = strtotime($value);
            $hours_count = floor(($dead_line - $cur_time) / $sec_in_hour);

            return $hours_count;
        }
    }

    /**
     * Считает количество задач в проекте/категории
     * @param array $taskArray Ассоциативный массив с задачами
     * @param string $project Строка с именем проекта/категории
     * @return integer Количество задач в проекте/категории
     */
    function get_task_count ($task_array, $project) {
        $count = 0;
        foreach ($task_array as $key => $value) {
            if ($value["project"] === $project) {
                $count++;
            }
        }
        return $count;
    };


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
?>
