<?php

class ModuleTagSave extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('tag','TagValidator');
    $validator->initValues();
    $validator->validateValues();
    
    $eventId = $validator->getVarValue('id');    
    $bTag = new BTag($eventId?$eventId:null);
    $data = array(
        'name'                  => $validator->getVarValue('name'),
        'portal'                => $validator->getVarValue('portal'),
        );
    $bTag->save($data);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editTag_saveOk'), $validator->getVarValue('name')));

    return 'eBack';
  }
}

?>
