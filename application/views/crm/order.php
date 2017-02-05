<?php
$amount = (int)$order['delivery_price'];
?>
<form method="post" class="form-group">
    <div class="row order-data">
        <div class="well col-lg-5">
            <a class="btn btn-default copy-order-btn" href="/crm/new_order/?copy=<?=$order['id'];?>" title="Копировать заказ">
                <span class="fa fa-copy fa-fw"></span>
            </a>
            <a class="btn btn-default invoice-btn" target="_blank" href="/crm/invoice/<?=$order['id'];?>" title="Создать счет">
                <span class="fa fa-file fa-fw"></span>
            </a>
            <div class="form-horizontal" role="form">
                <h3 class="text-center">Заказ № <?=$order['id'];?></h3>
                <div class="form-group">
                    <div class="col-lg-12 text-center">
                        <strong>Принят <?=date('d.m.Y H:s', strtotime($order['created_at']));?></strong>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputLeadTime" class="col-lg-3 control-label">Время вып.</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="lead_time" id="inputLeadTime" placeholder="Время выполнения" required value="<?=(!empty($order['lead_time']) ? date('H:i d.m.Y', strtotime($order['lead_time'])) : null);?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputOrderStatus" class="col-lg-3 control-label">Статус</label>
                    <div class="col-lg-9">
                        <?=Form::select('status_id', $orderStatusesList, $order['status_id'], ['class' => 'form-control', 'id' => 'inputOrderStatus']);?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPaymentStatus" class="col-lg-3 control-label">Оплата</label>
                    <div class="col-lg-9">
                        <?=Form::select('payment_status_id', $paymentStatusesList, $order['payment_status_id'], ['class' => 'form-control', 'id' => 'inputPaymentStatus']);?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputBrand" class="col-lg-3 control-label">Марка</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="brand" id="inputBrand" placeholder="Марка" required value="<?=$order['brand'];?>" data-provide="typeahead" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputModel" class="col-lg-3 control-label">Модель</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="model" id="inputModel" placeholder="Модель" required value="<?=$order['model'];?>" data-provide="typeahead" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputFrame" class="col-lg-3 control-label">Кузов</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" class="form-control" name="frame" id="inputFrame" placeholder="Кузов" required value="<?=$order['frame'];?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" onclick="window.open('http://'+$('#inputBrand').val()+'.epcdata.ru/search_frame/?frame_no='+$('#inputFrame').val(),'_blank');"  title='Посмотреть в каталоге epcdata'>
                                    <span class="fa fa-search fa-fw"></span>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputComment" class="col-lg-3 control-label">Комментарий</label>
                    <div class="col-lg-9">
                        <textarea class="form-control" name="comment" id="inputComment" placeholder="Комментарий" rows="3"><?=$order['comment'];?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-1">
        </div>
        <div class="well col-lg-6">
            <h4 class="text-center">Информация о клиенте</h4>
            <div class="form-horizontal" role="form">
                <div class="form-group">
                    <label for="inputSecondName" class="col-lg-3 control-label">Фамилия</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="second_name" id="inputSecondName" placeholder="Фамилия" value="<?=$order['second_name'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputFirstName" class="col-lg-3 control-label">Имя</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="first_name" id="inputFirstName" placeholder="Имя" required value="<?=$order['first_name'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputFatherName" class="col-lg-3 control-label">Отчество</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="father_name" id="inputFatherName" placeholder="Отчество" value="<?=$order['father_name'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputCity" class="col-lg-3 control-label">Город</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="city" id="inputCity" placeholder="Город" value="<?=$order['city'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPhone" class="col-lg-3 control-label">Телефон</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="phone" id="inputPhone" placeholder="Телефон" required value="<?=$order['phone'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPhone2" class="col-lg-3 control-label">Телефон (доп.)</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="phone2" id="inputPhone2" placeholder="Телефон" value="<?=$order['phone2'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail" class="col-lg-3 control-label">E-mail</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="email" id="inputEmail" placeholder="E-mail" value="<?=$order['email'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputTc" class="col-lg-3 control-label">ТК</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="tc" id="inputTc" placeholder="Транспортная компания" value="<?=$order['tc'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputTtn" class="col-lg-3 control-label">ТТН</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="ttn" id="inputTtn" placeholder="Товарно-транспортная накладная" value="<?=$order['ttn'];?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputDeliveryPrice" class="col-lg-3 control-label">Доставка (руб.)</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="delivery_price" id="inputDeliveryPrice" placeholder="Стоимость доставки" value="<?=$order['delivery_price'];?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 text-left">
            <button class="btn btn-primary btn-lg" name="redactOrder" value="1">Сохранить</button>
        </div>
    </div>
</form>
<div class="row">
    <div class="well col-lg-12">
        <h2 class="col-lg-12 form-group">
            Список запчастей
        </h2>
        <div class="col-lg-12 form-group">
            <form method="post">
                <button class="btn btn-default pull-left" title="Добавить строку" name="addSpare" value="1">
                    <span class="fa fa-plus fa-fw"></span>
                </button>
            </form>
        </div>
        <form method="post">
            <table class="table table-bordered order-spares-table">
                <tbody>
                <?foreach ($orderSpares as $spare) {?>
                    <tr class="table-head spare-row" data-id="<?=$spare['id'];?>">
                        <th colspan="4" class="text-center">
                            <span class="pull-left fa fa-remove fa-fw remove-spare" onclick="removeSpare(<?=$spare['id'];?>);" title="Удалить позицию заказа"></span>
                            Название
                        </th>
                        <th colspan="1" class="text-center">Кол-во</th>
                    </tr>
                    <tr id="spareRow1_<?=$spare['id'];?>" class="spare-row" data-id="<?=$spare['id'];?>">
                        <td colspan="4">
                            <input type="text" value="<?=$spare['name'];?>" class="form-control" name="name[]">
                            <input type="hidden" name="spare_id[]" value="<?=$spare['id'];?>">
                        </td>
                        <td colspan="1">
                            <input type="text" value="<?=$spare['quantity'];?>" class="form-control" name="quantity[]">
                        </td>
                    </tr>
                    <tr class="spare-row" data-id="<?=$spare['id'];?>">
                        <th class="text-center">Поставщик</th>
                        <th class="text-center">Бренд</th>
                        <th class="text-center">Артикул</th>
                        <th class="text-center">Цена закуп.</th>
                        <th class="text-center">Цена прод.</th>
                    </tr>
                    <tr id="spareRow2_<?=$spare['id'];?>" class="spare-row" data-id="<?=$spare['id'];?>">
                        <td>
                            <?=$spare['supplier_name'];?>
                            <input type="hidden" name="supplier_id[]" value="<?=$spare['supplier_id'];?>">
                        </td>
                        <td>
                            <input type="text" value="<?=$spare['brand'];?>" class="form-control" name="brand[]">
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="text" value="<?=$spare['article'];?>" class="form-control spare-article" name="article[]">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="searchOrderSpareOffer(<?=$spare['id'];?>, $('#spareRow2_<?=$spare['id'];?> .spare-article').val());">
                                        <span class="fa fa-search fa-fw"></span>
                                    </button>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?=$spare['start_price'];?>
                            <input type="hidden" name="start_price[]" value="<?=$spare['start_price'];?>">
                        </td>
                        <td>
                            <input type="text" value="<?=$spare['offer_price'];?>" name="offer_price[]" class="form-control">
                        </td>
                    </tr>
                    <?$amount += ($spare['offer_price'] * $spare['quantity']);?>
                <?}?>
                </tbody>
            </table>
            <div class="col-lg-12 text-right">
                <h3>Итого: <?=$amount;?> руб.</h3>
            </div>
            <div class="col-lg-12 text-left">
                <button class="btn btn-primary btn-lg" name="redactOrderSpares" value="1">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="searchModalLabel">Поиск</h4>
            </div>
            <div class="modal-body" id="searchModalBody">
                <table class="table table-bordered search-spares-table">
                    <thead>
                    <tr>
                        <th>Поставщик</th>
                        <th>Бренд</th>
                        <th>Артикул</th>
                        <th>Цена</th>
                        <th>Наличие</th>
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