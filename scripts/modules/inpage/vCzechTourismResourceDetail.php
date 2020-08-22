<?php

class ModuleInPageCzechTourismResourceDetail extends InPageModule {
  
  protected function _userInsert() {
    $this->setTemplateString('
        <form class="normal event" action="{%basefile%}" method="post">
          <div>
            <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
            <input type="hidden" name="id" value="{resource_id}" />
            <input type="hidden" name="from" value="{from}" />
            <input type="hidden" name="to" value="{to}" />
            <div class="title"><label class="bold">{__label.inpage_resource_name}:</label>
            <label>{name}</label></div>
            <label class="bold">{__label.inpage_resource_center}:</label>
            <label>{center_name}</label><br/>
            <label class="bold">{__label.inpage_resource_start}:</label>
            <label>{start}</label><br/>
            <label class="bold">{__label.inpage_resource_end}:</label>
            <label>{end}</label><br/>
            <label class="bold">{__label.inpage_resource_description}:</label>
            <label>{description}</label><br/>
            <br/>
            <input class="button" type="submit" name="action_eBack" value="{__button.back}" />
            {reserveButton}
          </div>
        </form>');
    
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_app->request->getParams('id'), '%s=%s'));
    $s->setColumnsMask(array('resource_id','name','center_name','description'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    $row['description'] = str_replace("\n",'<br/>',$row['description']);
    $row['from'] = $this->_app->request->getParams('time');
    $row['to'] = $this->_app->regionalSettings->increaseDateTime($row['from'],0,0,0,0,10);
    $row['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['from']);
    $row['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['to']);
    foreach($row as $key=>$value) {
      $this->insertTemplateVar($key, $value,false); 
    }
    
    if (!$this->_app->auth->getUserId()) {
      $onclick = sprintf(' onclick="alert(\'%s\');return false;"', $this->_app->textStorage->getText('label.inpage_loginRequired'));
    } else $onclick = '';
    
    $this->insertTemplateVar('reserveButton', sprintf('<input class="button" type="submit" name="action_eInPageResourceReserve" value="%s" %s/>',
                              $this->_app->textStorage->getText('button.inpage_event_reserve'), $onclick), false);
  }
}

?>
