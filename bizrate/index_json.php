<?php

header('content-type: application/javascript');

ini_set('display_errors', true);

$return = '';

$return .='<!DOCTYPE html>';
$return .='<html>';
$return .='<meta name="viewport" content="width=device-width, initial-scale=1">';
$return .='<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">';
$return .='<link rel="stylesheet" type="text/css" href="/bizrate/css/ad.css">';
$return .='<body>';

require_once("geo/geoip.inc");

$geo_country = array("US");
$remoteip = $_GET['l'];
$placement = (isset($_GET['p']) AND is_numeric($_GET['p'])) ? $_GET['p'] : '0';
$keylist = (isset($_GET['k']) AND file_exists('keywords/' . $_GET['k'] . '.txt')) ? $_GET['k'] : 'general';
$category = (isset($_GET['c'])) ? $_GET['c'] : '';

$gi = geoip_open("geo/GeoIP.dat",GEOIP_STANDARD);
$countrycode = geoip_country_code_by_addr($gi, $remoteip);
geoip_close($gi);
$geo= array_search($countrycode, $geo_country);


if (isset($geo) && $geo !== FALSE) 
{
    //do something for visitors coming from $geo_country array, i.e. United States, United Kingdom, Canada, Australia
    
    // Get random keywords from keyword list      
    $keywords = explode("\n", trim(file_get_contents('keywords/'.$keylist.'.txt')));
    $search = $keywords[array_rand($keywords)];

    $xmlurl = 'http://catalog.bizrate.com/services/catalog/v1/us/product?apiKey=476422c250af7f542f13ac3fa133f9ce&publisherId=617185&placementId='.$placement.'&categoryId='.$catgory.'&keyword='.$search.'&productId=&productIdType=&offersOnly=&merchantId=&brandId=&biddedOnly=&minPrice=&maxPrice=&minMarkdown=&zipCode=&freeShipping=&start=0&results=1&backfillResults=0&startOffers=0&resultsOffers=0&sort=relevancy_desc&attFilter=&attWeights=&attributeId=&resultsAttribute=&resultsAttributeValues=&showAttributes=&showProductAttributes=&minRelevancyScore=100&maxAge=&showRawUrl=&imageOnly=&reviews=&retailOnly=&format=xml&callback=callback&mature=0';
    
    //phpinfo(); exit;
     // Pass ramdom keywords into the "search" variable. Set the parameter "&mature=1" to get adult products.
    $productsResult = simplexml_load_file($xmlurl);

    /*
    echo "<pre>";
    print_r($productsResult);
    echo "</pre>";
    exit;
    */

    foreach($productsResult->Products->Offer as $result) 
    { 
        
        //Get the third Image 160x160
        $image = $result->Images->Image[2];
        // Get Deal URL
        $url = $result->url;
        //Get Title
        $title = htmlspecialchars($result->title);
        //Get Merchant Name
        $merchantName = $result->merchantName;
        //Get Price
        $price = htmlspecialchars($result->price);
        // Get Original Price
        $originalPrice = htmlspecialchars($result->originalPrice);
        // Get shipType
        $shipType =  $result->shipType;
        
        
        $return .= '<div class="w3-card-0 w3-large w3-center"><header class="w3-container"><a href="'.$url.'" target="_blank"><img src="'.$image.'"/></a></header>';
        $return .= '<a href="'.$url.'" target="_blank" style="color: #D40606"><b>'.$merchantName.'</b></a><br>';
        $return .= '<a href="'.$url.'" target="_blank">'.$title.'</a><br><br>'; 
        
        if ($price == $originalPrice) {
            $return .= '<b>'.$originalPrice.'</b><br>';
        } 
        elseif ($price !== $originalPrice) {
            $return .= '<del>'.$originalPrice.'</del> <b>'.$price.'</b><br>';
        }
        if ($shipType == 'FREE') {
            $return .=  '<i>'.$shipType.' Shipping</i><br><br>';
        } else {
            $return .=  '<br>';
        }
            
        $return .= '<footer class="w3-container"><a class="w3-btn" style="background-color: #D40606;color: #FFFFFF" href="'.$url.'" target="_blank"><b>Go to store</b></a></footer></div>';
        $return .= '</body></html>';

        echo json_encode(array("request" => array("htmlString" => $return)));    
    }
}
else
{
    echo json_encode(array("error" => "out of geo"));  
}

?>

