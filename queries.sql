INSERT INTO projects (project, id_user)
VALUES
	("Входящие", 1),
	("Учеба", 2),
	("Работа", 1),
	("Домашние дела", 2),
	("Авто", 2);

INSERT INTO tasks (status, task, deadline, id_user, id_project)
VALUES
  (0, "Собеседование в IT компании", "10.06.2019", 1, 3),
  (0, "Выполнить тестовое задание", "25.12.2018", 2, 3),
  (1, "Сделать задание первого раздела", "21.12.2018", 1, 2),
  (0, "Встреча с другом", "22.12.2018", 2, 1),
  (0, "Купить корм для кота", null, 1, 4),
  (0, "Заказать пиццу", null, 2, 4);

INSERT INTO users (email, name, password)
VALUES
  ("pupkin@mail.ru", "Вася", "1234"),
  ("ivanov@mail.ru", "Петя", "4321");

/*получить список из всех проектов для одного пользователя*/
SELECT name, project FROM projects p
JOIN users u
ON p.id_user = u.id_user
WHERE p.id_user = 2;

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
