<?php

class GuiEditPageTemplate extends GuiElement {

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/PageTemplateEdit.html');

    $validator = Validator::get('tag', 'PageTemplateValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editPageTemplate_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editPageTemplate_titleExisting'));
    }
  }
}

?>
