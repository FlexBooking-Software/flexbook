<?php

class ModuleTagPrepareCommodityCopy extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $gs = new GridSettingsTag('listSimilarTag');
      $filter = $gs->getFilter();

      $bTag = new BTag($id);
      $provider = ifsetor($filter['provider']);
      $commodity = $bTag->getCommodityAssoc($provider);

      $validator = Validator::get('tag','TagValidator');

      $this->_app->response->addParams(array('deleteCopied'=>'0','id'=>$id,'targetTag'=>$validator->getVarValue('id'),
                                              'event'=>implode(',',$commodity['event']),
                                              'resource'=>implode(',',$commodity['resource']),
                                              'resourcePool'=>implode(',',$commodity['resourcePool'])));
      return 'eTagCommodityCopy';
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
