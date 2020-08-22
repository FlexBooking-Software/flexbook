<?php

class GuiEventCalendar extends GuiElement {
  protected $_id;
  protected $_backButton;
  
  public function __construct($params=array()) {
    parent::__construct($params);
    
    $this->_id = ifsetor($params['id']);
    $this->_backButton = ifsetor($params['backButton'], true);
  }
  
  private function _insertBackButton() {
    if ($this->_backButton) {
      $this->insert(new GuiElement(array('template'=>'
          <form action="{%basefile%}" method="post">
            <div id="resourceCalendar" class="inputForm ui-widget-content">
              <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
              <input type="hidden" name="id" value="{id}" />
              <div class="formButton">
                <input class="button" id="fb_eBack" type="button" onclick="document.getElementById(\'fb_eBackHidden\').click();" name="action_eBack" value="{__button.back}" />
                <input class="fb_eHidden" id="fb_eBackHidden" type="submit" name="action_eBack" value="{__button.back}" />
              </div>')), 'backButtonStart');
      $this->insert(new GuiElement(array('template'=>'
              <div class="formButton">
                <input class="button" id="fb_eBack" type="button" onclick="document.getElementById(\'fb_eBackHidden\').click();" name="action_eBack" value="{__button.back}" />
                <input class="fb_eHidden" id="fb_eBackHidden" type="submit" name="action_eBack" value="{__button.back}" />
              </div>
            </div>
          </form>')), 'backButtonEnd');
    } else {
      $this->insertTemplateVar('backButtonStart', '');
      $this->insertTemplateVar('backButtonEnd', '');
    }
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/EventCalendar.html');
    
    $this->_insertBackButton();
      
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event_id','name','start','provider','center'));
    $res = $this->_app->db->doQuery($s->toString());
    $row1 = $this->_app->db->fetchAssoc($res);
    list($date,$time) = explode(' ', $row1['start']);
    list($year,$month,$day) = explode('-', $date);
    $date = sprintf('%s,%s,%s', $year, --$month, $day);
      
    $s = new SCenter;
    $s->addStatement(new SqlStatementBi($s->columns['center_id'], $row1['center'], '%s=%s'));
    $s->setColumnsMask(array('center_id','description'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    
    $minTime = '0';
    $maxTime = '24';
    
    $this->insertTemplateVar('title', $row1['name']);
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/EventCalendar.js',
                          array('url'=>$AJAX['adminUrl'],'providerId'=>$row1['provider'],'centerId'=>$row1['center'],'date'=>$date,'minTime'=>$minTime,'maxTime'=>$maxTime));
  }
}

?>
