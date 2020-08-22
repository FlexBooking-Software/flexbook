<?php

class GuiEditCustomer extends GuiElement {

  private function _insertStateSelect($data) {
    $select = new SState;
    $select->setColumnsMask(array('code','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_state',
            'name' => 'state',
            'dataSource' => $ds,
            'value' => $data['state'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_state');
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_invoiceState',
            'name' => 'invoiceState',
            'dataSource' => $ds,
            'value' => $data['invoiceState'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_invoiceState');
    $this->insert(new GuiFormSelect(array(
            'id' => 'editCenter_state',
            'name' => 'state',
            'classInput' => 'text ui-widget-content ui-corner-all',
            'dataSource' => $ds,
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_centerState');
  }

  private function _insertProvider($data) {
    if (!$this->_app->auth->isAdministrator()) {
      if (($data['provider']=='Y')&&$data['myData']) {
        $this->insertTemplateVar('fi_providerCheckbox', '<input type="checkbox" class="inputCheckbox" id="fi_providerCheck" name="no_value" value="Y" checked="yes" disabled="yes"/>', false);
      } else {
        $this->insertTemplateVar('fi_providerCheckbox', '<input type="checkbox" class="inputCheckbox" id="fi_providerCheck" name="no_value" value="Y" disabled="yes"/>', false);
      }

      $this->insertTemplateVar('readonlyShortName', 'readonly="yes"', false);
    } else {
      $this->insertTemplateVar('fi_providerCheckbox', '<input type="hidden" name="provider" value="N"/>', false);
      if ($data['provider']=='Y') {
        $this->insertTemplateVar('fi_providerCheckbox', '
                <input type="checkbox" class="inputCheckbox" id="fi_providerCheck" name="provider" value="Y" checked="yes"/>', false);
      } else {
        $this->insertTemplateVar('fi_providerCheckbox', '
                <input type="checkbox" class="inputCheckbox" id="fi_providerCheck" name="provider" value="Y" />', false);
      }

      $this->insertTemplateVar('readonlyShortName', '');
    }

    if ($data['invoiceOther']=='Y') $this->insertTemplateVar('invoiceOtherChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('invoiceOther', '');
  }

  private function _insertEmployee($data) {
    $gui = new GuiElement(array('template'=>'
                  <input type="button" class="inputSubmit button" id="fi_newEmployee_button" value="{__button.editCustomer_newEmployee}"/>
                  <div class="gridTable"><table id="fi_employeeTable">
                    <thead><tr><th>{__label.editCustomer_employeeName}</th><th>{__label.editCustomer_employeeEmail}</th><th>{__label.editCustomer_employeeCredit}</th><th>&nbsp;</th></tr></thead><tbody>
                      {employee}
                    </tbody>
                    </table>
                  </div>
                  '));

    $template = '';
    if (is_array($data['employee'])&&count($data['employee'])) {
      $i = 0;
      foreach ($data['employee'] as $key=>$employee) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $formVariable = sprintf('<input type="hidden" name="newEmployee[%d]" value="employeeId:%s;userId:%s;fullname:%s;email:%s;creditAccess:%s"/>',
                                $key,$employee['employeeId'],$employee['userId'],$employee['fullname'],$employee['email'],$employee['creditAccess']);
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="employeeFullname">%s</td><td id="employeeEmail">%s</td><td id="employeeCredit">%s</td>
                              <input type="hidden" id="employeeUser" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_employeeEdit">%s</a>][<a href="#" id="fi_employeeRemove">%s</a>]</td>%s</tr>',
                             $class,
                             $key,
                             $employee['employeeId'],
                             $employee['fullname'],
                             $employee['email'],
                             $employee['creditAccess']=='Y'?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $employee['userId'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
    }
    $gui->insertTemplateVar('employee', $template, false);

    $this->insert($gui, 'fi_employee');

    /*$select = new SUser;
    if (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementMono($select->columns['registration_provider'], sprintf('%%s IN (%s)', implode(',',$this->_app->auth->getAllowedProvider('user_admin')))));
    $select->addOrder(new SqlStatementAsc($select->columns['fullname']));
    $select->setColumnsMask(array('user_id_with_name_email','fullname'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'editEmployee_userId',
            'inputClass' => 'text ui-widget-content ui-corner-all',
            'name' => 'user',
            'dataSource' => $ds,
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_user');*/
  }

  private function _insertCoworker($data) {
    $gui = new GuiElement(array('template'=>'
                  <input type="hidden" id="fi_authUser_id" value="{userId}"/>
                  <input type="button" class="inputSubmit button" id="fi_newCoworker_button" value="{__button.editCustomer_newCoworker}"/>
                  <div class="gridTable"><table id="fi_coworkerTable">
                    <thead><tr><th>{__label.editCustomer_coworkerName}</th><th>{__label.editCustomer_coworkerEmail}</th><th>{__label.editCustomer_coworkerRole}</th><th>&nbsp;</th></tr></thead><tbody>
                      {coworker}
                    </tbody>
                    </table>
                  </div>
                  '));

    $gui->insertTemplateVar('userId', $this->_app->auth->getUserId());

    $template = '';
    if (is_array($data['coworker'])&&count($data['coworker'])) {
      $i = 0;
      foreach ($data['coworker'] as $key=>$coworker) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $role = '';
        if ($coworker['admin']=='Y') { $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_admin'); }
        if ($coworker['supervisor']=='Y') { if ($role) $role .= ', '; $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_supervisor'); }
        if ($coworker['reception']=='Y') { if ($role) $role .= ', '; $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_reception'); }
        if ($coworker['organiser']=='Y') { if ($role) $role .= ', '; $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_organiser'); }
        if ($coworker['powerOrganiser']=='Y') { if ($role) $role .= ', '; $role .= $this->_app->textStorage->getText('label.editCustomerCoworker_powerOrganiser'); }

        $formVariable = sprintf('<input type="hidden" name="newCoworker[%d]" value="coworkerId:%s;userId:%s;fullname:%s;email:%s;admin:%s;supervisor:%s;reception:%s;organiser:%s;powerOrganiser:%s;roleCenter:%s"/>',
                                $key,$coworker['coworkerId'],$coworker['userId'],$coworker['fullname'],$coworker['email'],
                                $coworker['admin'],$coworker['supervisor'],$coworker['reception'],$coworker['organiser'],$coworker['powerOrganiser'],$coworker['roleCenter']);
        if ($coworker['userId']!=$this->_app->auth->getUserId()) $removeAction = sprintf('[<a href="#" id="fi_coworkerRemove">%s</a>]', $this->_app->textStorage->getText('button.grid_remove'));
        else $removeAction = '';
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="coworkerFullname">%s</td><td id="coworkerEmail">%s</td><td id="coworkerRole">%s</td>
                              <input type="hidden" id="coworkerUser" value="%s"/>
                              <input type="hidden" id="coworkerRoleCenter" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_coworkerEdit">%s</a>]%s</td>%s</tr>',
                             $class,
                             $key,
                             $coworker['coworkerId'],
                             $coworker['fullname'],
                             $coworker['email'],
                             $role,
                             $coworker['userId'],
                             $coworker['roleCenter'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $removeAction,
                             $formVariable);
      }
    }
    $gui->insertTemplateVar('coworker', $template, false);

    $this->insert($gui, 'fi_coworker');
  }

  private function _insertCenter($data) {
    $template = sprintf('<input type="button" class="inputSubmit button" id="fi_newCenter_button" value="%s"/><div class="gridTable"><table id="fi_centerTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('button.editCustomer_newCenter'),
                        $this->_app->textStorage->getText('label.editCustomer_centerId'),
                        $this->_app->textStorage->getText('label.editCustomer_centerName'),
                        $this->_app->textStorage->getText('label.editCustomer_street'),
                        $this->_app->textStorage->getText('label.editCustomer_city'),
                        $this->_app->textStorage->getText('label.editCustomer_postalCode'),
                        $this->_app->textStorage->getText('label.editCustomer_state'));
    if (is_array($data['center'])&&count($data['center'])) {
      $i = 0;
      foreach ($data['center'] as $key=>$center) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $formVariable = sprintf('<input type="hidden" name="newCenter[%d]" value="centerId:%s;name:%s;street:%s;city:%s;region:%s;postalCode:%s;state:%s;paymentInfo:%s"/>',
                                $key,$center['centerId'],$center['name'],$center['street'],$center['city'],$center['region'],$center['postalCode'],$center['state'],$center['paymentInfo']);
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td>%s</td><td id="centerName">%s</td><td id="centerStreet">%s</td><td id="centerCity">%s</td><td id="centerPostalCode">%s</td><td id="centerState">%s</td>
                              <input type="hidden" id="centerPaymentInfo" value="%s"/>
                              <input type="hidden" id="centerRegion" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_centerEdit">%s</a>][<a href="#" id="fi_centerRemove">%s</a>]</td>%s</tr>',
                             $class,
                             $key,
                             $center['centerId'],
                             $center['centerId'],
                             $center['name'],
                             $center['street'],
                             $center['city'],
                             $center['postalCode'],
                             $center['state'],
                             $center['paymentInfo'],
                             $center['region'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
    }
    $template .= '</tbody></table></div>';

    $this->insertTemplateVar('fi_center', $template, false);
  }

  private function _insertRegistration($data) {
    $template = sprintf('<input type="button" class="inputSubmit button" id="fi_newRegistration_button" value="%s"/><div class="gridTable"><table id="fi_registrationTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('button.editCustomer_newRegistration'),
                        $this->_app->textStorage->getText('label.editCustomerRegistration_provider'),
                        $this->_app->textStorage->getText('label.editCustomerRegistration_timestamp'),
                        $this->_app->textStorage->getText('label.editCustomerRegistration_advertising'),
                        $this->_app->textStorage->getText('label.editCustomerRegistration_credit'));
    if (is_array($data['registration'])&&count($data['registration'])) {
      $i = 0;
      foreach ($data['registration'] as $key=>$reg) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $formVariable = sprintf('<input type="hidden" class="registrationHidden" name="newRegistration[%d]" value="registrationId:%s;providerId:%s;providerName:%s;timestamp:%s;advertising:%s;credit:%s"/>',
                                $key,$reg['registrationId'],$reg['providerId'],$reg['providerName'],$reg['timestamp'],$reg['advertising'],$reg['credit']);
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="providerName">%s</td><td id="timestamp">%s</td><td id="advertising">%s</td><td id="credit">%s %s</td>
                              <input type="hidden" id="providerId" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_registrationEdit">%s</a>][<a href="#" id="fi_registrationRemove">%s</a>]</td>%s</tr>',
                             $class,
                             $key,
                             $reg['registrationId'],
                             $reg['providerName'],
                             $reg['timestamp'],
                             ($reg['advertising']=='Y')?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $reg['credit'], $this->_app->textStorage->getText('label.currency_CZK'),
                             $reg['providerId'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
    }
    $template .= '</tbody></table></div>';

    $this->insertTemplateVar('fi_registration', $template, false);

    $select = new SProvider;
    if (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementMono($select->columns['provider_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider(null,'list'))));
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

  private function _alterElementsVisibility($data) {
    if ($data['fromReservation']) {
      $this->insertTemplatevar('employeeHidden', 'class="hidden"', false);
      $this->insertTemplateVar('providerHidden', 'class="hidden"', false);
      $this->insertTemplateVar('fi_searchButton', sprintf('
              <input class="fb_eSave inputSubmit ui-button" type="submit"
               name="action_eCustomerSearch" value="%s" />', $this->_app->textStorage->getText('button.editCustomer_search')), false);
    } else {
      if ($data['myData']) {
        $this->insertTemplatevar('employeeHidden', 'class="hidden"', false);
        $this->insertTemplatevar('registrationHidden', 'class="hidden"', false);
        $this->insertTemplatevar('providerHidden', '');
        $this->insertTemplatevar('providerCheckboxHidden', 'hidden', false);
        $this->insertTemplateVar('fi_searchButton', '');
      } else {
        $this->insertTemplatevar('employeeHidden', '');
        $this->insertTemplatevar('registrationHidden', '');
        if (!$data['id']) {
          $this->insertTemplateVar('fi_searchButton', sprintf('
              <input class="fb_eSave inputSubmit ui-button" type="submit"
               name="action_eCustomerSearch" value="%s" />', $this->_app->textStorage->getText('button.editCustomer_search')), false);
        } else {
          $this->insertTemplateVar('fi_searchButton', '');
        }
        if (!$this->_app->auth->isAdministrator()) {
          $this->insertTemplatevar('providerHidden', 'class="hidden"', false);
        } else {
          $this->insertTemplatevar('providerHidden', '');
          $this->insertTemplatevar('providerCheckboxHidden', '');
        }
      }
    }
  }

  private function _insertAttribute($data) {
    $templateUser = sprintf('<div class="attributeTitle">%s</div><input type="button" class="inputSubmit button" id="fi_newUserAttribute_button" value="%s"/><div class="gridTable"><table class="sorted_table" id="fi_userAttributeTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody id="fi_userAttributeTableBody">',
                        $this->_app->textStorage->getText('label.editCustomerAttribute_customer'),
                        $this->_app->textStorage->getText('button.editCustomer_newAttribute'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_category'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_shortName'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_name'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_mandatory'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_type'));
    $templateUser .= "\n";
    if (is_array($data['userAttribute'])&&count($data['userAttribute'])) {
      $i = 0;
      foreach ($data['userAttribute'] as $key=>$reg) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" id="attributeString" class="attributeHidden" name="newUserAttribute[%d]" value="attributeId_:_%s_;_short_:_%s_;_name_:_%s_;_url_:_%s_;_restricted_:_%s_;_mandatory_:_%s_;_category_:_%s_;_type_:_%s_;_allowedValues_:_%s_;_disabled_:_%s_;_applicable_:_%s_;_applicableType_:_%s"/>
                                 <input type="hidden" id="attributeUrl" value="%s"/>
                                 <input type="hidden" id="attributeApplicable" value="%s"/>
                                 <input type="hidden" id="attributeApplicableType" value="%s"/>
                                 <input type="hidden" id="attributeRestricted" value="%s"/>
                                 <input type="hidden" id="attributeType" value="%s"/>
                                 <input type="hidden" id="attributeAllowedValues" value="%s"/>
                                 <input type="hidden" id="attributeDisabled" value="%s"/>',
                                $key,$reg['attributeId'],$reg['short'],$reg['name'],$reg['url'],$reg['restricted'],$reg['mandatory'],$reg['category'],$reg['type'],$reg['allowedValues'],$reg['disabled'],$reg['applicable'],$reg['applicableType'],
                                $reg['url'],$reg['applicable'],$reg['applicableType'],$reg['restricted'],$reg['type'],$reg['allowedValues'],$reg['disabled']);
        $templateUser .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="attributeCategory">%s</td><td id="attributeShort">%s</td><td id="attributeName">%s</td><td id="attributeMandatory">%s</td><td id="attributeTypeHTML">%s</td>
                              <td class="tdAction">[<a href="#" id="fi_attributeEdit">%s</a>][<a href="#" id="fi_attributeRemove">%s</a>]<span id="attributeSpecialAction">%s</span></td>%s</tr>',
                             $class,
                             $key,
                             $reg['attributeId'],
                             $reg['category'],
                             $reg['short'],
                             $reg['name'],
                             ($reg['mandatory']=='Y')?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$reg['type']),
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             ($reg['disabled']=='Y')?sprintf('[<a href="#" id="fi_attributeEnable">%s</a>]',$this->_app->textStorage->getText('button.grid_restore')):sprintf('[<a href="#" id="fi_attributeDisable">%s</a>]',$this->_app->textStorage->getText('button.grid_disable')),
                             $formVariable);
        $templateUser .= "\n";
      }
    }
    $templateUser .= '</tbody></table></div>';

    $templateCommodity = sprintf('<div class="attributeTitle">%s</div><input type="button" class="inputSubmit button" id="fi_newCommodityAttribute_button" value="%s"/><div class="gridTable"><table class="sorted_table" id="fi_commodityAttributeTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody id="fi_commodityAttributeTableBody">',
                        $this->_app->textStorage->getText('label.editCustomerAttribute_commodity'),
                        $this->_app->textStorage->getText('button.editCustomer_newAttribute'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_category'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_shortName'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_name'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_type'));
    $templateCommodity .= "\n";
    if (is_array($data['commodityAttribute'])&&count($data['commodityAttribute'])) {
      $i = 0;
      foreach ($data['commodityAttribute'] as $key=>$reg) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" id="attributeString" class="attributeHidden" name="newCommodityAttribute[%d]" value="attributeId_:_%s_;_short_:_%s_;_name_:_%s_;_url_:_%s_;_restricted_:_%s_;_category_:_%s_;_type_:_%s_;_allowedValues_:_%s_;_disabled_:_%s_;_applicable_:_%s_;_applicableType_:_%s"/>
                                 <input type="hidden" id="attributeUrl" value="%s"/>
                                 <input type="hidden" id="attributeApplicable" value="%s"/>
                                 <input type="hidden" id="attributeApplicableType" value="%s"/>
                                 <input type="hidden" id="attributeRestricted" value="%s"/>
                                 <input type="hidden" id="attributeType" value="%s"/>
                                 <input type="hidden" id="attributeAllowedValues" value="%s"/>
                                 <input type="hidden" id="attributeDisabled" value="%s"/>',
                                $key,$reg['attributeId'],$reg['short'],$reg['name'],$reg['url'],$reg['restricted'],$reg['category'],$reg['type'],$reg['allowedValues'],$reg['disabled'],$reg['applicable'],$reg['applicableType'],
                                $reg['url'],$reg['applicable'],$reg['applicableType'],$reg['restricted'],$reg['type'],$reg['allowedValues'],$reg['disabled']);
        $templateCommodity .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="attributeCategory">%s</td><td id="attributeShort">%s</td><td id="attributeName">%s</td><td id="attributeTypeHTML">%s</td>
                              <td class="tdAction">[<a href="#" id="fi_attributeEdit">%s</a>][<a href="#" id="fi_attributeRemove">%s</a>]<span id="attributeSpecialAction">%s</span></td>%s</tr>',
                             $class,
                             $key,
                             $reg['attributeId'],
                             $reg['category'],
                             $reg['short'],
                             $reg['name'],
                             $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$reg['type']),
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             ($reg['disabled']=='Y')?sprintf('[<a href="#" id="fi_attributeEnable">%s</a>]',$this->_app->textStorage->getText('button.grid_restore')):sprintf('[<a href="#" id="fi_attributeDisable">%s</a>]',$this->_app->textStorage->getText('button.grid_disable')),
                             $formVariable);
        $templateCommodity .= "\n";
      }
    }
    $templateCommodity .= '</tbody></table></div>';

    $templateReservation = sprintf('<div class="attributeTitle">%s</div><input type="button" class="inputSubmit button" id="fi_newReservationAttribute_button" value="%s"/><div class="gridTable"><table class="sorted_table" id="fi_reservationAttributeTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody id="fi_reservationAttributeTableBody">',
                        $this->_app->textStorage->getText('label.editCustomerAttribute_reservation'),
                        $this->_app->textStorage->getText('button.editCustomer_newAttribute'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_category'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_shortName'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_name'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_mandatory'),
                        $this->_app->textStorage->getText('label.editCustomerAttribute_type'));
    $templateReservation .= "\n";
    if (is_array($data['reservationAttribute'])&&count($data['reservationAttribute'])) {
      $i = 0;
      foreach ($data['reservationAttribute'] as $key=>$reg) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" id="attributeString" class="attributeHidden" name="newReservationAttribute[%d]" value="attributeId_:_%s_;_short_:_%s_;_name_:_%s_;_url_:_%s_;_restricted_:_%s_;_mandatory_:_%s_;_category_:_%s_;_type_:_%s_;_allowedValues_:_%s_;_disabled_:_%s_;_applicable_:_%s_;_applicableType_:_%s"/>
                                 <input type="hidden" id="attributeUrl" value="%s"/>
                                 <input type="hidden" id="attributeApplicable" value="%s"/>
                                 <input type="hidden" id="attributeApplicableType" value="%s"/> 
                                 <input type="hidden" id="attributeRestricted" value="%s"/>
                                 <input type="hidden" id="attributeType" value="%s"/>
                                 <input type="hidden" id="attributeAllowedValues" value="%s"/>
                                 <input type="hidden" id="attributeDisabled" value="%s"/>',
                                $key,$reg['attributeId'],$reg['short'],$reg['name'],$reg['url'],$reg['restricted'],$reg['mandatory'],$reg['category'],$reg['type'],$reg['allowedValues'],$reg['disabled'],$reg['applicable'],$reg['applicableType'],
                                $reg['url'],$reg['applicable'],$reg['applicableType'],$reg['restricted'],$reg['type'],$reg['allowedValues'],$reg['disabled']);
        $templateReservation .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="attributeCategory">%s</td><td id="attributeShort">%s</td><td id="attributeName">%s</td><td id="attributeMandatory">%s</td><td id="attributeTypeHTML">%s</td>
                              <td class="tdAction">[<a href="#" id="fi_attributeEdit">%s</a>][<a href="#" id="fi_attributeRemove">%s</a>]<span id="attributeSpecialAction">%s</span></td>%s</tr>',
                             $class,
                             $key,
                             $reg['attributeId'],
                             $reg['category'],
                             $reg['short'],
                             $reg['name'],
                             ($reg['mandatory']=='Y')?$this->_app->textStorage->getText('label.yes'):$this->_app->textStorage->getText('label.no'),
                             $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$reg['type']),
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             ($reg['disabled']=='Y')?sprintf('[<a href="#" id="fi_attributeEnable">%s</a>]',$this->_app->textStorage->getText('button.grid_restore')):sprintf('[<a href="#" id="fi_attributeDisable">%s</a>]',$this->_app->textStorage->getText('button.grid_disable')),
                             $formVariable);
        $templateReservation .= "\n";
      }
    }
    $templateReservation .= '</tbody></table></div>';

    $this->insertTemplateVar('fi_attribute', $templateUser, false);
    $this->insertTemplateVar('fi_attribute', $templateCommodity, false);
    $this->insertTemplateVar('fi_attribute', $templateReservation, false);
  }

  private function _insertFile($data) {
    $template = sprintf('<input type="button" class="inputSubmit button" id="fi_newFile_button" value="%s"/><div class="gridTable"><table id="fi_fileTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('button.editCustomer_newFile'),
                        $this->_app->textStorage->getText('label.editCustomer_fileShort'),
                        $this->_app->textStorage->getText('label.editCustomer_fileName'),
                        $this->_app->textStorage->getText('label.editCustomer_fileSource'),
                        $this->_app->textStorage->getText('label.editCustomer_fileSize')
                        );
    if (is_array($data['file'])&&count($data['file'])) {
      $i = 0;
      foreach ($data['file'] as $key=>$file) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $formVariable = sprintf('<input type="hidden" name="newFile[%d]" value="id:%s;short:%s;name:%s;length:%s;sourceName:%s;sourceId:%s;sourceHash:%s;newSource:%s"/>',
                                $key,$file['id'],$file['short'],$file['name'],$file['length'],$file['sourceName'],$file['sourceId'],ifsetor($file['sourceHash']),ifsetor($file['newSource']));
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td id="short">%s</td><td id="name">%s</td><td>%s</td><td id="length">%s</td>
                              <input type="hidden" id="sourceId" value="%s"/>
                              <td class="tdAction">[<a href="#" id="fi_fileEdit">%s</a>][<a href="#" id="fi_fileRemove">%s</a>]</td>%s</tr>',
                             $class,
                             $key,
                             $file['id'],
                             $file['short'],
                             $file['name'],
                             !isset($file['sourceHash'])||!$file['sourceHash']?sprintf('<span id="source">%s</span>', $file['sourceName']):sprintf('<a target="_file" href="getfile.php?id=%s"><span id="source">%s</span></a>', $file['sourceHash'], $file['sourceName']),
                             $file['length'],
                             $file['sourceId'],
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
    }
    $template .= '</tbody></table></div>';

    $this->insertTemplateVar('fi_file', $template, false);
  }

  private function _insertNotificationTemplateSelect($data) {
    if ($data['providerId']) {
      $select = new SNotificationTemplate;
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['target'], "%s='GENERAL'"));
      $select->setColumnsMask(array('notificationtemplate_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_notificationTemplate',
              'name' => 'notificationTemplateId',
              'label' => $this->_app->textStorage->getText('label.editCustomer_notification'),
              'dataSource' => $ds,
              'value' => $data['notificationTemplateId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_notificationTemplate');
    } else $this->insertTemplateVar('fi_notificationTemplate', '');
  }

  private function _insertVatSelect($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_vat',
      'name' => 'vat',
      'showDiv' => false,
      'dataSource' => $ds,
      'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
      'value' => $data['vat'],
      'userTextStorage' => false)), 'fi_vat');
  }

  private function _insertSmtpSelect($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('tls'=>$this->_app->textStorage->getText('label.editCustomer_smtpSecureTls'),'ssl'=>$this->_app->textStorage->getText('label.editCustomer_smtpSecureSsl')));
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_smtpSecure',
      'name' => 'smtpSecure',
      'showDiv' => false,
      'dataSource' => $ds,
      'firstOption' => $this->_app->textStorage->getText('label.noValue'),
      'value' => $data['smtpSecure'],
      'userTextStorage' => false)), 'fi_smtpSecure');
  }

  private function _insertInvoice($data) {
    global $NOTIFICATION;

    if (!in_array($this->_app->auth->getUsername(),explode(',',$NOTIFICATION['adminEmail']))) {
      $this->insertTemplateVar('invoiceReadonly','readonly="1"',true);

      $this->insertTemplateVar('generateSelect','');
      $this->insertTemplateVar('generateButton','');
      $this->insertTemplateVar('invoiceAccountFrom_id','');
    } else {
      $this->insertTemplateVar('invoiceReadonly','');
      $this->insertTemplateVar('invoiceAccountFrom_id', 'id="fi_invoiceAccountFrom"', false);

      if ($data['id']) {
        $hash = array();
        $start = $this->_app->regionalSettings->decreaseDate(date('Y-m-01'), 0, 12);
        $end = date('Y-m-d');
        $months = explode(',', $this->_app->textStorage->getText('label.calendar_monthLabels'));
        while ($start <= $end) {
          $hash[substr($start, 0, 7)] = sprintf('%s %s', substr($start, 0, 4), str_replace("'", '', $months[substr($start, 5, 2) - 1]));
          $start = $this->_app->regionalSettings->increaseDate($start, 0, 1);
        }
        krsort($hash);
        $ds = new HashDataSource(new DataSourceSettings, $hash);
        $this->insert(new GuiFormSelect(array(
          'name' => 'invoicePeriod',
          'showDiv' => false,
          'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
          'dataSource' => $ds,
        )), 'generateSelect');

        $this->insertTemplateVar('generateButton', sprintf('<input class="fb_eSave" id="fb_eCustomerGenerateInvoice" type="submit" name="action_eCustomerSave?generateInvoice=1" value="%s" />',
          $this->_app->textStorage->getText('button.editCustomer_generateInvoice')), false);
      } else {
        $this->insertTemplateVar('generateSelect','');
        $this->insertTemplateVar('generateButton','');
      }
    }

    /*$this->insert(new GuiFormSelect(array(
      'id' => 'fi_invoiceReservationPricePaid',
      'name' => 'invoiceReservationPricePaid',
      'showDiv' => false,
      'dataSource' => new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.editCustomer_invoiceReservationPriceFee_paid'),'N'=>$this->_app->textStorage->getText('label.editCustomer_invoiceReservationPriceFee_realised'))),
      'value' => $data['invoiceReservationPricePaid'],
      'readonly' => !in_array($this->_app->auth->getUsername(),$INVOICE['admin']),
      'userTextStorage' => false,
    )), 'fi_invoiceReservationPricePaid');*/
    $this->insertTemplateVar('fi_invoiceReservationPricePaid', sprintf('<input type="hidden" name="invoiceReservationPricePaid" value="N"/>%s',
      $this->_app->textStorage->getText('label.editCustomer_invoiceReservationPriceFee_realised')), false);

    $this->insert(new GuiListProviderInvoice, 'invoiceList');
  }

  private function _insertCenterCheckboxes($data) {
    if ($data['providerId']) {
      $s = new SCenter;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('center_id', 'name'));
      $res = $this->_app->db->doQuery($s->toString());
      $template = sprintf('<input type="checkbox" name="coworkerCenter[]" meaning="roleCenter" id="editCoworker_center_ALL" value="ALL" class="inputCheckbox text ui-widget-content ui-corner-all">
          <label for="reception">%s</label><br/>', $this->_app->textStorage->getText('label.validFor_allCenter'));
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $template .= sprintf('<input type="checkbox" name="coworkerCenter[]" meaning="roleCenter" id="editCoworker_center_%d" value="%d" class="inputCheckbox text ui-widget-content ui-corner-all">
          <label for="reception">%s</label>', $row['center_id'], $row['center_id'], $row['name']);
      }

      $this->insertTemplateVar('fi_centerCheckboxes', $template, false);
    } else $this->insertTemplateVar('fi_centerCheckboxes', '');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/CustomerEdit.html');

    $validator = Validator::get('customer', 'CustomerValidator');
    $data = $validator->getValues();
    //adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editCustomer_titleNew'));
    } else {
      if ($data['myData']) $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editCustomer_titleMyData'));
      else $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editCustomer_titleExisting'));
    }

    $this->_alterElementsVisibility($data);

    $this->_insertStateSelect($data);
    $this->_insertEmployee($data);
    $this->_insertCoWorker($data);
    $this->_insertRegistration($data);
    $this->_insertProvider($data);
    $this->_insertCenter($data);
    $this->_insertCenterCheckboxes($data);
    $this->_insertAttribute($data);
    $this->_insertFile($data);
    $this->_insertNotificationTemplateSelect($data);
    $this->_insertVatSelect($data);
    $this->_insertInvoice($data);
    $this->_insertSmtpSelect($data);

    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/CustomerEdit.js', array(
      'url'               => $AJAX['adminUrl'],
      'urlDir'            => dirname($AJAX['url']),
      'providerId'        => $data['providerId'],
      'subaccountEnabled' => BCustomer::getProviderSettings($data['providerId'],'userSubaccount')=='Y'?1:0,
    ));
  }
}

?>
