<?php

class ModulePortalTemplateSave extends ExecModule {
  
  protected function _getPreview($validator) {
    if (isset($_FILES['previewFile']['size'])&&$_FILES['previewFile']['size']) {
      global $TMP_DIR;
  
      $newFileName = tempnam($TMP_DIR, 'FB_');
      copy($_FILES['previewFile']['tmp_name'], $newFileName);
      $_FILES['previewFile']['tmp_name'] = $newFileName;
      
      $validator->setValues(array('previewNew'=>array('file'=>$newFileName,'name'=>$_FILES['previewFile']['name'],'type'=>$_FILES['previewFile']['type'])));
    }
  }

  protected function _userRun() {
    $validator = Validator::get('portalTemplate','PortalTemplateValidator');
    $validator->initValues();
    
    $this->_getPreview($validator);
    
    $validator->validateValues();
    
    $id = $validator->getVarValue('id');    
    $bPortalTemplate = new BPortalTemplate($id?$id:null);
    $data = array(
        'name'                  => $validator->getVarValue('name'),
        'css'                   => $validator->getVarValue('css'),
        'content'               => $validator->getVarValue('content'),
        'page'                  => array_filter($validator->getVarValue('page')),
        );
    if ($validator->getVarValue('previewNew')) $data['preview'] = $validator->getVarValue('previewNew');
    
    $bPortalTemplate->save($data);
    
    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editPortalTemplate_saveOk'), $validator->getVarValue('name')));

    return 'eBack';
  }
}

?>
