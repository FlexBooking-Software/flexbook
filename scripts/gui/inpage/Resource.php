<?php

class GuiInPageResource extends GuiElement {

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/Resource.html');
      
    $id = $this->_app->request->getParams('id');
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $id, '%s=%s'));
    $s->setColumnsMask(array('name','description','availabilityprofile_name','price'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    
    $minTime = '24';
    $maxTime = '0';
    $b = new BResource($id);
    $bAvailability = $b->getAvailabilityProfileData();
    foreach ($bAvailability as $weekDay=>$dayAvailability) {
      if ($dayAvailability['from']<$minTime) $minTime = substr($dayAvailability['from'],0,2);
      if ($maxTime<$dayAvailability['to']) $maxTime = substr($dayAvailability['to'],0,2);
    }
    
    $this->insertTemplateVar('name', $row['name']);
    $this->insertTemplateVar('description', $row['description']);
    $this->insertTemplateVar('price', $this->_app->regionalSettings->convertNumberToHuman($row['price']));
    
    if (!$this->_app->auth->getUserId()) {
      $loginRequiredResource = sprintf('select: function(start,end,allDay) { alert("%s"); calendar.fullCalendar(\'unselect\'); },',
                               $this->_app->textStorage->getText('label.inpage_loginRequired'));
      $loginRequiredEvent = sprintf('alert(\'%s\'); return;',
                               $this->_app->textStorage->getText('label.inpage_loginRequired'));
    } else {
      $loginRequiredResource = '';
      $loginRequiredEvent = '';
    }
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/Resource.js',
                          array('url'=>$AJAX['url'],'resourceId'=>$id,'minTime'=>$minTime,'maxTime'=>$maxTime,
                                'loginRequiredResource'=>$loginRequiredResource,'loginRequiredEvent'=>$loginRequiredEvent,
                                'userId'=>$this->_app->auth->getUserId(),'userName'=>$this->_app->auth->getFullName()));
  }
}

?>
