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
        ]
    ];

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

    private function getSoapResponse()
    {

    }

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
        }

        return [];
    }

    private function parseResponse($supplier, $response)
    {
        switch ($supplier) {
            case 'mxGroup':
               return $this->parseMxGroupResponse($response);
        }

        return [];
    }

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
}