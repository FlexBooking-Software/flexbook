<?php

class GuiSettingsGeneral extends GuiElement {
  
  private function _insertBadgePhotoSelect($data) {
    $select = new SAttribute;
    $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    $select->setColumnsMask(array('attribute_id','category'));
    $select->addOrder(new SqlStatementAsc($select->columns['category']));
    $select->addOrder(new SqlStatementAsc($select->columns['sequence']));
    $res = $this->_app->db->doQuery($select->toString());
    $hash = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $name = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $name[$row1['lang']] = $row1['name']; }
      $name = ifsetor($name[$this->_app->language->getLanguage()], array_values($name)[0]);
      
      $label = sprintf('%s (%s)', $name, $row['category']);
      $hash[$row['attribute_id']] = $label;  
    }
    
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_badgePhoto',
            'name' => 'badgePhoto',
            'label' => $this->_app->textStorage->getText('label.settingsGeneral_badgePhoto'),
            'dataSource' => $ds,
            'value' => $data['badgePhoto'],
            'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
            'userTextStorage' => false)), 'fi_badgePhoto');
  }

  private function _insertReservationConditionSelect($data) {
    $select = new SReservationCondition;
    if ($p=$this->_app->auth->getActualProvider()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $p, '%s=%s'));
    $select->setColumnsMask(array('reservationcondition_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_userReservationCondition',
      'name' => 'userReservationCondition',
      'dataSource' => $ds,
      'value' => $data['userReservationCondition'],
      'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
      'showDiv' => false,
      'userTextStorage' => false)), 'fi_userReservationCondition');
  }

  private function _insertDocumenttemplateSelect($data) {
    $select = new SDocumentTemplate();
    if ($p=$this->_app->auth->getActualProvider()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $p, '%s=%s'));
    $select->setColumnsMask(array('documenttemplate_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_documenttemplate',
      'name' => 'documenttemplate',
      'dataSource' => $ds,
      'value' => $data['documenttemplate'],
      'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
      'showDiv' => false,
      'userTextStorage' => false)), 'fi_documenttemplate');
  }

  private function _insertUserUnique($data) {
    $userSubaccount = BCustomer::getProviderSettings($data['providerId'],'userSubaccount')=='Y';

    $available = array(
      'USER' => array(
        'firstname' => $this->_app->textStorage->getText('label.editProviderUserUnique_firstname'),
        'lastname'  => $this->_app->textStorage->getText('label.editProviderUserUnique_lastname'),
      ),
      'SUBACCOUNT' => array(
        'firstname' => $this->_app->textStorage->getText('label.editProviderUserUnique_firstname'),
        'lastname'  => $this->_app->textStorage->getText('label.editProviderUserUnique_lastname'),
      ),
    );

    $s = new SAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], "%s=%s"));
    $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
    $s->addStatement(new SqlStatementMono($s->columns['restricted'], "%s IS NULL"));
    $s->addStatement(new SqlStatementMono($s->columns['mandatory'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s='N'"));
    $s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('applicable_type','attribute_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['applicable_type']) $available[$row['applicable_type']]['attr_'.$row['attribute_id']] = $row['name'];
      else {
        $available['USER']['attr_'.$row['attribute_id']] = $row['name'];
        $available['SUBACCOUNT']['attr_'.$row['attribute_id']] = $row['name'];
      }
    }


    if ($userSubaccount) $template = '<table><tr><th>{__label.editProviderUserUnique_user</th><th>{__label.editProviderUserUnique_subaccount</th></tr>';

    $userTemplate = '<input type="hidden" name="userUnique[]" value=""/>';
    foreach ($available['USER'] as $key=>$value) {
      $userTemplate .= sprintf('<div><input type="checkbox" name="userUnique[]" value="%s" %s/>&nbsp;%s</div>', $key, in_array($key, $data['userUnique'])?'checked="checked"':'', $value);
    }

    if ($userSubaccount) {
      $subaccountTemplate = '<input type="hidden" name="subaccountUnique[]" value=""/>';
      foreach ($available['SUBACCOUNT'] as $key=>$value) {
        $subaccountTemplate .= sprintf('<div><input type="checkbox" name="subaccountUnique[]" value="%s" %s/>&nbsp;%s</div>', $key, in_array($key, $data['subaccountUnique'])?'checked="checked"':'', $value);
      }

      $template = sprintf('<table>
                             <tr><th>%s</th><th>%s</th></tr>
                             <tr valign="top"><td>%s</td><td>%s</td></tr>
                           </table>', $this->_app->textStorage->getText('label.editProviderUserUnique_user'), $this->_app->textStorage->getText('label.editProviderUserUnique_subaccount'),
                            $userTemplate, $subaccountTemplate);
    } else $template = $userTemplate;

    $this->insertTemplateVar('fi_userUniqueElements', $template, false);
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/General.html');

    $validator = Validator::get('settings', 'SettingsValidator');
    $data = $validator->getValues();
    
    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    $this->_insertBadgePhotoSelect($data);
    $this->_insertReservationConditionSelect($data);
    $this->_insertDocumenttemplateSelect($data);
    $this->_insertUserUnique($data);
    
    if ($data['userConfirm']=='N') $this->insertTemplateVar('userConfirmChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('userConfirmChecked', '');
    if ($data['userSubaccount']=='Y') $this->insertTemplateVar('userSubaccountChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('userSubaccountChecked', '');
    if ($data['showCompany']=='Y') $this->insertTemplateVar('showCompanyChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('showCompanyChecked', '');
    if ($data['generateAccounting']=='Y') {
      $this->insertTemplateVar('generateAccountingChecked', 'checked="checked"', false);
      $this->insertTemplateVar('hideAccounting', '');
    } else {
      $this->insertTemplateVar('generateAccountingChecked', '');
      $this->insertTemplateVar('hideAccounting', 'style="display:none;"', false);
    }
    if ($data['allowSkipReservationCondition']=='Y') $this->insertTemplateVar('allowSkipReservationConditionChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('allowSkipReservationConditionChecked', '');
    if ($data['allowMandatoryReservation']=='Y') $this->insertTemplateVar('allowMandatoryReservationChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('allowMandatoryReservationChecked', '');
    if ($data['organiserMandatoryReservation']=='Y') $this->insertTemplateVar('organiserMandatoryReservationChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('organiserMandatoryReservationChecked', '');
    if ($data['organiserMandatorySubstitute']=='Y') $this->insertTemplateVar('organiserMandatorySubstituteChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('organiserMandatorySubstituteChecked', '');
    if ($data['allowOnlinePaymentOnly']=='Y') $this->insertTemplateVar('allowOnlinePaymentOnlyChecked', 'checked="checked"', false);
    else $this->insertTemplateVar('allowOnlinePaymentOnlyChecked', '');

    $this->insert(new GuiListProviderAccountType, 'providerAccountTypeList');
    $this->insert(new GuiFormButton(array(
      'label' => $this->_app->textStorage->getText('button.listProviderAccountType_new'),
      'classInput' => 'inputSubmit',
      'action' => 'eProviderAccountTypeEdit',
      'showDiv' => false)), 'newProviderAccountType');

    $this->insert(new GuiListProviderTextStorage, 'providerTextStorageList');

    if ($this->_app->auth->isAdministrator()) {
      $this->insertTemplateVar('backButton', sprintf('<input class="fb_eSave" id="fb_eSave" type="submit" name="action_eSettingsGeneralBack" value="%s" />',
                                                     $this->_app->textStorage->getText('button.settingsGeneral_back')), false);
    } else $this->insertTemplateVar('backButton', '');
    
    $this->_app->document->addJavascript("$(document).ready(function() { 
          var tabCookieName = 'ui-settingsGeneral-tab';
          var tab = $('#tab').tabs({
              active : ($.cookie(tabCookieName) || 0),
              activate : function( event, ui ) {
                var newIndex = ui.newTab.parent().children().index(ui.newTab);
                // my setup requires the custom path, yours may not
                $.cookie(tabCookieName, newIndex);
              }
          })
          
          $('#fi_badgeHelpDiv').dialog({ autoOpen: false, width: 600 });
          $('#fi_badgeHelp').click(function() { $('#fi_badgeHelpDiv').dialog('open'); });
          
          $('#fi_ticketHelpDiv').dialog({ autoOpen: false, width: 600 });
          $('#fi_ticketHelp').click(function() { $('#fi_ticketHelpDiv').dialog('open'); });
          
          $('#fi_numberHelpDiv').dialog({ autoOpen: false, width:  600 });
          $('.fi_numberHelp').click(function() { $('#fi_numberHelpDiv').dialog('open'); });
          
          $('#fi_accountingHelpDiv').dialog({ autoOpen: false, width: 800 });
          $('.fi_accountingHelp').click(function() { $('#fi_accountingHelpDiv').dialog('open'); });
        });");
  }
}

?>
