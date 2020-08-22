<?php

class ModuleTagCommodityCopy extends ExecModule {

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $origTag = $this->_app->request->getParams('id');
    $targetTag = $this->_app->request->getParams('targetTag');
    $event = $this->_app->request->getParams('event');
    $resource = $this->_app->request->getParams('resource');
    $resourcePool = $this->_app->request->getParams('resourcePool');

    if ($targetTag&&($event||$resource||$resourcePool)) {
      $targetTag = explode(',',$targetTag);

      if ($event) $event = explode(',',$event);
      else $event = array();
      if ($resource) $resource = explode(',',$resource);
      else $resource = array();
      if ($resourcePool) $resourcePool = explode(',',$resourcePool);
      else $resourcePool = array();

      $params = array('addOnly'=>true,'event'=>$event,'resource'=>$resource,'resourcePool'=>$resourcePool);
      foreach($targetTag as $tag) {
        $bTag = new BTag($tag);
        $bTag->saveCommodityAssoc($params);
      }

      if ($origTag&&$this->_app->request->getParams('deleteCopied')) {
        $bTag = new BTag($origTag);
        $bTag->deleteCommodityAssoc($params);

        $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.editTag_commodityMoveOk'));
      } else {
        $this->_app->messages->addMessage('userInfo', $this->_app->textStorage->getText('info.editTag_commodityCopyOk'));
      }
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
