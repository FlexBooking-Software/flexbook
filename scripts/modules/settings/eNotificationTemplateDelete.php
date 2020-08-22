<?php

class ModuleNotificationTemplateDelete extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $bNotificationTemplate = new BNotificationTemplate($this->_app->request->getParams('id'));
    $data = $bNotificationTemplate->getData();
    $bNotificationTemplate->delete();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listNotificationTemplate_deleteOk'), $data['name']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
