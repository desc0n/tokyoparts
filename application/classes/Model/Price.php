<?php

/**
 * Class Model_Price
 */
class Model_Price extends Kohana_Model
{
    public function __construct()
    {
        DB::query(Database::UPDATE, "SET time_zone = '+10:00'")->execute();
    }

    private $parsingSettings = [
        'mxGroup' => [
            'type' => 'ftp',
            'dir' => 'mx',
            'file' => 'PriceMX_d084-df4d.xlsx',
            'parsingType' => 'Excel',
            'brand' => 'B',
            'article' => 'A',
            'name' => 'D',
            'quantity' => 'E',
            'price' => 'F',
            'ignoreFirstRow' => true
        ]
    ];

    private $manualParsingSettings = [
        'brand' => 0,
        'article' => 1,
        'name' => 2,
        'quantity' => 4,
        'price' => 3,
    ];

    /**
     * @return array
     */
    public function getParsingSettings()
    {
        return $this->parsingSettings;
    }

    /**
     * @return string
     */
    public function autoUpdateSupplierItems()
    {
        $supplierItemsTmp = DB::select()->from('items__tmp')->limit(300)->execute()->as_array();

        if (count($supplierItemsTmp) === 0) {
            return 'end';
        }

        $data = [];

        foreach ($supplierItemsTmp as $row) {
            DB::delete('items__tmp')->where('id', '=', $row['id'])->execute();

            if (empty($row['brand']) || empty($row['article_search'])) {
                continue;
            }

            $row['name'] = $row['description'];
            $supplierId = !isset($supplierId) ? $row['supplier_id'] : $supplierId;
            $updateTask = !isset($updateTask) ? $row['update_task'] : $updateTask;

            $data[] = $row;
        }

        if (count($data) > 0 && !empty($supplierId) && !empty($updateTask)) {
            $this->loadSupplierPrice($data, $supplierId, $updateTask);
        }

        return 'continue';
    }

    /**
     * @param $supplierId
     * @return bool
     */
    public function insertItemsTmpForAutoUpdate($supplierId)
    {
        /** @var Model_Supplier $supplierModel */
        $supplierModel = Model::factory('Supplier');

        $supplier = $supplierModel->findSupplierById($supplierId);
        $supplierAlias = Arr::get($supplier, 'alias');
        $parsingSettings = Arr::get($this->parsingSettings, $supplierAlias, []);
        $updateType = Arr::get($parsingSettings, 'type');

        switch ($updateType) {
            case 'ftp':
                return $this->loadPriceFromFtp($supplierId, $parsingSettings);
        }

        return true;
    }

    /**
     * @param array $fileData
     * @param int $supplierId
     * @return bool
     */
    public function manualUpdateSupplierItems(array $fileData, $supplierId)
    {
        $data = file($fileData['priceName']['tmp_name']);

        return $this->loadSupplierPrice($this->parseManualFile($data), $supplierId, $this->generateUpdateTask('file'));
    }

    /**
     * @param $supplierId
     * @param $settings
     *
     * @return bool
     */
    public function loadPriceFromFtp($supplierId, $settings)
    {
        $fileName =  'public/prices/' . Arr::get($settings, 'dir') . '/' . Arr::get($settings, 'file');

        if (!is_file($fileName)) {
            return false;
        }

        switch (Arr::get($settings, 'parsingType')) {
            case 'Excel':
            return $this->loadTmpSupplierPrice($this->parseXlsFile($settings, $fileName), $supplierId);
        }

        return false;
    }

    /**
     * @param array $settings
     * @param string $fileName
     *
     * @return array
     */
    public function parseXlsFile($settings, $fileName)
    {
        $objPHPExcel = Model::factory('Excel_PHPExcel_IOFactory')->load($fileName);
        $positions = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        $data = [];

        foreach ($positions as $row) {
            $data[] = [
                'brand' => Arr::get($row, $settings['brand']),
                'article' => Arr::get($row, $settings['article']),
                'name' => Arr::get($row, $settings['name']),
                'quantity' => Arr::get($row, $settings['quantity']),
                'price' => Arr::get($row, $settings['price']),
            ];
        }

        unset($positions);
        unset($objPHPExcel);
        return $data;
    }

    /**
     * @param array $fileData
     * @return array
     */
    public function parseManualFile(array $fileData)
    {
        $data = [];

        foreach ($fileData as $row) {
            $cells = explode(';', str_replace(chr(10), '', str_replace("\n", '', str_replace('"', '', $row))));
            $data[] = [
                'brand' => Arr::get($cells, 0),
                'article' => Arr::get($cells, 1),
                'name' => Arr::get($cells, 2),
                'quantity' => Arr::get($cells, 4),
                'price' => Arr::get($cells, 3),
            ];
        }

        return $data;
    }

    /**
     * @param array $data
     * @param int $supplierId
     *
     * @return bool
     */
    public function loadTmpSupplierPrice(array $data, $supplierId)
    {
        /** @var Model_CRM $crmModel */
        $crmModel = Model::factory('CRM');

        DB::query(Database::UPDATE, 'truncate table `items__tmp`')->execute();

        $updateTask = $this->generateUpdateTask('auto');

        foreach ($data as $key => $value) {
            if (empty($value['brand']) || empty($value['article']) || empty($value['quantity']) || empty($value['price'])) {
                unset($data[$key]);
                continue;
            }

            DB::insert('items__tmp', [
                'supplier_id',
                'brand',
                'article_search',
                'description',
                'price',
                'article',
                'usages',
                'crosses',
                'images',
                'quantity',
                'update_task'
            ])
                ->values([
                    $supplierId,
                    $value['brand'],
                    $crmModel->getSearchArticle($value['article']),
                    $value['name'],
                    $value['price'],
                    $value['article'],
                    '',
                    '',
                    '',
                    $value['quantity'],
                    $updateTask
                ])
                ->execute();

            unset($data[$key]);
        }

        return true;
    }

    /**
     * @param array $data
     * @param int $supplierId
     * @param string $updateTask
     *
     * @return bool
     */
    public function loadSupplierPrice(array $data, $supplierId, $updateTask)
    {
        foreach ($data as $row) {
            $validData = $this->validateLoadPrice((int)$supplierId, $row['brand'], $row['article'], $row['name'], $row['price'], $row['quantity']);

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

        return true;
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
        /** @var Model_CRM $crmModel */
        $crmModel = Model::factory('CRM');

        if(empty($supplierId) || empty($brand) || empty($article) || empty($name) || empty($price) || empty($quantity)) {
            return [];
        }

        $price = $this->validatePrice($price);
        $quantity = $this->validateQuantity($quantity);

        return [$supplierId, $brand, $article, $crmModel->getSearchArticle($article), $name, $price, $quantity, md5($brand . $article)];
    }

    /**
     * @param array $validData
     */
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
     * @param array $apiData
     * @return null|string
     */
    public function loadSupplierPriceFromApi(array $apiData)
    {
        /** @var Model_API $apiModel */
        $apiModel = Model::factory('API');

        /** @var Model_Supplier $supplierModel */
        $supplierModel = Model::factory('Supplier');

        if (empty($apiData)) {
            return null;
        }

        $updateTask = $this->generateUpdateTask('api');
        $loadPriceData = [];

        foreach ($apiData as $supplierApiData) {
            foreach ($supplierApiData as $supplierId => $warehouseApiData) {
                $supplierData = $supplierModel->findSupplierById($supplierId);
                $apiSettings = Arr::get($apiModel->getApiSettings(), $supplierData['alias'], []);

                foreach ($warehouseApiData as $warehouseId => $data) {
                    if (count($apiSettings) && !in_array($warehouseId, Arr::get($apiSettings, 'access_warehouse', []))) {
                        continue;
                    }

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
        }

        foreach ($loadPriceData as $validData) {
            $this->setSupplierItem($validData);
        }

        return $updateTask;
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
     * @param string $type
     *
     * @return string
     */
    private function generateUpdateTask($type)
    {
        $now = new DateTime();
        return $type . '.' . $now->format('YmdHis');
    }

    public function validateQuantity($value)
    {
        $replaceVariant = [
            'меньше 10' => 5,
            'больше 10' => 15,
            '10-100' => 20,
            '10100' => 20,
        ];

        $value = Arr::get($replaceVariant, $value, $value);
        $value = preg_replace( '/[^[:print:]]/', '',(int)$value);

        return preg_replace('/[\D]+/', '', $value);
    }

    public function validatePrice($value)
    {
        return preg_replace( '/[^[:print:]]/', '',(int)$value);
    }
}