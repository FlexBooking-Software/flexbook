<?php

class ModuleDocumentTemplateDelete extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $bDocumentTemplate = new BDocumentTemplate($this->_app->request->getParams('id'));
    $data = $bDocumentTemplate->getData();
    $bDocumentTemplate->delete();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listDocumentTemplate_deleteOk'), $data['name']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
