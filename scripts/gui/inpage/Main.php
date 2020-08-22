<?php

class GuiInPageMain extends GuiElement {
  
  private function _insertResourceList($data) {
    $template = '';
    
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name','center','center_name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) {
      $template .= sprintf('<b>%s:</b> <br/>', $this->_app->textStorage->getText('label.inpage_resource'));
    }
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $template .= sprintf('%s (%s) [<a href="inpage.php?action=vInPageResource&id=%s%s">%s</a>]<br/>', $row['name'], $row['center_name'],
                           $row['resource_id'], $this->_app->session->getTagForUrl(), 
                           $this->_app->textStorage->getText('button.inpage_resource_calendar'));
    }
    
    $this->insertTemplateVar('resourceList', $template, false);
  }
  
  private function _insertEventList($data) {
    $template = '';
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    //$s->addStatement(new SqlStatementMono($s->columns['resource'], '%s IS NULL'));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('event_id','start','name','center','center_name','free','free_substitute'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)) {
      $template .= sprintf('<b>%s:</b> <br/>', $this->_app->textStorage->getText('label.inpage_event'));
    }
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['free']) {
        $reserveButton = sprintf('[<a href="inpage.php?action=vInPageEvent&id=%s%s">%s</a>]',
                                 $row['event_id'], $this->_app->session->getTagForUrl(), 
                                 $this->_app->textStorage->getText('button.inpage_event_reserve'));
      } elseif ($row['free_substitute']) {
        $reserveButton = sprintf('[<a href="inpage.php?action=vInPageEvent&id=%s%s">%s</a>]',
                                 $row['event_id'], $this->_app->session->getTagForUrl(), 
                                 $this->_app->textStorage->getText('button.inpage_event_reserveSubstitute'));
      } else $reserveButton = '';
      
      $template .= sprintf('%s (%s) - %s %s %s<br/>', $row['name'], $this->_app->regionalSettings->convertDateTimeToHuman($row['start']),
                           $row['free'], $this->_app->textStorage->getText('label.inpage_event_free'), $reserveButton);
    }
    
    $this->insertTemplateVar('eventList', $template, false);
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/Main.html');

    $validator = Validator::get('inpage', 'InPageValidator');
    $data = $validator->getValues();
    
    $this->_insertResourceList($data);
    $this->_insertEventList($data);
  }
}

?>
