<?php
    /**
     * Считает количество задач в проекте/категории
     * @param array $taskArray Ассоциативный массив с задачами
     * @param string $project Строка с именем проекта/категории
     * @return integer Количество задач в проекте/категории
     */
    function getTaskCount ($taskArray, $project) {
        $count = 0;
        foreach ($taskArray as $key => $value) {
            if ($value["category"] === $project) {
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
