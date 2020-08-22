<?php

class ModulePageTemplateEdit extends ProjectModule {

  protected function _userInsert() {
    $this->setTemplateString('
        <div class="settings">
          <div class="settingsContent">{children}</div>
        </div>');
  
    $this->insert(new GuiEditPageTemplate);
  }
}

?>
