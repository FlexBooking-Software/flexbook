<?php

class ModuleUserDocumentDelete extends ExecModule {

  protected function _userRun() {
    $bD = new BDocument($this->_app->request->getParams('id'));
    $data = $bD->getData();
    $bD->delete();

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.listDocument_deleteOk'), $data['number']));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
