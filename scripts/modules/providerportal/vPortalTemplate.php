<?php

class ModulePortalTemplate extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSubSection('tag');
  
    $this->setTemplateString('
          <div class="tags">
            <div class="contentTitle">{__label.listPortalTemplate_title}</div>
            <div class="listPortalTemplate">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newPortalTemplate}
                </div>
              </form>
              {listPortalTemplate}
            </div>
          </div>');
    
    $this->insert(new GuiListPortalTemplate, 'listPortalTemplate');
    
    if ($this->_app->auth->haveRight('customer_admin')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listPortalTemplate_new'),
              'classInput' => 'inputSubmit',
              'action' => 'ePortalTemplateEdit',
              'showDiv' => false)), 'newPortalTemplate');
    } else {
      $this->insertTemplateVar('newPortalTemplate', '');
    }
  }
}

?>
