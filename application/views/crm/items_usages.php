<?php
/** @var Model_CRM $crmModel */
$crmModel = Model::factory('CRM');
?>
<div class="row usages-page">
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
                <th>Машина</th>
                <th class="text-center">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?foreach ($usagesList as $usage){?>
                <tr id="usageRow<?=$usage['id'];?>">
                    <td><?=$usage['brand'];?></td>
                    <td><?=$usage['article'];?></td>
                    <td><?=$usage['car'];?></td>
                    <td class="text-center">
                        <button class="btn btn-danger" onclick="deleteItemUsage(<?=$usage['id'];?>);">
                            <span class="fa fa-remove"></span>
                        </button>
                    </td>
                </tr>
            <?}?>
        </table>
    </div>
    <div class="col-lg-12 form-group">
        <ul class="pagination">
            <?for ($i = 1; $i <= ceil($usagesCount / $crmModel->defaultLimit); $i++){?>
            <li <?=($i === $page ? 'class="active"' : null);?>><a href="/crm/items_usages/?brand=<?=$brand;?>&article=<?=$article;?>&page=<?=$i;?>"><?=$i;?></a></li>
            <?}?>
        </ul>
    </div>
</div>
<div class="well col-lg-12">
    <form method="post" enctype='multipart/form-data'>
        <h3>Загрузить с файла</h3>
        <div class="alert alert-info">
            <strong>Формат прайса (csv).</strong> Бренд, артикул, машина (может быть несколько, разделенных запятой)
        </div>
        <div class="row">
            <div class="col-lg-6">
                <input type="file" name="usagesPackage">
            </div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-default">Загрузить</button>
            </div>
        </div>
    </form>
</div>