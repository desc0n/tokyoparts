<div class="row search-items-page">
    <div class="col-lg-12 well form-group">
        <form id="searchOfferForm" class="col-lg-8">
            <div class="row">
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="order_id" id="orderId" value="<?=$orderId;?>" placeholder="№ заказа">
                </div>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="article" value="<?=$article;?>" placeholder="Артикул">
                </div>
                <div class="col-lg-3">
                    <button class="btn btn-primary">Поиск</button>
                </div>
            </div>
        </form>
        <div class="col-lg-4">
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="showOfferPriceFlag">Вывести цену
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-12 form-group">
        <table class="table table-bordered search-spares-table">
            <thead>
            <tr>
                <th>Поставщик</th>
                <th>Бренд</th>
                <th>Артикул</th>
                <th>Название</th>
                <th class="hidden-start-price">Цена закуп.</th>
                <th>Цена прод.</th>
                <th>Наличие</th>
                <th class="text-center">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?foreach ($itemsList as $item){?>
                <tr id="searchItemRow<?=$item['id'];?>">
                    <td><?=$item['supplier_name'];?></td>
                    <td class="item-brand"><?=$item['brand'];?></td>
                    <td class="item-article"><?=$item['article'];?></td>
                    <td><?=$item['name'];?></td>
                    <td class="hidden-start-price"><?=$item['price'];?></td>
                    <td><?=$item['offer_price'];?></td>
                    <td><?=$item['quantity'];?></td>
                    <td class="text-center">
                        <button class="btn btn-default" onclick="addSpareToOrderFromSearch(<?=$item['id'];?>)" title="Добавить в заказ">
                            <span class="fa fa-plus-circle"></span>
                        </button>
                        <button class="btn btn-default" onclick="searchOrderSpare('<?=$orderId;?>', <?=$item['id'];?>);">
                            <span class="fa fa-refresh"></span>
                        </button>
                    </td>
                </tr>
            <?}?>
            <tr id="searchSpareByApiPreview"><td colspan="8"></td></tr>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="setSpareModal" tabindex="-1" role="dialog" aria-labelledby="setSpareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h2 class="modal-title" id="setSpareModalLabel">Редактирование позиций заказа</h2>
            </div>
            <div class="modal-body" id="setSpareModalBody">
                <h3>Заменить на <span id="exchangeItem"></span></h3>
                <table class="table table-bordered set-spares-table">
                    <thead>
                    <tr>
                        <th>Поставщик</th>
                        <th>Название</th>
                        <th>Бренд</th>
                        <th>Артикул</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Кол-во</th>
                        <th class="text-center">Действия</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>