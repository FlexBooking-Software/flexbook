<?php

class AjaxGuiRegistration extends AjaxGuiProfileDetail {
  
  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_registration', $this->_params['prefix']);
    $this->_class = 'flb_profile';
  }
  
  protected function _createTemplate() {
    switch ($this->_params['step']) {
      case 1:
        // formular na vlozeni emailove adresy
        $this->_guiHtml = sprintf('
            <!--<div class="label flb_title"><span>{__label.ajax_profile_registration_title1}</span></div>-->
            {externalAccountGui}
            <div class="group">
              <label class="label shortLabel" id="{prefix}flb_profile_username_label"><span>{__label.ajax_profile_username}:</span></label>
              <input type="text" id="{prefix}flb_registration_email" value="%s" />
            </div>
            <input type="hidden" id="{prefix}flb_registration_step" value="1" />
            <input type="hidden" id="{prefix}flb_registration_language" value="{language}" />
            <div class="button">
              <input type="button" id="{prefix}flb_registration_prev" value="{buttonBackLabel}" />
              <input type="button" id="{prefix}flb_registration_next" value="{__button.ajax_profile_next}" />
            </div>', ifsetor($this->_params['email']));
        
        if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) $backAction = "$('#{prefix}flb_profile_extra').hide(); enableButtons();";
        else $backAction = "flbLoadHtml('guiProfile', $('#{prefix}flb_registration').parent(), {params});";
        
        $this->_guiHtml .= sprintf("
              <script>
              $(document).ready(function() {
                function enableButtons() {
                  $('#{prefix}flb_profile_login_prepare').attr('disabled', false);
                  $('#{prefix}flb_profile_login').attr('disabled', false);
                  $('#{prefix}flb_profile_login_account').attr('disabled', false);
                  $('#{prefix}flb_profile_registrate').attr('disabled', false);
                } 
              
                $('#{prefix}flb_registration').on('click','#{prefix}flb_registration_prev', function() {
                  %s
                });
                
                $('#{prefix}flb_registration').on('click','#{prefix}flb_registration_next', function() {
                  $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        language: $('#{prefix}flb_registration_language').val(),
                        provider: $('#flb_core_provider').val(),
                        step: $('#{prefix}flb_registration_step').val(),
                        email: $('#{prefix}flb_registration_email').val()
                    },
                    url: $('#flb_core_url').val()+'action=registrateUser',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      else {
                        var newParams = { step:data.step, email: $('#{prefix}flb_registration_email').val() };
                        if (data.userNotValidated) newParams.userNotValidated = data.userNotValidated;
                        
                        flbLoadHtml('guiRegistration',$('#{prefix}flb_registration').parent(), $.extend({params}, newParams));
                      }
                    },
                    error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                  });  
                });
              });
              </script>", $backAction);

        break;
      case 2:
        // formular na emailovou adresu a heslo pro potvrzeni existujiciho uctu
        // v pripade, ze ucet jeste neni potvrzen, je vyzadovano nejdrive potvrzeni uctu
        if (isset($this->_params['userNotValidated'])&&$this->_params['userNotValidated']) {
          $this->_guiHtml = sprintf('
            <div class="label flb_title"><span>{__label.ajax_profile_registration_title2_notValidated}</span></div>
            <br />
            <input type="hidden" id="{prefix}flb_registration_email" value="%s" />
            <input type="hidden" id="{prefix}flb_registration_step" value="2" />
            <input type="hidden" id="{prefix}flb_registration_language" value="{language}" />
            <div class="button">
              <input type="button" id="{prefix}flb_registration_prev" value="{__button.back}" />
              <input type="button" id="{prefix}flb_registration_sendConfirmation" value="{__button.ajax_profile_sendConfirmation}" />
            </div>', ifsetor($this->_params['email']));
        } else {
          $this->_guiHtml = sprintf('
            <div class="label flb_title"><span>{__label.ajax_profile_registration_title2}</span></div>
            <div class="group">
              <label class="label flb_profile_email_label"><span>{__label.ajax_profile_email}:</span></label>
              <input type="text" id="{prefix}flb_registration_email" value="%s" />
            </div>
            <div class="group">
              <label class="label flb_profile_password_label"><span>{__label.ajax_profile_password}:</span></label>
              <input type="password" id="{prefix}flb_registration_password" value="" />
            </div>
            <input type="hidden" id="{prefix}flb_registration_step" value="2" />
            <input type="hidden" id="{prefix}flb_registration_language" value="{language}" />
            <div class="button">
              <input type="button" id="{prefix}flb_registration_prev" value="{__button.back}" />
              <input type="button" id="{prefix}flb_registration_sendPassword" value="{__button.ajax_profile_sendPassword}" />
              <input type="button" id="{prefix}flb_registration_next" value="{__button.ajax_profile_next}" />
            </div>', ifsetor($this->_params['email']));
        }
        
        if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) $backAction = "$('#{prefix}flb_profile_extra').hide(); if (typeof flbRefresh == 'function') flbRefresh('.flb_output');";
        else $backAction = "flbLoadHtml('guiProfile', $('#{prefix}flb_registration').parent(), {params});";
        
        $this->_guiHtml .= sprintf("
              <script>
              $(document).ready(function() {
                $('#{prefix}flb_registration').on('click','#{prefix}flb_registration_prev', function() {
                  flbLoadHtml('guiRegistration', $('#{prefix}flb_registration').parent(), $.extend({params}, { step: 1, email: $('#{prefix}flb_registration_email').val() })); 
                });
                
                $('#{prefix}flb_registration').on('click','#{prefix}flb_registration_sendPassword', function() {
                  $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        language: $('#{prefix}flb_registration_language').val(),
                        provider: $('#flb_core_provider').val(),
                        step: 'sendPassword',
                        email: $('#{prefix}flb_registration_email').val()
                    },
                    url: $('#flb_core_url').val()+'action=registrateUser',
                    success: function(data) {
                      if (data.error) alert(data.message);
                      else alert('{__label.ajax_profile_passwordSent}');
                    },
                    error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                  });  
                });
                
                $('#{prefix}flb_registration').on('click','#{prefix}flb_registration_sendConfirmation', function() {
                  $.ajax({
                    type: 'POST',
                    url: $('#flb_core_url').val()+'action=sendRegistrationEmail',
                    dataType: 'json',
                    data: {
                        language: $('#{prefix}flb_registration_language').val(),
                        provider: $('#flb_core_provider').val(),
                        email: $('#{prefix}flb_registration_email').val()
                    },
                    success: function(data) {
                      if (data.error) alert(data.message);
                      else {
                        alert('{__label.ajax_profile_confirmationSent}');
                        $('#{prefix}flb_registration_prev').click();
                      }
                    },
                    error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                  });  
                });
                
                $('#{prefix}flb_registration').on('click','#{prefix}flb_registration_next', function() {
                  $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        language: $('#{prefix}flb_registration_language').val(),
                        provider: $('#flb_core_provider').val(),
                        step: $('#{prefix}flb_registration_step').val(),
                        email: $('#{prefix}flb_registration_email').val(),
                        password: $('#{prefix}flb_registration_password').val()
                    },
                    url: $('#flb_core_url').val()+'action=registrateUser',
                    success: function(data) {
                      if (data.error) {
                        alert(data.message);
                      } else {
                        if (data.step == 'done') {
                          if (data.userid) {
                            $('#flb_core_userid').val(data.userid);
                            $('#flb_core_username').val(data.username);
                            $('#flb_core_useremail').val(data.useremail);
                            $('#flb_core_sessionid').val(data.sessionid);
                            
                            if ($.cookie) {
                              $.cookie('flb_core_userid', data.userid, { expires: 3, path: '/' });
                              $.cookie('flb_core_username', data.username, { expires: 3, path: '/' });
                              $.cookie('flb_core_useremail', data.useremail, { expires: 3, path: '/' });
                              $.cookie('flb_core_sessionid', data.sessionid, { expires: 3, path: '/' });
                            }
                          }
                          
                          %s
                          
                          if (data.popup) alert(data.popup);
                        } else {
                          flbLoadHtml('guiRegistration',$('#{prefix}flb_registration').parent(), $.extend({params}, { step: data.step, email: $('#{prefix}flb_registration_email').val(), data: data })); 
                        }
                      }
                    },
                    error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); }
                  });  
                });
              });
              </script>", $backAction);
        
        break;
      case 3:
        // formular na zalozeni/potvrzeni registracnich udaju uctu
        $this->_guiHtml = sprintf('
            <div class="label flb_title"><span>{__label.ajax_profile_title}</span></div>
            <div class="flb_profile_main">
            <input type="hidden" id="{prefix}flb_registration_email" value="%s" />
            <input type="hidden" id="{prefix}flb_registration_userid" value="{userid}" />
            <input type="hidden" id="{prefix}flb_registration_step" value="3" />
            <input type="hidden" id="{prefix}flb_registration_language" value="{language}" />
            <input type="hidden" id="{prefix}flb_registration_checkAttributeMandatory" value="{checkAttributeMandatory}" />
            <input type="hidden" id="{prefix}flb_profile_facebook_id" value="{facebookId}" />
            <input type="hidden" id="{prefix}flb_profile_google_id" value="{googleId}" />
            <input type="hidden" id="{prefix}flb_profile_twitter_id" value="{twitterId}" />
            <div class="group">
              <label class="label flb_profile_email_label"><span>* {__label.ajax_profile_email}:</span></label>
              <input type="text" id="{prefix}flb_registration_email_ro" class="text flb_readonly" value="{email}" readonly="yes" />
            </div>
            <div class="group">
              <label class="label flb_profile_firstname_label"><span>* {__label.ajax_profile_firstname}:</span></label>
              <input class="text" type="text" id="{prefix}flb_registration_firstname" value="{firstname}" />
            </div>
            <div class="group">
              <label class="label flb_profile_lastname_label"><span>* {__label.ajax_profile_lastname}:</span></label>
              <input class="text" type="text" id="{prefix}flb_registration_lastname" value="{lastname}" />
            </div>
            <div class="group">
              <label class="label flb_profile_phone_label"><span>* {__label.ajax_profile_phone}:</span></label>
              <input class="text flb_phone" type="text" id="{prefix}flb_registration_phone" value="{phone}" />
            </div>
            {attribute}
            %s
            %s
            <div class="button">
              <input type="button" id="{prefix}flb_registration_prev" value="{__button.back}" />
              <input type="button" id="{prefix}flb_registration_finish" class="flb_primaryButton" value="{__button.ajax_profile_registration_finish}" />
            </div>', ifsetor($this->_params['email']),
                     (!isset($this->_params['data']['userid'])||!$this->_params['data']['userid'])?
                     '<div id="{prefix}flb_profile_newpassword">
                        <div class="group">
                          <label class="label flb_profile_password_label"><span>* {__label.ajax_profile_password}:</span></label>
                          <input class="text" type="password" id="{prefix}flb_registration_password" value="" />
                        </div>
                        <div class="group">
                          <label class="label flb_profile_password_retype_label"><span>* {__label.ajax_profile_passwordRetype}:</span></label>
                          <input class="text" type="password" id="{prefix}flb_registration_retype_password" value="" />
                        </div>
                      </div>':'', $this->_getExternalAccountHtml());
        
        if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) $backAction = "$('#{prefix}flb_profile_extra').hide(); if (typeof flbRefresh == 'function') flbRefresh('.flb_output'); enableButtons();";
        else $backAction = "if (typeof flbRefresh == 'function') flbRefresh('.flb_output','{prefix}flb_registration'); flbLoadHtml('guiProfile', $('#{prefix}flb_registration').parent(), {params});";
        
        $this->_guiHtml .= sprintf("
              <script>
              function displayAccountIcon() {
                var any=false;
                
                if ($('#{prefix}flb_profile_facebook_id').val()) { $('#{prefix}flb_profile_facebook_icon').show(); any = true; }
                else $('#{prefix}flb_profile_facebook_icon').hide();
                
                if ($('#{prefix}flb_profile_google_id').val()) { $('#{prefix}flb_profile_google_icon').show(); any = true; }
                else $('#{prefix}flb_profile_google_icon').hide();
                
                if ($('#{prefix}flb_profile_twitter_id').val()) { $('#{prefix}flb_profile_twitter_icon').show(); any = true; }
                else $('#{prefix}flb_profile_twitter_icon').hide();
                
                if (any) $('#{prefix}flb_profile_newpassword').hide(); 
                else $('#{prefix}flb_profile_newpassword').show();
              }
              
              $(document).ready(function() {
                {attributeJS}
                
                %s
                
                if ($.isFunction(fitToWindow)) {
                  var parent = $('#{prefix}flb_registration').parent();
                  fitToWindow(parent.attr('id'));
                }
                
                function enableButtons() {
                  $('#{prefix}flb_profile_login').attr('disabled', false);
                  $('#{prefix}flb_profile_login_account').attr('disabled', false);
                  $('#{prefix}flb_profile_registrate').attr('disabled', false);
                }
                
                if ($.fn.intlTelInput) {
                  $('#{prefix}flb_registration_phone').intlTelInput({
                    preferredCountries: ['cz','sk'],
                    utilsScript: $('#flb_core_url_path').val()+'jq/intlTelInputUtils.js'
                  });
                }
                
                $('#{parentNode}').on('click.flb','#{prefix}flb_registration_prev', function() {
                  $('#{parentNode}').off('.flb');
                  
                  flbLoadHtml('guiRegistration', $('#{prefix}flb_registration').parent(), $.extend({params}, { step: 1, email: $('#{prefix}flb_registration_email').val() })); 
                });
                
                $('#{parentNode}').on('click.flb','#{prefix}flb_registration_finish', function(event) {
                  var attrib = new Array();
                  $('#{prefix}flb_registration [meaning=attribute]').each(function () {
                    var idExt = $(this).attr('id');
                    attrib.push({id:idExt.replace('{prefix}attr_',''),value:$(this).val()});
                  });
                  var phone = $('#{prefix}flb_registration_phone').val();
                  if ($.fn.intlTelInput) {
                    if (!$('#{prefix}flb_registration_phone').intlTelInput('isValidNumber')) {
                      alert('{__error.ajax_profile_invalidPhone}');
                      return false;
                    }
                    phone = $('#{prefix}flb_registration_phone').intlTelInput('getNumber');
                  }
                  
                  $(event.target).attr('disabled', true);
                  $('#{parentNode}').css({'cursor' : 'wait'});
                  $('#{prefix}flb_profile_extra').css({'cursor' : 'wait'});
                  $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: { provider: $('#flb_core_provider').val(), step: $('#{prefix}flb_registration_step').val(),
                        language:                 $('#{prefix}flb_registration_language').val(),
                        userid:                   $('#{prefix}flb_registration_userid').val(),
                        email:                    $('#{prefix}flb_registration_email').val(),
                        firstname:                $('#{prefix}flb_registration_firstname').val(),  
                        lastname:                 $('#{prefix}flb_registration_lastname').val(),
                        street:                   null,
                        city:                     null,
                        postalCode:               null,
                        state:                    null,
                        phone:                    phone,
                        facebookId:               $('#{prefix}flb_profile_facebook_id').val(),
                        googleId:                 $('#{prefix}flb_profile_google_id').val(),
                        twitterId:                $('#{prefix}flb_profile_twitter_id').val(),
                        attribute:                attrib,
                        checkAttributeMandatory:  $('#{prefix}flb_registration_checkAttributeMandatory').val(),
                        advertising:              'N',
                        password:                 $('#{prefix}flb_registration_password').val(),
                        retype_password:          $('#{prefix}flb_registration_retype_password').val(),
                    },
                    url: $('#flb_core_url').val()+'action=registrateUser',
                    success: function(data) {
                      if (data.error) {
                        $(event.target).attr('disabled', false);
                        
                        alert(data.message);
                      } else {
                        $('#{parentNode}').off('.flb');
                        
                        if (data.userid) {
                          $('#flb_core_userid').val(data.userid);
                          $('#flb_core_username').val(data.username);
                          $('#flb_core_useremail').val(data.useremail);
                          $('#flb_core_sessionid').val(data.sessionid);
                        }
                          
                        %s
                          
                        if (data.popup) alert(data.popup);
                      }
                      
                      $('#{parentNode}').css({'cursor' : 'default'});
                      $('#{prefix}flb_profile_extra').css({'cursor' : 'default'});
                    },
                    error: function(jqXHR, jqTextStatus, jqException) {
                      $('#{parentNode}').css({'cursor' : 'default'});
                      $('#{prefix}flb_profile_extra').css({'cursor' : 'default'});
                      
                      flbAjaxParseError(jqXHR, jqTextStatus, jqException); 
                    },
                  });  
                });
              });
              </script>", $this->_getExternalAccountJSJQuery('registration'), $backAction);

        break;
      default: throw new ExceptionUserTextStorage('error.inpage_registration_error');
    }
  }
  
  protected function _getData() {
    if ($this->_params['step']==3) {
      if (!isset($this->_params['data'])||!is_array($this->_params['data'])) {
          $this->_params['data'] = array(
                'userid'          => '',
                'email'           => $this->_params['email'],
                'firstname'       => '',
                'lastname'        => '',
                'phone'           => '',
                'facebookId'      => '',
                'googleId'        => '',
                'twitterId'       => '',
                );
      }
      $this->_guiParams = array_merge($this->_guiParams, $this->_params['data']);
    }
    
    $this->_guiParams['checkAttributeMandatory'] = ifsetor($this->_params['checkAttributeMandatory'],0);
        
    if (isset($this->_params['hideAdvertising'])&&$this->_params['hideAdvertising']) {
      $this->_guiParams['advertising'] = sprintf('<input style="display:none;" type="checkbox" id="%sflb_registration_advertising" value="Y" />', $this->_params['prefix']);
    } else {
      $template = sprintf('
            <div class="group">
              <label class="label flb_profile_advertising_label" id="%sflb_registration_advertising_label"><span>%s:</span></label>
              <input type="checkbox" id="%sflb_registration_advertising" value="Y" />
            </div>', $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_profile_advertising'), $this->_params['prefix']);
      
      $this->_guiParams['advertising'] = $template;
    }

    if (evaluateLogicalValue($this->_params['externalAccount'])) {
      $this->_guiParams['externalAccountGui'] = sprintf('<div class="flb_external_account">
              <div>%s</div>
              <div class="button">
                <!--<input type="button" class="flb_other_login flb_facebook_login" id="%sflb_profile_login_facebook" value="" />-->
                <input type="button" class="flb_other_login flb_google_login" id="%sflb_profile_login_google" value="" />
                <input type="button" class="flb_other_login flb_twitter_login" id="%sflb_profile_login_twitter" value="" />
              </div>
              <div class="flexbookAccount"><span>%s</span></div>
            </div>',
        $this->_app->textStorage->getText('label.ajax_profile_externalAccount'),
        $this->_params['prefix'], $this->_params['prefix'], $this->_params['prefix'],
        $this->_app->textStorage->getText('label.ajax_profile_flexbookAccount'));
    } else $this->_guiParams['externalAccountGui'] = '';
    
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) $this->_guiParams['buttonBackLabel'] = $this->_app->textStorage->getText('button.close');
    else $this->_guiParams['buttonBackLabel'] = $this->_app->textStorage->getText('button.back');
    
    $this->_getAttribute($this->_params['registrationAttributeMandatoryOnly']);
  }
}

?>
