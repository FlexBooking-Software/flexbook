<?php

class ModuleReservationCondition extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('reservationCondition');
  
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.listReservationCondition_title}</div>
          <div class="listReservationCondition">
            <form action="{%basefile%}" method="post">
              <div>
                <input type="hidden" name="sessid" value="{%sessid%}" />
                {newReservationCondition}
              </div>
            </form>
            {listReservationCondition}
          </div>
        </div>');
    
    $this->insert(new GuiListReservationCondition, 'listReservationCondition');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listReservationCondition_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eReservationConditionEdit',
            'showDiv' => false)), 'newReservationCondition');
  }
}

?>
