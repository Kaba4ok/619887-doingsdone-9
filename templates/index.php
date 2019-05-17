<h2 class="content__main-heading">Список задач</h2>

<form class="search-form" action="index.php" method="get" autocomplete="off">
    <input class="search-form__input" type="text" name="search" value="" placeholder="Поиск по задачам">

    <input class="search-form__submit" type="submit" name="" value="Искать">
</form>

<div class="tasks-controls">
    <nav class="tasks-switch">
        <a href="index.php?filter=all_tasks<?php if(isset($_GET['id_project'])): ?>&id_project=<?= $_GET['id_project'] ?><?php endif ?>" class="tasks-switch__item <?php if(isset($_GET['filter']) && $_GET['filter'] === 'all_tasks'): ?>tasks-switch__item--active<?php endif ?>">Все задачи</a>
        <a href="index.php?filter=today<?php if(isset($_GET['id_project'])): ?>&id_project=<?= $_GET['id_project'] ?><?php endif ?>" class="tasks-switch__item <?php if(isset($_GET['filter']) && $_GET['filter'] === 'today'): ?>tasks-switch__item--active<?php endif ?>">Повестка дня</a>
        <a href="index.php?filter=tomorrow<?php if(isset($_GET['id_project'])): ?>&id_project=<?= $_GET['id_project'] ?><?php endif ?>" class="tasks-switch__item <?php if(isset($_GET['filter']) && $_GET['filter'] === 'tomorrow'): ?>tasks-switch__item--active<?php endif ?>">Завтра</a>
        <a href="index.php?filter=expired<?php if(isset($_GET['id_project'])): ?>&id_project=<?= $_GET['id_project'] ?><?php endif ?>" class="tasks-switch__item <?php if(isset($_GET['filter']) && $_GET['filter'] === 'expired'): ?>tasks-switch__item--active<?php endif ?>">Просроченные</a>
    </nav>

    <label class="checkbox">
        <!--добавить сюда аттрибут "checked", если переменная $show_complete_tasks равна единице-->
        <input class="checkbox__input visually-hidden show_completed" type="checkbox"
        <?php if($show_completed_status): ?> checked <?php endif ?>>
        <span class="checkbox__text">Показывать выполненные</span>
    </label>
</div>

<?php if(isset($_GET["search"]) && $error_search_message): ?>
    <p>Ничего не найдено по вашему запросу</p>
<?php endif ?>

<table class="tasks">
    <?php foreach ($tasks as $key => $value): ?>
        <?php if(!($value["status"]) || ($value["status"] && $show_completed_status)): ?>
            <tr class="tasks__item task <?php if ((int)check_time($value["deadline"]) <= 24 && $value["deadline"] !== null && !($value["status"])): ?> task--important <?php endif ?> <?php if($value["status"]): ?> task--completed <?php endif ?>">
                <td class="task__select">
                    <label class="checkbox task__checkbox">
                        <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="<?= $value['id_task'] ?>" <?php if($value["status"]): ?> checked <?php endif ?>>
                        <span class="checkbox__text"><?=htmlspecialchars($value["task"]); ?></span>
                    </label>
                </td>

                <td class="task__file">
                    <?php if(isset($value["file"]) && $value["file"] !== null): ?>
                    <a class="download-link" href="<?= $value['file'] ?>" download><?= get_file_name($value["file"]); ?></a>
                    <?php endif ?>
                </td>

                <td class="task__date"><?=$value["deadline"]; ?></td>
            </tr>
        <?php endif ?>
    <?php endforeach ?>
</table>

<?php include "pagination.php" ?>
