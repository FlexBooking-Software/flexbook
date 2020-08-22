<?php

class ModulePriceList extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSubSection('priceList');
  
    $this->setTemplateString('
          <div class="priceList">
            <div class="contentTitle">{__label.listPriceList_title}</div>
            <div class="listPriceList">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {new}
                </div>
              </form>
              {list}
            </div>
          </div>');
    
    $this->insert(new GuiListPriceList, 'list');
    
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listPriceList_new'),
            'classInput' => 'inputSubmit',
            'action' => 'ePriceListEdit',
            'showDiv' => false)), 'new');
  }
}

?>
