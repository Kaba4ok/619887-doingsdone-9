<?php if ($pages_count > 1): ?>
    <div class="pagination">
        <ul class="pagination__list">
            <?php foreach ($pages as $page): ?>
                <li class="pagination__item <?php if($page == $cur_page): ?>pagination__item--active <?php endif ?>">
                    <a class="pagination__link" href="index.php?page=<?= $page; ?><?php if(isset($_GET['id_project'])): ?>&id_project=<?= $_GET['id_project'] ?><?php endif ?>"><?= $page; ?></a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>
