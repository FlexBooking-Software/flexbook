<?php

class ModuleInPageCzechTourismReservation extends InPageModule {

  protected function _userInsert() {
    $this->setTemplateString('
          <form action="{%basefile%}" method="post">
            <div id="resourceCalendar">
              <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
              {reservationList}
              <div class="formButton">
                <input class="fb_eHidden button" type="submit" name="action_eBack" value="{__button.back}" />
              </div>
            </div>
          </form>');
    
    $validator = Validator::get('inpage', 'InPageValidator');
    $select = new SReservation;
    $select->addStatement(new SqlStatementBi($select->columns['provider'], $validator->getVarValue('providerId'), '%s=%s'));
    $select->addStatement(new SqlStatementBi($select->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['cancelled'], '%s IS NULL'));
    $select->addOrder(new SqlStatementAsc($select->columns['start']));
    $select->setColumnsMask(array('reservation_id','created','number','center_name','total_price','event','event_name','event_places','event_start','resource','resource_name','resource_from','resource_to'));
    $res = $this->_app->db->doQuery($select->toString());
    $template = '<table class="reservation eventList"><tr class="header">
                       <th class="number"><b>{__label.czechTourism_reservationList_number}</b><br/><span class="italic">{__label.czechTourism_reservationList_created}</span></th>
                       <th class="place"><b>{__label.czechTourism_reservationList_center}</b></th>
                       <th class="subject"><b>{__label.czechTourism_reservationList_subject}</b></th><th class="price"><b>{__label.czechTourism_reservationList_price}</b></th>
                       <th>&nbsp;</th>
                       </tr>';
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['event']) {
        $subject = sprintf('%s - %dx<br/>%s', $row['event_name'], $row['event_places'], $this->_app->regionalSettings->convertDateTimeToHuman($row['event_start']));
      } else {
        $from = $this->_app->regionalSettings->convertDateTimeToHuman($row['resource_from']);
        if (substr($row['resource_from'],0,10)==substr($row['resource_to'],0,10)) {
          $to = $this->_app->regionalSettings->convertTimeToHuman(substr($row['resource_to'],11),'h:m');
        } else {
          $to = $this->_app->regionalSettings->convertDateTimeToHuman($row['resource_to']);  
        }
        $interval = sprintf('%s - %s', $from, $to);
        
        $subject = sprintf('%s<br/>%s', $row['resource_name'], $interval);
      }
      
      $action = sprintf('[<a href="inpage.php?action=eInPageReservationCancel&id=%s%s">%s</a>]',
                                 $row['reservation_id'], $this->_app->session->getTagForUrl(), 
                                 $this->_app->textStorage->getText('button.grid_cancel'));
      
      $template .= sprintf('<tr><td><b>%s</b><br/><span class="italic">%s</span></td><td>%s</td><td>%s</td><td>%s {__label.currency_CZK}</td><td>%s</td></tr>',
                           $row['number'], $this->_app->regionalSettings->convertDateTimeToHuman($row['created']),
                           $row['center_name'], $subject, $row['total_price'], $action);
    }
    $template .= '</table>';
    
    $this->insert(new GuiElement(array('template'=>$template)), 'reservationList');
  }
}

?>
