<style type="text/css">
    .pagination {
        margin: 20px 0;
    }

    .pagination__list {
        display: flex;
        justify-content: center;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .pagination__item {
        margin: 0 5px;
        background-color: #ffffff;
        border: 1px solid #cccccc;
        border-radius: 50%;
    }

    .pagination__item:hover {
        border-color: #b5b5b5;
        background-color: #f2f2f2;
    }

    .pagination__item--active,
    .pagination__item--active:hover {
        border-color: #cccccc;
        background-color: #D7DBE8;
    }

    .pagination__link {
        display: block;
        width: 40px;
        height: 40px;
        padding: 12px;
        font-size: 14px;
        color: #000000;
        text-align: center;
        box-sizing: border-box;
    }

    .pagination__link:hover,
    .pagination__link:focus {
        text-decoration: none;
    }
</style>

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
