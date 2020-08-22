<?php

class GuiReservationTicketForm extends GuiElement {

  private function _insertEqualSelect($event,$resource) {
    $hash = array('USER'=>$this->_app->textStorage->getText('label.reservationTicketForm_equalUser'));
    if ($event) $hash['EVENT'] = $this->_app->textStorage->getText('label.reservationTicketForm_equalEvent');
    if ($resource) $hash['RESOURCE'] = $this->_app->textStorage->getText('label.reservationTicketForm_equalResource');
    $ds = new HashDataSource(new DataSourceSettings, $hash);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_equal',
      'name' => 'equal',
      'dataSource' => $ds,
      'showDiv' => false,
      'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
      'userTextStorage' => false)), 'fi_equalSelect');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ReservationTicketForm.html');
    
    $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.reservationTicketForm_title'));
    
    $this->_app->document->addJavascript("
              $(document).ready(function() {
                $('#fi_from').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
                $('#fi_to').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank:true,scrollInput:false});
              });");
  
    $id = $this->_app->request->getParams('id');
    
    $this->insertTemplateVar('id', $id);
  
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $id, '%s=%s'));
    $s->setColumnsMask(array('number','user_name','provider_name','center_name','event','resource'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->insertTemplateVar('number', $row['number']);
      $this->insertTemplateVar('userName', $row['user_name']);
      $this->insertTemplateVar('providerName', $row['provider_name']);
      $this->insertTemplateVar('centerName', $row['center_name']);
    
      $this->insert(new GuiReservationTicket, 'ticket');

      $this->_insertEqualSelect($row['event'], $row['resource']);
    }
  }
}

?>
