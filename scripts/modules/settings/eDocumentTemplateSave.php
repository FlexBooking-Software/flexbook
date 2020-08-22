<?php

class ModuleDocumentTemplateSave extends ExecModule {
  
  private function _saveItem($validator) {
    $newItem = $this->_app->request->getParams('newItem');
    $newItemContent = $this->_app->request->getParams('newItemContent');
    #adump($newItem);
    
    $item = array();
    if (is_array($newItem)) {
      foreach ($newItem as $index=>$oneItem) {
        $cParams = explode(';', $oneItem);
        
        $params = array();
        foreach ($cParams as $par) {
          list($key,$value) = explode('~',$par);
          $params[$key] = $value;
        }

        // content mam v samostatnem poli
        $params['content'] = $newItemContent[$index];
        
        $item[$index] = $params;
      }
    }
    
    $validator->setValues(array('item'=>$item));
  }
  
  private function _getItem($item) {
    $ret = array();

    foreach ($item as $t) {
      $i = array(
        'itemId'         => ifsetor($t['itemId']),
        'name'           => ifsetor($t['name']),
        'code'           => ifsetor($t['code']),
        'type'           => ifsetor($t['type']),
        'number'         => ifsetor($t['number']),
        'content'        => ifsetor($t['content'])
      );

      $ret[] = $i;
    }
    
    return $ret;
  }

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('documentTemplate','DocumentTemplateValidator');
    $validator->initValues();
    
    $this->_saveItem($validator);
    
    $validator->validateValues();

    $id = $validator->getVarValue('id');
    $item = $this->_getItem($validator->getVarValue('item'));
    #adump($item);die;

    $bDocumentTemplate = new BDocumentTemplate($id?$id:null);
    $bDocumentTemplate->save(array(
      'name'        => $validator->getVarValue('name'),
      'target'      => $validator->getVarValue('target'),
      'providerId'  => $validator->getVarValue('providerId'),
      'description' => $validator->getVarValue('description'),
      'item'        => $item,
    ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editDocumentTemplate_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('documentTemplateId' => $bDocumentTemplate->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('documentTemplateId' => $bDocumentTemplate->getId()));
    }

    return 'eBack';
  }
}

?>
