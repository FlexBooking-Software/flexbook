<?php

class ModuleUser extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSection('user');
  
    $this->setTemplateString('
      <div class="users">
        <div class="contentTitle">{__label.listUser_title}</div>
        <div class="listUser">
          <form action="{%basefile%}" method="post">
            <div>
              <input type="hidden" name="sessid" value="{%sessid%}" />
              {newUser}
            </div>
          </form>
          {listUser}
        </div>
      </div>');
    
    $this->insert(new GuiListUser, 'listUser');
    
    if ($this->_app->auth->haveRight('user_admin', 'ANY')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listUser_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eUserEdit',
              'showDiv' => false)), 'newUser');
    } else {
      $this->insertTemplateVar('newUser', '');
    }
  }
}

?>
