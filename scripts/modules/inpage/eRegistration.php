<?php

class ModuleInPageRegistration extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('registration', 'InPageRegistrationValidator', true);
    $validator->setValues(array('step'=>1));
    if ($action = $this->_app->request->getParams('progressAction')) $validator->setValues(array('progressAction'=>$action));
    if ($action = $this->_app->request->getParams('finishAction')) $validator->setValues(array('finishAction'=>$action));
    
    if ($a = $validator->getVarValue('progressAction')) {
      parseNextActionFromString($module, $params, $a);
      if (is_array($params)&&count($params)) $this->_app->response->addParams($params);
      return $module;
    } else return 'vInPageRegistration';
  }
}

?>
