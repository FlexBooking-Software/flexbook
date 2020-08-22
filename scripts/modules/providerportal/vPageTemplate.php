<?php

class ModulePageTemplate extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSubSection('tag');
  
    $this->setTemplateString('
          <div class="tags">
            <div class="contentTitle">{__label.listPageTemplate_title}</div>
            <div class="listPageTemplate">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newPageTemplate}
                </div>
              </form>
              {listPageTemplate}
            </div>
          </div>');
    
    $this->insert(new GuiListPageTemplate, 'listPageTemplate');
    
    if ($this->_app->auth->haveRight('customer_admin')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listPageTemplate_new'),
              'classInput' => 'inputSubmit',
              'action' => 'ePageTemplateEdit',
              'showDiv' => false)), 'newPageTemplate');
    } else {
      $this->insertTemplateVar('newPageTemplate', '');
    }
  }
}

?>
