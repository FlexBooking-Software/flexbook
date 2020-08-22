<?php

class ModuleResourcePoolSave extends ExecModule {
  
  protected function _userRun() {
    $validator = Validator::get('resourcePool','ResourcePoolValidator');
    $validator->initValues();
    #adump($validator->getValues());die;
    
    parseNextActionFromRequest($nextAction, $nextActionParams);

    switch ($nextAction) {
      case 'resourcePoolAdd':
        return 'vResourcePoolResourceSelect';
      default: break;
    }
    
    $validator->validateValues();
    
    $id = $validator->getVarValue('id');
    $data = array();
    $data['providerId'] = $validator->getVarValue('providerId');
    $data['centerId'] = $validator->getVarValue('centerId');
    $data['externalId'] = $validator->getVarValue('externalId');
    $data['name'] = $validator->getVarValue('name');
    $data['description'] = $validator->getVarValue('description');
    $data['tag'] = $validator->getVarValue('tag');
    $data['active'] = $validator->getVarValue('active');
    $data['resource'] = $validator->getVarValue('resourceId');
    $data['urlPhoto'] = $validator->getVarValue('urlPhoto');
      
    $b = new BResourcePool($id?$id:null);
    $name = $b->save($data);
      
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editResourcePool_saveOk'), $data['name']));

    return 'eBack';
  }
}

?>
