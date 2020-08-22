<?php

class ModuleAvailExProfileDelete extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $bAvailExProfile = new BAvailabilityExceptionProfile($this->_app->request->getParams('id'));
    $data = $bAvailExProfile->getData();
    $bAvailExProfile->delete();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listAvailExProfile_deleteOk'), $data['name']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
