<?php

class ModuleProviderAccountType extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('providerAccountType');
  
    $this->setTemplateString('
          <div class="providerAccountType">
            <div class="contentTitle">{__label.listProviderAccountType_title}</div>
            <div class="listProviderAccountType">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {new}
                </div>
              </form>
              {list}
            </div>
          </div>');
    
    $this->insert(new GuiListProviderAccountType, 'list');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listProviderAccountType_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eProviderAccountTypeEdit',
            'showDiv' => false)), 'new');
  }
}

?>
