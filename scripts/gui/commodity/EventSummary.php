<?php

class GuiEventSummary extends GuiElement {
  private $_data;  

  public function __construct($params=array()) {
    parent::__construct($params);
    
    if (isset($params['eventId'])) {
      $b = new BEvent($params['eventId']);
      $this->_data = $b->getData();
      
      $this->_data['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['start']);
      $this->_data['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['end']);
    }
  }

  private function _insertAttendee() {    
    // radni ucastnici
    $template = '';
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_data['id'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
    $s->addStatement(new SqlStatementMono($s->columns['reservation_failed'], '%s IS NULL'));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('eventattendee_id','fullname','places','all_person_fullname_with_email',
                             'reservation_number','reservation_price','reservation_id','reservation_payed','reservation_event_pack'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $template .= sprintf('<tr class="Even"><td>%s</td><td>%s</td><td>%sx</td><td>%s</td><td>%s %s %s</td><td class="tdAction">',
                           $row['reservation_number'], $row['fullname'], $row['places'], str_replace(array('  ',','),'<br/>',$row['all_person_fullname_with_email']),
                           $row['reservation_price'], $this->_app->textStorage->getText('label.currency_CZK'),
                           $row['reservation_event_pack']=='Y'?sprintf(' (%s)', $this->_app->textStorage->getText('label.editEvent_pricePack')):'');
      if ($row['reservation_event_pack']=='Y') {
        $template .= sprintf('<a href="index.php?action=eReservationChooseCancel&id=%s&event=%s%s" title="%s"><img src="img/button_grid_cancel.png"/></a>',
                             $row['reservation_id'], $this->_data['id'], $this->_app->session->getTagForUrl(), $this->_app->textStorage->getText('button.grid_cancel'));
      } else {
        $template .= sprintf('<a href="index.php?action=eReservationPrepareCancel&id=%s%s" title="%s" onclick="return confirm(\'%s\');"><img src="img/button_grid_cancel.png"/></a>',
                             $row['reservation_id'], $this->_app->session->getTagForUrl(), $this->_app->textStorage->getText('button.grid_cancel'),
                             str_replace('{number}', $row['reservation_number'], $this->_app->textStorage->getText('label.listReservation_confirmCancel')));
      }
      $template .= '</td></tr>';
    }
    if ($template) $template = sprintf('<div class="gridTable"><table>%s</table></div>', $template);
    
    $this->insertTemplateVar('attendee', $template, false);
    
    // nahradnici
    $template = '';
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_data['id'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('eventattendee_id','fullname','places','all_person_fullname_with_email',
                             'reservation_number','reservation_price','reservation_id','reservation_payed'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $template .= sprintf('<tr class="Even"><td>%s</td><td>%sx</td><td>%s</td>',
                    $row['fullname'], $row['places'], str_replace(',','<br/>',$row['all_person_fullname_with_email']));
      $template .= '</td></tr>';
    }
    if ($template) $template = sprintf('
        <div class="formItem">
          <label for="fi_attendee" class="bold">%s:</label>
          <div class="gridTable"><table>%s</table></div>
        </div>', $this->_app->textStorage->getText('label.calendar_editEvent_substitute'), $template);
    
    $this->insertTemplateVar('attendee', $template, false);
  }
  
  private function _insertPrice() {
    if (!$this->_data['repeatReservation']||in_array($this->_data['repeatReservation'],array('SINGLE','BOTH'))) {
      $gui = new GuiElement(array('template'=>'
        <div class="formItem">
          <label for="fi_price" class="bold">{__label.editEvent_price}:</label>
          <label class="asInput">{price}&nbsp;{__label.currency_CZK}<label/>
        </div>'));
      $gui->insertTemplateVar('price', $this->_data['price']);
      
      $this->insert($gui, 'priceInfo');
    }
    if (in_array($this->_data['repeatReservation'],array('BOTH','PACK'))) {
      $gui = new GuiElement(array('template'=>'
        <div class="formItem">
          <label for="fi_price" class="bold">{__label.editEvent_repeatPrice}:</label>
          <label class="asInput">{repeatPrice}&nbsp;{__label.currency_CZK}<label/>
        </div>'));
      $gui->insertTemplateVar('repeatPrice', $this->_data['repeatPrice']);
      
      $this->insert($gui, 'priceInfo');
    }
  }

  private function _insertReservation() {
    $this->insert(new GuiListReservation('listEventReservation', $this->_data['id']), 'attendee');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/EventSummary.html');

    foreach ($this->_data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    $this->insertTemplatevar('title', $this->_data['name']);

    $this->_insertPrice();
    #$this->_insertAttendee();
    $this->_insertReservation();
  }
}

?>
