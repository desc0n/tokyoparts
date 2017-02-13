<!DOCTYPE html>
<html>
<head>
    <title>Счет на оплату</title>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <script type="text/javascript" src="/public/js/jquery.1.11.2.min.js"></script>
    <style>
        td {
            border: 1px solid;
        }
        td.empty {
            border:0;
        }
    </style>
</head>
<div style='width:800px;font-family: Arial;font-size:11pt;'>
    <p>Индивидуальный предприниматель Аксенов Максим Сергеевич </p>
    <p>Приморский край, г. Владивосток, ул. Иртышская, д. 23</p>
    <p></p>
<!--    <p style='font-size:8pt;'>Образец заполнения платежного поручения</p>-->
<!--    <table width=800 cellpadding='5' cellspacing='0' style='border-collapse: collapse;font-size: 11pt;'>-->
<!--        <tr>-->
<!--            <td rowspan=2 colspan=4 width=500 valign=top>-->
<!--                Банк Филиал «Хабаровский» АО «Альфа-Банк»-->
<!--                <br>-->
<!--                <p style='font-size:8pt;'>Банк получателя</p>-->
<!--            </td>-->
<!--            <td valign=top>-->
<!--                БИК-->
<!--            </td>-->
<!--            <td rowspan=2 valign=top>-->
<!--                040813770<br>-->
<!--                <p style='margin-top:10px;'>40802810420050000419</p>-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td valign=top>-->
<!--                Сч. №-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td width=50>-->
<!--                ИНН-->
<!--            </td>-->
<!--            <td width=200>-->
<!--                253715758405-->
<!--            </td>-->
<!--            <td width=50>-->
<!--                КПП-->
<!--            </td>-->
<!--            <td width=200>-->
<!--            </td>-->
<!--            <td valign=top rowspan=2>-->
<!--                Сч. №-->
<!--            </td>-->
<!--            <td valign=top rowspan=2>-->
<!--                30101810800000000770-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td colspan=4>-->
<!--                Индивидуальный предприниматель Аксенов Максим Сергеевич-->
<!--                <p style='font-size:8pt;'>Получатель</p>-->
<!--            </td>-->
<!--        </tr>-->
<!--    </table>-->
    <p style='margin-top:20px;font-weight:bold;font-size:14pt;text-align:center;'>Счет на оплату № <?=$order['id'];?> от <?=date("d.m.Y");?> г.</p><br>
    <p>Поставщик: Индивидуальный предприниматель Аксенов Максим Сергеевич <br>
        Покупатель: <?=$order['second_name'];?> <?=$order['first_name'];?> <?=$order['father_name'];?></p><br>
    <table width=800 cellpadding='5' cellspacing='0' style='border-collapse: collapse;font-size: 11pt;'>
        <tr>
            <td align=center>№</td>
            <td align=center>Товары (работы,услуги)</td>
            <td align=center>Кол-во</td>
            <td align=center>Ед.</td>
            <td align=center>Цена</td>
            <td align=center>Сумма</td>
        </tr>
        <?
        /** @var Model_CRM $crmModel */
        $crmModel = Model::factory('CRM');

        $n=1;
        $total = 0;

        foreach ($orderSpares as $spare) {?>
            <tr>
                <td align=center><?=$n;?></td>
                <td><?=$spare['name'];?> <?=$spare['brand'];?> <?=substr($spare['article'], 0, (strlen($spare['article']) - 3));?>***</td>
                <td align=right><?=$spare['quantity'];?></td>
                <td align=center>шт.</td>
                <td align=right><?=$spare['offer_price'];?>.00</td>
                <td align=right><?=($spare['offer_price'] * $spare['quantity']);?>.00</td>
            </tr>
            <?
            $n++;
            $total += ($spare['offer_price'] * $spare['quantity']);
        }

            if ($order['delivery_price'] > 0) {
            ?>
            <tr>
                <td align=center><?= $n; ?></td>
                <td>Услуги доставки</td>
                <td align=right>1</td>
                <td align=center>услуга</td>
                <td align=right><?=$order['delivery_price'];?>.00</td>
                <td align=right><?=$order['delivery_price'];?>.00</td>
            </tr>
            <?
                $total += $order['delivery_price'];
        }
        ?>
        <tr>
            <td colspan=5 align=right class='empty'>
                Всего к оплате:
            </td>
            <td align=right>
                <?=$total;?>
            </td>
        </tr>
    </table>
    <p>Всего к оплате: <?=($textTotal = $crmModel->numberToString($total));?>
        <?
        if (substr($textTotal,(strlen($textTotal)-2))=='01')
            $text_ruble="рубль";
        else if (substr($textTotal,(strlen($textTotal)-2))=='2' || substr($textTotal,(strlen($textTotal)-2))=='3' || substr($textTotal,(strlen($textTotal)-2))=='4')
            $text_ruble="рубля";
        else
            $text_ruble="рублей";
        ?>
        <?=$text_ruble;?> 00 копеек</p>
    <hr>
    <br>
    <table width=710 cellpadding='5' style='border-collapse: collapse;font-size: 11pt;'>
        <tr>
            <td class='empty' width=50>
                Поставщик:
            </td>
            <td class='empty' style='border-bottom: 1px solid;' align=center>
                Индивидуальный предприниматель
            </td>
            <td class='empty' width=2>
            </td>
            <td class='empty' style='border-bottom: 1px solid;' align=left>
                Аксенов М. С.
            </td>
            <td class='empty' width=2>
            </td>
            <td class='empty' width=190 style='border-bottom: 1px solid;position:relative;' align=center>
<!--                <img class="img" src='/public/img/sign.png' style='position:absolute;z-index:1;width:200px;margin-left: -80px;margin-top: -80px;'>-->
<!--                <img class="img" src='/public/img/stamp.png' style='position:absolute;z-index:2;margin-left: -80px;margin-top: -80px;'>-->
            </td>
        </tr>
        <!--<tr>
            <td class='empty'>
            </td>
            <td class='empty' align=center>
                должность
            </td>
            <td class='empty'>
            </td>
            <td class='empty' align=center>
                подпись
            </td>
            <td class='empty'>
            </td>
            <td class='empty' align=center>
                расшифровка подписи
            </td>
        </tr>-->
    </table>
</div>
<script>
    window.onload = function(){
        window.print();
    };
</script>