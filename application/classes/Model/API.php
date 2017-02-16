<?php

/**
 * Class Model_API
 */
class Model_API extends Kohana_Model
{
    private $apiUrl;

    private $type = 'post';

    private $header;

    private $queryString;

    private $apiSettings = [
        'mxGroup' => [
            'login' => 'tokyo.vladivostok@gmail.com',
            'password' => 'n012nn125',
            'url' => 'http://zakaz.mxgroup.ru/mxapi/',
            'type' => 'get',
            'responseType' => 'curl',
            'access_warehouse' => [84,91,133]
        ],
        'rossko' => [
            'key1' => '929e473ddca098b52473628fd7546ecd',
            'key2' => 'abeaabec87f7d6ba68724d5f007b9c07',
            'url' => 'http://vl.rossko.ru/service/v1/GetSearch?wsdl',
            'type' => 'get',
            'responseType' => 'soap',
            'access_warehouse' => ['ОТ0000093']
        ]
    ];

    /**
     * @param $type
     * @return mixed|null|SoapClient
     */
    private function getResponse($type)
    {
        switch ($type) {
            case 'curl':
                return $this->getCurlResponse();
            case 'soap':
                return $this->getSoapResponse();
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    private function getCurlResponse()
    {
        if ($this->apiUrl === null || $this->queryString === []) {
            return null;
        }

        if ($this->type === 'get') {
            $this->apiUrl .= '?' . http_build_query($this->queryString);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($this->header !== null) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        if ($this->type === 'post' && $this->queryString !== null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->queryString);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = @curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    /**
     * @return null|SoapClient
     */
    private function getSoapResponse()
    {
        if ($this->apiUrl === null || $this->queryString === []) {
            return null;
        }

        return new \SoapClient($this->apiUrl, [
            'soap_version' => SOAP_1_2,
            'connection_timeout' => 10
        ]);
    }

    /**
     * @param string $supplier
     * @param string $article
     * @return array
     */
    public function getApiData($supplier, $article)
    {
        $apiSettings = Arr::get($this->apiSettings, $supplier, []);

        if ($apiSettings === []) {
            return [];
        }

        $this->apiUrl = Arr::get($apiSettings, 'url');
        $this->type = Arr::get($apiSettings, 'type');
        $this->queryString = $this->getQueryString($supplier, $article);

        $response = $this->getResponse(Arr::get($apiSettings, 'responseType'));

        return $this->parseResponse($supplier, $response);
    }

    /**
     * @param string $supplier
     * @param string $article
     * @return array
     */
    private function getQueryString($supplier, $article)
    {
        switch ($supplier) {
            case 'mxGroup':
                return [
                    'm' => 'search',
                    'zapros' => $article,
                    'login' => $this->apiSettings[$supplier]['login'],
                    'password' => $this->apiSettings[$supplier]['password'],
                    'out' => 'json',
                ];
            case 'rossko':
                return $article;
        }

        return [];
    }

    /**
     * @param $supplier
     * @param $response
     * @return array
     */
    private function parseResponse($supplier, $response)
    {
        switch ($supplier) {
            case 'mxGroup':
               return $this->parseMxGroupResponse($response);
            case 'rossko':
               return $this->parseRosskoResponse($response);
        }

        return [];
    }

    /**
     * @param $response
     * @return array
     */
    private function parseMxGroupResponse($response)
    {
        $data = [];
        $responseData = json_decode($response, true);

        if(!empty($responseData['result'])) {
            foreach ($responseData['result'] as $result) {
                if (in_array(Arr::get($result, 'id'), $this->apiSettings['mxGroup']['access_warehouse'])) {
                    $data[] = [
                        'brand' => Arr::get($result, 'brand'),
                        'article' => Arr::get($result, 'articul'),
                        'name' => Arr::get($result, 'name'),
                        'price' => Arr::get($result, 'discountprice', 0),
                        'quantity' => Arr::get($result, 'count', 0),
                        'vendor_id' => Arr::get($result, 'code')
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param null|SoapClient $client
     * @return array
     */
    private function parseRosskoResponse($client)
    {
        $data = [];

        if (empty($client)) {
            return $data;
        }

        $client->KEY1 = $this->apiSettings['rossko']['key1'];
        $response = $client->getSearch([
            'KEY1' => $this->apiSettings['rossko']['key1'],
            'KEY2' => $this->apiSettings['rossko']['key2'],
            'TEXT' => $this->queryString
        ]);

        $responseArray = json_decode(json_encode($response), TRUE);

        if (!array_key_exists('SearchResults', $responseArray)) {
            return $data;
        }

        $apiResult = $responseArray['SearchResults']['SearchResult'];

        if($apiResult['Success'] && !empty($apiResult['PartsList']['Part'])){
            if (!empty($apiResult['PartsList']['Part']['StocksList']['Stock']['StockID'])) {
                return $this->addSingleSpareToRosskoApiData($data, $apiResult['PartsList']['Part']);
            }

            return $this->addBatchSpareToRosskoApiData($data, $apiResult['PartsList']['Part']);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $part
     * @return array
     */
    private function addSingleSpareToRosskoApiData(array $data, array $part)
    {
        if (!empty($part['StocksList']['Stock']['StockID'])) {
            if (in_array($part['StocksList']['Stock']['StockID'], $this->apiSettings['rossko']['access_warehouse'])) {
                $data[] = [
                    'brand' => $part['Brand'],
                    'article' => $part['PartNumber'],
                    'name' => $part['Name'],
                    'price' => $part['StocksList']['Stock']['Price'],
                    'quantity' => $part['StocksList']['Stock']['Count'],
                    'vendor_id' => $part['GUID'] . $part['StocksList']['Stock']['DeliveryTime'],
                ];
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $part
     * @return array
     */
    private function addBatchSpareToRosskoApiData(array $data, array $part)
    {
        if (!empty($part['StocksList']['Stock'])) {
            foreach ($part['StocksList']['Stock'] as $item) {
                if (!empty($item['StockID'])) {
                    if (in_array($item['StockID'], $this->apiSettings['rossko']['access_warehouse'])) {
                        $data[] = [
                            'brand' => $part['Brand'],
                            'article' => $part['PartNumber'],
                            'name' => $part['Name'],
                            'price' => $item['Price'],
                            'quantity' => $item['Count'],
                            'vendor_id' => $part['GUID'] . $item['DeliveryTime']
                        ];
                    }
                }
            }
        } else {
            foreach ($part as $item) {
                if (!empty($item['StocksList']['Stock']['StockID'])) {
                    $data = $this->addSingleSpareToRosskoApiData($data, $item);

                    continue;
                }

                if (is_array($item)) {
                    $data = $this->addBatchSpareToRosskoApiData($data, $item);
                }
            }
        }

        return $data;
    }
}