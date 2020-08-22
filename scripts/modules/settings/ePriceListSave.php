<?php

class ModulePriceListSave extends ExecModule {

  private function _saveSeason($validator) {
    $newSeason = $this->_app->request->getParams('newSeason');
    
    $season = array();
    if (is_array($newSeason)) {
      foreach ($newSeason as $index=>$oneSeason) {
        $cParams = explode(';', $oneSeason);
        
        $params = array();
        foreach ($cParams as $par) {
          list($key,$value) = explode('~',$par);
          $params[$key] = $value;
        }
        
        $season[$index] = $params;
      }
    }
    
    $validator->setValues(array('season'=>$season));
  }

  private function _getSeason($season) {
    $ret = array();
    foreach ($season as $index=>$s) {
      $ret[$index] = array(
          'name'      => $s['name'],
          'start'     => $this->_app->regionalSettings->convertHumanToDate($s['start'], 'd.m.'),
          'end'       => $this->_app->regionalSettings->convertHumanToDate($s['end'], 'd.m.'),
          'basePrice' => $this->_app->regionalSettings->convertHumanToNumber($s['basePrice'],10,2),
          'monPrice'  => $s['monPrice'],
          'tuePrice'  => $s['tuePrice'],
          'wedPrice'  => $s['wedPrice'],
          'thuPrice'  => $s['thuPrice'],
          'friPrice'  => $s['friPrice'],
          'satPrice'  => $s['satPrice'],
          'sunPrice'  => $s['sunPrice'],
          );
    }
    
    return $ret;
  }
  
  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('priceList','PriceListValidator');
    $validator->initValues();
    $this->_saveSeason($validator);
    
    $validator->validateValues();

    $id = $validator->getVarValue('id');
    
    $bPriceList = new BPriceList($id?$id:null);
    $bPriceList->save(array(
        'name'        => $validator->getVarValue('name'),
        'providerId'  => $validator->getVarValue('providerId'),
        'season'      => $this->_getSeason($validator->getVarValue('season')),
        ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editPriceList_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('priceList' => $bPriceList->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('priceList' => $bPriceList->getId()));
    }

    return 'eBack';
  }
}

?>
