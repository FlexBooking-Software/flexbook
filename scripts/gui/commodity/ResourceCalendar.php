<?php

class GuiResourceCalendar extends GuiElement {
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
    $this->setTemplateFile(dirname(__FILE__).'/ResourceCalendar.html');
    
    $this->_insertBackButton();
      
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('name','provider'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    
    $minTime = '24';
    $maxTime = '0';
    $b = new BResource($this->_id);
    $bAvailability = $b->getAvailabilityProfileData();
    foreach ($bAvailability as $weekDay=>$dayAvailability) {
      if ($dayAvailability['from']<$minTime) $minTime = substr($dayAvailability['from'],0,2);
      if ($maxTime<$dayAvailability['to']) $maxTime = substr($dayAvailability['to'],0,2);
    }
    
    $this->insertTemplateVar('title', $row['name']);
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/ResourceCalendar.js',
                          array('url'=>$AJAX['adminUrl'],'providerId'=>$row['provider'],'resourceId'=>$this->_id,'minTime'=>$minTime,'maxTime'=>$maxTime));
  }
}

?>
