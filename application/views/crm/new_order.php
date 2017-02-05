<form method="post">
    <div class="row order-data">
        <div class="well col-lg-5">
            <div class="form-horizontal" role="form">
                <div class="form-group">
                    <label for="inputBrand" class="col-lg-3 control-label">Марка</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="brand" id="inputBrand" placeholder="Марка" required data-provide="typeahead" autocomplete="off" value="<?=Arr::get($order, 'brand');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputModel" class="col-lg-3 control-label">Модель</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="model" id="inputModel" placeholder="Модель" required data-provide="typeahead" autocomplete="off" value="<?=Arr::get($order, 'model');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputFrame" class="col-lg-3 control-label">Кузов</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" class="form-control" name="frame" id="inputFrame" placeholder="Кузов" required value="<?=Arr::get($order, 'frame');?>">
                            <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="window.open('http://'+$('#inputBrand').val()+'.epcdata.ru/search_frame/?frame_no='+$('#inputFrame').val(),'_blank');"  title='Посмотреть в каталоге epcdata'>
                            <span class="fa fa-search fa-fw"></span>
                        </button>
                    </span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <h4 class="text-center">Необходимые запчасти</h4>
                    <div class="form-group" id="needParts">
                        <div class="form-group col-lg-12">
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="partsName[]" id="needPartsName1" placeholder="Название" required>
                            </div>
                            <div class="col-lg-3">
                                <input type="text" class="form-control" name="partsQuantity[]" id="needPartsQuantity1" placeholder="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button class="btn btn-success add-need-part" type="button">
                            <span class="fa fa-plus-circle fa-fw"></span>
                        </button>
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
                    <label for="inputLastName" class="col-lg-3 control-label">Фамилия</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="last_name" id="inputLastName" placeholder="Фамилия" value="<?=Arr::get($order, 'second_name');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputFirstName" class="col-lg-3 control-label">Имя</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="first_name" id="inputFirstName" placeholder="Имя" required value="<?=Arr::get($order, 'first_name');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputFatherName" class="col-lg-3 control-label">Отчество</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="father_name" id="inputFatherName" placeholder="Отчество" value="<?=Arr::get($order, 'father_name');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputCity" class="col-lg-3 control-label">Город</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="city" id="inputCity" placeholder="Город" value="<?=Arr::get($order, 'city');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPhone" class="col-lg-3 control-label">Телефон</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="phone" id="inputPhone" placeholder="Телефон" required value="<?=Arr::get($order, 'phone');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPhone2" class="col-lg-3 control-label">Телефон (доп.)</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="phone2" id="inputPhone2" placeholder="Телефон" value="<?=Arr::get($order, 'phone2');?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail" class="col-lg-3 control-label">E-mail</label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="email" id="inputEmail" placeholder="E-mail" value="<?=Arr::get($order, 'email');?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-offset-6 col-lg-6 text-right">
            <button class="btn btn-primary btn-lg" name="newOrder" value="1">Сохранить</button>
        </div>
    </div>
</form>