<?php

class BAddress extends BusinessObject {
  
  private function _checkAccess() { return true; }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oAddress = new OAddress($this->_id);
      $data = $oAddress->getData();
      
      $returnData = array();
      $returnData['id'] = $data['address_id'];
      $returnData['street'] = $data['street'];
      $returnData['city'] = $data['city'];
      $returnData['region'] = $data['region'];
      $returnData['postalCode'] = $data['postal_code'];
      $returnData['state'] = $data['state'];
      $returnData['gpsLatitude'] = $data['gps_latitude'];
      $returnData['gpsLongitude'] = $data['gps_longitude'];
      
      $this->_data = $returnData;
      $this->_loaded = true;
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OAddress($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['street'])) $oData['street'] = $params['street'];
    if (isset($params['city'])) $oData['city'] = $params['city'];
    if (isset($params['region'])) $oData['region'] = $params['region'];
    if (isset($params['postalCode'])) $oData['postal_code'] = $params['postalCode'];
    if (isset($params['state'])) $oData['state'] = $params['state'];  
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    // aktualizace GPS souradnic
    if ((isset($params['street'])&&isset($params['city'])&&isset($params['state']))&&
        (($params['street']!=$this->_data['street'])||
         ($params['city']!=$this->_data['city'])||
         ($params['state']!=$this->_data['state']))) {
      $oData = $o->getData();
     
      $gps = new BGps;
      $coo = $gps->getCoordinatesFromAddress($oData['street'], $oData['city'], $oData['state']);
      #adump($coo);die;
      if (is_array($coo)) {
        $oData = array('gps_latitude'=>$coo['latitude'],'gps_longitude'=>$coo['longitude']);
        $o->setData($oData);
        $o->save();
      }
    }
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
        
    // kdyz se zaklada nova adresa jsou tyto atributy povinne
    if (!$this->_id) {
      #if (!isset($params['street']))  throw new ExceptionUserTextStorage('error.saveAddress_emptyStreet'); 
      #if (!isset($params['city'])) throw new ExceptionUserTextStorage('error.saveAddress_emptyCity'); 
      #if (!isset($params['postalCode'])) throw new ExceptionUserTextStorage('error.saveAddress_emptyPostalCode'); 
      #if (!isset($params['state'])) throw new ExceptionUserTextStorage('error.saveAddress_emptyState');
    }
    
    // tyto nesmi byt prazdny nikdy
    #if (isset($params['street'])&&!$params['street'])  throw new ExceptionUserTextStorage('error.saveAddress_emptyStreet'); 
    #if (isset($params['city'])&&!$params['city']) throw new ExceptionUserTextStorage('error.saveAddress_emptyCity'); 
    #if (isset($params['postalCode'])&&!$params['postalCode']) throw new ExceptionUserTextStorage('error.saveAddress_emptyPostalCode'); 
    #if (isset($params['state'])&&!$params['state']) throw new ExceptionUserTextStorage('error.saveAddress_emptyState');

    $this->_save($params);
  }
  
  public function delete() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
  
    $this->_delete();
  }
}

?>
