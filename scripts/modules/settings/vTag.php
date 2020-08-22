<?php

class ModuleTag extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('tag');
  
    $this->setTemplateString('
          <div class="tags">
            <div class="contentTitle">{__label.listTag_title}</div>
            <div class="listTag">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newTag}
                </div>
              </form>
              {listTag}
            </div>
          </div>');
    
    $this->insert(new GuiListTag, 'listTag');
    
    if ($this->_app->auth->haveRight('customer_admin')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listTag_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eTagEdit',
              'showDiv' => false)), 'newTag');
    } else {
      $this->insertTemplateVar('newTag', '');
    }
  }
}

?>
