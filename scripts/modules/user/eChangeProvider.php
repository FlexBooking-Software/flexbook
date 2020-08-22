<?php

class ModuleChangeProvider extends ExecModule {

  protected function _userRun() {
    $newProvider = $this->_app->request->getParams('provider');
    
    // kontrola, jestli prihlaseny uzivatel muze mit nastaveneho vybraneho poskytovatele
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $newProvider, '%s=%s'));
    $s->setColumnsMask(array('userregistration_id','admin','reception','power_organiser','provider_name','customer_id'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    if (($row['admin']!='Y')&&($row['reception']!='Y')&&($row['power_organiser']!='Y')) throw new ExceptionUserTextStorage('error.changeProvider');
    
    $this->_app->auth->setActualProvider($newProvider, $row['provider_name'], $row['customer_id']);
    setcookie('actualProvider', $newProvider);
    
    // jeste musim upravit vyber centra
    $s = new SCenter;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $newProvider, '%s=%s'));
    $s->setColumnsMask(array('center_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.changeCenter');
    
    $this->_app->auth->setActualCenter($row['center_id']);
    setcookie('actualCenter', $row['center_id']);
    
    $validator = Validator::get('home','HomeValidator',true);
    
    return 'eMain';
  }
}

?>
