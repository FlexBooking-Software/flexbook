<?php

class ModuleReportSort extends ExecModule {

  protected function _userRun() {
    $orderColumn = $this->_app->request->getParams('order');
    $direction = $this->_app->request->getParams('orderDirection');
    
    $validator = Validator::get('result', 'ReportValidator');
    $result = $validator->getVarValue('result');
    //adump($result);
    
    $head = $result[0];
    unset($result[0]);
    
    $sortHash = array();
    foreach ($result as $index=>$row) {
      $sortHash[$index] = $row[array_keys($row)[$orderColumn]];
    }
    array_multisort($sortHash, !strcmp($direction,'asc')?SORT_ASC:SORT_DESC, $result);
    
    $result = array_merge(array($head),$result);

    $validator->setValues(array('result'=>$result));
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
