<?php

class BGps extends BusinessObject {
  private $_geocodeUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=';

  public function getCoordinatesFromAddress($street, $city, $state='CZ') {
    $coordinates = null;
    
    $addressString = sprintf('%s,%s,%s', $street, $city, $state);
    $addressString = urlencode(removeDiakritics($addressString,$this->_app->getCharset()));
    $request = $this->_geocodeUrl.$addressString;
    
    $this->_app->messages->addMessage('message','GOOGLE geocode request: '.$request, 100);
    
    $curl = new CURL;
    $curl->setHeader(false);
    $ret = $curl->get($request);
    #adump($ret);die;
    $xmlDOM = new DOMDocument('1.0');
    if (@$xmlDOM->loadXML($ret)) {
      $locationNode = $xmlDOM->getElementsByTagName('location');
      if ($locationNode->length) {
        $latitude=null; $longitude=null;
        
        $latNode = $locationNode->item(0)->getElementsByTagName('lat');
        if ($latNode->length) {
          $latitude = $latNode->item(0)->nodeValue;
        }
        $longNode = $locationNode->item(0)->getElementsByTagName('lng');
        if ($longNode->length) {
          $longitude = $longNode->item(0)->nodeValue;
        }
        
        if ($latitude&&$longitude) {
          $coordinates = array('latitude'=>$latitude,'longitude'=>$longitude);
          
          $this->_app->messages->addMessage('message',sprintf('GOOGLE geocode response: %s %s', $latitude, $longitude), 100);
        }
      }
    }
    
    return $coordinates;
  }
  
  public function getDistance($lat1, $lng1, $lat2, $lng2) {
    $pi80 = M_PI / 180;
    $lat1 *= $pi80;
    $lng1 *= $pi80;
    $lat2 *= $pi80;
    $lng2 *= $pi80;
     
    $r = 6372.797; // mean radius of Earth in km
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $r * $c;
     
    return $distance;
  }
}

?>
