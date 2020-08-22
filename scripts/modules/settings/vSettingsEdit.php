<?php

class ModuleSettingsEdit extends ProjectModule {

  private function _insertGeneralForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          {children}
        </div>');
    
    $validator = Validator::get('settings', 'SettingsValidator');
    $this->insert(new GuiSettingsGeneral);
  }
  
  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSection('settings');
    
    if (!$subSection = $this->_app->request->getParams('section')) $subSection = 'general';
    $this->_app->auth->setSubSection($subSection);
    
    $this->_insertGeneralForm(); 
  }
}

?>
