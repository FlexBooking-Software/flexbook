<?php

class GuiEditResource extends GuiElement {
  
  private function _insertActive($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_active',
            'name' => 'active',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $data['active'],
            'userTextStorage' => false)), 'fi_active');
  }
  
  private function _insertCenterSelect($data) {
    $select = new SCenter;
    $select->setColumnsMask(array('center_id','description'));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['center_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    
    $gui = new GuiElement(array('template'=>'<div class="formItem">
        <label class="bold">{__label.editEvent_center}:</label>
        <input type="hidden" name="groupSaveItem[centerId]" value="0" />
        <input type="checkbox" class="inputCheckbox groupSaveCheck" name="groupSaveItem[centerId]" value="1" {groupSave_center_checked}/>
        {fi_center}
      </div>'));
    $gui->insert(new GuiFormSelect(array(
            'id' => 'fi_center',
            'name' => 'centerId',
            'classLabel' => 'bold',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $data['centerId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'userTextStorage' => false)), 'fi_center');
    
    $this->insert($gui, 'fi_center');
  }
  
  private function _insertProviderSelect($data) {
    if ($this->_app->auth->isAdministrator()) {
      if (!$data['groupSave']) {
        $select = new SProvider;
        $select->setColumnsMask(array('provider_id','name'));
        $ds = new SqlDataSource(new DataSourceSettings, $select);
        $this->insert(new GuiFormSelect(array(
                'id'              => 'fi_provider',
                'classLabel'      => 'bold',
                'name'            => 'providerId',
                'label'           => $this->_app->textStorage->getText('label.editResource_provider'),
                'dataSource'      => $ds,
                'value'           => $data['providerId'],
                'firstOption'     => Application::get()->textStorage->getText('label.select_choose'),
                'userTextStorage' => false)), 'fi_provider');
      } else $this->insertTemplateVar('fi_provider', '');
    } else {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" id="fi_provider" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    }
  }

  private function _insertOrganiserSelect($data) {
    $select = new SUserRegistration;
    $select->addStatement(new SqlStatementBi($select->columns['organiser'], $select->columns['power_organiser'], "(%s='Y' OR %s='Y')"));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->addOrder(new SqlStatementAsc($select->columns['lastname']));
    $select->addOrder(new SqlStatementAsc($select->columns['firstname']));
    $select->addOrder(new SqlStatementAsc($select->columns['email']));
    $select->setColumnsMask(array('user','fullname_reversed'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $selectParams = array(
      'id' => 'fi_organiser',
      'name' => 'organiserId',
      'dataSource' => $ds,
      'value' => $data['organiserId'],
      'showDiv' => false,
      'userTextStorage' => false);
    $selectParams['firstOption'] = Application::get()->textStorage->getText('label.select_choose');
    $this->insert(new GuiFormSelect($selectParams), 'fi_organiser');

    /*if ($this->_app->auth->haveRight('commodity_admin', $this->_app->auth->getActualProvider())) {
      $this->insert($g = new GuiElement(array('template'=>'
        <div id="fi_organiserDiv">
          &nbsp;
          <input class="button" type="button" onclick="return mySubmit(\'fb_eEventSave\',\'fi_nextAction\',\'newOrganiser\');" name="action_eNewOrganiser" value="{__button.new_m}" />
        </div>
        ')), 'fi_organiser');
    }*/
  }

  private function _insertProfileSelect($data) {
    $select = new SAvailabilityProfile;
    $select->setColumnsMask(array('availabilityprofile_id','name'));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_availProfile',
            'name' => 'availProfile',
            'dataSource' => $ds,
            'value' => $data['availProfile'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_availProfile');
    
    $select = new SAvailabilityExceptionProfile;
    $select->setColumnsMask(array('availabilityexceptionprofile_id','name'));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_availExProfile',
            'name' => 'availExProfile',
            'dataSource' => $ds,
            'value' => $data['availExProfile'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_availExProfile');
    
    $select = new SUnitProfile;
    $select->setColumnsMask(array('unitprofile_id','name'));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_unitProfile',
            'name' => 'unitProfile',
            'dataSource' => $ds,
            'value' => $data['unitProfile'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_unitProfile');
  }

  private function _insertFeAllowedPayment($data) {
    if (in_array('credit',$data['feAllowedPayment'])) $this->insertTemplateVar('feAllowedPayment_credit_checked', 'checked="yes"', false);
    else $this->insertTemplateVar('feAllowedPayment_credit_checked', '');
    if (in_array('ticket',$data['feAllowedPayment'])) $this->insertTemplateVar('feAllowedPayment_ticket_checked', 'checked="yes"', false);
    else $this->insertTemplateVar('feAllowedPayment_ticket_checked', '');
    if (in_array('online',$data['feAllowedPayment'])) $this->insertTemplateVar('feAllowedPayment_online_checked', 'checked="yes"', false);
    else $this->insertTemplateVar('feAllowedPayment_online_checked', '');
  }
  
  private function _insertReservationConditionSelect($data) {
    $select = new SReservationCondition;
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('reservationcondition_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_reservationCondition',
            'name' => 'reservationConditionId',
            'dataSource' => $ds,
            'value' => $data['reservationConditionId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_reservationCondition');
  }
  
  private function _insertNotificationTemplateSelect($data) {
    $select = new SNotificationTemplate;
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['target'], "%s='COMMODITY'"));
    $select->setColumnsMask(array('notificationtemplate_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_notificationTemplate',
            'name' => 'notificationTemplateId',
            'dataSource' => $ds,
            'value' => $data['notificationTemplateId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_notificationTemplate');
  }

  private function _insertDocumentTemplateSelect($data) {
    $select = new SDocumentTemplate;
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['target'], "%s='COMMODITY'"));
    $select->setColumnsMask(array('documenttemplate_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_documentTemplate',
      'name' => 'documentTemplateId',
      'dataSource' => $ds,
      'value' => $data['documentTemplateId'],
      'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
      'showDiv' => false,
      'userTextStorage' => false)), 'fi_documentTemplate');
  }
  
  private function _insertPriceListSelect($data) {
    $select = new SPriceList;
    $select->setColumnsMask(array('pricelist_id','name'));
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_priceList',
            'name' => 'priceList',
            'dataSource' => $ds,
            'value' => $data['priceList'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_priceList');
  }
  
  private function _insertButton($data) {
    if ($this->_app->auth->haveRight('commodity_admin', $this->_app->auth->getActualProvider())) {
      if ($data['id']) {
        if ($data['groupSave']) {
          $this->insertTemplateVar('fb_resourceSave',
            sprintf('<input class="fb_eSave" id="fb_eResourceSave" type="submit" name="action_eResourceSave" value="%s" />',
                    $this->_app->textStorage->getText('button.editResource_groupSave')), false);
          $this->insertTemplateVar('fb_newReservation', '');
        } else {
          $this->insertTemplateVar('fb_resourceSave',
            sprintf('<input class="fb_eSave" id="fb_eResourceSave" type="submit" name="action_eResourceSave" value="%s" />',
                    $this->_app->textStorage->getText('button.editResource_save')), false);
          $this->insertTemplateVar('fb_newReservation',
            sprintf('<input class="fb_eSave" id="fb_eResourceReservation" type="submit" name="action_eResourceReservation" value="%s" />',
                    $this->_app->textStorage->getText('button.grid_newReservation')), false);
        }
      } else {
        $this->insertTemplateVar('fb_resourceSave',
            sprintf('<input class="fb_eSave" id="fb_eResourceSave" type="submit" name="action_eResourceSave" value="%s" />',
                    $this->_app->textStorage->getText('button.editResource_save')), false);
        $this->insertTemplateVar('fb_newReservation', '');
      }

      if ($this->_app->auth->isAdministrator()||$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) {
        $this->insertTemplateVar('fb_newAvailProfile', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newAvailProfile\');" name="action_eNewAccountType" value="%s" />', $this->_app->textStorage->getText('button.new_f')), false);
        $this->insertTemplateVar('fb_newAvailExProfile', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newAvailExProfile\');" name="action_eNewAccountType" value="%s" />', $this->_app->textStorage->getText('button.new_f')), false);
        $this->insertTemplateVar('fb_newUnitProfile', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newUnitProfile\');" name="action_eNewUnitProfile" value="%s" />', $this->_app->textStorage->getText('button.new_p')), false);
        $this->insertTemplateVar('fb_newReservationCondition', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newReservationCondition\');" name="action_eNewReservationCondition" value="%s" />', $this->_app->textStorage->getText('button.new_p')), false);
        $this->insertTemplateVar('fb_newNotificationTemplate', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newNotificationTemplate\');" name="action_eNewNotificationTemplate" value="%s" />', $this->_app->textStorage->getText('button.new_p')), false);
        $this->insertTemplateVar('fb_newPriceList', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newPriceList\');" name="action_eNewPriceList" value="%s" />', $this->_app->textStorage->getText('button.new_t')), false);
        $this->insertTemplateVar('fb_newAccountType', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eResourceSave\',\'fi_nextAction\',\'newAccountType\');" name="action_eNewAccountType" value="%s" />', $this->_app->textStorage->getText('button.new_f')), false);
      } else {
        $this->insertTemplateVar('fb_newAvailProfile', '');
        $this->insertTemplateVar('fb_newAvailExProfile', '');
        $this->insertTemplateVar('fb_newUnitProfile', '');
        $this->insertTemplateVar('fb_newReservationCondition', '');
        $this->insertTemplateVar('fb_newNotificationTemplate', '');
        $this->insertTemplateVar('fb_newPriceList', '');
        $this->insertTemplateVar('fb_newAccountType', '');
      }
    } else {
      $this->insertTemplateVar('fb_resourceSave', '');
      $this->insertTemplateVar('fb_newReservation',
            sprintf('<input class="fb_eSave" id="fb_eResourceReservation" type="submit" name="action_eResourceReservation" value="%s" />',
                    $this->_app->textStorage->getText('button.grid_newReservation')), false);

      $this->insertTemplateVar('fb_newAvailProfile', '');
      $this->insertTemplateVar('fb_newAvailExProfile', '');
      $this->insertTemplateVar('fb_newUnitProfile', '');
      $this->insertTemplateVar('fb_newReservationCondition', '');
      $this->insertTemplateVar('fb_newNotificationTemplate', '');
      $this->insertTemplateVar('fb_newPriceList', '');
      $this->insertTemplateVar('fb_newAccountType', '');
    }
    
    $this->insertTemplateVar('fb_listReservation', '');
  }
  
  private function _insertAccountTypeSelect($data) {
    $select = new SProviderAccountType;
    if ($data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->setColumnsMask(array('provideraccounttype_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_accountType',
            'name' => 'accountTypeId',
            'dataSource' => $ds,
            'value' => $data['accountTypeId'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_accountType');
  }

  private function _insertPortalSelect($data) {
    $select = new SPortal;
    $select->setColumnsMask(array('portal_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_portal',
            'name' => 'portal[]',
            'multiple' => true,
            'dataSource' => $ds,
            'value' => $data['portal'],
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_portal');
  }
  
  private function _insertAttribute($data) {
    $hash = new HashDataSource(new DataSourceSettings, array(
      ''            => ' ',
      'add'         => $this->_app->textStorage->getText('label.editResource_tagAddOnly'),
      'replaceAdd'  => $this->_app->textStorage->getText('label.editResource_tagReplaceAdd'),
    ));
    
    $select = new GuiFormSelect(array(
      'id'          => 'fi_attributeEditSelect',
      'name'        => 'groupSaveItem[attribute]',
      'dataSource'  => $hash,
      'value'       => ifsetor($data['groupSaveItem']['attribute'],-1),
    ));
    
    $templateResource = sprintf('<div class="attributeTitle">%s</div>
                                %s
                                %s
                                <div class="gridTable"><table id="fi_resourceAttributeTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('label.editResourceAttribute_resource'),
                        $data['groupSave']?$select->render():'',
                        $this->_app->auth->haveRight('commodity_admin', $data['providerId'])?'<input type="button" class="inputSubmit button" id="fi_newResourceAttribute_button" value="'.$this->_app->textStorage->getText('button.editResource_newAttribute').'"/>':'',
                        $this->_app->textStorage->getText('label.editResourceAttribute_category'),
                        $this->_app->textStorage->getText('label.editResourceAttribute_name'),
                        $this->_app->textStorage->getText('label.editResourceAttribute_type'),
                        $this->_app->textStorage->getText('label.editResourceAttribute_value'));
    if (is_array($data['attribute'])&&count($data['attribute'])) {
      $i = 0;
      foreach ($data['attribute'] as $key=>$reg) {
        if ($this->_app->auth->haveRight('commodity_admin', $data['providerId'])) {
          $action = sprintf('[<a href="#" id="fi_attributeEdit">%s</a>][<a href="#" id="fi_attributeRemove">%s</a>]', $this->_app->textStorage->getText('button.grid_change'), $this->_app->textStorage->getText('button.grid_remove'));
        } else $action = '';
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" class="attributeHidden" name="newResourceAttribute[%d]" value="attributeId:~%s;category:~%s;name:~%s;type:~%s;allowedValues:~%s;valueId:~%s;value:~%s;disabled:~%s"/>',
                                $reg['attributeId'],$reg['attributeId'],$reg['category'],$reg['name'],$reg['type'],$reg['allowedValues'],ifsetor($reg['valueId']),$reg['value'],$reg['disabled']);
        $value = $reg['value'];
        if (!strcmp($reg['type'],'FILE')) {
          if (isset($reg['valueId'])) $value = sprintf('<a target="_file" href="getfile.php?id=%s">%s</a>', $reg['valueId'], $reg['value']);
        } elseif (!strcmp($reg['type'],'TEXTAREA')) {
          $value = substr($value, 0, 30);
          if (strlen($reg['value'])>30) $value .= ' ...';
        }
        $templateResource .= sprintf('<tr class="%s" id="%d"><td id="category">%s</td><td id="name">%s</td><td id="typeHtml">%s</td><td>%s</td>
                              <input type="hidden" id="type" value="%s"/>
                              <input type="hidden" id="value" value="%s"/>
                              <input type="hidden" id="allowedValues" value="%s"/>
                              <input type="hidden" id="disabled" value="%s"/>
                              <td class="tdAction">%s</td>
                              %s</tr>',
                             $class,
                             $reg['attributeId'],
                             $reg['category'],
                             $reg['name'],
                             $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$reg['type']),
                             $value,
                             $reg['type'],
                             $reg['value'],
                             $reg['allowedValues'],
                             $reg['disabled'],
                             $action,
                             $formVariable);
      }
    }
    $templateResource .= '</tbody></table></div>';
    
    $select = new GuiFormSelect(array(
      'id'          => 'fi_reservationAttributeEditSelect',
      'name'        => 'groupSaveItem[reservationAttribute]',
      'dataSource'  => $hash,
      'value'       => ifsetor($data['groupSaveItem']['reservationAttribute'],-1),
    ));
    
    $templateReservation = sprintf('<div class="attributeTitle">%s</div>
                                %s
                                %s
                                <div class="gridTable"><table id="fi_reservationAttributeTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('label.editResourceAttribute_reservation'),
                        $data['groupSave']?$select->render():'',
                        $this->_app->auth->haveRight('commodity_admin', $data['providerId'])?'<input type="button" class="inputSubmit button" id="fi_newReservationAttribute_button" value="'.$this->_app->textStorage->getText('button.editResource_newAttribute').'"/>':'',
                        $this->_app->textStorage->getText('label.editResourceAttribute_category'),
                        $this->_app->textStorage->getText('label.editResourceAttribute_name'),
                        $this->_app->textStorage->getText('label.editResourceAttribute_type'),
                        $this->_app->textStorage->getText('label.editResourceAttribute_mandatory'));
    if (is_array($data['reservationAttribute'])&&count($data['reservationAttribute'])) {
      $i = 0;
      foreach ($data['reservationAttribute'] as $key=>$reg) {
        if ($this->_app->auth->haveRight('commodity_admin', $data['providerId'])) {
           $action = sprintf('[<a href="#" id="fi_attributeRemove">%s</a>]', $this->_app->textStorage->getText('button.grid_remove'));
        } else $action = '';
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" class="attributeHidden" name="newReservationAttribute[%d]" value="attributeId:~%s;category:~%s;name:~%s;type:~%s;allowedValues:~%s;mandatory:~%s;value:~;disabled:~%s"/>',
                                $reg['attributeId'],$reg['attributeId'],$reg['category'],$reg['name'],$reg['type'],$reg['allowedValues'],$reg['mandatory'],$reg['disabled']);
        $templateReservation .= sprintf('<tr class="%s" id="%d"><td id="category">%s</td><td id="name">%s</td><td id="typeHtml">%s</td><td id="mandatory">%s</td>
                              <input type="hidden" id="type" value="%s"/>
                              <input type="hidden" id="allowedValues" value="%s"/>
                              <input type="hidden" id="disabled" value="%s"/>
                              <td class="tdAction">%s</td>
                              %s</tr>',
                             $class,
                             $reg['attributeId'],
                             $reg['category'],
                             $reg['name'],
                             $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$reg['type']),
                             ($reg['mandatory']=='Y')?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $reg['type'],
                             $reg['allowedValues'],
                             $reg['disabled'],
                             $action,
                             $formVariable);
      }
    }
    $templateReservation .= '</tbody></table></div>';
    
    $this->insertTemplateVar('fi_attribute', $templateResource, false);
    $this->insertTemplateVar('fi_attribute', $templateReservation, false);
  }
  
  protected function _insertReservation($data) {
    $this->insert(new GuiListReservation('listResourceReservation'), 'fi_reservation');    
  }
  
  protected function _evaluateGroupSave($data) {
    if ($data['groupSave']) {
      $this->_app->document->addJavascript("
          $(document).ready(function() {
            $('.groupSaveCheck').show();
            $('.formItemExternalId').hide();
            $('#a-tab-5').hide();
            $('.token-input-list-facebook').css('margin-left','18px'); 
          })");
      
      foreach ($data['groupSaveItem'] as $name=>$value) {
        if ($value) $this->insertTemplateVar('groupSave_'.$name.'_checked', 'checked="yes"', false);
        else $this->insertTemplateVar('groupSave_'.$name.'_checked', '');
      }  
      
      $hash = new HashDataSource(new DataSourceSettings, array(
                ''            => ' ',
                'add'         => $this->_app->textStorage->getText('label.editResource_tagAddOnly'),
                'replaceAdd'  => $this->_app->textStorage->getText('label.editResource_tagReplaceAdd'),
                ));
      $this->insert(new GuiFormSelect(array(
            'classInput'  => 'tagEdit',
            'id'          => 'fi_tagEditSelect',
            'name'        => 'groupSaveItem[tag]',
            'dataSource'  => $hash,
            'value'       => ifsetor($data['groupSaveItem']['tag'],-1),
            )), 'fi_tagEditSelect');
    } else {
      $this->insertTemplateVar('fi_tagEditSelect', '');
    }
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ResourceEdit.html');

    $validator = Validator::get('resource', 'ResourceValidator');
    $data = $validator->getValues();
    #adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editResource_titleNew'));
    } else {
      if (!$data['groupSave']) $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editResource_titleExisting').' '.$data['name']);
      else {
        $s = new SResource;
        $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', $data['id'])));
        $s->addOrder(new SqlStatementAsc($s->columns['name']));
        $s->setColumnsMask(array('name'));
        $res = $this->_app->db->doQuery($s->toString());
        $name = '';
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($name) $name .= ', ';
          $name .= $row['name'];
        }
        $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editResource_titleGroupExisting').' '.$name);
      }
    }
    
    $editJS = $readonlyTag = '';
    if (!$this->_app->auth->haveRight('commodity_admin', $data['providerId'])) {
      $editJS .= "$('#editResource .formItem').find('input, textarea, button, select').attr('disabled','disabled');
                  $('.token-input-input-token-facebook').hide();";
      $readonlyTag = ', readonly:true ';
    }
    
    $tokenValues = '';
    foreach (explode(',',$data['tag']) as $tag) {
      if (!$tag) continue;
      
      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['name'], $tag, '%s=%s'));
      $s->setColumnsMask(array('tag_id', 'name'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $id = ifsetor($row['tag_id']);
        
      $tokenValues .= "$('#fi_tag').tokenInput('add', {id: '$id', name: '$tag'$readonlyTag});";
    }
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/ResourceEdit.js',
              array(
                  'ajaxUrl'           => $AJAX['adminUrl'],
                  'ajaxUrlPath'       => dirname($AJAX['adminUrl']),
                  'tagTokenInit'      => $tokenValues,
                  'additionalEditJS'  => $editJS, 
                  'language'          => $this->_app->language->getLanguage(),
                  'provider'          => $this->_app->auth->getActualProvider(),
                  ));
    
    $this->_insertProviderSelect($data);
    $this->_insertCenterSelect($data);
    $this->_insertProfileSelect($data);
    $this->_insertReservationConditionSelect($data);
    $this->_insertNotificationTemplateSelect($data);
    $this->_insertDocumentTemplateSelect($data);
    $this->_insertPriceListSelect($data);
    $this->_insertOrganiserSelect($data);
    $this->_insertAccountTypeSelect($data);
    $this->_insertFeAllowedPayment($data);
    $this->_insertActive($data);
    $this->_insertPortalSelect($data);
    $this->_insertAttribute($data);
    $this->_insertReservation($data);
    $this->_insertButton($data);
    
    $this->_evaluateGroupSave($data);
  }
}

?>
