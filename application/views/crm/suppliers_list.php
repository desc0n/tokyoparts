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
        <tr>
            <td><?=$supplier['name'];?></td>
            <td class="text-center"><?=$supplier['price_count'];?></td>
            <td class="text-center">
                <form role="form" method="post" enctype='multipart/form-data'>
                    <div class="row">
                        <div class="col-lg-4">
                            <input type="file" name="priceName">
                        </div>
                        <div class="col-lg-4">
                            <button type="submit" class="btn btn-default">Загрузить</button>
                        </div>
                    </div>
                    <input type="hidden" name="supplierId" value="<?=$supplier['id'];?>">
                </form>
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