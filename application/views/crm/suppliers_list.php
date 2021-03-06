<?php
/** @var Model_Price $priceModel */
$priceModel = Model::factory('Price');
?>
<div class="form-group">
    <div class="alert alert-info">
        <strong>Формат прайса.</strong> Бренд, артикул, название, цена, количество
    </div>
<table class="table table-bordered suppliers-list-table">
    <thead>
        <tr>
            <th class="text-center">Название</th>
            <th class="text-center">Кол-во позиций</th>
            <th class="text-center">Загрузить прайс</th>
        </tr>
    </thead>
    <tbody>
        <?foreach ($suppliersList as $supplier) {?>
        <tr id="supplierRow<?=$supplier['id'];?>">
            <td><?=$supplier['name'];?></td>
            <td class="text-center"><?=$supplier['price_count'];?></td>
            <td class="text-center row">
                <div class="col-lg-5">
                    <form role="form" method="post" enctype='multipart/form-data'>
                        <div class="row">
                            <div class="col-lg-6">
                                <input type="file" name="priceName">
                            </div>
                            <div class="col-lg-6">
                                <button type="submit" class="btn btn-default btn-sm">Загрузить</button>
                            </div>
                        </div>
                        <input type="hidden" name="supplierId" value="<?=$supplier['id'];?>">
                    </form>
                </div>
                <div class="col-lg-3">
                    <button class="btn btn-danger btn-sm" onclick="clearSuppliersItems(<?=$supplier['id'];?>);">Очистить прайс</button>
                </div>
                <div class="col-lg-2">
                    <a class="btn btn-primary btn-sm" href="/crm/supplier_markup/<?=$supplier['id'];?>">Наценка</a>
                </div>
                <div class="col-lg-2">
                    <button class="btn btn-success btn-sm autoload-btn" <?=(!empty(Arr::get($priceModel->getParsingSettings(), $supplier['alias'], [])) ? null : 'disabled');?> onclick="insertItemsTmpForAutoUpdate(<?=$supplier['id'];?>);">Обновить</button>
                </div>
            </td>
        </tr>
        <?}?>
    </tbody>
</table>
</div>
<form method="post">
    <h3>Добавить поставщика</h3>
    <div class="input-group col-lg-6">
        <input type="text" class="form-control" name="name" placeholder="Название поставщика" required>
        <span class="input-group-btn">
            <button class="btn btn-default" name="addSupplier" value="1">
                <span class="fa fa-check-circle fa-fw"></span> Сохранить
            </button>
        </span>
    </div>
</form>
<div class="form-group">
    <h3>Выгрузить на фарпост</h3>
    <div class="form-group col-lg-12">
        <button class="btn btn-success" id="exportPriceToFarpostBtn">
            <span class="fa fa-upload fa-fw"></span> Выгрузить
        </button>
    </div>
    <div class="form-group col-lg-12">
        <div class="alert alert-success">
            <strong>Прайс доступен по ссылке.</strong> <a href="/public/ftp/farpost/price.csv" download>http://<?=$_SERVER['HTTP_HOST'];?>/public/ftp/farpost/price.csv</a>
        </div>
    </div>
</div>