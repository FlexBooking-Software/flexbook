<?php

class ModuleInPageCzechTourismResource extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('inpage', 'InPageValidator');
    $validator->initValues();
    $tag = $validator->getVarValue('czechTourism_resourceTag');
    
    /*if (($tag=='praha')&&($validator->getVarValue('czechTourism_password')!='vipklient')) {
      $this->_app->dialog->set(array(
                'width'     => 400,
                'template'  => sprintf('
                    <div class="message">{__label.czechTourism_resourcePassword}</div>
                    <br/><input class="password" type="password" name="czechTourism_password" value=""/>
                    <br/><br />
                    <div class="button">
                      <input type="button" class="ui-button inputSubmit button" name="save" value="{__label.czechTourism_confirmPassword}" onclick="document.getElementById(\'%s\').click();"/>
                    </div>', $tag=='brno'?'buttonResource2':'buttonResource1'),
              ));
          
          $this->_app->response->addParams(array('backwards'=>1));
          return 'eBack';
    }*/
    
    #$validator->setValues(array('czechTourism_password'=>null));
    return 'vInPageCzechTourismResource';
  }
}

?>
