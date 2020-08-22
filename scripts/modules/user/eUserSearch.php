<?php

class ModuleUserSearch extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('user','UserValidator');
    $validator->initValues();
    $registration = $validator->getVarValue('registration');
    if (!$email = $validator->getVarValue('email')) throw new ExceptionUserTextStorage('error.searchUser_missingEmail');
    $phone = $this->_app->request->getParams('searchPhone');
    
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['email'], strtolower($email), '%s=%s'));
    if ($phone) $s->addStatement(new SqlStatementBi($s->columns['phone'], $phone, '%s=%s'));
    $s->setColumnsMask(array('user_id','phone'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      if (!$phone) {
        // kdyz se vyhledava existujici zakaznik, musi se po emailu zadat i telefon
        $this->_app->dialog->set(array(
              'width'     => 400,
              'template'  => sprintf('
                  <div class="message">{__label.editUser_confirmSearch}</div>
                  <br/> {__label.editUser_searchPhone}: <input type="text" name="searchPhone" value="%s"/>
                  <div class="button">
                    <input type="button" class="ui-button inputSubmit" name="save" value="{__button.editUser_confirmSearch}" onclick="document.getElementById(\'fb_eUserSearch\').click();"/>
                  </div>', substr($row['phone'],0,-3)),
            ));
        
        $this->_app->response->addParams(array('backwards'=>1));
        return 'eBack';
      }
      
      // po vyhledani uzivatele pridam registraci aktualniho poskytovatele, pokuz uz neni pridana
      $validator->loadData($row['user_id']);
      $oldRegistration = $validator->getVarValue('registration');
      $regExists = false;
      foreach ($oldRegistration as $reg) {
        if ($reg['providerId']==$this->_app->auth->getActualProvider()) {
          $regExists = true;
          break;
        }
      }
      if (!$regExists) {
        $s = new SProvider;
        $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $this->_app->auth->getActualProvider(), '%s=%s'));
        $s->setColumnsMask(array('provider_id','name'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);

        $oldRegistration[] = array(
            'registrationId'        => null,
            'providerId'            => $row['provider_id'],
            'providerName'          => $row['name'],
            'timestamp'             => date('d.m.Y'),
            'advertising'           => 'Y',
            'credit'                => 0,
            'organiser'             => 'N',
            'admin'                 => 'N',
            'reception'             => 'N',
            );
        $validator->setValues(array('registration'=>$oldRegistration));
      }
    } else throw new ExceptionUserTextStorage('error.searchUser_notFound');

    return 'vUserEdit';
  }
}

?>
