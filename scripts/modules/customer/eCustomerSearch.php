<?php

class ModuleCustomerSearch extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('customer','CustomerValidator');
    $validator->initValues();
    $registration = $validator->getVarValue('registration');
    if (!$email = $validator->getVarValue('email')) throw new ExceptionUserTextStorage('error.searchCustomer_missingEmail');
    $phone = $this->_app->request->getParams('searchPhone');
    
    $s = new SCustomer;
    $s->addStatement(new SqlStatementBi($s->columns['email'], $email, '%s=%s'));
    if ($phone) $s->addStatement(new SqlStatementBi($s->columns['phone'], $phone, '%s=%s'));
    $s->setColumnsMask(array('customer_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      if (!$phone) {
        // kdyz se vyhledava existujici zakaznik, musi se po emailu zadat i telefon
        $this->_app->dialog->set(array(
              'width'     => 400,
              'template'  => '
                  <div class="message">{__label.editCustomer_confirmSearch}</div>
                  <br/> {__label.editCustomer_searchPhone}: <input type="text" name="searchPhone" value=""/>
                  <div class="button">
                    <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editCustomer_confirmSearch}" onclick="document.getElementById(\'fb_eCustomerSave\').click();"/>
                  </div>',
            ));
        
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      }
      
      $validator = Validator::get('customer','CustomerValidator',true);
      $validator->loadData($row['customer_id']);
      $oldRegistration = $validator->getVarValue('registration');
      if (!is_array($oldRegistration)||!count($oldRegistration)) $validator->setValues(array('registration'=>$registration));
    } else throw new ExceptionUserTextStorage('error.searchCustomer_notFound');

    return 'vCustomerEdit';
  }
}

?>
