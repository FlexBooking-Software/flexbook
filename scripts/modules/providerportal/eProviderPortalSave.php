<?php

class ModuleProviderPortalSave extends ExecModule {
  
  private function _saveMenu($validator) {
    $menuId = $this->_app->request->getParams('menuId');
    $menuName = $this->_app->request->getParams('menuName');
    $menuPage = $this->_app->request->getParams('menuPage');
    
    $newMenu = array();
    foreach ($menuName as $index=>$name) {
      if (!$index) continue;
      
      $newMenu[$index] = array(
          'id'      => $menuId[$index],
          'name'    => $name,
          'page'    => $menuPage[$index],
          );
    }
    
    $validator->setValues(array('menu'=>$newMenu));
  }
  
  private function _validateMenu($validator) {
    foreach ($validator->getVarValue('menu') as $item) {
      if (!$item['name']) throw new ExceptionUserTextStorage('error.editProviderPortal_menuNoName');
      if (!$item['page']) throw new ExceptionUserTextStorage('error.editProviderPortal_menuNoPage');
    }
  }
  
  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator');
    $validator->initValues();  
    $this->_saveMenu($validator);
    
    $validator->validateLastValues();
    $this->_validateMenu($validator);
    
    $id = $validator->getVarValue('id');    
    $bProviderPortal = new BProviderPortal($id?$id:null);
    $data = array(
        'providerId'            => $validator->getVarValue('providerId'),
        'active'                => $validator->getVarValue('active'),
        'name'                  => $validator->getVarValue('name'),
        'urlName'               => $validator->getVarValue('urlName'),
        'css'                   => $validator->getVarValue('css'),
        'javascript'            => $validator->getVarValue('javascript'),
        'content'               => $validator->getVarValue('content'),
        'menu'                  => $validator->getVarValue('menu'),
        'homePage'              => $validator->getVarValue('homePage'),
        );
    
    $bProviderPortal->save($data);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editProviderPortal_saveOk'), $validator->getVarValue('name')));

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
