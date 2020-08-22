<?php

class ModuleInPageInit extends ExecModule {

  protected function _userRun() {
    $providerShort = $this->_app->request->getParams('id');
    if ($providerShort) {
      $s = new SProvider;
      $s->addStatement(new SqlStatementBi($s->columns['short_name'], $providerShort, '%s=%s'));
      $s->setColumnsMask(array('provider_id','customer_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $validator = Validator::get('inpage', 'InPageValidator', true);
        $validator->setValues(array(
            'providerId'    => $row['provider_id'],
            'customerId'    => $row['customer_id'],
            'providerName'  => $row['name'],
            ));
      } else {
        echo "Invalid provider!";
        die;
      }
    }
    
    if (isset($row['provider_id'])&&($row['provider_id']==14)) {
      $this->_app->language->setLanguage('en');
      
      $validator->setValues(array('portalData'=>array('section'=>'registration')));
      
      return 'vCzechTourismPortal';
    } else return 'vInPage';
  }
}

?>
