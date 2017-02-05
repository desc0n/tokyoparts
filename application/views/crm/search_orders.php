<div class="row search-orders-page">
    <div class="col-lg-4 well">
        <form>
            <div class="form-group">
                <label for="inputCar" class="control-label">Машина (марка, модель или кузов)</label>
                <div>
                    <input type="text" class="form-control" name="car" id="inputCar" placeholder="Машина (марка, модель или кузов)" value="<?=Arr::get($query, 'car');?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputCustomer" class="control-label">Клиент (фамилия, имя или отчество)</label>
                <div>
                    <input type="text" class="form-control" name="customer" id="inputCustomer" placeholder="Клиент (фамилия, имя или отчество)" value="<?=Arr::get($query, 'customer');?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputPhone" class="control-label">Телефон</label>
                <div>
                    <input type="text" class="form-control" name="phone" id="inputPhone" placeholder="Телефон" value="<?=Arr::get($query, 'phone');?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputEmail" class="control-label">Email</label>
                <div>
                    <input type="text" class="form-control" name="email" id="inputEmail" placeholder="Email" value="<?=Arr::get($query, 'email');?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputArticle" class="control-label">Артикул</label>
                <div>
                    <input type="text" class="form-control" name="article" id="inputArticle" placeholder="Артикул" value="<?=Arr::get($query, 'article');?>">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-block">Искать</button>
            </div>
        </form>
    </div>
    <div class="col-lg-8">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>№ заказа</th>
                <th>Дата оформления</th>
                <th>Машина</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?foreach ($ordersList as $order) {?>
                <tr>
                    <td><?=$order['id'];?></td>
                    <td><?=date('H:i d.m.Y', strtotime($order['created_at']));?></td>
                    <td><?=$order['brand'];?> <?=$order['model'];?> <?=$order['frame'];?></td>
                    <td class="text-center">
                        <a class="btn btn-default" href="/crm/order/<?=$order['id'];?>"><i class="fa fa-file fa-fw"></i></a>
                    </td>
                </tr>
            <?}?>
            </tbody>
        </table>
    </div>
</div>