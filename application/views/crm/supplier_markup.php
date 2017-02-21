<h2 class="text-center">Наценки поставщика <?=mb_strtoupper(Arr::get($supplier, 'name'));?></h2>
<form method="post" class="form-horizontal markup-form" role="form">
    <div class="well col-lg-12 form-group">
        <div class="col-lg-5 text-left">
            <label for="inputMarkup" class="col-lg-8 control-label">Наценка на поставщика (%)</label>
            <div class="col-lg-4">
                <input type="text" class="form-control" id="inputMarkup" name="markup" value="<?=Arr::get($supplierMarkup, 'markup', 0);?>">
            </div>
        </div>
    </div>
    <div class="well col-lg-12 form-group">
        <h4 class="text-center">Диапазоны наценок</h4>
        <div class="row form-group">
            <div class="col-lg-5 text-center">
                <strong>от</strong>
            </div>
            <div class="col-lg-5 text-center">
                <strong>до</strong>
            </div>
            <div class="col-lg-2 text-center">
                <strong>наценка</strong>
            </div>
        </div>
        <?foreach ($supplierMarkupRanges as $markupRange) {?>
        <div class="row form-group">
            <div class="col-lg-5">
                <input type="text" class="form-control" name="markup_range_first[]" value="<?=$markupRange['range_first'];?>">
            </div>
            <div class="col-lg-5">
                <input type="text" class="form-control" name="markup_range_last[]" value="<?=$markupRange['range_last'];?>">
            </div>
            <div class="col-lg-2">
                <input type="text" class="form-control" name="markup_range_value[]" value="<?=$markupRange['value'];?>">
            </div>
            <input type="hidden" name="markup_range_id[]" value="<?=$markupRange['id'];?>">
        </div>
        <?}?>
        <div class="row form-group">
            <div class="col-lg-4">
                <button class="btn btn-success" type="button" onclick="addSupplierMarkupRange(<?=Arr::get($supplier, 'id');?>);"><span class="fa fa-plus-circle fa-fw"></span> Добавить диапазон</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 text-left">
            <button class="btn btn-primary btn-lg" name="redactMarkup" value="1">Сохранить</button>
        </div>
    </div>
</form>