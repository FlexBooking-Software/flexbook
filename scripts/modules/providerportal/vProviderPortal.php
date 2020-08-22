<?php

class ModuleProviderPortal extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSubSection('tag');
  
    $this->setTemplateString('
          <div class="tags">
            <div class="contentTitle">{__label.listProviderPortal_title}</div>
            <div class="listProviderPortal">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newProviderPortal}
                </div>
              </form>
              {listProviderPortal}
            </div>
          </div>');
    
    $this->insert(new GuiListProviderPortal, 'listProviderPortal');
    
    if ($this->_app->auth->haveRight('customer_admin')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listProviderPortal_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eProviderPortalPrepare',
              'showDiv' => false)), 'newProviderPortal');
    } else {
      $this->insertTemplateVar('newProviderPortal', '');
    }
  }
}

?>
