<h2 class="content__main-heading">Вход на сайт</h2>

<?php
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
?>

<form class="form" action="" method="post" autocomplete="off">
    <div class="form__row">
        <label class="form__label" for="email">E-mail <sup>*</sup></label>

        <input class="form__input <?php if (!empty($errors['email'])): ?> form__input--error <?php endif ?>" type="text" name="email" id="email" value="<?= $email ?>" placeholder="Введите e-mail">

        <?php if (!empty($errors["email"])): ?>
            <p class="form__message"><?= $errors["email"] ?></p>
        <?php endif ?>
    </div>

    <div class="form__row">
        <label class="form__label" for="password">Пароль <sup>*</sup></label>

        <input class="form__input <?php if (!empty($errors['password'])): ?> form__input--error <?php endif ?>" type="password" name="password" id="password" value="<?= $password ?>" placeholder="Введите пароль">

        <?php if (!empty($errors["password"])): ?>
            <p class="form__message"><?= $errors["password"] ?></p>
        <?php endif ?>
    </div>

    <?php if (!empty($errors["password_invalid"]) || !empty($errors["email_invalid"])): ?>
        <p class="error-message">Вы ввели неверный email/пароль</p>
    <?php elseif (!empty($errors)): ?>
        <p class="error-message">Пожалуйста, исправьте ошибки в форме</p>
    <?php endif ?>

    <div class="form__row form__row--controls">
        <input class="button" type="submit" name="" value="Войти">
    </div>
</form>
