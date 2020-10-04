<?php

namespace App\Service\API;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;

class GeoApi
{
    private $apiurl = "https://geo.api.gouv.fr/communes";

    public function RequestApi(string $indicator, string $request)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $this->apiurl."?".$indicator."=".$request."&fields=nom,codeDepartement,centre&format=json");
        return $response;
    }

    public function setCity(Array $response, CityRepository $cityRepository, EntityManagerInterface $manager)
    {
        $responseName = $response[0]['nom']." (".$response[0]['codeDepartement'].")";
        $responseCode = $response[0]['code'];
        
        $isCityExistingInDBB = $cityRepository->findOneBy(["name" => $responseName, "code" => $responseCode]);

        if(!$isCityExistingInDBB){
            $city = new City();
            $city   ->setName($responseName)
                    ->setCode($responseCode)
                    ->setLongitude($response[0]["centre"]["coordinates"][0])
                    ->setLatitude($response[0]["centre"]["coordinates"][1]);

            $manager->persist($city);
            $manager->flush();
            
            return $city;
        }
        else {
            return $isCityExistingInDBB;
        }
    }
}


?>