<?php if ($pages_count > 1): ?>
    <div class="pagination">
        <ul class="pagination__list">
            <?php foreach ($pages as $page): ?>
                <li class="pagination__item <?php if($page == $cur_page): ?>pagination__item--active <?php endif ?>">
                    <a class="pagination__link" href="index.php?page=<?= $page; ?>"><?= $page; ?></a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>
