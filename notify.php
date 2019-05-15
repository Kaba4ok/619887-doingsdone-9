<?php

    //подключаем composer
    require_once("vendor/autoload.php");
    require_once("functions.php");

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

    $sql_users = "SELECT u.id_user, name, email, COUNT(task) AS tasks_count "
        ."FROM users AS u "
        ."JOIN tasks AS t "
        ."ON u.id_user = t.id_user "
        ."WHERE STATUS = 0 "
        ."AND deadline = CURDATE() "
        ."GROUP BY name";

    $users = db_fetch_data($connect, $sql_users, []);

    $sql_tasks = "SELECT id_user, task, DATE_FORMAT(deadline, '%d.%m.%Y') AS deadline "
    ."FROM tasks "
    ."WHERE STATUS = 0 "
    ."AND deadline = CURDATE()";

    $tasks = db_fetch_data($connect, $sql_tasks, []);

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

                    $message_part = implode("\n",$user_tasks);
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
