<?php

    //подключаем composer
    require_once("vendor/autoload.php");
    require_once("functions.php");
    require_once("data.php");

    $transport = new Swift_SmtpTransport("phpdemo.ru", 25);
    $transport -> setUsername("keks@phpdemo.ru");
    $transport -> setPassword("htmlacademy");

    $mailer = new Swift_Mailer($transport);

    $logger = new Swift_Plugins_Loggers_ArrayLogger();
    $mailer -> registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

    $connect = mysqli_connect("localhost", "root", "", "dvp");

    mysqli_set_charset($connect, "utf8");

    if (!$connect) {
        $error_connect = mysqli_connect_error();
        echo($error_connect);
    }

//запрос на получение списка пользователей и количества задач у них с истекающим сроком
    $users = get_users_with_count_tasks_deadline_curdate($connect);

//запрос на получение списка задач с истекающим сроком
    $tasks = get_tasks_deadline_curdate($connect);

//формирование письма
    foreach ($users as $user) {

        $message = new Swift_Message();
        $message -> setSubject("Уведомление от сервиса «Дела в порядке»");
        $message -> setFrom(["keks@phpdemo.ru" => "Дела в порядке"]);
        $message -> addTo($user["email"], $user["name"]);

        if ($user["tasks_count"] === 1) {
            foreach ($tasks as $task) {
                if ($task["id_user"] === $user["id_user"]) {
                    $message_part = $task["task"] . " на " . $task["deadline"];
                }
            }

            $msg_content = "Уважаемый, " . $user["name"] . "!" . " У вас запланирована задача:\n" . $message_part;
        } else {
            $user_tasks = [];

            foreach ($tasks as $task) {
                if ($task["id_user"] === $user["id_user"]) {

                    $user_tasks[] = $task["task"] . " на " . $task["deadline"];

                    $message_part = implode("\n", $user_tasks);
                }
            }

            $msg_content = "Уважаемый, " . $user["name"] . "!" . " У вас запланированы следующие задачи:\n" . $message_part;
        }

        $message -> setBody($msg_content, "text/plain");

        $result = $mailer->send($message);
    }

    if ($result) {
        print("Рассылка успешно отправлена");
    }
    else {
        print("Не удалось отправить рассылку: " . $logger->dump());
    }

?>
