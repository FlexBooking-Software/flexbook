<?php

class ModuleInvoice extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSection('invoice');
  
    $this->setTemplateString('
          <div class="invoice">
            <div class="contentTitle">{__label.listInvoice_title}</div>
            <div class="listInvoice">
              {list}
            </div>
          </div>');
    
    $this->insert(new GuiListProviderInvoice('listInvoice'), 'list');
  }
}

?>
