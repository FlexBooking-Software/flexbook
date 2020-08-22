<?php

class GuiEditUser extends GuiElement {

  private function _insertStateSelect($data) {
    $s = new SState;
    $s->setColumnsMask(array('code','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_state',
            'name' => 'state',
            'dataSource' => $ds,
            'value' => $data['state'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_state');
  }

  private function _insertReservationConditionSelect($data) {
    if ($data['subaccountUser']=='Y') $this->insertTemplateVar('fi_reservationCondition', '');
    else {
      $select = new SReservationCondition;
      if ($p=$this->_app->auth->getActualProvider()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $p, '%s=%s'));
      $select->setColumnsMask(array('reservationcondition_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);

      $gui = new GuiElement(array('template'=>'
        <div class="formItem">
          <label>{__label.editUser_reservationCondition}:</label>
          {fi_reservationCondition}
        </div>
        <br/>'));
      $gui->insert(new GuiFormSelect(array(
        'id' => 'fi_reservationCondition',
        'name' => 'reservationConditionId',
        'dataSource' => $ds,
        'value' => $data['reservationConditionId'],
        'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
        'showDiv' => false,
        'userTextStorage' => false)), 'fi_reservationCondition');

      $this->insert($gui, 'fi_reservationCondition');
    }

  }
  
  private function _insertAdminElements($data) {
    if ($this->_app->auth->isAdministrator()) {
      $g = new GuiElement(array('template'=>'
            <div class="formItem">
              <label for="fi_username">{__label.editUser_username}:</label>
              <input class="longText" id="fi_username" type="text" name="username" value="{username}"/>
            </div>
            <div id="fi_passwordDiv">
              <div class="formItem">
                <label for="fi_password">{__label.editUser_password}:</label>
                <input class="longText" id="fi_password" type="password" autocomplete="off" name="passwordEdit" value="{password}"/>
              </div>
              <div class="formItem">
                <label for="fi_retypePassword">{__label.editUser_retypePassword}:</label>
                <input class="longText" id="fi_retypePassword" type="password" autocomplete="off" name="retypePasswordEdit" value="{retypePassword}"/>
              </div>
            </div>
            <br/>
            <div class="formItem">
              <label for="fi_facebookId">{__label.editUser_facebookId}:</label>
              <input id="fi_facebookId" type="text" name="facebookId" value="{facebookId}"/>
              <input type="submit" class="socialButton button" name="action_eFacebookCall?type=assignToUser" value="{__button.editUser_assignFacebook}"/>
            </div>
            <div class="formItem">
              <label for="fi_googleId">{__label.editUser_googleId}:</label>
              <input id="fi_googleId" type="text" name="googleId" value="{googleId}"/>
              <input type="submit" class="socialButton button" name="action_eGoogleCall?type=assignToUser" value="{__button.editUser_assignGoogle}"/>
            </div>
            <div class="formItem">
              <label for="fi_twitterId">{__label.editUser_twitterId}:</label>
              <input id="fi_twitterId" type="text" name="twitterId" value="{twitterId}"/>
              <input type="submit" class="socialButton button" name="action_eTwitterCall?type=assignToUser" value="{__button.editUser_assignTwitter}"/>
            </div>
            <br/>
            <div class="formItem">
              <label for="fi_admin">{__label.editUser_admin}:</label>
              <input type="hidden" name="admin" value="N" />
              <input id="fi_admin" class="inputCheckbox" type="checkbox" name="admin" value="Y" {adminCheckbox}/>
            </div>
            <br/>'));
      
      $g->insertTemplateVar('username', $data['username']);
      $g->insertTemplateVar('password', $data['passwordEdit']);
      $g->insertTemplateVar('retypePassword', $data['retypePasswordEdit']);
      $g->insertTemplateVar('facebookId', $data['facebookId']);
      $g->insertTemplateVar('googleId', $data['googleId']);
      $g->insertTemplateVar('twitterId', $data['twitterId']);
      if ($data['admin']=='Y') $g->insertTemplateVar('adminCheckbox', 'checked="yes"', false);
      else $g->insertTemplateVar('adminCheckbox', '');
      
      $this->insert($g, 'fi_admin');
      
      $this->insertTemplateVar('emailReadonly', '');
    } else {
      $this->insertTemplateVar('fi_admin', '');
      
      if ($data['id']&&($data['subaccountUser']!='Y')) $this->insertTemplateVar('emailReadonly', 'readonly="yes"', false);
      else $this->insertTemplateVar('emailReadonly', '');
    }
  }
  
  private function _insertRegistration($data) {
    $template = '';
    if ($this->_app->auth->isAdministrator()) {
      $template .= sprintf('<input type="button" class="inputSubmit button" id="fi_newRegistration_button" value="%s"/>',$this->_app->textStorage->getText('button.editUserRegistration_new'));
    }
    $template .= sprintf('<div class="gridTable"><table id="fi_registrationTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('label.editUserRegistration_provider'),
                        $this->_app->textStorage->getText('label.editUserRegistration_timestamp'),
                        $this->_app->textStorage->getText('label.editUserRegistration_advertising'),
                        $this->_app->textStorage->getText('label.editUserRegistration_credit'),
                        $this->_app->textStorage->getText('label.editUserRegistration_role'));
    if (is_array($data['registration'])&&count($data['registration'])) {
      $i = 0;
      foreach ($data['registration'] as $key=>$reg) {
        if (!$this->_app->auth->isAdministrator()&&($reg['providerId']!=$this->_app->auth->getActualProvider())) continue;
        
        $role = '';
        if ($reg['admin']=='Y') { $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_admin'); }
        if ($reg['reception']=='Y') { if ($role) $role .= ', '; $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_reception'); }
        if ($reg['organiser']=='Y') { if ($role) $role .= ', '; $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_organiser'); }
        
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $formVariable = sprintf('<input type="hidden" class="registrationHidden" name="newRegistration[%d]" value="registrationId:%s;providerId:%s;providerName:%s;timestamp:%s;advertising:%s;credit:%s;organiser:%s;admin:%s;reception:%s"/>',
                                $key,$reg['registrationId'],$reg['providerId'],$reg['providerName'],$reg['timestamp'],$reg['advertising'],$reg['credit'],$reg['organiser'],$reg['admin'],$reg['reception']);
        $removeAction = sprintf('[<a href="#" id="fi_registrationRemove">%s</a>]', $this->_app->textStorage->getText('button.grid_remove'));
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="providerName">%s</td><td id="timestamp">%s</td><td id="advertising">%s</td><td id="credit">%s %s</td><td id="role">%s</td>
                              <input type="hidden" id="providerId" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_registrationEdit">%s</a>]%s</td>%s</tr>',
                             $class,
                             $key,
                             $reg['registrationId'],
                             $reg['providerName'],
                             $reg['timestamp'],
                             ($reg['advertising']=='Y')?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $reg['credit'], $this->_app->textStorage->getText('label.currency_CZK'),
                             $role,
                             $reg['providerId'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->auth->isAdministrator()?$removeAction:'',
                             $formVariable);
      }
    }
    $template .= '</tbody></table></div>';
    
    $this->insertTemplateVar('fi_registration', $template, false);
    
    $select = new SProvider;
    if (!$this->_app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementMono($select->columns['provider_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
    }
    $select->setColumnsMask(array('provider_id_with_name','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'editRegistration_providerId',
            'inputClass' => 'text ui-widget-content ui-corner-all',
            'name' => 'provider',
            'dataSource' => $ds,
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_provider');
  }
  
  private function _insertAttribute($data) {
    $template = sprintf('<input type="button" class="inputSubmit button" id="fi_newAttribute_button" value="%s"/><div class="gridTable"><table id="fi_attributeTable">
                        <thead><tr>%s<th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('button.editUser_newAttribute'),
                        ($this->_app->auth->isProviderMultiple()?'<th>'.$this->_app->textStorage->getText('label.editUserAttribute_provider').'</th>':''),
                        $this->_app->textStorage->getText('label.editUserAttribute_category'),
                        $this->_app->textStorage->getText('label.editUserAttribute_name'),
                        $this->_app->textStorage->getText('label.editUserAttribute_type'),
                        $this->_app->textStorage->getText('label.editUserAttribute_value'));
    if (is_array($data['attribute'])&&count($data['attribute'])) {
      $i = 0;
      foreach ($data['attribute'] as $key=>$reg) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" class="attributeHidden" name="newAttribute[%d]" value="attributeId:~%s;providerId:~%s;providerName:~%s;category:~%s;name:~%s;mandatory:~%s;type:~%s;allowedValues:~%s;valueId:~%s;value:~%s;disabled:~%s"/>',
                                $reg['attributeId'],$reg['attributeId'],$reg['providerId'],$reg['providerName'],$reg['category'],$reg['name'],$reg['mandatory'],$reg['type'],$reg['allowedValues'],ifsetor($reg['valueId']),$reg['value'],$reg['disabled']);
        $value = $reg['value'];
        if (!strcmp($reg['type'],'FILE')) {
          if (isset($reg['valueId'])) $value = sprintf('<a target="_file" href="getfile.php?id=%s">%s</a>', $reg['valueId'], $reg['value']);
        } elseif (!strcmp($reg['type'],'TEXTAREA')) {
          $value = substr($value, 0, 30);
          if (strlen($reg['value'])>30) $value .= ' ...';
        }
        $template .= sprintf('<tr class="%s" id="%d">%s<td id="category">%s</td><td id="name">%s</td><td id="typeHtml">%s</td><td>%s</td>
                              <input type="hidden" id="providerId" value="%s"/>
                              <input type="hidden" id="type" value="%s"/>
                              <input type="hidden" id="value" value="%s"/>
                              <input type="hidden" id="allowedValues" value="%s"/>
                              <input type="hidden" id="disabled" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_attributeEdit">%s</a>][<a href="#" id="fi_attributeRemove">%s</a>]
                              %s</tr>',
                             $class,
                             $reg['attributeId'],
                             ($this->_app->auth->isProviderMultiple()?'<td id="providerName">'.$reg['providerName'].'</td>':''),
                             $reg['category'],
                             $reg['name'],
                             $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$reg['type']),
                             //($reg['mandatory']=='Y')?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $value,
                             $reg['providerId'],
                             $reg['type'],
                             $reg['value'],
                             $reg['allowedValues'],
                             $reg['disabled'],
                             $this->_app->textStorage->getText('button.grid_change'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
    }
    $template .= '</tbody></table></div>';
    
    $this->insertTemplateVar('fi_attribute', $template, false);
  }
  
  private function _insertMyDataElements($data) {
    if ($data['myProfile']) {
      $this->insertTemplateVar('saveLabel', $this->_app->textStorage->getText('button.editUser_saveProfile'));
      $this->insert(new GuiFormButton(array(
            'id'        => 'fb_editPassword',
            'label'     => $this->_app->textStorage->getText('button.changePassword_change'),
            'action'    => 'ePasswordEdit',
            'showDiv'   => false,
            )), 'fb_password');
  
      $g = new GuiElement(array('template'=>'
            <div class="formItem">
              <label for="fi_facebookId">{__label.editUser_facebookId}:</label>
              <input id="fi_facebookId" type="text" name="facebookId" value="{facebookId}"/>
              <input type="submit" class="socialButton button" name="action_eFacebookCall?type=assignToUser" value="{__button.editUser_assignFacebook}"/>
            </div>
            <div class="formItem">
              <label for="fi_googleId">{__label.editUser_googleId}:</label>
              <input id="fi_googleId" type="text" name="googleId" value="{googleId}"/>
              <input type="submit" class="socialButton button" name="action_eGoogleCall?type=assignToUser" value="{__button.editUser_assignGoogle}"/>
            </div>
            <div class="formItem">
              <label for="fi_twitterId">{__label.editUser_twitterId}:</label>
              <input id="fi_twitterId" type="text" name="twitterId" value="{twitterId}"/>
              <input type="submit" class="socialButton button" name="action_eTwitterCall?type=assignToUser" value="{__button.editUser_assignTwitter}"/>
            </div>
            '));
      
      $g->insertTemplateVar('facebookId', $data['facebookId']);
      $g->insertTemplateVar('googleId', $data['googleId']);
      $g->insertTemplateVar('twitterId', $data['twitterId']);
      
      $this->insert($g, 'fi_admin');
    } else {
      $this->insertTemplateVar('saveLabel', $this->_app->textStorage->getText('button.editUser_save'));
  
      if ($data['id']&&($data['subaccountUser']!='Y')) {
        $this->insert(new GuiFormButton(array(
            'id'        => 'fb_sendPassword',
            'label'     => $this->_app->textStorage->getText('button.editUser_sendPassword'),
            'action'    => 'eUserSendPassword',
            'showDiv'   => false,
            )), 'fb_password');
      } else {
        $this->insertTemplateVar('fb_password', '');
      }
    }

    if ($data['id']&&($data['subaccountUser']!='Y')) {
      if (BCustomer::getProviderSettings($this->_app->auth->getActualProvider(),'userSubaccount')=='Y') {
        $this->insert(new GuiFormButton(array(
          'id'        => 'fb_newSubaccount',
          'label'     => $this->_app->textStorage->getText('button.editUser_newSubaccount'),
          'action'    => 'eUserEdit?subaccount=1&new=1',
          'showDiv'   => false,
        )), 'fb_subaccount');
      } else $this->insertTemplateVar('fb_subaccount', '');
    } else {
      $this->insertTemplateVar('fb_subaccount', '');
    }
  }
  
  protected function _insertReservation($data) {
    $this->insert(new GuiListReservation('listUserReservation'), 'fi_reservation');

    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $data['id'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>=NOW()'));
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
    $s->setColumnsMask(array('sum_places'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    $substitute = ifsetor($row['sum_places'],0);
    if ($substitute) {
      $gui = new GuiElement(array('template'=>'
                                    <br/>
                                    <b>{__label.editEvent_substitute}:</b>
                                    {children}'));
      $gui->insert(new GuiListEventSubstitute('listUserSubstitute', array('user'=>$data['id'])));
      $this->insert($gui, 'fi_substitute');
    } else $this->insertTemplateVar('fi_substitute', '');
  }

  protected function _insertDocument($data) {
    $this->insert(new GuiListDocument('listUserDocument'), 'fi_document');
  }

  private function _insertCustomer($data) {
    if ($data['myProfile']) {
      $this->insertTemplateVar('fi_customerTab', sprintf('<li><a href="#tab-5">%s</a></li>', $this->_app->textStorage->getText('label.editUser_customer')), false);
      $this->insert($g=new GuiElement(array('template'=>'
            <div id="tab-5">
              <div id="fi_customerList">
                {children}
              </div>
            </div>')), 'fi_customerContent');
      $g->insert(new GuiListCustomer('listUserCustomer'));
    } else {
      $this->insertTemplateVar('fi_customerTab', '');
      $this->insertTemplateVar('fi_customerContent', '');
    }
  }

  private function _insertValidation($data) {
    if ($data['validationUrl']) {
      $g = new GuiElement(array('template'=>'
            <div class="formItem">
              <label>{__label.editUser_validationUrl}:</label>
              <label class="asInput">{url}</label>
            </div>
            <br/>'));
      $g->insertTemplateVar('url', $data['validationUrl']);
      $this->insert($g, 'fi_validationUrl');

      $this->insert(new GuiFormButton(array(
        'id'        => 'fb_validate',
        'label'     => $this->_app->textStorage->getText('button.editUser_validate'),
        'action'    => 'eUserValidate',
        'showDiv'   => false,
      )), 'fb_validate');
    } else {
      $this->insertTemplateVar('fi_validationUrl', '');
      $this->insertTemplateVar('fb_validate', '');
    }
  }

  private function _insertSubaccountSelect($data) {
    if (BCustomer::getProviderSettings($this->_app->auth->getActualProvider(),'userSubaccount')=='Y') {
      $gui = new GuiElement(array('template'=>sprintf('
        <br/>
        <input type="hidden" name="subaccount" value="%d"/>
        <div class="formItem">
          <label>{__label.editUser_subaccount}:</label>
          <input type="hidden" name="subaccountUser" value="N"/>
          <input type="checkbox" name="subaccountUser" id="fi_subaccountUser" value="Y" {checked} style="width: auto;"/>
          {select}
        </div>', $this->_app->request->getParams('subaccount'))));
      $gui->insertTemplateVar('checked', $data['subaccountUser']=='Y'?'checked="checked"':'', false);

      $select = new SUser;
      if ($this->_app->auth->getActualProvider()) $select->addStatement(new SqlStatementBi($select->columns['registration_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementBi($select->columns['user_id'], $data['id'], '%s<>%s'));
      $select->addStatement(new SqlStatementMono($select->columns['parent_user'], '%s IS NULL'));
      $select->addOrder(new SqlStatementAsc($select->columns['lastname']));
      $select->setColumnsMask(array('user_id','fullname_reversed_with_email'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $gui->insert(new GuiFormSelect(array(
        'id' => 'fi_parent',
        'name' => 'parent',
        'dataSource' => $ds,
        'value' => $data['parent'],
        'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
        'userTextStorage' => false,
        'showDiv' => false)), 'select');

      $this->insert($gui, 'fi_subaccount');
    } else $this->insertTemplateVar('fi_subaccount', '');
  }

  private function _insertSubaccountTab($data) {
    if ((BCustomer::getProviderSettings($this->_app->auth->getActualProvider(),'userSubaccount')=='Y')&&$data['id']&&($data['subaccountUser']!='Y')) {
      $this->insertTemplateVar('fi_subaccountTab', sprintf('<li><a href="#tab-3.1">%s</a></li>', $this->_app->textStorage->getText('label.editUser_subaccounts')), false);
      $this->insert($g=new GuiElement(array('template'=>'
            <div id="tab-3.1">
              <div id="fi_subaccountList">
                {children}
              </div>
            </div>')), 'fi_subaccountContent');
      $g->insert(new GuiListUser('listUserSubaccount'));
    } else {
      $this->insertTemplateVar('fi_subaccountTab', '');
      $this->insertTemplateVar('fi_subaccountContent', '');
    }
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/UserEdit.html');

    $subaccountEdit = $this->_app->request->getParams('subaccount');

    $validator = Validator::get($subaccountEdit?'userSubaccount':'user','UserValidator');
    $data = $validator->getValues();
    #adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if ($data['myProfile']) {
      $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.editUser_titleProfile'));
    } elseif (!$data['id']) {
      $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.editUser_titleNew'));
    } else {
      $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.editUser_titleExisting'));
    }
    
    $this->_insertStateSelect($data);
    $this->_insertReservationConditionSelect($data);
    $this->_insertAdminElements($data);
    $this->_insertValidation($data);
    $this->_insertMyDataElements($data);
    $this->_insertRegistration($data);
    $this->_insertAttribute($data);
    $this->_insertReservation($data);
    $this->_insertDocument($data);
    #$this->_insertCustomer($data);
    $this->_insertSubaccountSelect($data);
    $this->_insertSubaccountTab($data);
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/UserEdit.js',
                                                     array('ajaxUrl' => $AJAX['adminUrl'],
                                                           'url' => dirname($AJAX['url']),
                                                           'allowedProvider' => $this->_app->auth->isAdministrator()?$this->_app->auth->getAllowedProvider(null,'list'):$this->_app->auth->getActualProvider(),
                                                           'language' => $this->_app->language->getLanguage(),
                                                           'userType' => $subaccountEdit?'SUBACCOUNT':'USER',
                                                          ));
  }
}

?>
