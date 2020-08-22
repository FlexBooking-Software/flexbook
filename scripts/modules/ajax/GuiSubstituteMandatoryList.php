<?php

class AjaxGuiSubstituteMandatoryList extends AjaxGuiSubstituteDetail {
  
  public function __construct($request) {
    AjaxGuiAction2::__construct($request);
    
    $this->_id = sprintf('%sflb_substitute_mandatory_list', $this->_params['prefix']);
    $this->_class = 'flb_substitute_list';

    $this->_showBackButton = false;

    // akce, ktera se provadi po "vyrizeni" nahradnika
    // smaze se div s nahradnikem a pokud nejsou zadni dalsi nahradnici, tak se schova cele okno a reloadne parent profil
    $this->_finishAction = sprintf("button.closest('.flb_reservation_detail').remove();
        if (!$('#%sflb_substitute_mandatory_list .flb_reservation_detail').length) {
          $('#%sflb_substitute_mandatory_list').parent().hide();
          flbRefresh('#%sflb_profile');
        };", $this->_params['prefix'], $this->_params['prefix'], $this->_params['prefix']);
  }

  protected function _initDefaultParams() { }

  protected function _getSubstituteHtml() {
    $ret = '<div class="label flb_substitute_list_label"><span>{__label.ajax_substitute_title}</span></div>';

    foreach ($this->_data['substitute'] as $substitute) {
      $gui = new GuiElement(array('template'=>parent::_getSubstituteHtml()));
      foreach ($substitute as $key=>$value) $gui->insertTemplateVar($key, $value, false);

      $ret .= sprintf('<div class="flb_reservation_detail flb_substitute_detail">%s</div>', $gui->render());
    }

    return $ret;
  }

  protected function _getData() {
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['substitute_mandatory'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>NOW()'));
    $s->setColumnsMask(array('eventattendee_id','subscription_time','price',
                             'event','start','name','places','free'));
    $res = $this->_app->db->doQuery($s->toString());
    $this->_data['substitute'] = array();
    while ($substitute=$this->_app->db->fetchAssoc($res)) {
      $substitute['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($substitute['start']);
      if (isset($this->_params['format']['datetime'])) $substitute['start'] = date($this->_params['format']['datetime'], strtotime($substitute['start']));
      $substitute['commodity'] = sprintf('%s - %dx (%s)', $substitute['name'], $substitute['places'], $substitute['start']);
      
      $person = '';
      $s1 = new SEventAttendeePerson;
      $s1->addStatement(new SqlStatementBi($s1->columns['eventattendee'], $substitute['eventattendee_id'], '%s=%s'));
      $s1->addOrder(new SqlStatementAsc($s1->columns['firstname']));
      $s1->setColumnsMask(array('firstname','lastname','email'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        if ($person) $person .= ', ';
        $person .= sprintf('%s %s', $row1['firstname'], $row1['lastname']);
        if ($row1['email']) $person .= sprintf(' (%s)', $row1['email']);
      }
      $substitute['eventAttendee'] = sprintf(
          '<div class="label flb_reservation_attendee_label"><span>%s:</span></div>
           <div class="value flb_reservation_attendee">%s</div>',
           $this->_app->textStorage->getText('label.ajax_reservation_attendee'), $person);

      if ($substitute['free']) $substitute['reservationButton'] = sprintf('<input type="button" class="flb_primaryButton flb_substitute_swap_button" value="%s" data-event-id="%s" data-substitute-id="%s"/>',
        $this->_app->textStorage->getText('button.ajax_substitute_swap'), $substitute['event'], $substitute['eventattendee_id']);
      else $substitute['reservationButton'] = '';
      
      $substitute['attribute'] = $this->_getAttributeGui($substitute['event'], $substitute['eventattendee_id']);

      $substitute['substitute_id'] = $substitute['eventattendee_id'];
      $substitute['created'] = $this->_app->regionalSettings->convertDateTimeToHuman($substitute['subscription_time']);
      if (isset($this->_params['format']['datetime'])) $substitute['created'] = date($this->_params['format']['datetime'], strtotime($substitute['created']));
      $substitute['payed'] = '---';
      $substitute['total_price'] = $this->_app->regionalSettings->convertNumberToHuman($substitute['price']);

      $this->_data['substitute'][] = $substitute;
    }
  }
}

?>
