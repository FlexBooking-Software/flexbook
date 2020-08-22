<?php

class ModuleAvailExProfileCopy extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $bAvailExProfile = new BAvailabilityExceptionProfile($this->_app->request->getParams('id'));
    $data = $bAvailExProfile->getData();
    $bAvailExProfile->copy();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listAvailExProfile_copyOk'), $data['name']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
