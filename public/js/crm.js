$(document).ready(function () {
    $('.add-need-part').click(function () {
        addNeedPart();
    });
    $('.order-data #inputBrand').typeahead({
        source: function (query, process) {return $.get('/ajax/find_vehicles_brand', {query: query}, function (data) {var json = JSON.parse(data);return process(json);});}
    });
    $('.order-data #inputModel').typeahead({
        source: function (query, process) {return $.get('/ajax/find_vehicles_model', {brand: $('.order-data #inputBrand').val(), query: query}, function (data) {var json = JSON.parse(data);return process(json);});}
    });
    $('.order-data #inputCity').typeahead({
        source: function (query, process) {return $.get('/ajax/find_cities', {query: query}, function (data) {var json = JSON.parse(data);return process(json);});}
    });
    $('.order-data #inputTc').typeahead({
        source: function (query, process) {return $.get('/ajax/find_transport_companies', {query: query}, function (data) {var json = JSON.parse(data);return process(json);});}
    });
    $('#inputLeadTime').datetimepicker({locale: 'ru'});
    $('#firstDate').datetimepicker({locale: 'ru',format: 'DD.MM.YYYY'});
    $('#lastDate').datetimepicker({locale: 'ru',format: 'DD.MM.YYYY'});
    $('.orders-list-table .action-ceil').on('click', function () {document.location='/crm/order/' + $(this).parent('tr').data('order-id');});
    if ($('#searchSpareByApiPreview').length && $('#searchOfferForm input[type=text][name=article]').val() != '') {searchSpareByApi($('#searchOfferForm input[type=text][name=article]').val());}
});
function getOrderId() {return $('#orderId').val();}
function addNeedPart() {
    $('#needParts').append(
        '<div class="form-group col-lg-12">' +
        '<div class="col-lg-9">' +
            '<input type="text" class="form-control" name="partsName[]" id="needPartsName1" placeholder="Название">' +
        '</div>' +
        '<div class="col-lg-3">' +
            '<input type="text" class="form-control" name="partsQuantity[]" id="needPartsQuantity1" placeholder="0">' +
        '</div>' +
        '</div>'
    );
}
function searchOrderSpareOffer(id, article) {$.ajax({url:'/ajax/search_order_spare_offer', type: 'POST', async: true, data: {article:article}}).done(function (data) {writeSearchOrderSpareOfferResult(id, data);});}
function writeSearchOrderSpareOfferResult(id, jsonData) {
    var data = JSON.parse(jsonData);

    $('#searchModalBody .search-spares-table tbody').html('');

    for (i = 0;i < data.length;i++) {
        if(!$('#searchItemRow' + data[i].id).length) {
            $('#searchModalBody .search-spares-table tbody').append(
                '<tr id="searchItemRow' + data[i].id + '">' +
                '<td>' +
                    data[i].supplier_name +
                '</td>' +
                '<td>' +
                    data[i].brand +
                '</td>' +
                '<td>' +
                    data[i].article +
                '</td>' +
                '<td>' +
                    data[i].name +
                '</td>' +
                '<td>' +
                    data[i].price +
                '</td>' +
                '<td>' +
                    data[i].offer_price +
                '</td>' +
                '<td>' +
                    data[i].quantity +
                '</td>' +
                '<td class="text-center">' +
                    '<button class="btn btn-default" onclick="setOrderSpareBySearch(' + data[i].id + ', ' + id + ');"><span class="fa fa-check-circle"></span></button>' +
                '</td>' +
                '</tr>'
            );
        }
    }

    $('#searchModal').modal('toggle');
}
function setOrderSpareBySearch(itemId, id) {$.ajax({url:'/ajax/set_order_spare_by_search', type: 'POST', async: true, data: {id:id, itemId:itemId}}).done(function () {location.reload();});}
function searchOrderByNumber() {$.ajax({url:'/ajax/search_order_by_number', type: 'POST', async: true, data: {id:$('#searchOrder').val()}}).done(function (response) {if(response == 0) {alert('Заказ не найден!');}else{document.location='/crm/order/' + response;}});}
function addSpareToOrderFromSearch(itemId) {if($('#searchOfferForm #orderId').val() == '') {alert('Не указан заказ!'); return false;}$.ajax({url:'/ajax/add_spare_to_order_from_search', type: 'POST', async: true, data: {orderId:$('#searchOfferForm #orderId').val(), itemId: itemId}}).done(function (result) {if(result.indexOf('success') != -1){alert('Товар удачно добавлен в заказ!');}else{alert('Ошибка добавления товара в заказ!');}});}
function searchOrderSpare(orderId, itemId) {if(orderId == '') {alert('Не указан заказ!'); return false;}$.ajax({url:'/ajax/search_order_spare', type: 'POST', async: true, data: {orderId:orderId}}).done(function (data) {writeOrderSpare(itemId, data);});}
function writeExchangeItem(itemId) {$('#exchangeItem').html($('#searchItemRow' + itemId + ' .item-brand').text() + ' / ' + $('#searchItemRow' + itemId + ' .item-article').text());}
function writeOrderSpare(itemId, jsonData) {
    writeExchangeItem(itemId);
    var data = JSON.parse(jsonData);

    $('#setSpareModalBody .set-spares-table tbody').html('');

    for (i = 0;i < data.length;i++) {
        $('#setSpareModalBody .set-spares-table tbody').append(
            '<tr>' +
            '<td>' +
            data[i].supplier_name +
            '</td>' +
            '<td>' +
            data[i].name +
            '</td>' +
            '<td>' +
            data[i].brand +
            '</td>' +
            '<td>' +
            data[i].article +
            '</td>' +
            '<td>' +
            data[i].name +
            '</td>' +
            '<td>' +
            data[i].offer_price +
            '</td>' +
            '<td>' +
            data[i].quantity +
            '</td>' +
            '<td class="text-center">' +
            '<button class="btn btn-default" onclick="setOrderSpareBySearch(' + itemId + ', ' + data[i].id + ');"><span class="fa fa-exchange"></span></button>' +
            '</td>' +
            '</tr>'
        );
    }

    $('#setSpareModal').modal('toggle');
}
function removeSpare(spare) {var den = confirm('Подтверждает удаление позиции заказа?');if (den){$.ajax({url:'/ajax/remove_spare', type: 'POST', async: true, data: {spareId:spare}}).done(function () {$('.spare-row[data-id=' + spare +']').remove();});}}
function searchSpareByApi(article) {
    $('#searchSpareByApiPreview td').append('<div class="progress progress-striped active">' +
        '<div class="progress-bar"  role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>' +
        '</div>');
    $('#searchSpareByApiPreview td').find('.progress-bar').animate({width:'100%'}, 1000);
    $.ajax({url:'/ajax/search_spare_by_api', type: 'POST', async: true, data:{article:article}}).done(function (data) {
        writeSearchSpareByApiOfferResult(data);
    });
}
function writeSearchSpareByApiOfferResult(jsonData) {
    var data = JSON.parse(jsonData);

    $('#searchSpareByApiPreview').remove();

    for (i = 0;i < data.length;i++) {
        if(!$('#searchItemRow' + data[i].id).length) {
            $('.search-spares-table tbody').append(
                '<tr id="searchItem' + data[i].id + '">' +
                '<td>' +
                data[i].supplier_name +
                '</td>' +
                '<td>' +
                data[i].brand +
                '</td>' +
                '<td>' +
                data[i].article +
                '</td>' +
                '<td>' +
                data[i].name +
                '</td>' +
                '<td>' +
                data[i].price +
                '</td>' +
                '<td>' +
                data[i].offer_price +
                '</td>' +
                '<td>' +
                data[i].quantity +
                '</td>' +
                '<td class="text-center">' +
                '<button class="btn btn-default" onclick="addSpareToOrderFromSearch(' + data[i].id + ')" title="Добавить в заказ">' +
                '<span class="fa fa-plus-circle"></span>' +
                '</button>' +
                '<button class="btn btn-default" onclick="searchOrderSpare(\'' + getOrderId() +'\', ' + data[i].id + ');">' +
                '<span class="fa fa-refresh"></span>' +
                '</button>' +
                '</td>' +
                '</td>' +
                '</tr>'
            );
        }
    }

    $('#searchModal').modal('toggle');
}
function clearSuppliersItems(supplierId) {$.ajax({url:'/ajax/clear_suppliers_items', type: 'POST', async: true, data:{supplierId:supplierId}}).done(function () {location.reload();});}
function addSupplierMarkupRange(supplierId) {$.ajax({url:'/ajax/add_supplier_markup_range', type: 'POST', async: true, data:{supplierId:supplierId}}).done(function () {location.reload();});}