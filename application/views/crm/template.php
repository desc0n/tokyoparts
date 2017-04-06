<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <!-- Bootstrap -->
    <link href="/public/css/bootstrap.css" rel="stylesheet">
    <link href="/public/css/bootstrap-datetimepicker.css" rel="stylesheet">
    <link href="/public/css/font-awesome.css" rel="stylesheet">
    <link href="/public/css/crm.css?v=5" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div id="wrapper">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <!-- /.navbar-header -->
        <h1>
            Панель управления
            <div class="pull-right">
                <form method="post" action="/crm/logout">
                    <button class="btn btn-default" name="logout"><i class="fa fa-sign-out fa-fw"></i></button>
                </form>
            </div>
        </h1>
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li>
                        <div class="input-group search-order-field">
                            <input type="text" class="form-control" id="searchOrder" placeholder="№ заказа">
                            <span class="input-group-btn">
                  <button class="btn btn-default" type="button" onclick="searchOrderByNumber();"  title='Пооиск по номеру заказа'>
                      <span class="fa fa-search fa-fw"></span>
                  </button>
              </span>
                        </div>
                    </li>
                    <li><a href="/crm/new_order"><i class="fa fa-plus-circle fa-fw"></i> Создать заказ</a></li>
                    <li><a href="/crm/orders_list"><i class="fa fa-list fa-fw"></i> Список заказов</a></li>
                    <li><a href="/crm/suppliers_list"><i class="fa fa-truck fa-fw"></i> Список поставщиков</a></li>
                    <li><a href="/crm/search_orders"><i class="fa fa-search fa-fw"></i> Поиск заказов</a></li>
                    <li><a href="/crm/search_items"><i class="fa fa-wrench fa-fw"></i> Поиск запчастей</a></li>
                    <li><a href="/crm/crosses"><i class="fa fa-exchange fa-fw"></i> Работа с кроссами</a></li>
                    <li><a href="/crm/items_images"><i class="fa fa-image fa-fw"></i> Изображения товара</a></li>
                    <li><a href="/crm/items_usages"><i class="fa fa-car fa-fw"></i> Применимость товара</a></li>
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    </nav>
    <div id="page-wrapper">
        <?=$content;?>
    </div>
    <!-- /#page-wrapper -->
</div>
<!-- /#wrapper -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="errorModalLabel">Ошибка</h4>
            </div>
            <div class="modal-body" id="errorModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/public/js/jquery.1.11.2.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/public/js/bootstrap.js"></script>
<script src="/public/js/moment-with-locales.js"></script>
<script src="/public/js/bootstrap-datetimepicker.js"></script>
<script src="/public/js/bootstrap3-typeahead.js"></script>
<script src="/public/js/crm.js?v=12"></script>
</body>
</html>
