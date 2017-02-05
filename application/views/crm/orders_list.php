<?php
/** @var Model_CRM $crmModel */
$crmModel = Model::factory('CRM');
?>
<form>
    <div class="row">
        <div class="col-lg-2">
            <input type="text" class="form-control" name="first_date" id="firstDate" value="<?=$first_date;?>">
        </div>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="last_date" id="lastDate" value="<?=$last_date;?>">
        </div>
        <div class="col-lg-3">
            <button class="btn btn-primary">Фильтровать</button>
        </div>
    </div>
</form>
<table class="table table-bordered table-hover orders-list-table">
    <thead>
        <tr>
            <th>№ заказа</th>
            <th>Дата оформления</th>
            <th>Статус</th>
            <th>Машина</th>
            <th>Принял</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?foreach ($ordersList as $order) {?>
        <tr data-order-id="<?=$order['id'];?>">
            <td class="action-ceil"><?=$order['id'];?></td>
            <td class="action-ceil"><?=date('H:i d.m.Y', strtotime($order['created_at']));?></td>
            <td class="action-ceil text-center alert <?=Arr::get($crmModel->orderSatusesColor, $order['status_id'], '');?>"><?=$order['status_name'];?></td>
            <td class="action-ceil"><?=$order['brand'];?> <?=$order['model'];?> <?=$order['frame'];?></td>
            <td class="action-ceil"><?=$order['username'];?></td>
            <td class="text-center">
                <a class="btn btn-default" href="/crm/order/<?=$order['id'];?>"><i class="fa fa-file fa-fw"></i></a>
            </td>
        </tr>
        <?}?>
    </tbody>
</table>