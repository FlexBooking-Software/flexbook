<?php

class GuiEditEvent extends GuiElement {
  private $_attendee;
  private $_substitute;
  private $_editable=false;
  private $_powerOrganiser;
  
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
  
  private function _insertBadge($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_badge',
            'name' => 'badge',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $data['badge'],
            'userTextStorage' => false)), 'fi_badge');
  }

  private function _insertFeAttendeeVisible($data) {
    $ds = new HashDataSource(new DataSourceSettings, array(
      'Y'           => $this->_app->textStorage->getText('label.editEvent_feAttendeeVisible_Y'),
      'N'           => $this->_app->textStorage->getText('label.editEvent_feAttendeeVisible_N'),
      'LOGGED_USER' => $this->_app->textStorage->getText('label.editEvent_feAttendeeVisible_LOGGED_USER'),));
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_feAttendeeVisible',
      'name' => 'feAttendeeVisible',
      'showDiv' => false,
      'dataSource' => $ds,
      'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
      'value' => $data['feAttendeeVisible'],
      'userTextStorage' => false)), 'fi_feAttendeeVisible');
  }

  private function _insertFeQuickReservation($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_feQuickReservation',
      'name' => 'feQuickReservation',
      'showDiv' => false,
      'dataSource' => $ds,
      'value' => $data['feQuickReservation'],
      'userTextStorage' => false)), 'fi_feQuickReservation');
  }

  private function _insertFeAllowedPayment($data) {
    if (in_array('credit',$data['feAllowedPayment'])) $this->insertTemplateVar('feAllowedPayment_credit_checked', 'checked="yes"', false);
    else $this->insertTemplateVar('feAllowedPayment_credit_checked', '');
    if (in_array('ticket',$data['feAllowedPayment'])) $this->insertTemplateVar('feAllowedPayment_ticket_checked', 'checked="yes"', false);
    else $this->insertTemplateVar('feAllowedPayment_ticket_checked', '');
    if (in_array('online',$data['feAllowedPayment'])) $this->insertTemplateVar('feAllowedPayment_online_checked', 'checked="yes"', false);
    else $this->insertTemplateVar('feAllowedPayment_online_checked', '');
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
        <input type="checkbox" class="inputCheckbox groupSaveCheck" name="groupSaveItem[centerId]" value="1" {groupSave_centerId_checked}/>
        {fi_center}
      </div>'));
    $gui->insert(new GuiFormSelect(array(
            'id' => 'fi_center',
            'name' => 'centerId',
            'classLabel' => 'bold',
            'showDiv' => false,
            'dataSource' => $ds,
            'value' => $data['centerId'],
            #'readonly' => $this->_attendee,
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
                'id' => 'fi_provider',
                'name' => 'providerId',
                'classLabel' => 'bold',
                'label' => $this->_app->textStorage->getText('label.editEvent_provider'),
                'dataSource' => $ds,
                'value' => $data['providerId'],
                'readonly' => $this->_attendee,
                'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
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
    if ($this->_editable&&$this->_powerOrganiser) $select->addStatement(new SqlStatementBi($select->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
    elseif (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $select->addOrder(new SqlStatementAsc($select->columns['fullname_reversed']));
    $select->addOrder(new SqlStatementAsc($select->columns['email']));
    $select->setColumnsMask(array('user','fullname_reversed','role_center'));
    $res = $this->_app->db->doQuery($select->toString());
    $hash = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $organiserCenter = explode(',',$row['role_center']);
      if (!$data['centerId']||((strpos($row['role_center'],'ALL')!==false)||in_array($data['centerId'], $organiserCenter))) {
        $hash[$row['user']] = $row['fullname_reversed'];
      }
    }

    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $selectParams = array(
      'id' => 'fi_organiser',
      'name' => 'organiserId',
      'dataSource' => $ds,
      'value' => $data['organiserId'],
      'showDiv' => false,
      'userTextStorage' => false);
    if (!$this->_powerOrganiser) $selectParams['firstOption'] = Application::get()->textStorage->getText('label.select_choose');
    $this->insert(new GuiFormSelect($selectParams), 'fi_organiser');

    if ($this->_app->auth->haveRight('commodity_admin', $this->_app->auth->getActualProvider())) {
      $this->insert($g = new GuiElement(array('template'=>'
        <div id="fi_organiserDiv">
          &nbsp;
          <input class="button" type="button" onclick="return mySubmit(\'fb_eEventSave\',\'fi_nextAction\',\'newOrganiser\');" name="action_eNewOrganiser" value="{__button.new_m}" />
        </div>
        ')), 'fi_organiser');
    }
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

  private function _insertAttendee($data) {
    if ($data['id']&&!$data['groupSave']) {
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $data['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
      $s->setColumnsMask(array('person_email'));
      $res = $this->_app->db->doQuery($s->toString());
      $this->_attendee = $this->_app->db->getRowsNumber($res);
      $attendeeEmail = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $attendeeEmail[] = $row['person_email'];
      }
      /*if ($this->_attendee) {
        $gui = new GuiElement(array('template'=>'
                                    <div class="formItem">
                                      <label>{__label.editEvent_attendee}:</label>
                                      <div style="float:left;">{children}</div>
                                    </div>
                                    '));
        $gui->insert(new GuiListReservation('listEventReservation'));
        $this->insert($gui, 'fi_attendee');
      } else $this->insertTemplateVar('fi_attendee', '');*/
      
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $data['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
      $s->setColumnsMask(array('sum_places'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $this->_substitute = ifsetor($row['sum_places'],0);
      if ($this->_substitute) {
        $gui = new GuiElement(array('template'=>'
                                    <br/>
                                    <b>{__label.editEvent_substitute}:</b>
                                    {children}'));
        $gui->insert(new GuiListEventSubstitute('listEventSubstitute', array('event'=>$data['id'])));
        $this->insert($gui, 'fi_substitute');
      } else $this->insertTemplateVar('fi_substitute', '');
      
      if ($data['active']=='Y') {
        if ($data['maxAttendees']-$this->_attendee) {
          $this->insertTemplateVar('fb_newReservation',
              sprintf('<input class="fb_eSave" id="fb_eEventReservation" type="submit" onclick="$(\'#form\').attr(\'target\',\'_self\');" name="action_eEventReservation" value="%s" />',
                      $this->_app->textStorage->getText('button.grid_newReservation')), false);
          $this->insertTemplateVar('fb_newReservation', "\n");
        }
        if ($data['maxSubstitutes']-$this->_substitute) {
          $this->insertTemplateVar('fb_newReservation',
              sprintf('<input class="fb_eSave" id="fb_eEventReservation" type="submit" onclick="$(\'#form\').attr(\'target\',\'_self\');" name="action_eEventSubstituteEdit?event=%s" value="%s" />',
                      $data['id'], $this->_app->textStorage->getText('button.editEvent_newSubstitute')), false);
        } 
      } 
      $this->insertTemplateVar('fb_newReservation', '');
      
      $this->insertTemplateVar('fb_userExport',
          sprintf('<input class="fb_eSave" id="fb_eUserExport" type="submit" onclick="$(\'#form\').attr(\'target\',\'_blank\');" name="action_vEventUserExport?event=%s" value="%s" />',
                  $data['id'], $this->_app->textStorage->getText('button.editEvent_userExport')), false);

      if ($this->_attendee) {
        $this->insertTemplateVar('fb_attendeeEmail',
          sprintf('<input class="fb_eSave" id="fb_eAttendeeEmail" type="submit" onclick="location.href=\'mailto:%s\';return false;" name="action_vEventAttendeeEmail" value="%s" />',
            implode(',',array_unique($attendeeEmail)), $this->_app->textStorage->getText('button.editEvent_attendeeEmail')), false);
      } else $this->insertTemplateVar('fb_attendeeEmail', '');
    } else {
      //$this->insertTemplateVar('fi_attendee', '');
      //$this->insertTemplateVar('fi_substitute', '');
      $this->insertTemplateVar('fb_newReservation', '');
      $this->insertTemplateVar('fb_attendeeEmail', '');
      $this->insertTemplateVar('fb_userExport', '');
    }
  }
  
  private function _insertResourceCheckbox($data) {
    $s = new SResource;
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
    else $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) {
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $checked = in_array($row['resource_id'], $data['resource'])?' checked="yes"':'';
        $disabled = false;//$this->_attendee?' disabled="yes"':'';
        $this->insertTemplateVar('fi_resource', sprintf('<div><input type="checkbox" class="inputCheckbox" name="resource[]" value="%s"%s%s/>&nbsp;%s</div>',
                                                        $row['resource_id'], $checked, $disabled, $row['name']), false);
      }
    } else $this->insertTemplateVar('fi_resource','');
  }
  
  private function _insertRepeatElement($data) {
    if ($data['repeat']) $this->insertTemplateVar('repeatChecked', 'checked="yes"', false);
    else $this->insertTemplateVar('repeatChecked', '');
    
    global $EVENT_REPEAT_CYCLE;
    $hash = array();
    foreach ($EVENT_REPEAT_CYCLE as $cycle) {
      $hash[$cycle] = $this->_app->textStorage->getText('label.editEvent_repeatCycle_'.$cycle);
    }
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
          'id'          => 'fi_repeatCycle',
          'name'        => 'repeatCycle',
          'dataSource'  => $ds,
          'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
          'value'       => $data['repeatCycle'],
          'showDiv'     => false,
          )), 'fi_repeatCycle');

    $hash = array(
      '0'   => Application::get()->textStorage->getText('label.editEvent_repeatWeekdayOrder_0'),
      '1'   => Application::get()->textStorage->getText('label.editEvent_repeatWeekdayOrder_1'),
      '2'   => Application::get()->textStorage->getText('label.editEvent_repeatWeekdayOrder_2'),
      '3'   => Application::get()->textStorage->getText('label.editEvent_repeatWeekdayOrder_3'),
      '4'   => Application::get()->textStorage->getText('label.editEvent_repeatWeekdayOrder_4'),
    );
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
      'id'          => 'fi_repeatWeekdayOrder',
      'name'        => 'repeatWeekdayOrder',
      'dataSource'  => $ds,
      'value'       => $data['repeatWeekdayOrder'],
      'showDiv'     => false,
    )), 'fi_repeatWeekdayOrder');


    global $EVENT_REPEAT_RESERVATION;
    $hash = array();
    foreach ($EVENT_REPEAT_RESERVATION as $res) {
      $hash[$res] = $this->_app->textStorage->getText('label.editEvent_repeatReservation_'.$res);
    }
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
          'name'        => 'repeatReservation',
          'dataSource'  => $ds,
          'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
          'value'       => $data['repeatReservation'],
          'showDiv'     => false,
          )), 'fi_repeatReservation');
    
    foreach (array('mon','tue','wed','thu','fri','sat','sun') as $day) {  
      if (isset($data['repeatWeekday'][$day])&&($data['repeatWeekday'][$day])) {
        $this->insertTemplateVar('repeatWeekday_'.$day.'_checked', 'checked="yes"', false);
      } else $this->insertTemplateVar('repeatWeekday_'.$day.'_checked', '');
    }
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
  
  private function _insertButton($data) {
    if ($this->_editable) {
      if ($data['groupSave']) {
        $this->insertTemplateVar('fb_saveEvent', sprintf('<input class="fb_eSave" id="fb_eEventSave" type="submit" onclick="$(\'#form\').attr(\'target\',\'_self\');" name="action_eEventSave" value="%s" />',
                                                         $this->_app->textStorage->getText('button.editEvent_groupSave')), false);
      } elseif ($data['repeatParent']) {
        #$this->insertTemplateVar('fb_saveEvent', sprintf('<input class="fb_eSave" id="fb_eEventSave" type="submit" onclick="$(\'#form\').attr(\'target\',\'_self\');" name="action_eEventSave?repeatSave=this" value="%s" />',
        #                                                 $this->_app->textStorage->getText('button.editEvent_saveThis')), false);
        $this->insertTemplateVar('fb_saveEvent', sprintf('&nbsp;<input class="fb_eSave" id="fb_eEventSave" type="submit" onclick="$(\'#form\').attr(\'target\',\'_self\');" name="action_eEventSave?repeatSave=all" value="%s" />',
                                                         $this->_app->textStorage->getText('button.editEvent_save')), false);
      } else {
        $this->insertTemplateVar('fb_saveEvent', sprintf('<input class="fb_eSave" id="fb_eEventSave" type="submit" onclick="$(\'#form\').attr(\'target\',\'_self\');" name="action_eEventSave" value="%s" />',
                                                         $this->_app->textStorage->getText('button.editEvent_save')), false);
      }

      if ($this->_app->auth->isAdministrator()||$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) {
				$this->insertTemplateVar('fb_newReservationCondition', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eEventSave\',\'fi_nextAction\',\'newReservationCondition\');" name="action_eNewReservationCondition" value="%s" />', $this->_app->textStorage->getText('button.new_p')), false);
				$this->insertTemplateVar('fb_newNotificationTemplate', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eEventSave\',\'fi_nextAction\',\'newNotificationTemplate\');" name="action_eNewNotificationTemplate" value="%s" />', $this->_app->textStorage->getText('button.new_p')), false);
				$this->insertTemplateVar('fb_newAccountType', sprintf('<input class="button" type="button" onclick="return mySubmit(\'fb_eEventSave\',\'fi_nextAction\',\'newAccountType\');" name="action_eNewAccountType" value="%s" />', $this->_app->textStorage->getText('button.new_f')), false);
			} else {
				$this->insertTemplateVar('fb_newReservationCondition', '');
				$this->insertTemplateVar('fb_newNotificationTemplate', '');
				$this->insertTemplateVar('fb_newAccountType', '');
			}
    } else {
      $this->insertTemplateVar('fb_saveEvent', '');

      $this->insertTemplateVar('fb_newReservationCondition', '');
      $this->insertTemplateVar('fb_newNotificationTemplate', '');
      $this->insertTemplateVar('fb_newAccountType', '');
    }
  }
  
  private function _insertAttribute($data) {
    $hash = new HashDataSource(new DataSourceSettings, array(
      ''            => ' ',
      'add'         => $this->_app->textStorage->getText('label.editEvent_tagAddOnly'),
      'replaceAdd'  => $this->_app->textStorage->getText('label.editEvent_tagReplaceAdd'),
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
                                <div class="gridTable"><table id="fi_eventAttributeTable">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('label.editEventAttribute_resource'),
                        $data['groupSave']?$select->render():'',
                        $this->_app->auth->haveRight('commodity_admin', $data['providerId'])?'<input type="button" class="inputSubmit button" id="fi_newEventAttribute_button" value="'.$this->_app->textStorage->getText('button.editEvent_newAttribute').'"/>':'',
                        $this->_app->textStorage->getText('label.editEventAttribute_category'),
                        $this->_app->textStorage->getText('label.editEventAttribute_name'),
                        $this->_app->textStorage->getText('label.editEventAttribute_type'),
                        $this->_app->textStorage->getText('label.editEventAttribute_value'));
    if (is_array($data['attribute'])&&count($data['attribute'])) {
      $i = 0;
      foreach ($data['attribute'] as $key=>$reg) {
        if ($this->_app->auth->haveRight('commodity_admin', $data['providerId'])) {
          $action = sprintf('[<a href="#" id="fi_attributeEdit">%s</a>][<a href="#" id="fi_attributeRemove">%s</a>]', $this->_app->textStorage->getText('button.grid_change'), $this->_app->textStorage->getText('button.grid_remove'));
        } else $action = '';
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        if ($reg['disabled']=='Y') $class .= ' disabled';
        $formVariable = sprintf('<input type="hidden" class="attributeHidden" name="newEventAttribute[%d]" value="attributeId:~%s;category:~%s;name:~%s;type:~%s;allowedValues:~%s;valueId:~%s;value:~%s;disabled:~%s"/>',
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
                        $this->_app->textStorage->getText('label.editEventAttribute_reservation'),
                        $data['groupSave']?$select->render():'',
                        $this->_app->auth->haveRight('commodity_admin', $data['providerId'])?'<input type="button" class="inputSubmit button" id="fi_newReservationAttribute_button" value="'.$this->_app->textStorage->getText('button.editEvent_newAttribute').'"/>':'',
                        $this->_app->textStorage->getText('label.editEventAttribute_category'),
                        $this->_app->textStorage->getText('label.editEventAttribute_name'),
                        $this->_app->textStorage->getText('label.editEventAttribute_type'),
                        $this->_app->textStorage->getText('label.editEventAttribute_mandatory'));
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
    if ($data['id']) {
      $this->insert(new GuiListReservation('listEventReservation', $data['id']), 'fi_reservation');
    }
  }

  protected function _getEditJs($data, & $tagValues, & $repeatIndividualValues, & $editJS) {
    if ($this->_editable) {
      $this->insertTemplatevar('readonlyAttendee', '');

      $repeatIndividualValues = "$('#fi_repeatIndividual_input').click(function(evt) { \n$('#fi_repeatIndividual').css({left: evt.clientX-90}); \n$('#fi_repeatIndividual').show(); });\n$('#fi_repeatIndividual').on('mouseleave', function() { $(this).hide(); });";
      $repeatIndividualValues .= "$.datepicker.setDefaults($.datepicker.regional['cs']);\n";
      $repeatIndividualValues .= "$('#fi_repeatIndividual').multiDatesPicker({ altField:'#fi_repeatIndividual_input'});\n";
			if ($data['repeatIndividual']) {
        foreach (explode(',',$data['repeatIndividual']) as $date) {
          $repeatIndividualValues .= sprintf("$('#fi_repeatIndividual').multiDatesPicker({addDates:[new Date('%s')]});\n", $date);
        }
			}

      $editJS .= "
                $('#fi_start').datetimepicker({format:'d.m.Y H:i',lang:'cz',dayOfWeekStart:'1',allowBlank:true});
                $('#fi_end').datetimepicker({format:'d.m.Y H:i',lang:'cz',dayOfWeekStart:'1',allowBlank:true});
                
                $('#fi_start').blur(function () {
                  if ($('#fi_start').val()) {
                    if ($('#fi_end').val()=='') $('#fi_end').val($('#fi_start').val());
                    else if (moment($('#fi_end').val(),'DD.MM.YYYY HH:mm').unix()<moment($('#fi_start').val(),'DD.MM.YYYY HH:mm').unix()) $('#fi_end').val($('#fi_start').val());
                  }
                });
                $('#fi_end').blur(function () {
                  if ($('#fi_end').val()) {
                    if ($('#fi_start').val()=='') $('#fi_start').val($('#fi_end').val());
                    else if (moment($('#fi_end').val(),'DD.MM.YYYY HH:mm').unix()<moment($('#fi_start').val(),'DD.MM.YYYY HH:mm').unix()) $('#fi_end').val($('#fi_start').val());
                  }
                });
                ";

      $readonlyTag = '';
    } else {
			$repeatIndividualValues = sprintf("$('#fi_repeatIndividual_input').val('%s');\n", $data['repeatIndividual']);

      $editJS .= "$('#editEvent .formItem').find('input, textarea, button, select').attr('disabled','disabled');
                  $('.token-input-input-token-facebook').hide();";

      $readonlyTag = ', readonly:true ';
    }

    $tagValues = '';
    foreach (explode(',',$data['tag']) as $tag) {
      if (!$tag) continue;

      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['name'], $tag, '%s=%s'));
      $s->setColumnsMask(array('tag_id', 'name'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $id = ifsetor($row['tag_id']);

      $tagValues .= "$('#fi_tag').tokenInput('add', {id: '$id', name: '$tag'$readonlyTag});";
    }
  }
  
  protected function _evaluateGroupSave($data) {
    if ($data['groupSave']) {
      $this->_app->document->addJavascript("
          $(document).ready(function() {
            $('.groupSaveCheck').show();
            $('.formItemSingle').hide();
            $('#a-tab-2').hide();
            $('#a-tab-7').hide();
            $('.token-input-list-facebook').css('margin-left','18px'); 
          })");
      
      foreach ($data['groupSaveItem'] as $name=>$value) {
        if ($value) $this->insertTemplateVar('groupSave_'.$name.'_checked', 'checked="yes"', false);
        else $this->insertTemplateVar('groupSave_'.$name.'_checked', '');
      }
      
      $hash = new HashDataSource(new DataSourceSettings, array(
                ''            => ' ',
                'add'         => $this->_app->textStorage->getText('label.editEvent_tagAddOnly'),
                'replaceAdd'  => $this->_app->textStorage->getText('label.editEvent_tagReplaceAdd'),
                ));
      $this->insert(new GuiFormSelect(array(
            'classInput'  => 'tagEdit',
            'id'          => 'fi_tagEditSelect',
            'name'        => 'groupSaveItem[tag]',
            'dataSource'  => $hash,
            'value'       => ifsetor($data['groupSaveItem']['tag'],-1),
            )), 'fi_tagEditSelect');
    } else {
      if ($data['singleEventEdit']) {
        $this->_app->document->addJavascript("
          $(document).ready(function() {
            $('#a-tab-2').hide(); 
          })");
      }

      $this->insertTemplateVar('fi_tagEditSelect', '');
    }
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/EventEdit.html');

		$validator = Validator::get('event', 'EventValidator');
    $data = $validator->getValues();
    #adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editEvent_titleNew'));
    } else {
      if (!$data['groupSave']) $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editEvent_titleExisting').' '.$data['name']);
      else {
        $s = new SEvent;
        $s->addStatement(new SqlStatementMono($s->columns['event_id'], sprintf('%%s IN (%s)', $data['id'])));
        $s->addOrder(new SqlStatementAsc($s->columns['name']));
        $s->setColumnsMask(array('name'));
        $res = $this->_app->db->doQuery($s->toString());
        $name = '';
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($name) $name .= ', ';
          $name .= $row['name'];
        }
        $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editEvent_titleGroupExisting').' '.$name);
      }
    }
    
    $this->_insertAttendee($data);
    $validator->setValues(array('attendee'=>$this->_attendee));

    $this->_powerOrganiser = !$this->_app->auth->haveRight('commodity_admin', $this->_app->auth->getActualProvider())&&
      $this->_app->auth->haveRight('power_organiser', $this->_app->auth->getActualProvider());

    // editovat muze, kdyz je commodity_admin nebo power_organiser (pak muze vytvaret akce a editove akce, kde je organisator)
    $this->_editable = $this->_app->auth->haveRight('commodity_admin', $data['providerId'])||
      ($this->_app->auth->haveRight('power_organiser', $data['providerId'])&&(!$data['id']||($data['organiserId']==$this->_app->auth->getUserId())));

    $this->_getEditJs($data, $tagValues, $repeatIndividual, $editJS);
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/EventEdit.js',
                array(
                  'ajaxUrl'           		=> $AJAX['adminUrl'],
                  'ajaxUrlPath'       		=> dirname($AJAX['adminUrl']),
                  'tagTokenInit'      		=> $tagValues,
                  'repeatIndividualInit'	=> $repeatIndividual,
                  'additionalEditJS'  		=> $editJS,
                  'language'          		=> $this->_app->language->getLanguage(),
                  'provider'          		=> $this->_app->auth->getActualProvider(),
                  'notPowerOrganiser'     => $this->_powerOrganiser?'false':'true',
                ));
    
    $this->_insertCenterSelect($data);
    $this->_insertProviderSelect($data);
    $this->_insertOrganiserSelect($data);
    $this->_insertReservationConditionSelect($data);
    $this->_insertNotificationTemplateSelect($data);
    $this->_insertDocumentTemplateSelect($data);
    $this->_insertResourceCheckbox($data);
    $this->_insertRepeatElement($data);
    $this->_insertBadge($data);
    $this->_insertFeAttendeeVisible($data);
    $this->_insertFeQuickReservation($data);
    $this->_insertFeAllowedPayment($data);
    $this->_insertActive($data);
    $this->_insertAccountTypeSelect($data);
    $this->_insertPortalSelect($data);
    $this->_insertAttribute($data);
    $this->_insertReservation($data);

    $this->_insertButton($data);
    $this->_evaluateGroupSave($data);
  }
}

?>
