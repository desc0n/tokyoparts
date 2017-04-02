<?php
/** @var Model_CRM $crmModel */
$crmModel = Model::factory('CRM');
?>
<div class="row images-page">
    <div class="col-lg-12 well form-group">
        <form>
            <div class="row">
                <div class="col-lg-4">
                    <input type="text" class="form-control" name="brand"  value="<?=$brand;?>" placeholder="Бренд">
                </div>
                <div class="col-lg-4">
                    <input type="text" class="form-control" name="article" value="<?=$article;?>" placeholder="Артикул">
                </div>
                <div class="col-lg-4">
                    <button class="btn btn-primary">Поиск</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-12 form-group">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Бренд</th>
                <th>Артикул</th>
                <th>Локальный адрес</th>
                <th>Внешняя ссылка</th>
                <th class="text-center">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?foreach ($imagesList as $image){?>
                <tr id="imageRow<?=$image['id'];?>">
                    <td><?=$image['brand'];?></td>
                    <td><?=$image['article'];?></td>
                    <td><a href="<?=$image['local_src'];?>" target="_blank"><?=$image['local_src'];?></a></td>
                    <td><a href="<?=$image['outer_link'];?>" target="_blank"><?=$image['outer_link'];?></a></td>
                    <td class="text-center">
                        <button class="btn btn-danger" onclick="deleteItemImage(<?=$image['id'];?>);">
                            <span class="fa fa-remove"></span>
                        </button>
                    </td>
                </tr>
            <?}?>
        </table>
    </div>
</div>
<div class="col-lg-12 form-group">
    <ul class="pagination">
        <?
        /* Входные параметры */
        $limitPage = ceil($imagesCount / $crmModel->defaultLimit);
        $count_show_pages = 10;
        $url = "/crm/items_images/?brand=" . $brand . "&article=" . $article;
        $url_page = "/crm/items_images/?brand=" . $brand . "&article=" . $article . "&page=";

        if ($limitPage > 1) { // Всё это только если количество страниц больше 1
            /* Дальше идёт вычисление первой выводимой страницы и последней (чтобы текущая страница была где-то посредине, если это возможно, и чтобы общая сумма выводимых страниц была равна count_show_pages, либо меньше, если количество страниц недостаточно) */
            $left = $page - 1;
            $right = $limitPage - $page;

            if ($left < floor($count_show_pages / 2)) {
                $start = 1;
            } else {
                $start = $page - floor($count_show_pages / 2);
            }

            $end = $start + $count_show_pages - 1;

            if ($end > $limitPage) {
                $start -= ($end - $limitPage);
                $end = $limitPage;
                if ($start < 1) $start = 1;
            }
            ?>
            <!-- Дальше идёт вывод Pagination -->
            <?if ($page != 1) { ?>
                <li><a href="<?=$url?>" title="Первая страница">&laquo;&laquo;</a></li>
                <li><a href="<?if ($page == 2) { ?><?=$url?><?} else { ?><?=$url_page.($page - 1)?><?}?>" title="Предыдущая страница">&laquo;</a></li>
            <?}?>
            <?for ($i = $start; $i <= $end; $i++) { ?>
                <li <?=($i === $page ? 'class="active"' : null);?>>
                    <a href="<?php if ($i == 1) { ?><?=$url?><?} else { ?><?=$url_page.$i?><?}?>"><?=$i?></a>
                </li>
            <?}?>
            <?if ($page != $limitPage) { ?>
                <li><a href="<?=$url_page.($page + 1)?>" title="Следующая страница">&raquo;</a></li>
                <li><a href="<?=$url_page.$limitPage?>" title="Последняя страница">&raquo;&raquo;</a></li>
            <?}?>
        <?}?>
    </ul>
</div>
<form method="post" enctype='multipart/form-data' class="form-horizontal" role="form">
    <h3 class="text-center">Добавить изображение</h3>
    <div class="form-group">
        <label for="inputBrand" class="col-lg-3 control-label">Бренд</label>
        <div class="col-lg-9">
            <input type="text" class="form-control" name="brand" id="inputBrand" placeholder="Бренд" required>
        </div>
    </div>
    <div class="form-group">
        <label for="inputArticle" class="col-lg-3 control-label">Артикул</label>
        <div class="col-lg-9">
            <input type="text" class="form-control" name="article" id="inputArticle" placeholder="Артикул" required>
        </div>
    </div>
    <div class="form-group">
        <label for="inputLocalSrc" class="col-lg-3 control-label">Файл на сайте</label>
        <div class="col-lg-9">
            <input type="file" name="images">
        </div>
    </div>
    <div class="form-group">
        <label for="inputOuterLink" class="col-lg-3 control-label">Внешняя ссылка</label>
        <div class="col-lg-9">
            <input type="text" class="form-control" name="outer_link" id="inputOuterLink" placeholder="Внешняя ссылка">
        </div>
    </div>
    <div class="form-group">
        <div class="col-lg-12 text-right">
            <button class="btn btn-success" name="loadImage" value="1">Добавить</button>
        </div>
    </div>
</form>
<div class="well col-lg-12">
    <form method="post" enctype='multipart/form-data'>
        <h3>Загрузить с файла</h3>
        <div class="alert alert-info">
            <strong>Формат прайса (csv).</strong> Бренд, артикул, ссылка на адрес на сайте(может быть несколько, разделенных запятой), ссылка на внешний источник (может быть несколько, разделенных запятой)
        </div>
        <div class="row">
            <div class="col-lg-6">
                <input type="file" name="imagesPackage">
            </div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-default">Загрузить</button>
            </div>
        </div>
    </form>
</div>