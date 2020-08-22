<?php

class GuiBadge extends GuiElement {
  private $_objectId = null;
  private $_forObject = null;
  private $_providerId = null;
  private $_providerSettings = null;
  
  public function __construct($params=array()) {
    parent::__construct($params);
    
    $this->_forObject = $params['for'];
    $this->_objectId = $params['id'];
    $this->_providerId = $params['provider'];
    $this->_providerSettings = BCustomer::getProviderSettings($this->_providerId);
  }
  
  protected function _getOneBadge($data) {
    if ($this->_providerSettings['badgeTemplate']) $template = $this->_providerSettings['badgeTemplate'];
    else $template = file_get_contents(dirname(__FILE__).'/Badge.html');
    
    $template = str_replace(array_keys($data), $data, $template);
    
    $gui = new GuiElement(array('template'=>sprintf('<div class="badge">%s</div>',$template)));
    
    return $gui;
  }
  
  protected function _userRender() {
    global $AJAX;
    
    $this->setTemplateString('<div class="badgePage">{children}</div>');
    
    $ids = array();
    
    if (!strcmp($this->_forObject,'event')) {
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_objectId, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
      $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
      $s->setColumnsMask(array('reservation_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $ids[] = $row['reservation_id'];
      }
    } else {
      $ids = array($this->_objectId);
    }
    
    $count = 0;
    foreach ($ids as $id) {
      $count++;
      
      $data = array();
      
      $s = new SReservation;
      $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $id, '%s=%s'));
      $s->setColumnsMask(array('user_id','user_name','user_email','user_state_code','user_state_name',
                         'provider','provider_name','center_name','center_street','center_city','center_postal_code',
                         'event_name','event_start','event_end'
                         ));
      $res = $this->_app->db->doQuery($s->toString());
      $row1 = $this->_app->db->fetchAssoc($res);
      foreach ($row1 as $key=>$val) {
        $data[sprintf('@@%s', strtoupper($key))] = $val;
      }
      
      // nactu atributy uzivatele
      // nejdriv vsechny atributy poskytovatele "vynuluju"
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $row1['provider'], '%s=%s'));
      $s->setColumnsMask(array('attribute_id','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@USER_ATTRIBUTE('.$row['short_name'].')'] = ''; }
      $s = new SUserAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $row1['user_id'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $row1['provider'], '%s=%s'));
      $s->setColumnsMask(array('attribute','short_name','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@USER_ATTRIBUTE('.$row['short_name'].')'] = $row['value']; }
      
      // badge fotka uzivatele
      $data['@@USER_PHOTO'] = '';
      $s = new SUserAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $row1['user_id'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $row1['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['attribute'], $this->_providerSettings['badgePhoto'], '%s=%s'));
      $s->setColumnsMask(array('attribute','value'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $s2 = new SFile;
        $s2->addStatement(new SqlStatementBi($s2->columns['file_id'], $row['value'], '%s=%s'));
        $s2->setColumnsMask(array('hash'));
        $res2 = $this->_app->db->doQuery($s2->toString());
        if ($row2 = $this->_app->db->fetchAssoc($res2)) $data['@@USER_PHOTO'] = sprintf('<img src="%s/getfile.php?id=%s"/>', dirname($AJAX['url']), $row2['hash']);
      }
      
      if (strcmp($row1['provider_name'],$row1['center_name'])) $data['@@CENTER_NAME'] = sprintf('%s - %s', $row1['provider_name'], $row1['center_name']);
      list($row1['date_from'],$row1['time_from']) = explode(' ', $row1['event_start']);
      list($row1['date_to'],$row1['time_to']) = explode(' ', $row1['event_end']);
      $data['@@DATE_FROM'] = $this->_app->regionalSettings->convertDateToHuman($row1['date_from']);
      $data['@@TIME_FROM'] = $this->_app->regionalSettings->convertTimeToHuman($row1['time_from'], 'h:m');
      $data['@@DATE_TO'] = $this->_app->regionalSettings->convertDateToHuman($row1['date_to']);
      $data['@@TIME_TO'] = $this->_app->regionalSettings->convertTimeToHuman($row1['time_to'], 'h:m');
      
      // soubory poskytovatele
      $s = new SProviderFile;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $row1['provider'], '%s=%s'));
      $s->setColumnsMask(array('file_id','hash','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $data['@@FILE('.$row['short_name'].')'] = sprintf('%s/getfile.php?id=%s', dirname($AJAX['url']), $row['hash']);
      }
      //adump($data);die;
      
      $this->insert($this->_getOneBadge($data));
      
      if (!($count%10)) {
        $this->insert(new GuiElement(array('template'=>'<hr/><div class="pageBreak">&nbsp;</div>')));
      }
    }
    
    $this->_app->document->addCssFile('style_badge.css');
  }
}

?>
