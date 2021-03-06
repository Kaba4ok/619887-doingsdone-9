<h2 class="content__main-heading">Добавление задачи</h2>

<form class="form"  action="" method="post" enctype="multipart/form-data" autocomplete="off">
  <?php
    $task_name = $_POST["name"] ?? "";
    $task_date = $_POST["date"] ?? "";
    $task_project = $_POST["project"] ?? "";
  ?>
  <div class="form__row">
    <?php $error_class = isset($errors['name']) ? "form__input--error" : ""; ?>
    <label class="form__label" for="name">Название <sup>*</sup></label>

    <input class="form__input <?= $error_class; ?>" type="text" name="name" id="name" value="<?= htmlspecialchars($task_name) ?>" placeholder="Введите название">
    <?php if (isset($errors['name'])): ?>
    <p class="form__message"><?=$errors['name']; ?></p>
    <?php endif; ?>
  </div>

  <div class="form__row">
    <?php $error_class = isset($errors['project']) ? "form__input--error" : ""; ?>
    <label class="form__label" for="project">Проект <sup>*</sup></label>

    <select class="form__input form__input--select <?= $error_class; ?>" name="project" id="project">
      <?php foreach ($projects as $value): ?>
        <option value="<?= $value['id_project'] ?>" <?php if ((int)$task_project === $value["id_project"]): ?> selected <?php endif ?> ><?=htmlspecialchars($value["project"]); ?></option>
      <?php endforeach ?>
    </select>
    <?php if (isset($errors['project'])): ?>
    <p class="form__message"><?=$errors['project']; ?></p>
    <?php endif; ?>
  </div>

  <div class="form__row">
    <?php $error_class = isset($errors['date']) ? "form__input--error" : ""; ?>
    <label class="form__label" for="date">Дата выполнения</label>

    <input class="form__input form__input--date <?= $error_class; ?>" type="text" name="date" id="date" value="<?= htmlspecialchars($task_date) ?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
    <?php if (isset($errors['date'])): ?>
    <p class="form__message"><?=$errors['date']; ?></p>
    <?php endif; ?>
  </div>

  <div class="form__row">
    <label class="form__label" for="file">Файл</label>

    <div class="form__input-file">
      <input class="visually-hidden" type="file" name="file" id="file" value="">

      <label class="button button--transparent" for="file">
        <span>Выберите файл</span>
      </label>
    </div>
  </div>

  <div class="form__row form__row--controls">
    <input class="button" type="submit" name="" value="Добавить">
  </div>
</form>
