<?php

class dataSource extends CAction
{
    public function run(){
        $type = $_GET['type'];
        switch($type)
        {
            case 'cities':
                $query = $_GET['query'];
                $country = $_GET['country'];

                $data = CVkLocation::suggestCities($country, $query);
                echo json_encode($data);
                break;

            case 'large_cities':
                $country = $_GET['country'];
                $data = CVkLocation::listLargeCities($country);
                echo json_encode($data);
                break;

            case 'countries':
                $data = CVkLocation::listCountries();
                echo json_encode($data);
                break;

            default:
                throw new CHttpException(404, 'Type field should be specified');
        }
    }
}
