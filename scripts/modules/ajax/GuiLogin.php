<?php

class AjaxGuiLogin extends AjaxGuiProfile {
  
  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_login', $this->_params['prefix']);
    $this->_class = 'flb_profile';
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = '{externalAccountGui}
        <div class="group">
          <label class="label shortLabel" id="{prefix}flb_profile_username_label"><span>{__label.ajax_profile_username}:</span></label>
          <input type="text" name="username" id="{prefix}flb_profile_username" value="" />
        </div>
        <div class="group">
          <label class="label shortLabel" id="{prefix}flb_profile_password_label"><span>{__label.ajax_profile_password}:</span></label>
          <input type="password" name="password" id ="{prefix}flb_profile_password" value="" />
        </div>
        {toggle_sendPassword}
        <div class="button">
          {button_close}
          {button_sendPassword}
          {button_login}
          {button_registratePrepare}
          {button_sendConfirmation}
        </div>';
  }
  
  protected function _getData() {
    $this->_getButtons();
  }
}

?>
