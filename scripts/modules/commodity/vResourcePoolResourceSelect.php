<?php

class ModuleResourcePoolResourceSelect extends ProjectModule {

  protected function _userInsert() {
    $this->setTemplateString('
          <div class="listResourceForResourcePool">
            <div class="contentTitle">{title}</div>
            <form action="{%basefile%}" method="post">
              <div class="formButton">
                <input type="hidden" name="sessid" value="{%sessid%}" />
                <input type="submit" name="action_eBack" value="{__button.back}"/>
              </div>
            </form>
            {listResource}
            <form action="{%basefile%}" method="post">
              <div class="formButton">
                <input type="hidden" name="sessid" value="{%sessid%}" />
                <input type="submit" name="action_eBack" value="{__button.back}"/>
              </div>
            </form>
          </div>');
    
    $validator = Validator::get('resourcePool', 'ResourcePoolValidator');
    $data = $validator->getValues();
    
    $this->insertTemplateVar('title', sprintf($this->_app->textStorage->getText('label.editResourcePool_titleResource'), $data['name']));
    $this->insert(new GuiListResource('listResourceForResourcePool'), 'listResource');
  }
}

?>
