<?php

class ModuleEvent extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSection('event');
  
    $this->setTemplateString('
      <div class="event">
        <div id="tab">
          <ul>
            <li><a href="#tab-1">{__label.listEvent_title}</a></li>
            <li><a href="#tab-2">{__label.listEvent_titleCycle}</a></li>
          </ul>
          <div id="tab-1">
            <div id="fi_eventList" class="listEvent">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newEvent}{newGroupEvent}
                </div>
              </form>
              {listEvent}
            </div>
          </div>
          <div id="tab-2">
            <div id="fi_eventCycleList" class="listEventCycle">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newEventCycle}
                </div>
              </form>
              {listEventCycle}
            </div>
          </div>
        </div>
      </div>');

    $this->_app->document->addJavascript("
      $(function() {
        var tabCookieName = 'ui-eventlist-tab';
        var tab = $('#tab').tabs({
          active : $.cookie(tabCookieName),
          activate : function( event, ui ) {
            var newIndex = ui.newTab.parent().children().index(ui.newTab);
            // my setup requires the custom path, yours may not
            $.cookie(tabCookieName, newIndex);
          }
        });
      })");
    
    $this->insert(new GuiListEvent, 'listEvent');
    $this->insert(new GuiListEventCycle, 'listEventCycle');
    
    if ($this->_app->auth->haveRight('commodity_admin',$this->_app->auth->getActualProvider())) {
      $this->insert(new GuiFormButton(array(
        'label' => $this->_app->textStorage->getText('button.listEvent_new'),
        'classInput' => 'inputSubmit',
        'action' => 'eEventEdit',
        'showDiv' => false
      )), 'newEvent');
      $this->insertTemplateVar('newGroupEvent', '&nbsp;', false);
      $this->insert(new GuiFormButton(array(
        'label' => $this->_app->textStorage->getText('button.listEvent_newGroup'),
        'classInput' => 'inputSubmit',
        'action' => 'eEventGroupCreate',
        'showDiv' => false
      )), 'newGroupEvent');
      $this->insert(new GuiFormButton(array(
        'label' => $this->_app->textStorage->getText('button.listEvent_newCycle'),
        'classInput' => 'inputSubmit',
        'action' => 'eEventCycleCreate',
        'showDiv' => false
      )), 'newEventCycle');
    } elseif ($this->_app->auth->haveRight('power_organiser',$this->_app->auth->getActualProvider())) {
      $this->insert(new GuiFormButton(array(
        'label' => $this->_app->textStorage->getText('button.listEvent_new'),
        'classInput' => 'inputSubmit',
        'action' => 'eEventEdit',
        'showDiv' => false
      )), 'newEvent');
      $this->insert(new GuiFormButton(array(
        'label' => $this->_app->textStorage->getText('button.listEvent_newCycle'),
        'classInput' => 'inputSubmit',
        'action' => 'eEventCycleCreate',
        'showDiv' => false
      )), 'newEventCycle');
      $this->insertTemplateVar('newGroupEvent', '');
    } else {
      $this->insertTemplateVar('newEvent', '');
      $this->insertTemplateVar('newGroupEvent', '');
      $this->insertTemplateVar('newEventCycle', '');
    }
  }
}

?>
