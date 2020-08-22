<?php

class GuiChangePassword extends GuiElement {

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ChangePassword.html');

    $validator = Validator::get('changePassword', 'ChangePasswordValidator');
    $data = $validator->getValues();

    foreach ($data as $key=>$value) {
      $this->insertTemplateVar($key, $value);
    }
  }
}

?>
