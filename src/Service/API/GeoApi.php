<?php

namespace App\Service\API;

use Symfony\Component\HttpClient\HttpClient;

class GeoApi
{
    private $apiurl = "https://geo.api.gouv.fr/communes";

    public function RequestApi(string $indicator, string $request)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $this->apiurl."?".$indicator."=".$request."&format=json");
        return $response;
    }
}


?>