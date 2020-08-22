<?php

class ModuleInPageCzechTourismEvent extends InPageModule {

  protected function _userInsert() {
    $this->setTemplateString('<form class="normal event" action="{%basefile%}" method="post">
  <div>
    <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
    <input type="hidden" name="id" value="{event_id}" />
    <div class="title"><label class="bold">{__label.inpage_event_name}:</label>
    <label>{name}</label></div>
    <label class="bold">{__label.inpage_event_center}:</label>
    <label>{center_name}</label><br/>
    <label class="bold">{__label.inpage_event_start}:</label>
    <label>{start}</label><br/>
    <label class="bold">{__label.inpage_event_end}:</label>
    <label>{end}</label><br/>
    <label class="bold">{__label.inpage_event_description}:</label>
    <label>{description}</label><br/>
    <div class="title"><label class="bold">{__label.inpage_event_price}:</label>
    <label>{price} {__label.currency_CZK}</label></div>
    <br/>
    {confirmDialog}
    <input class="button" type="submit" name="action_eBack" value="{__button.back}" />
    {reserveButton}
  </div>
</form>');
    
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
    
    $this->insertTemplateVar('confirmDialog', czechTourismGetConfirmDialog(), false);
    
    if (!$this->_app->auth->getUserId()) {
      $onclick = sprintf(' onclick="alert(\'%s\');return false;"', $this->_app->textStorage->getText('label.inpage_loginRequired'));
    } else {
      $onclick = '';
    }
    
    if ($row['free']) {
      $this->insertTemplateVar('reserveButton', sprintf('<input class="button" type="submit" name="action_eInPageCzechTourismEventReserve" value="%s" %s/>',
                                                        $this->_app->textStorage->getText('button.inpage_event_reserve'), $onclick), false);
    } else $this->insertTemplateVar('reserveButton', '');
  }
}

?>
