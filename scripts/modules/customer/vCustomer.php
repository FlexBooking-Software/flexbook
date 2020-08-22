<?php

class ModuleCustomer extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSection('customer');
  
    $this->setTemplateString('
      <div class="users">
        <div class="contentTitle">{__label.listCustomer_title}</div>
        <div class="listCustomer">
          <form action="{%basefile%}" method="post">
            <div>
              <input type="hidden" name="sessid" value="{%sessid%}" />
              {newCustomer}
            </div>
          </form>
          {listCustomer}
        </div>
      </div>');
    
    $this->insert(new GuiListCustomer, 'listCustomer');
    
    if ($this->_app->auth->haveRight('user_admin', 'ANY')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listCustomer_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eCustomerEdit',
              'showDiv' => false)), 'newCustomer');
    } else {
      $this->insertTemplateVar('newCustomer', '');
    }
  }
}

?>
