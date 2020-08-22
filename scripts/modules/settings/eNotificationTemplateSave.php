<?php

class ModuleNotificationTemplateSave extends ExecModule {
  
  private function _saveItem($validator) {
    $newItem = $this->_app->request->getParams('newItem');
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
        
        $item[$index] = $params;
      }
    }
    
    $validator->setValues(array('item'=>$item));
  }
  
  private function _getItem($item) {
    $ret = array();

    foreach ($item as $t) {
      $i = array(
        'name'           => ifsetor($t['name']),
        'type'           => ifsetor($t['type']),
        'toProvider'     => ifsetor($t['toProvider']),
        'toOrganiser'    => ifsetor($t['toOrganiser']),
        'toUser'         => ifsetor($t['toUser']),
        'toAttendee'     => ifsetor($t['toAttendee']),
        'toSubstitute'   => ifsetor($t['toSubstitute']),
        'fromAddress'    => ifsetor($t['fromAddress']),
        'ccAddress'      => ifsetor($t['ccAddress']),
        'bccAddress'     => ifsetor($t['bccAddress']),
        'contentType'    => ifsetor($t['contentType']),
        'subject'        => ifsetor($t['subject']),
        'body'           => ifsetor($t['body'])
      );
      
      $offset = ifsetor($t['offsetCount']);
      if (!strcmp($t['offsetUnit'],'hour')) $offset = 60*$offset;
      elseif (!strcmp($t['offsetUnit'],'day')) $offset = 1440*$offset;
      $i['offset'] = $offset;
      
      $ret[] = $i;
    }
    
    return $ret;
  }

  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('notificationTemplate','NotificationTemplateValidator');
    $validator->initValues();
    
    $this->_saveItem($validator);
    
    $validator->validateValues();

    $id = $validator->getVarValue('id');
    $item = $this->_getItem($validator->getVarValue('item'));
    #adump($validator->getVarValue('condition'));
    #adump($item);die;

    $bNotificationTemplate = new BNotificationTemplate($id?$id:null);
    $bNotificationTemplate->save(array(
      'name'        => $validator->getVarValue('name'),
      'target'      => $validator->getVarValue('target'),
      'providerId'  => $validator->getVarValue('providerId'),
      'description' => $validator->getVarValue('description'),
      'item'        => $item,
    ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editNotificationTemplate_saveOk'), $validator->getVarValue('name')));

    if ($validator->getVarValue('fromEvent')) {
      $eValidator = Validator::get('event', 'EventValidator');
      $eValidator->setValues(array('notificationTemplateId' => $bNotificationTemplate->getId()));
    } elseif ($validator->getVarValue('fromResource')) {
      $eValidator = Validator::get('resource', 'ResourceValidator');
      $eValidator->setValues(array('notificationTemplateId' => $bNotificationTemplate->getId()));
    }

    return 'eBack';
  }
}

?>
