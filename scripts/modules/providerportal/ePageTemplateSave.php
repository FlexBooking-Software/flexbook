<?php

class ModulePageTemplateSave extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('tag','PageTemplateValidator');
    $validator->initValues();
    $validator->validateValues();
    
    $id = $validator->getVarValue('id');    
    $bPageTemplate = new BPageTemplate($id?$id:null);
    $data = array(
        'name'                  => $validator->getVarValue('name'),
        'content'                => $validator->getVarValue('content'),
        );
    $bPageTemplate->save($data);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editPageTemplate_saveOk'), $validator->getVarValue('name')));

    return 'eBack';
  }
}

?>
