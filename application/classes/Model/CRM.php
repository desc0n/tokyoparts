<?php

/**
 * Class Model_CRM
 */
class Model_CRM extends Kohana_Model
{
    public $orderSatusesColor = [
            1 => 'alert-danger',
            2 => 'alert-warning',
            3 => 'alert-success',
            4 => 'alert-info'
        ];

	private $user_id;

	public function __construct()
	{
        $this->user_id = Auth::instance()->logged_in() ? Auth::instance()->get_user()->id : null;

		DB::query(Database::UPDATE, "SET time_zone = '+10:00'")->execute();
	}

	public function addOrder($postQuery)
    {
        $partsName = Arr::get($postQuery, 'partsName', []);
        $partsQuantity = Arr::get($postQuery, 'partsQuantity', []);

        $order = DB::insert('orders__orders', ['user_id', 'created_at'])
            ->values([$this->user_id, DB::expr('NOW()')])
            ->execute()
        ;

        $orderId = $order[0];


        DB::insert('orders__statuses', ['order_id', 'status_id', 'payment_status_id', 'updated_at'])
            ->values([$orderId, 1, 1, DB::expr('NOW()')])
            ->execute()
        ;

        DB::insert('orders__vehicles', ['order_id', 'brand', 'model', 'frame'])
            ->values([
                $orderId,
                Arr::get($postQuery, 'brand', ''),
                Arr::get($postQuery, 'model', ''),
                Arr::get($postQuery, 'frame', '')
            ])
            ->execute()
        ;

        DB::insert('orders__customers', ['order_id', 'first_name', 'second_name', 'father_name', 'city', 'phone', 'phone2', 'email'])
            ->values([
                $orderId,
                Arr::get($postQuery, 'first_name', ''),
                Arr::get($postQuery, 'second_name', ''),
                Arr::get($postQuery, 'father_name', ''),
                Arr::get($postQuery, 'city', ''),
                Arr::get($postQuery, 'phone', ''),
                Arr::get($postQuery, 'phone2', ''),
                Arr::get($postQuery, 'email', '')
            ])
            ->execute()
        ;

        DB::insert('orders__deliveries', ['order_id'])
            ->values([
                $orderId
            ])
            ->execute()
        ;

        foreach ($partsName as $key => $name) {
            $this->addSpare($orderId, null, $name, $partsQuantity[$key], '', '', '', 0, 0);
        }

        return $orderId;
    }

    /**
     * @param int $orderId
     */
    public function addEmptySpare($orderId)
    {
        $this->addSpare($orderId, null, '', 1, '', '', '', 0, 0);
    }

    /**
     * @param DateTime $firstDate
     * @param DateTime $lastDate
     *
     * @return array
     */
    public function getOrdersList(DateTime $firstDate, DateTime $lastDate)
    {
        $orders = DB::select()
            ->from('orders__orders')
            ->where('created_at', 'BETWEEN', [$firstDate->format('Y-m-d 00:00:00'), $lastDate->modify('+ 1 day')->format('Y-m-d 00:00:00')])
            ->execute()
            ->as_array()
        ;

        $ordersList = [];

        foreach ($orders as $order) {
            $ordersList[] = $this->findOrderById($order['id']);
        }

        return $ordersList;
    }

    /**
     * @param int $orderId
     *
     * @return mixed
     */
    public function findOrderById($orderId)
    {
        return DB::select(
                'oo.id',
                'oo.lead_time',
                'oo.comment',
                'oo.created_at',
                'oc.first_name',
                'oc.second_name',
                'oc.father_name',
                'oc.city',
                'oc.phone',
                'oc.phone2',
                'oc.email',
                'od.tc',
                'od.ttn',
                ['od.price', 'delivery_price'],
                'ov.brand',
                'ov.model',
                'ov.frame',
                'os.status_id',
                'os.payment_status_id',
                ['sos.name', 'status_name'],
                'u.username'
            )
            ->from(['orders__orders', 'oo'])
            ->join(['orders__customers', 'oc'])
                ->on('oc.order_id', '=', 'oo.id')
            ->join(['orders__deliveries', 'od'])
                ->on('od.order_id', '=', 'oo.id')
            ->join(['orders__vehicles', 'ov'])
                ->on('ov.order_id', '=', 'oo.id')
            ->join(['orders__statuses', 'os'])
                ->on('os.order_id', '=', 'oo.id')
            ->join(['statuses__order_statuses', 'sos'], 'left')
                ->on('sos.id', '=', 'os.status_id')
            ->join(['users', 'u'], 'left')
                ->on('u.id', '=', 'oo.user_id')
            ->where('oo.id', '=', $orderId)
            ->limit(1)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param int $orderId
     *
     * @return array
     */
    public function findOrderSpares($orderId)
    {
        return DB::select('os.*', ['ss.name', 'supplier_name'])
            ->from(['orders__spares', 'os'])
            ->join(['suppliers__suppliers', 'ss'], 'left')
                ->on('ss.id', '=', 'os.supplier_id')
            ->where('os.order_id', '=', $orderId)
            ->execute()
            ->as_array()
        ;
    }

    /**
     * @param array $params
     * @param int $orderId
     */
    public function setOrder(array $params, $orderId)
    {
        DB::update('orders__orders')
            ->set([
                'lead_time' => date('Y-m-d H:i:s', strtotime(Arr::get($params, 'lead_time', ''))),
                'comment' => Arr::get($params, 'comment', '')
            ])
            ->where('id', '=', $orderId)
            ->execute()
        ;

        DB::update('orders__vehicles')
            ->set([
                'brand' => Arr::get($params, 'brand', ''),
                'model' => Arr::get($params, 'model', ''),
                'frame' => Arr::get($params, 'frame', '')
            ])
            ->where('order_id', '=', $orderId)
            ->execute()
        ;

        DB::update('orders__statuses')
            ->set([
                'status_id' => Arr::get($params, 'status_id', 1),
                'payment_status_id' => Arr::get($params, 'payment_status_id', 1),
                'updated_at' => DB::expr('NOW()')
            ])
            ->where('order_id', '=', $orderId)
            ->execute()
        ;

        DB::update('orders__customers')
            ->set([
                'first_name' => Arr::get($params, 'first_name', ''),
                'second_name' => Arr::get($params, 'second_name', ''),
                'father_name' => Arr::get($params, 'father_name', ''),
                'city' => Arr::get($params, 'city', ''),
                'phone' => Arr::get($params, 'phone', ''),
                'phone2' => Arr::get($params, 'phone2', ''),
                'email' => Arr::get($params, 'email', '')
            ])
            ->where('order_id', '=', $orderId)
            ->execute()
        ;

        DB::update('orders__deliveries')
            ->set([
                'tc' => Arr::get($params, 'tc', ''),
                'ttn' => Arr::get($params, 'ttn', ''),
                'price' => Arr::get($params, 'delivery_price', 0)
            ])
            ->where('order_id', '=', $orderId)
            ->execute()
        ;
    }

    /**
     * @return array
     */
    public function getSuppliersList()
    {
        return DB::select('ss.*', [DB::select(DB::expr('COUNT(si.id)'))->from(['suppliers__items', 'si'])->where('si.supplier_id', '=', DB::expr('ss.id')), 'price_count'])
            ->from(['suppliers__suppliers', 'ss'])
            ->execute()
            ->as_array()
        ;
    }

    /**
     * @param $supplierId
     * @return false|array
     */
    public function findSupplierById($supplierId)
    {
        return DB::select()
            ->from('suppliers__suppliers')
            ->where('id', '=', $supplierId)
            ->limit(1)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param $name
     */
    public function addSupplier($name)
    {
        DB::insert('suppliers__suppliers', ['name'])
            ->values([$name])
            ->execute()
        ;
    }

    public function loadSupplierPrice(array $fileData, $supplierId)
    {
        $data = file($fileData['priceName']['tmp_name']);
        $updateTask = $this->generateUpdateTask();

        foreach ($data as $row) {
            $ceils = explode(';', $row);

            $validData = $this->validateLoadPrice((int)$supplierId, Arr::get($ceils, 0), Arr::get($ceils, 1), Arr::get($ceils, 2), Arr::get($ceils, 3), Arr::get($ceils, 4));

            array_push($validData, null);
            array_push($validData, $updateTask);

            if (!empty($validData)) {
                $this->setSupplierItem($validData);
            }
        }

        DB::update('suppliers__items')
            ->set([
                'quantity' => 0
            ])
            ->where('supplier_id', '=', $supplierId)
            ->and_where('update_task', '!=', $updateTask)
            ->execute();
    }

    private function setSupplierItem($validData)
    {
        if (count($validData) === 10) {
            if(!empty($validData[8])) {
                $supplierItem = $this->findSupplierItemBySupplierAndVendorId($validData[0], $validData[8]);
            } else {
                $supplierItem = $this->findSupplierItemBySupplierAndHash($validData[0], $validData[7]);
            }

            if (empty($supplierItem)) {
                DB::insert('suppliers__items', ['supplier_id', 'brand', 'article', 'article_search', 'name', 'price', 'quantity', 'item_hash', 'vendor_id', 'update_task'])
                    ->values($validData)
                    ->execute();
            } else {
                DB::update('suppliers__items')
                    ->set([
                        'price' => $validData[5],
                        'quantity' => $validData[6],
                        'update_task' => $validData[9],
                    ])
                    ->where('id', '=', $supplierItem['id'])
                    ->execute();
            }
        }
    }

    /**
     * @param int $supplierId
     * @param string $brand
     * @param string $article
     * @param string $name
     * @param string $price
     * @param string $quantity
     *
     * @return array
     */
    public function validateLoadPrice($supplierId, $brand, $article, $name, $price, $quantity)
    {
        if(empty($supplierId) || empty($brand) || empty($article) || empty($name) || empty($price) || empty($quantity)) {
            return [];
        }

        $price = $this->validatePrice($price);
        $quantity = $this->validateQuantity($quantity);

        return [$supplierId, $brand, $article, $this->getSearchArticle($article), $name, $price, $quantity, md5($brand . $article)];
    }

    public function validateQuantity($value)
    {
        $replaceVariant = [
            'меньше 10' => 5,
            'больше 10' => 15,
            '10-100' => 20,
        ];

        $value = Arr::get($replaceVariant, $value, $value);
        $value = preg_replace( '/[^[:print:]]/', '',$value);

        return preg_replace('/[\D]+/', '', $value);
    }

    public function validatePrice($value)
    {
        return preg_replace( '/[^[:print:]]/', '',$value);
    }

    public function getSearchArticle($article)
    {
        return preg_replace('/[^\w\d]+/i', '', $article);
    }

    /**
     * @param int $supplierId
     * @return string
     */
    public function clearSuppliersItems($supplierId)
    {
        DB::delete('suppliers__items')->where('supplier_id', '=', $supplierId)->execute();

        return 'success';
    }

    /**
     * @return array
     */
    public function getOrderStatusesList()
    {
        return DB::select()
            ->from('statuses__order_statuses')
            ->execute()
            ->as_array('id', 'name')
        ;
    }

    /**
     * @return array
     */
    public function getPaymentStatusesList()
    {
        return DB::select()
            ->from('statuses__payment_statuses')
            ->execute()
            ->as_array('id', 'name')
        ;
    }

    /**
     * @param string $article
     *
     * @return array
     */
    public function searchOrderSpareOffer($article)
    {
        $searchResult = [];
        $spares = [];
        $article = $this->getSearchArticle($article);

        if (empty($article)) {
            return [];
        }

        $items = DB::select('si.*', ['ss.name', 'supplier_name'])
            ->from(['suppliers__items', 'si'])
            ->join(['suppliers__suppliers', 'ss'])
            ->on('ss.id', '=', 'si.supplier_id')
            ->where('si.quantity', '!=', 0)
            ->and_where('si.article_search', '=', $article)
            ->execute()
            ->as_array()
        ;

        foreach ($items as $item) {
            $spares[md5($item['supplier_id'].$item['brand'].$item['article'])] = $item;
        }

        $this->searchSpareByApi($article);
        $oemCrosses = $this->findOemCrosses($article);
        $crosses = $this->findCrossesByOemId(Arr::get($oemCrosses, 'id'));

        foreach ($crosses as $cross) {
            $items = DB::select('si.*', ['ss.name', 'supplier_name'])
                ->from(['suppliers__items', 'si'])
                ->join(['suppliers__suppliers', 'ss'])
                ->on('ss.id', '=', 'si.supplier_id')
                ->where('si.quantity', '!=', 0)
                ->and_where('si.article_search', '=', $cross['article'])
                ->and_where('si.brand', '=', $cross['brand'])
                ->execute()
                ->as_array()
            ;

            foreach ($items as $item) {
                $spares[md5($item['supplier_id'].$item['brand'].$item['article'])] = $item;
            }
        }

        $i = 0;
        foreach ($spares as $result) {
            $searchResult[$i] = $result;
            $searchResult[$i]['offer_price'] = $this->calculateMarkupPrice((int)$result['supplier_id'], (float)$result['price']);
            $i++;
        }

        return $searchResult;
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function findOrderSpare($orderId)
    {
        return DB::select('os.*', ['ss.name', 'supplier_name'])
            ->from(['orders__spares', 'os'])
            ->join(['suppliers__suppliers', 'ss'], 'LEFT')
            ->on('ss.id', '=', 'os.supplier_id')
            ->where('order_id', '=', $orderId)
            ->execute()
            ->as_array()
        ;
    }


    /**
     * @param int $spareId
     * @return mixed
     */
    public function findOrderSpareById($spareId)
    {
        return DB::select()
            ->from('orders__spares')
            ->where('id', '=', $spareId)
            ->limit(1)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param int $itemId
     * @return mixed
     */
    public function findSupplierItemById($itemId)
    {
        return DB::select()
            ->from('suppliers__items')
            ->where('id', '=', $itemId)
            ->limit(1)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param int $supplierId
     * @param string $vendorId
     * @return mixed
     */
    public function findSupplierItemBySupplierAndVendorId($supplierId, $vendorId)
    {
        return DB::select()
            ->from('suppliers__items')
            ->where('vendor_id', '=', $vendorId)
            ->and_where('supplier_id', '=', $supplierId)
            ->limit(1)
            ->execute()
            ->current()
            ;
    }


    /**
     * @param int $supplierId
     * @param string $hash
     * @return mixed
     */
    public function findSupplierItemBySupplierAndHash($supplierId, $hash)
    {
        return DB::select()
            ->from('suppliers__items')
            ->where('item_hash', '=', $hash)
            ->and_where('supplier_id', '=', $supplierId)
            ->limit(1)
            ->execute()
            ->current()
            ;
    }

    /**
     * @param string $updateTask
     * @return mixed
     */
    public function findSupplierItemByUpdateTask($updateTask)
    {
        return DB::select('si.*', ['ss.name', 'supplier_name'])
            ->from(['suppliers__items', 'si'])
            ->join(['suppliers__suppliers', 'ss'])
            ->on('ss.id', '=', 'si.supplier_id')
            ->where('update_task', '=', $updateTask)
            ->execute()
            ->as_array()
            ;
    }

    /**
     * @param int $spareId
     * @param array $params
     */
    public function setOrderSpare($spareId, array $params)
    {
        DB::update('orders__spares')
            ->set([
                'order_id' => Arr::get($params, 'order_id'),
                'supplier_id' => Arr::get($params, 'supplier_id'),
                'brand' => Arr::get($params, 'brand'),
                'article' => Arr::get($params, 'article'),
                'name' => Arr::get($params, 'name'),
                'start_price' => Arr::get($params, 'start_price'),
                'offer_price' => Arr::get($params, 'offer_price'),
                'quantity' => Arr::get($params, 'quantity')
            ])
            ->where('id', '=', $spareId)
            ->execute()
        ;
    }

    /**
     * @param array $sparesParams
     * @param int $orderId
     */
    public function setOrderSpares(array $sparesParams, $orderId)
    {
        foreach (Arr::get($sparesParams, 'spare_id', []) as $key => $spareId) {
            $params = [
                'order_id' => $orderId,
                'supplier_id' => $sparesParams['supplier_id'][$key],
                'brand' => $sparesParams['brand'][$key],
                'article' => $sparesParams['article'][$key],
                'name' => $sparesParams['name'][$key],
                'offer_price' => $sparesParams['offer_price'][$key],
                'start_price' => $sparesParams['start_price'][$key],
                'quantity' => $sparesParams['quantity'][$key]
            ];

            $this->setOrderSpare((int)$spareId, $params);
        }
    }

    /**
     * @param int $spareId
     * @param int $itemId
     */
    public function setOrderSpareBySearch($spareId, $itemId)
    {
        $spareData = $this->findOrderSpareById($spareId);
        $itemData = $this->findSupplierItemById($itemId);

        if (empty($spareData) || empty($itemData)) {
            return;
        }

        $params = [
            'order_id' => $spareData['order_id'],
            'supplier_id' => $itemData['supplier_id'],
            'brand' => $itemData['brand'],
            'article' => $itemData['article'],
            'name' => $itemData['name'],
            'start_price' => $itemData['price'],
            'offer_price' => $this->calculateMarkupPrice((int)$itemData['supplier_id'], $itemData['price']),
            'quantity' => $spareData['quantity']
        ];

        $this->setOrderSpare($spareId, $params);
    }

    /**
     * @param array $query
     * @return array
     */
    public function searchOrders(array $query)
    {
        if (empty($query)) {
            return [];
        }

        $emptyQuery = true;

        $carQuery = $query['car'];
        $customerQuery = $query['customer'];
        $phoneQuery = $query['phone'];
        $emailQuery = $query['email'];
        $articleQuery = trim(preg_replace('/[- ]+/', '', $query['article']));

        $query = DB::select('oo.id')
            ->from(['orders__orders', 'oo'])
            ->join(['orders__customers', 'oc'])
            ->on('oc.order_id', '=', 'oo.id')
            ->join(['orders__deliveries', 'od'])
            ->on('od.order_id', '=', 'oo.id')
            ->join(['orders__vehicles', 'ov'])
            ->on('ov.order_id', '=', 'oo.id')
            ->join(['orders__statuses', 'os'])
            ->on('os.order_id', '=', 'oo.id')
            ->join(['statuses__order_statuses', 'sos'], 'left')
            ->on('sos.id', '=', 'os.status_id')
            ->join(['users', 'u'], 'left')
            ->on('u.id', '=', 'oo.user_id')
        ;

        if (!empty($carQuery)) {
            $query = $query
                ->where_open()
                    ->where('ov.brand', 'LIKE', "%$carQuery%")
                    ->or_where('ov.model', 'LIKE', "%$carQuery%")
                    ->or_where('ov.frame', 'LIKE', "%$carQuery%")
                ->where_close()
            ;

            $emptyQuery = false;
        }

        if (!empty($customerQuery)) {
            $query = !$emptyQuery ? $query->and_where_open() : $query;
            $query = $query
                ->where('oc.first_name', 'LIKE', "%$customerQuery%")
                ->or_where('oc.second_name', 'LIKE', "%$customerQuery%")
                ->or_where('oc.father_name', 'LIKE', "%$customerQuery%")
            ;
            $query = !$emptyQuery ? $query->and_where_close() : $query;

            $emptyQuery = false;
        }

        if (!empty($phoneQuery)) {
            $query = !$emptyQuery ? $query->and_where_open() : $query;
            $query = $query
                ->where('oc.phone', 'LIKE', "%$phoneQuery%")
                ->or_where('oc.phone2', 'LIKE', "%$phoneQuery%")
            ;
            $query = !$emptyQuery ? $query->and_where_close() : $query;

            $emptyQuery = false;
        }

        if (!empty($emailQuery)) {
            $query = !$emptyQuery ? $query->and_where_open() : $query;
            $query = $query
                ->where('oc.email', 'LIKE', "%$emailQuery%")
            ;
            $query = !$emptyQuery ? $query->and_where_close() : $query;

            $emptyQuery = false;
        }

        if (!empty($articleQuery)) {
            $query = !$emptyQuery ? $query->and_where_open() : $query;
            $query = $query
                ->where('oo.id', 'IN',
                    DB::select('osp.order_id')
                    ->from(['orders__spares', 'osp'])
                    ->where(DB::expr("REPLACE(REPLACE(osp.article, '-', ''), ' ', '')"), 'LIKE', "%$articleQuery%")
                )
            ;
            $query = !$emptyQuery ? $query->and_where_close() : $query;

            $emptyQuery = false;
        }

        if ($emptyQuery) {
            return [];
        }

        $orders = $query
            ->execute()
            ->as_array()
        ;

        $ordersList = [];

        foreach ($orders as $order) {
            $ordersList[] = $this->findOrderById($order['id']);
        }

        return $ordersList;
    }

    /**
     * @param int $orderId
     * @param int|null $supplierId
     * @param string $name
     * @param int $quantity
     * @param string $brand
     * @param string $oem
     * @param string $article
     * @param int $startPrice
     * @param int $offerPrice
     */
    public function addSpare($orderId, $supplierId, $name, $quantity, $brand, $oem, $article, $startPrice, $offerPrice)
    {
        DB::insert('orders__spares', [
            'order_id',
            'supplier_id',
            'name',
            'quantity',
            'brand',
            'oem',
            'article',
            'start_price',
            'offer_price'
        ])
            ->values([
                $orderId,
                $supplierId,
                $name,
                $quantity,
                $brand,
                $oem,
                $article,
                $startPrice,
                $offerPrice
            ])
            ->execute()
        ;
    }

    /**
     * @param int $orderId
     * @param int $itemId
     *
     * @return string
     */
    public function addSpareToOrderFromSearch($orderId, $itemId)
    {
        $itemData = $this->findSupplierItemById($itemId);

        if (empty($itemData)) {
            return 'error';
        }

        $this->addSpare(
            $orderId,
            (int)$itemData['supplier_id'],
            $itemData['name'],
            1,
            $itemData['brand'],
            '',
            $itemData['article'],
            (int)$itemData['price'],
            $this->calculateMarkupPrice((int)$itemData['supplier_id'], (int)$itemData['price'])
        );

        return 'success';
    }

    /**
     * @param int $spareId
     */
    public function removeSpare($spareId)
    {
        if (Auth::instance()->logged_in('admin')) {
            DB::delete('orders__spares')
                ->where('id', '=', $spareId)
                ->execute()
            ;
        }
    }

    /**
     * @param string $query
     * @return array
     */
    public function findVehicleBrands($query)
    {
        if (empty($query)) {
            return [];
        }

        return DB::select([DB::expr('UPPER(name)'), 'name'])
            ->from('vehicles__brands')
            ->where('name', 'like', "%$query%")
            ->execute()
            ->as_array(null, 'name')
            ;
    }

    /**
     * @param string $name
     * @return array
     */
    public function findVehicleBrandByName($name)
    {
        return DB::select()
            ->from('vehicles__brands')
            ->where('name', '=', $name)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param string $query
     * @return array
     */
    public function findVehicleModels($brandName, $query)
    {
        if (empty($query)) {
            return [];
        }

        $vehicleBrand = $this->findVehicleBrandByName($brandName);

        return DB::select([DB::expr('UPPER(name)'), 'name'])
            ->from('vehicles__model')
            ->where('name', 'like', "%$query%")
            ->and_where('brand_id', '=', Arr::get($vehicleBrand, 'id'))
            ->execute()
            ->as_array(null, 'name')
            ;
    }

    public function numberToString($number)
    {
        // Все варианты написания чисел прописью от 0 до 999 скомпануем в один небольшой массив
        $m = [
            ['ноль'],
            ['-', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            [
                'десять',
                'одиннадцать',
                'двенадцать',
                'тринадцать',
                'четырнадцать',
                'пятнадцать',
                'шестнадцать',
                'семнадцать',
                'восемнадцать',
                'девятнадцать'
            ],
            [
                '-',
                '-',
                'двадцать',
                'тридцать',
                'сорок',
                'пятьдесят',
                'шестьдесят',
                'семьдесят',
                'восемьдесят',
                'девяносто'
            ],
            [
                '-',
                'сто',
                'двести',
                'триста',
                'четыреста',
                'пятьсот',
                'шестьсот',
                'семьсот',
                'восемьсот',
                'девятьсот'
            ],
            ['-', 'одна', 'две']
        ];

        // Все варианты написания разрядов прописью скомпануем в один небольшой массив
        $r = [
            ['...ллион', '', 'а', 'ов'], // используется для всех неизвестно больших разрядов
            ['тысяч', 'а', 'и', ''],
            ['миллион', '', 'а', 'ов'],
            ['миллиард', '', 'а', 'ов'],
            ['триллион', '', 'а', 'ов'],
            ['квадриллион', '', 'а', 'ов'],
            ['квинтиллион', '', 'а', 'ов']
        ];

        if ($number == 0) {
            return $m[0][0];
        } // Если число ноль, сразу сообщить об этом и выйти
        $o = array(); // Сюда записываем все получаемые результаты преобразования

        // Разложим исходное число на несколько трехзначных чисел и каждое полученное такое число обработаем отдельно
        foreach (
            array_reverse(
                str_split(str_pad($number, ceil(strlen($number) / 3) * 3, '0', STR_PAD_LEFT), 3)
            ) as $k => $p
        ) {
            $o[$k] = array();

            // Алгоритм, преобразующий трехзначное число в строку прописью
            foreach ($n = str_split($p) as $kk => $pp) {
                if (!$pp) {
                    continue;
                } else {
                    switch ($kk) {
                        case 0:
                            $o[$k][] = $m[4][$pp];
                            break;
                        case 1:
                            if ($pp == 1) {
                                $o[$k][] = $m[2][$n[2]];
                                break 2;
                            } else {
                                $o[$k][] = $m[3][$pp];
                            }
                            break;
                        case 2:
                            if (($k == 1) && ($pp <= 2)) {
                                $o[$k][] = $m[5][$pp];
                            } else {
                                $o[$k][] = $m[1][$pp];
                            }
                            break;
                    }
                }
            }
            $p *= 1;
            if (!$r[$k]) {
                $r[$k] = reset($r);
            }

            // Алгоритм, добавляющий разряд, учитывающий окончание руского языка
            if ($p && $k) {
                switch (true) {
                    case
                    preg_match("/^[1]$|^\\d*[0,2-9][1]$/", $p):
                        $o[$k][] = $r[$k][0] . $r[$k][1];
                        break;
                    case
                    preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/", $p):
                        $o[$k][] = $r[$k][0] . $r[$k][2];
                        break;
                    default:
                        $o[$k][] = $r[$k][0] . $r[$k][3];
                        break;
                }
            }
            $o[$k] = implode(' ', $o[$k]);
        }

        return implode(' ', array_reverse($o));
    }

    /**
     * @param string $query
     * @return array
     */
    public function findCitiesByQuery($query)
    {
        if (empty($query)) {
            return [];
        }

        return DB::select('name')
            ->from('addresses__cities')
            ->where('name', 'like', "%$query%")
            ->execute()
            ->as_array(null, 'name')
            ;
    }

    /**
     * @param string $query
     * @return array
     */
    public function findTransportCompaniesByQuery($query)
    {
        if (empty($query)) {
            return [];
        }

        return DB::select('name')
            ->from('data__transport_companies')
            ->where('name', 'like', "%$query%")
            ->execute()
            ->as_array(null, 'name')
            ;
    }


    /**
     * @param string $article
     * @return null|string
     */
    public function searchSpareByApi($article)
    {
        /** @var Model_API $apiModel */
        $apiModel = Model::factory('API');


        $apiData = [];
        $suppliers = $this->getSuppliersList();

        foreach ($suppliers as $supplier) {
            if (!empty($supplier['api_name'])) {
                $apiData[] = [
                    $supplier['id'] => $apiModel->getApiData($supplier['api_name'], $article)
                ];
            }
        }

        if (count($apiData)) {
            $this->addCrosses(mb_strtoupper($article), $apiData, 'api');
        }

        return $this->loadSupplierPriceFromApi($apiData);
    }

    /**
     * @param array $apiData
     * @return null|string
     */
    public function loadSupplierPriceFromApi(array $apiData)
    {
        if (empty($apiData)) {
            return null;
        }

        $updateTask = $this->generateUpdateTask();
        $loadPriceData = [];

        foreach ($apiData as $supplierApiData) {
            foreach ($supplierApiData as $supplierId => $data) {
                foreach ($data as $value) {
                    $validData = $this->validateLoadPrice(
                        $supplierId,
                        $value['brand'],
                        $value['article'],
                        $value['name'],
                        $value['price'],
                        $value['quantity']
                    );

                    if (empty($validData)) {
                        continue;
                    }

                    array_push($validData, $value['vendor_id']);
                    array_push($validData, $updateTask);

                    $loadPriceData[] = $validData;
                }
            }
        }

        foreach ($loadPriceData as $validData) {
            $this->setSupplierItem($validData);
        }

        return $updateTask;
    }

    /**
     * @return string
     */
    private function generateUpdateTask()
    {
        $now = new DateTime();
        return $now->format('YmdHis');
    }

    /**
     * @param $supplierId
     * @return false|array
     */
    public function getSupplierMarkup($supplierId)
    {
        return DB::select()
            ->from('suppliers__markups')
            ->limit(1)
            ->where('supplier_id', '=', $supplierId)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param int $supplierId
     * @param array $post
     */
    public function setSupplierMarkups($supplierId, $post)
    {
        $this->setSupplierMarkup($supplierId, (float)Arr::get($post, 'markup', 0));

        foreach (Arr::get($post, 'markup_range_id', []) as $key => $value) {
            $this->setSupplierMarkupRanges((int)$value, (int)$post['markup_range_first'][$key], (int)$post['markup_range_last'][$key], (float)$post['markup_range_value'][$key]);
        }
    }

    /**
     * @param int $supplierId
     * @param float $markup
     */
    private function setSupplierMarkup($supplierId, $markup)
    {
        if (!$this->getSupplierMarkup($supplierId)) {
            DB::insert('suppliers__markups', ['supplier_id', 'markup', 'updated_at'])
                ->values([$supplierId, $markup, DB::expr('NOW()')])
                ->execute()
            ;

            return;
        }

        DB::update('suppliers__markups')
            ->set([
                'markup' => $markup,
                'updated_at' => DB::expr('NOW()')
            ])
            ->where('supplier_id', '=', $supplierId)
            ->execute()
        ;
    }

    /**
     * @param int $id
     * @return false|array
     */
    public function findSupplierMarkupRangesById($id)
    {
        return DB::select()
            ->from('suppliers__markups_ranges')
            ->where('id', '=', $id)
            ->limit(1)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param $supplierId
     * @return array
     */
    public function findSupplierMarkupRangesBySupplier($supplierId)
    {
        return DB::select()
            ->from('suppliers__markups_ranges')
            ->where('supplier_id', '=', $supplierId)
            ->execute()
            ->as_array()
        ;
    }

    /**
     * @param int $supplierId
     * @param int $price
     * @return false|array
     */
    public function findSupplierMarkupRangesBySupplierAndRanges($supplierId, $price)
    {
        return DB::select()
            ->from('suppliers__markups_ranges')
            ->where('supplier_id', '=', $supplierId)
            ->and_where('range_first', '<', $price)
            ->and_where('range_last', '>=', $price)
            ->limit(1)
            ->execute()
            ->current()
        ;
    }

    /**
     * @param int $supplierId
     */
    public function addSupplierMarkupRanges($supplierId)
    {
        DB::insert('suppliers__markups_ranges', ['supplier_id', 'updated_at'])
            ->values([$supplierId, DB::expr('NOW()')])
            ->execute()
        ;
    }

    /**
     * @param int $markupRangeId
     * @param int $rangeFirst
     * @param int $rangeLast
     * @param int $value
     */
    public function setSupplierMarkupRanges($markupRangeId, $rangeFirst, $rangeLast, $value)
    {
        DB::update('suppliers__markups_ranges')
            ->set([
                'range_first' => $rangeFirst,
                'range_last' => $rangeLast,
                'value' => $value,
                'updated_at' => DB::expr('NOW()')
            ])
            ->where('id', '=', $markupRangeId)
            ->execute()
        ;
    }

    /**
     * @param int $supplierId
     * @param float $price
     * @return float
     */
    public function calculateMarkupPrice($supplierId, $price)
    {
        $supplierMarkup = $this->getSupplierMarkup($supplierId);
        $price = $price * (1 + (Arr::get($supplierMarkup, 'markup', 0) / 100));
        $supplierMarkupRangeValue = $this->findSupplierMarkupRangesBySupplierAndRanges($supplierId, $price);

        return $price * (1 + (Arr::get($supplierMarkupRangeValue, 'value', 0) / 100));
    }


    /**
     * @param string $oem
     * @param array $newCrosses
     * @param string $source
     */
    private function addCrosses($oem, $newCrosses, $source)
    {
        $issetCrosses = [];
        $crosses = [];
        $oemCrossesId = $this->addOemCrosses($oem, $source);

        foreach ($this->findCrossesByOemId($oemCrossesId) as $cross) {
            if (!in_array(['brand' => $cross['brand'], 'article' => $cross['article']], $issetCrosses)) {
                $issetCrosses[] = ['brand' => $cross['brand'], 'article' => $cross['article']];
            }
        }

        foreach ($newCrosses as $suppliersList) {
            foreach ($suppliersList as $supplierCrossesData) {
                foreach ($supplierCrossesData as $newCross) {
                    if (
                        !in_array(['brand' => $newCross['brand'], 'article' => $newCross['article']], $issetCrosses)
                        && !in_array(['brand' => $newCross['brand'], 'article' => $newCross['article']], $crosses)
                    ) {
                        $this->addCross($oemCrossesId, mb_strtoupper($newCross['brand']), mb_strtoupper($newCross['article']));
                        $crosses[] = ['brand' => $newCross['brand'], 'article' => $newCross['article']];
                    }
                }
            }
        }
    }

    /**
     * @param $oem
     * @return array|bool
     */
    public function findOemCrosses($oem)
    {
        return DB::select()
            ->from('crosses__oem')
            ->where('oem', 'LIKE', $oem)
            ->limit(1)
            ->execute()
            ->current()
            ;
    }

    /**
     * @param string $oem
     * @param string $source
     *
     * @return int
     */
    private function addOemCrosses($oem, $source)
    {
        $oemCrosses = $this->findOemCrosses($oem);

        if(!$oemCrosses) {
            $res = DB::insert('crosses__oem', ['oem', 'source', 'created_at'])
                ->values([$oem, $source, DB::expr('NOW()')])
                ->execute()
            ;

            return (int)$res[0];
        }

        return (int)Arr::get($oemCrosses,'id');
    }

    /**
     * @param int $oemCrossesId
     * @param string $brand
     * @param string $article
     */
    private function addCross($oemCrossesId, $brand, $article)
    {
        $res = DB::insert('crosses__crosses', ['oem_crosses_id', 'brand', 'article'])
            ->values([$oemCrossesId, $brand, preg_replace('/[^0-9a-zA-Z]+/', '', $article)])
            ->execute()
        ;
    }

    /**
     * @param int $oemCrossesId
     * @return array
     */
    private function findCrossesByOemId($oemCrossesId)
    {
        return DB::select()
            ->from('crosses__crosses')
            ->where('oem_crosses_id', '=', $oemCrossesId)
            ->execute()
            ->as_array()
            ;
    }

    public function loadCrosses(array $fileData)
    {
        $data = file($fileData['crosses']['tmp_name']);
        $loadCrosses = [];

        foreach ($data as $row) {
            $ceils = explode(';', $row);

            if (!empty($ceils[0]) && !empty($ceils[1]) && !empty($ceils[2])) {
                if (!isset($loadCrosses[$ceils[0]][0][0])) {
                    $loadCrosses[$ceils[0]][0][0] = [];
                }

                $loadCrosses[$ceils[0]][0][0][] = ['brand' => $ceils[1], 'article' => $ceils[2]];
            }
        }

        foreach ($loadCrosses as $oem => $newCrosses) {
            $this->addCrosses($oem, $newCrosses, $fileData['crosses']['name']);
        }
    }
}
?>

