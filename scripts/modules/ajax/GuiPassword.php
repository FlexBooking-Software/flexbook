<?php

class AjaxGuiPassword extends AjaxGuiAction2 {
  
  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_password', $this->_params['prefix']);
    $this->_class = 'flb_password';
  }

  protected function _createTemplate() {
    $this->_guiHtml = '
          <div class="label flb_title"><span>{__label.ajax_password_title}</span></div>
          <div class="group">
            <label class="label flb_password_password_old_label"><span>{__label.ajax_password_old}:</span></label>
            <input type="password" id="{prefix}flb_password_old" value="" />
           </div>
          <div class="group">
            <label class="label flb_password_password_new_label"><span>{__label.ajax_password_new}:</span></label>
            <input type="password" id="{prefix}flb_password_new" value="" />
          </div>
          <div class="group">
            <label class="label flb_password_password_retype_label"><span>{__label.ajax_password_retype}:</span></label>
            <input type="password" id="{prefix}flb_password_retype" value="" />
          </div>
          <div class="button">
            <input type="button" id="{prefix}flb_password_back" value="{backLabel}" />
            <input type="button" id="{prefix}flb_password_change" class="flb_primaryButton" value="{__button.ajax_password_change}" />
          </div>';
          
    $this->_guiHtml .= "<script>
                     $(document).ready(function() {
                       $('#{prefix}flb_password').on('click','#{prefix}flb_password_change', function() {
                          $.ajax({
                              type: 'POST',
                              dataType: 'json',
                              data: {
                                  sessid: $('#flb_core_sessionid').val(),
                                  oldPassword: $('#{prefix}flb_password_old').val(),
                                  newPassword: $('#{prefix}flb_password_new').val(),
                                  retypePassword: $('#{prefix}flb_password_retype').val(),
                              },
                              url: $('#flb_core_url').val()+'action=changePassword',
                              success: function(data) {
                                  if (data.error) alert(data.message);
                                  else {
                                    {backAction}
                                    
                                    alert('{__info.changePassword_ok}');
                                  }
                              },
                              error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                          });            
                       });
                       
                       $('#{prefix}flb_password').on('click','#{prefix}flb_password_back', function() {
                          {backAction}
                       });
                     });
                   </script>";
  }
                   
  protected function _getData() {
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['backAction'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
    } else {
      $this->_guiParams['backAction'] = sprintf("flbLoadHtml('guiProfile', $('#%sflb_password').parent(), %s);", $this->_params['prefix'], $this->_guiParams['params']);
    }   
    
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.close');
    } else {
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.back');
    }
  }
  
  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    parent::_userRun();
  }
}

?>
