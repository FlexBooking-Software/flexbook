<?php

class ModuleTagCommodityDelete extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $event = $this->_app->request->getParams('event');
    $resource = $this->_app->request->getParams('resource');
    $resourcePool = $this->_app->request->getParams('resourcePool');

    if ($event||$resource||$resourcePool) {
      if ($event) $event = explode(',',$event);
      else $event = array();
      if ($resource) $resource = explode(',',$resource);
      else $resource = array();
      if ($resourcePool) $resourcePool = explode(',',$resourcePool);
      else $resourcePool = array();

      $validator = Validator::get('tag', 'TagValidator');
      $bTag = new BTag($validator->getVarValue('id'));
      $bTag->deleteCommodityAssoc(array('event'=>$event,'resource'=>$resource,'resourcePool'=>$resourcePool));

      $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.editTag_commodityDeleteOk'));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
