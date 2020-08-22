<?php

class GuiInPageEvent extends GuiElement {
  
  private function _insertDetail() {
    $template = '';
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_app->request->getParams('id'), '%s=%s'));
    $s->setColumnsMask(array('event_id','start','end','name','center_name','price','description','free','free_substitute'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    $row['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
    $row['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['end']);
    $row['description'] = str_replace("\n",'<br/>',$row['description']);
    foreach($row as $key=>$value) {
      $this->insertTemplateVar($key, $value,false); 
    }
    
    if (!$this->_app->auth->getUserId()) {
      $onclick = sprintf(' onclick="alert(\'%s\');return false;"', $this->_app->textStorage->getText('label.inpage_loginRequired'));
    } else $onclick = '';
    
    if ($row['free']) {
      $this->insertTemplateVar('reserveButton', sprintf('<input class="button" type="submit" name="action_eInPageEventReserve" value="%s" %s/>',
                                                        $this->_app->textStorage->getText('button.inpage_event_reserve'), $onclick), false);
    } elseif ($row['free_substitute']) {
      $this->insertTemplateVar('reserveButton', sprintf('<input class="button" type="submit" name="action_eInPageEventReserve?substitute=1" value="%s" %s/>',
                                                        $this->_app->textStorage->getText('button.inpage_event_reserveSubstitute'), $onclick), false);
    } else $this->insertTemplateVar('reserveButton', '');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/Event.html');

    $this->_insertDetail();
  }
}

?>
