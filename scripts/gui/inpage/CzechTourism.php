<?php

class GuiCzechTourismMain extends GuiElement {
  
  private function _insertEventList($data) {
    $template = '';
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['event_id'], '%s<>1714'));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    $s->setColumnsMask(array('event_id','start','end','name','center','center_name','free','free_substitute','price','description'));
    $res = $this->_app->db->doQuery($s->toString());
    $template .= '<table class="eventList"><tr class="header">
                       <th class="name"><b>{__label.czechTourism_eventList_title}</b>&nbsp;<span class="italic">{__label.czechTourism_eventList_place}</span></th>
                       <th class="price"><b>{__label.czechTourism_eventList_price}</b></th><th class="reserve"><b>{__label.czechTourism_eventList_reserve}</b></th>
                       </tr>
                       <tr class="header"><th class="italic" colspan="3">{__label.czechTourism_eventList_desc}</th></tr>';
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['free']) {
        $reserveButton = sprintf('[<a href="inpage.php?action=vInPageEvent&id=%s%s">%s</a>]',
                                 $row['event_id'], $this->_app->session->getTagForUrl(), 
                                 $this->_app->textStorage->getText('button.inpage_event_reserve'));
      } else $reserveButton = '';
      
      list($date,$time) = explode(' ', $row['end']);
      $template .= sprintf('<tr><td title="%s">%s (%s-%s)</td><td>%s {__label.currency_CZK}</td><td>%s %s %s</td></tr>',
                           $row['description'], $row['name'],
                           $this->_app->regionalSettings->convertDateTimeToHuman($row['start']),
                           $this->_app->regionalSettings->convertTimeToHuman($time,'h:m'),
                           $row['price'], $row['free'], $this->_app->textStorage->getText('label.inpage_event_free'), $reserveButton);
    }
    $template .= '</table>';
    
    $this->insert(new GuiElement(array('template'=>$template)), 'eventList');
  }
  
  private function _insertNetworkEventList($data) {
    $template = '';
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['event_id'], '%s=1714'));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('event_id','start','end','name','center','center_name','free','free_substitute','price','description'));
    $res = $this->_app->db->doQuery($s->toString());
    $template .= '<table class="eventList"><tr class="header">
                       <th class="name"><b>{__label.czechTourism_networkEventList_title}</b>&nbsp;<span class="italic">{__label.czechTourism_networkEventList_place}</span></th>
                       <th class="price"><b>{__label.czechTourism_eventList_price}</b></th><th class="reserve"><b>{__label.czechTourism_eventList_reserve}</b></th>
                       </tr>
                       <tr class="header"><th class="italic" colspan="3">{__label.czechTourism_networkEventList_desc}</th></tr>';
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($row['free']) {
        $reserveButton = sprintf('[<a href="inpage.php?action=vInPageCzechTourismEvent&id=%s%s">%s</a>]',
                                 $row['event_id'], $this->_app->session->getTagForUrl(), 
                                 $this->_app->textStorage->getText('button.inpage_event_reserve'));
      } else $reserveButton = '';
      
      list($date,$time) = explode(' ', $row['end']);
      $template .= sprintf('<tr><td title="%s">%s (%s-%s)</td><td>%s {__label.currency_CZK}</td><td>%s %s %s</td></tr>',
                           $row['description'], $row['name'],
                           $this->_app->regionalSettings->convertDateTimeToHuman($row['start']),
                           $this->_app->regionalSettings->convertTimeToHuman($time, 'h:m'),
                           $row['price'], $row['free'], $this->_app->textStorage->getText('label.inpage_event_free'), $reserveButton);
    }
    $template .= '</table>';
    
    $this->insert(new GuiElement(array('template'=>$template)), 'networkEventList');
  }
  
  private function _insertResource($data) {
    $template = '<form id="form" action="inpage.php" method="post">
                   <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
                   <input style="display: none;" type="submit" id="buttonResource1" name="action_eInPageCzechTourismResource?czechTourism_resourceTag=praha" value="1" />
                   <input style="display: none;" type="submit" id="buttonResource2" name="action_eInPageCzechTourismResource?czechTourism_resourceTag=brno" value="1" />
                   <table class="resource eventList">
                   <tr class="header"><th class="name"><b>{__label.czechTourism_resource_title}</b></th>
                       <th class="reserve"><b>{__label.czechTourism_eventList_reserve}</b></th>
                   </tr>
                   <tr class="header"><th class="italic" colspan="2">{__label.czechTourism_resource_desc}</th></tr>
                   <tr><td class="name">{__label.czechTourism_resource1}<br/><span class="italic">{__label.czechTourism_resource1_place}</span></td>
                       <td>[<a href="inpage.php?action=eInPageCzechTourismResource&czechTourism_resourceTag=praha{%sessionUrl%}">{__button.inpage_event_reserve}</a>]</td></tr>
                   <tr><td class="name">{__label.czechTourism_resource2}<br/><span class="italic">{__label.czechTourism_resource2_place}</span></td>
                       <td>[<a href="inpage.php?action=eInPageCzechTourismResource&czechTourism_resourceTag=brno{%sessionUrl%}">{__button.inpage_event_reserve}</a>]</td></tr>';
    $template .= '</table></form>';
    
    $this->insert(new GuiElement(array('template'=>$template)), 'resource');
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/CzechTourism.html');

    $validator = Validator::get('inpage', 'InPageValidator');
    $data = $validator->getValues();
    
    $this->_insertEventList($data);
    $this->_insertNetworkEventList($data);
    $this->_insertResource($data);
  }
}

?>
