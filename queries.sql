INSERT INTO projects (project, id_user)
VALUES
	("Входящие", 2),
	("Учеба", 1),
	("Работа", 2),
	("Домашние дела", 2),
	("Авто", 1),
  ("Спорт", 1),
  ("Отдых", 2),
  ("Творчество", 1);

INSERT INTO tasks (status, task, deadline, id_user, id_project)
VALUES
  (0, "Собеседование в IT компании", "2019.04.10", 1, 2),
  (0, "Выполнить тестовое задание", "2018.12.25", 2, 3),
  (1, "Сделать задание первого раздела", "2017.12.21", 1, 2),
  (0, "Встреча с другом", "2018.12.22", 2, 1),
  (0, "Купить корм для кота", null, 1, 5),
  (0, "Заказать пиццу", null, 2, 4),
  (1, "Купить ролики", "2018.06.04", 1, 6),
  (0, "Забронировать отель", "2016.10.18", 2, 7),
  (0, "Поиграть на гитаре", null, 1, 8);

INSERT INTO users (email, name, password)
VALUES
  ("pupkin@mail.ru", "Вася", "1234"),
  ("ivanov@mail.ru", "Петя", "4321");

/*получить список из всех проектов для одного пользователя*/
SELECT name, project FROM projects p
JOIN users u
ON p.id_user = u.id_user
WHERE p.id_user = 2;

/*посчитать количество задач в каждом проекте*/
SELECT p.project AS project, COUNT(*) AS tasks_count
FROM projects p
JOIN tasks t
ON p.id_project = t.id_project
GROUP BY project;

/*получить список из всех задач для одного проекта*/
SELECT project, task FROM projects p
JOIN tasks t
ON p.id_project = t.id_project
WHERE p.id_project = 3;

/*пометить задачу как выполненную*/
UPDATE tasks SET status = 1
WHERE id_task = 2;

/*обновить название задачи по её идентификатору*/
UPDATE tasks SET task = "Новая задача"
WHERE id_task = 2;
