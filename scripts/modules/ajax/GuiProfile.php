<?php

class AjaxGuiProfile extends AjaxGuiAction2 {
  private $_userData;

  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_profile', $this->_params['prefix']);
    $this->_class = 'flb_profile';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['buttons'])) $this->_params['buttons'] = array('login','sendPassword','registration','reservation','reservationPrint','profile','credit','subaccount','document','password','logout');
    // v seznamu rezervaci bude vzdy tlacitko zpet
    $this->_params['buttons'][] = 'reservationBack';
    // pokud poskytovatel nema povoleny credit a permanentky nebude se vubec zobrazovat dana zalozka
    $settings = BCustomer::getProviderSettings($this->_params['provider'],array('disableCredit','disableTicket'));
    if (($settings['disableCredit']=='Y')&&($settings['disableTicket']=='Y')) {
      if (($key = array_search('credit', $this->_params['buttons'])) !== false) {
        unset($this->_params['buttons'][$key]);
      }
    }

    if (!isset($this->_params['externalAccount'])) $this->_params['externalAccount'] = 0;
    if (evaluateLogicalValue($this->_params['externalAccount'])) {
      if (!isset($this->_params['externalAccountFirst'])) {
        if (isset($this->_params['extraDiv'])&&!strcmp($this->_params['extraDiv'],'all')) $this->_params['externalAccountFirst'] = 1;
        else $this->_params['externalAccountFirst'] = 0;
      }
    }
    if (!isset($this->_params['externalAccountFirst'])) $this->_params['externalAccountFirst'] = 0;
    if (!isset($this->_params['allowRoles'])) $this->_params['allowRoles'] = array('user','organiser','reception','admin');
    if (!isset($this->_params['format']['time'])) $this->_params['format']['time'] = 'H:i';
    if (!isset($this->_params['format']['date'])) $this->_params['format']['date'] = 'd.m. Y';
    if (!isset($this->_params['format']['datetime'])) $this->_params['format']['datetime'] = 'd.m. Y H:i';
    if (!isset($this->_params['loggedTemplate'])) $this->_params['loggedTemplate'] = '<span class="name">@@USER_NAME</span>';
    if (!isset($this->_params['registrationAttributeMandatoryOnly'])) $this->_params['registrationAttributeMandatoryOnly'] = 0;
  }

  private function _getLoggedString() {
    return str_replace(array('@@USER_NAME','@@USER_EMAIL'), array($this->_guiParams['fullname'], $this->_guiParams['email']), $this->_params['loggedTemplate']);
  }
  
  protected function _createTemplate() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) {
      // kdyz neni prihlaseny uzivatel, zobrazi se moznost prihlasit se nebo registrovat
      if (!isset($this->_params['extraDiv'])||strcmp($this->_params['extraDiv'],'all')) {
        $this->_guiHtml = '
            {notLoggedTitle}
            {externalAccountGui}
            <input type="hidden" id="{prefix}flb_profile_language" value="{language}" />
            <input type="hidden" id="{prefix}flb_profile_showSendPassword" value="0" />
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
              {button_closeProfile}
              {button_sendPassword}
              {button_login}
              {button_sendConfirmation}
              {button_registrate}
            </div>
            {extraDiv}';
      } else {
        // kdyz maji vsechny obladaci prvky byt v extra div, bude "na zacatku" pouze tlacitko Prihlasit
        $this->_guiHtml = '
            {notLoggedTitle}
            <input type="hidden" id="{prefix}flb_profile_language" value="{language}" />
            <input type="hidden" id="{prefix}flb_profile_showSendPassword" value="0" />
            {button_loginPrepare}
            {extraDiv}';
      }
      $this->_guiHtml .= "<script>
                       $(document).ready(function() {
                         function disableButtons() {
                           $('#{prefix}flb_profile_login_prepare').attr('disabled', true);
                           $('#{prefix}flb_profile_login').attr('disabled', true);
                           $('#{prefix}flb_profile_login_account').attr('disabled', true);
                           $('#{prefix}flb_profile_registrate').attr('disabled', true);
                         }
                      
                         {extraDivJS}
                         
                         $('#{prefix}flb_profile_sendPassword').hide();
                         $('#{prefix}flb_profile_sendConfirmation').hide();
                         
                         $('#{prefix}flb_profile_account_list').hide();
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_account', function() {
                           $('#{prefix}flb_profile_login').hide();
                           $('#{prefix}flb_profile_login_account').hide();
                           $('#{prefix}flb_profile_registrate').hide();
                           $('#{prefix}flb_profile_account_list').show();
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_back', function() {
                           $('#{prefix}flb_profile_login').show();
                           $('#{prefix}flb_profile_login_account').show();
                           $('#{prefix}flb_profile_registrate').show();
                           $('#{prefix}flb_profile_account_list').hide();
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_prepare', function() {
                           $.ajax({
                                type: 'GET',
                                dataType: 'json',
                                async: false,
                                data: {
                                  prefix: '{prefix}',
                                  externalAccount: '{externalAccount}',
                                  externalAccountFirst: '{externalAccountFirst}',
                                  registrationAttributeMandatoryOnly: '{registrationAttributeMandatoryOnly}',
                                  provider: $('#flb_core_provider').val(),
                                  language: $('#{prefix}flb_profile_language').val(),
                                },
                                url: $('#flb_core_url').val()+'action=guiLogin',
                                success: function(data) {
                                    if (data.error) {
                                      alert(data.message);
                                    } else {
                                      $('#{prefix}flb_profile_extra').html(data.output);
                                    }
                                },
                                error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                            });
                            $('#{prefix}flb_profile_account_list').hide();
                            $('#{prefix}flb_profile_sendPassword').hide();
                            $('#{prefix}flb_profile_sendConfirmation').hide();
                            $('#{prefix}flb_profile_extra').show();
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_registrate_prepare', function() {
                           $.ajax({
                                type: 'GET',
                                dataType: 'json',
                                async: false,
                                data: $.extend({params}, { provider: $('#flb_core_provider').val(), step: 1, email: '' }),
                                url: $('#flb_core_url').val()+'action=guiRegistration',
                                success: function(data) {
                                    if (data.error) {
                                      alert(data.message);
                                    } else {
                                      $('#{prefix}flb_profile_extra').html(data.output);
                                    }
                                },
                                error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                            });
                            $('#{prefix}flb_profile_sendPassword').hide();
                            $('#{prefix}flb_profile_sendConfirmation').hide();
                            $('#{prefix}flb_profile_extra').show();
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_close', function() {
                           $('#{prefix}flb_profile_extra').hide();
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_close', function() {
                           $('#flb_core_not_logged').hide();
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_toggleSendPassword', function() {
                           $(this).hide();
                           
                           hideButtonsToSendPassword();
                           
                           $('#{prefix}flb_profile_sendPassword').show();
                         });
                         
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_facebook', function() {
                           flbExternalAccount($('#flb_core_url_path').val()+'index.php?action=eFacebookCall','loginUser');
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_google', function() {
                           flbExternalAccount($('#flb_core_url_path').val()+'index.php?action=eGoogleCall','loginUser');
                         });
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login_twitter', function() {
                           flbExternalAccount($('#flb_core_url_path').val()+'index.php?action=eTwitterCall','loginUser');
                         });
                       
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_login', function() {
                            $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                data: { provider: $('#flb_core_provider').val(),
                                  username: $('#{prefix}flb_profile_username').val(),
                                  password: $('#{prefix}flb_profile_password').val(),
                                  language: $('#{prefix}flb_profile_language').val(),
                                },
                                url: $('#flb_core_url').val()+'action=loginUser',
                                success: function(data) {
                                    if (data.error) {
                                      alert(data.message);
                                      
                                      if (data.notValidated) {
                                        $('#{prefix}flb_profile_sendConfirmation').show();
                                        $('#{prefix}flb_profile_sendPassword').hide();
                                       } else if ($('#{prefix}flb_profile_username').val()&&$('#{prefix}flb_profile_password').val()) {
                                        $('#{prefix}flb_profile_sendConfirmation').hide();
                                        $('#{prefix}flb_profile_sendPassword').show();
                                      }
                                    } else {
                                      flbLoginUser(data.userid, data.username, data.useremail, data.sessionid);
                                    }
                                },
                                error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                            });            
                         });
                         
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_sendPassword', function() {
                            $.ajax({
                              type: 'POST',
                              url: $('#flb_core_url').val()+'action=sendPassword',
                              dataType: 'json',
                              data: {
                                  language: $('#{prefix}flb_profile_language').val(),
                                  provider: $('#flb_core_provider').val(),
                                  email: $('#{prefix}flb_profile_username').val()
                              },
                              success: function(data) {
                                if (data.error) alert(data.message);
                                else alert('{__label.ajax_profile_passwordSent}');
                              },
                              error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                            });  
                         });
                         
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_sendConfirmation', function() {
                            $.ajax({
                              type: 'POST',
                              url: $('#flb_core_url').val()+'action=sendRegistrationEmail',
                              dataType: 'json',
                              data: {
                                  language: $('#{prefix}flb_profile_language').val(),
                                  provider: $('#flb_core_provider').val(),
                                  email: $('#{prefix}flb_profile_username').val()
                              },
                              success: function(data) {
                                if (data.error) alert(data.message);
                                else alert('{__label.ajax_profile_confirmationSent}');
                              },
                              error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                            });  
                         });
                           
                         $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_registrate', function() {
                            $('#{prefix}flb_profile_extra').html(''); 
                            flbLoadHtml('guiRegistration', {placeHolder}, $.extend({params}, { step: 1, email: '' }));
                            $('#{prefix}flb_profile_extra').show();
                            disableButtons();
                            //flbLoadHtml('guiRegistration', $('#{prefix}flb_profile').parent(), $.extend({params}, { step: 1, email: '' }));
                         });
                       });
                     </script>";
    } else {
      if (!isset($this->_params['extraDiv'])||strcmp($this->_params['extraDiv'],'all')) {
        $this->_guiHtml = sprintf('
              <div class="value">%s</div>
              <div class="button">
                {button_reservation}
                {button_credit}
                {button_profile}
                {button_document}
                {button_subaccount}
                {button_password}
                {button_logout}
              </div>{extraDiv}', $this->_getLoggedString());
      } else {
        $this->_guiHtml = sprintf('
              <div class="value flb_profile_fullname_clickable" id="{prefix}flb_profile_fullname">%s</div>
              {loggedHint}
              {extraDiv}', $this->_getLoggedString());
      }
      $this->_guiHtml .= "<script>
                      $(document).ready(function() {
                        function disableButtons() {
                          $('#{prefix}flb_profile_reservation').attr('disabled', true);
                          $('#{prefix}flb_profile_change').attr('disabled', true);
                          $('#{prefix}flb_profile_subaccount').attr('disabled', true);
                          $('#{prefix}flb_profile_document').attr('disabled', true);
                          $('#{prefix}flb_profile_password').attr('disabled', true);
                          $('#{prefix}flb_profile_credit_btn').attr('disabled', true);
                          $('#{prefix}flb_profile_closebutton').hide();
                        }
                        
                        function alignFullnameHint() {
                          $('#{prefix}flb_profile_hint').css('top', $('#{prefix}flb_profile_fullname').offset().top-$(window).scrollTop()+$('#{prefix}flb_profile_fullname').height()+5);
                        }
                        
                        {extraDivJS}
                        
                        $('#{prefix}flb_profile_fullname').mouseover(function() { alignFullnameHint(); $('#{prefix}flb_profile_hint').show(); });
                        $('#{prefix}flb_profile').mouseleave(function() { $('#{prefix}flb_profile_hint').hide(); });
                        $('#{prefix}flb_profile_hint').mouseleave(function() { $('#{prefix}flb_profile_hint').hide(); });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_fullname', function() {
                          flbLoadHtml('guiReservationList', {placeHolder}, {params});
                          $('#{prefix}flb_profile_hint').hide();
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $('#{prefix}flb_profile_reservation').addClass('tab_selected');
                        });
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_hint_reservation', function() {
                          flbLoadHtml('guiReservationList', {placeHolder}, {params});
                          $('#{prefix}flb_profile_hint').hide();
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $('#{prefix}flb_profile_reservation').addClass('tab_selected');
                        });
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_hint_credit', function() {
                          flbLoadHtml('guiProfileCredit', {placeHolder}, {params});
                          $('#{prefix}flb_profile_hint').hide();
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $('#{prefix}flb_profile_credit_btn').addClass('tab_selected');
                        });
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_hint_profile', function() {
                          flbLoadHtml('guiProfileDetail', {placeHolder}, {params});
                          $('#{prefix}flb_profile_hint').hide();
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $('#{prefix}flb_profile_change').addClass('tab_selected');
                        });
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_hint_logout', function() {
                          $('#flb_core_userid').val('');
                          $('#flb_core_username').val('');
                          $('#flb_core_useremail').val('');
                          $('#flb_core_sessionid').val('');
                          
                          if ($.cookie) {
                            $.removeCookie('flb_core_userid', { path: '/' });
                            $.removeCookie('flb_core_username', { path: '/' });
                            $.removeCookie('flb_core_useremail', { path: '/' });
                            $.removeCookie('flb_core_sessionid', { path: '/' });
                          }
                        
                          flbLoadHtml('guiProfile', $('#{prefix}flb_profile').parent(), {params});
                          
                          if (typeof flbRefresh == 'function') flbRefresh('.flb_output','{prefix}flb_profile');
                          
                          $('.flb_cal_core').each(function() {
                            $('#'+this.id).fullCalendar('removeEventSource', window.calSource[this.id]);
                            window.calSource[this.id].data.sessid = '';
                            $('#'+this.id).fullCalendar('addEventSource', window.calSource[this.id]);
                          });
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_closebutton', function() {
                          $('#{prefix}flb_profile_extra').hide();
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_logout', function() {
                          $('#flb_core_userid').val('');
                          $('#flb_core_username').val('');
                          $('#flb_core_useremail').val('');
                          $('#flb_core_sessionid').val('');
                          
                          if ($.cookie) {
                            $.removeCookie('flb_core_userid', { path: '/' });
                            $.removeCookie('flb_core_username', { path: '/' });
                            $.removeCookie('flb_core_useremail', { path: '/' });
                            $.removeCookie('flb_core_sessionid', { path: '/' });
                          }
                        
                          flbLoadHtml('guiProfile', $('#{prefix}flb_profile').parent(), {params});
                          
                          if (typeof flbRefresh == 'function') flbRefresh('.flb_output','{prefix}flb_profile');
                          
                          $('.flb_cal_core').each(function() {
                            $('#'+this.id).fullCalendar('removeEventSource', window.calSource[this.id]);
                            window.calSource[this.id].data.sessid = '';
                            $('#'+this.id).fullCalendar('addEventSource', window.calSource[this.id]);
                          });
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_password', function() {
                          //$('#{prefix}flb_profile_extra').html(''); 
                          flbLoadHtml('guiPassword', {placeHolder}, {params});
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $(this).addClass('tab_selected');
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_reservation', function() {
                          //$('#{prefix}flb_profile_extra').html(''); 
                          flbLoadHtml('guiReservationList', {placeHolder}, {params});
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $(this).addClass('tab_selected');
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_subaccount', function() {
                          flbLoadHtml('guiSubaccountList', {placeHolder}, {params});
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $(this).addClass('tab_selected');
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_document', function() {
                          flbLoadHtml('guiDocumentList', {placeHolder}, {params});
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $(this).addClass('tab_selected');
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_change', function() {
                          //$('#{prefix}flb_profile_extra').html(''); 
                          flbLoadHtml('guiProfileDetail', {placeHolder}, {params});
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $(this).addClass('tab_selected');
                        });
                        
                        $('#{prefix}flb_profile').on('click','#{prefix}flb_profile_credit_btn', function() { 
                          //$('#{prefix}flb_profile_extra').html('');
                          flbLoadHtml('guiProfileCredit', {placeHolder}, {params});
                          $('#{prefix}flb_profile_extra').show();
                          $('.tab').removeClass('tab_selected');
                          $(this).addClass('tab_selected');
                        });
                       });
                     </script>";
    }
  }
  
  protected function _modifyTemplate() {
    if (isset($this->_params['extraDiv'])&&!strcmp($this->_params['extraDiv'],'all')) {
      // kdyz se vsechny ovladaci prvky zobrazuji v extradivu, vlozim extradiv do sablony pred parsovanim render (jsou tam webcore tagy)
      $this->_guiHtml = str_replace('{extraDiv}', $this->_guiParams['extraDiv'], $this->_guiHtml);
      unset($this->_guiParams['extraDiv']);
    }
  }
  
  protected function _getButtons() {
    if (in_array('login', $this->_params['buttons'])) {
      $this->_guiParams['button_login'] = sprintf('<input type="button" id="%sflb_profile_login" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_login'));
      if (evaluateLogicalValue($this->_params['externalAccount'])) {
        if (!evaluateLogicalValue($this->_params['externalAccountFirst'])) {
          $this->_guiParams['button_login'] .= sprintf('<div id="flb_profile_account">
                              <input type="button" id="%sflb_profile_login_account" value="%s" />
                              <div id="%sflb_profile_account_list">
                                <input type="button" class="flb_back_login" id="%sflb_profile_login_back" value="%s" />
                                <!--<input type="button" class="flb_other_login flb_facebook_login" id="%sflb_profile_login_facebook" value="" />-->
                                <input type="button" class="flb_other_login flb_google_login" id="%sflb_profile_login_google" value="" />
                                <input type="button" class="flb_other_login flb_twitter_login" id="%sflb_profile_login_twitter" value="" />
                              </div>', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_login_account'),
            $this->_params['prefix'], $this->_params['prefix'], $this->_app->textStorage->getText('button.back'),
            $this->_params['prefix'], $this->_params['prefix'], $this->_params['prefix']);

          $this->_guiParams['externalAccountGui'] = '';
        } else {
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
        }
      } else {
        $this->_guiParams['externalAccountGui'] = '';
      }
    } else $this->_guiParams['button_login'] = '';
    
    $this->_guiParams['button_loginPrepare'] = sprintf('<input type="button" id="%sflb_profile_login_prepare" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_login'));
    $this->_guiParams['button_registratePrepare'] = sprintf('<input type="button" id="%sflb_profile_registrate_prepare" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_registrate'));
    $this->_guiParams['button_close'] = sprintf('<input type="button" id="%sflb_profile_login_close" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.close'));
    $this->_guiParams['externalAccount'] = $this->_params['externalAccount'];
    $this->_guiParams['externalAccountFirst'] = $this->_params['externalAccountFirst'];
    $this->_guiParams['registrationAttributeMandatoryOnly'] = $this->_params['registrationAttributeMandatoryOnly'];

    $this->_guiParams['button_closeProfile'] = !in_array('close', $this->_params['buttons'])?'':
      sprintf('<input type="button" id="%sflb_profile_close" value="%s" />',
        $this->_params['prefix'], $this->_app->textStorage->getText('button.close'));

    $this->_guiParams['button_reservation'] = !in_array('reservation', $this->_params['buttons'])?'':
          sprintf('<input type="button" class="tab" id="%sflb_profile_reservation" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_reservation'));
          
    $this->_guiParams['button_credit'] = !in_array('credit', $this->_params['buttons'])?'':
          sprintf('<input type="button" class="tab" id="%sflb_profile_credit_btn" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_credit'));
    
    $this->_guiParams['button_profile'] = !in_array('profile', $this->_params['buttons'])?'':
          sprintf('<input type="button" class="tab" id="%sflb_profile_change" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_profile'));

    if (BCustomer::getProviderSettings($this->_params['provider'],'userSubaccount')=='Y') {
      $this->_guiParams['button_subaccount'] = !in_array('subaccount', $this->_params['buttons']) ? '' :
        sprintf('<input type="button" class="tab" id="%sflb_profile_subaccount" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_subaccounts'));
    } else $this->_guiParams['button_subaccount'] = '';

    if (!isset($this->_userData['documentCount'])||!$this->_userData['documentCount']||!in_array('document',$this->_params['buttons'])) $this->_guiParams['button_document'] = '';
    else {
      $this->_guiParams['button_document'] = sprintf('<input type="button" class="tab" id="%sflb_profile_document" value="%s" />',
        $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_documents'));
    }
    
    $this->_guiParams['button_password'] = !in_array('password', $this->_params['buttons'])?'':
          sprintf('<input type="button" class="tab" id="%sflb_profile_password" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_password'));
    
    $this->_guiParams['button_logout'] = !in_array('logout', $this->_params['buttons'])?'':
          sprintf('<input type="button" class="button" id="%sflb_profile_logout" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_logout'));
          
    $this->_guiParams['button_sendPassword'] = !in_array('sendPassword', $this->_params['buttons'])?'':
          sprintf('<input type="button" id="%sflb_profile_sendPassword" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_sendPassword'));

    $this->_guiParams['toggle_sendPassword'] = !in_array('sendPassword', $this->_params['buttons'])?'':
      sprintf('<div class="flb_profile_toggleSendPassword" id="%sflb_profile_toggleSendPassword">%s</div>',
        $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_profile_unknownPassword'));

    $this->_guiParams['button_sendConfirmation'] = !in_array('sendPassword', $this->_params['buttons'])?'':
      sprintf('<input type="button" id="%sflb_profile_sendConfirmation" value="%s" />',
        $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_sendConfirmation'));
          
    $this->_guiParams['button_registrate'] = !in_array('registration', $this->_params['buttons'])?'':
          sprintf('<input type="button" id="%sflb_profile_registrate" value="%s" />',
                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_registrate'));

    // titulek pro profil
    if (isset($this->_params['showNotLoggedTitle'])&&$this->_params['showNotLoggedTitle']) {
      $this->_guiParams['notLoggedTitle'] = sprintf('<div class="flb_title flb_notLoggedTitle" id="%sflb_profile_title">%s</div>', $this->_params['prefix'],
        $this->_app->textStorage->getText('error.ajax_loginRequired'));
    } else $this->_guiParams['notLoggedTitle'] = '';

  }
  
  private function _getExtraDiv() {
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $showExtraDiv = isset($this->_guiParams['extraDivContent'])&&$this->_guiParams['extraDivContent'];
      
      if (!strcmp($this->_params['extraDiv'],'all')&&$this->_app->auth->getUserId()) {
        // kdyz se vsechny ovladaci prvky zobrazuji v extradivu, tlacitka budou nahore porad, zbytek pujde do vnorenyho divu
        $this->_guiParams['extraDivContent'] = sprintf('
          <div class="tab">
            {button_reservation}
            {button_credit}
            {button_profile}
            {button_document}
            {button_subaccount}
            {button_password}
            {button_logout}
          </div>
          <div class="flb_profile_extra_all" id="{prefix}flb_profile_extra_all">%s</div>', ifsetor($this->_guiParams['extraDivContent'],''));
        
        $this->_guiParams['placeHolder'] =  sprintf("$('#%sflb_profile_extra_all')", $this->_params['prefix']);
      } else {
        $this->_guiParams['placeHolder'] =  sprintf("$('#%sflb_profile_extra')", $this->_params['prefix']);
      }
      
      $this->_guiParams['extraDiv'] = sprintf('<div class="flb_profile_extra" id="%sflb_profile_extra" style="display:none;" >
                                                  <button type="button" id="%sflb_profile_closebutton" class="flb_profile_closebutton ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="close">
                                                     <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                                                     <span class="ui-button-text">close</span>
                                                  </button>
                                               %s</div>',
                                              $this->_params['prefix'], $this->_params['prefix'], ifsetor($this->_guiParams['extraDivContent'],''));
      
      if ($showExtraDiv) {
        $this->_guiParams['extraDivJS'] = sprintf("$('#%sflb_profile_extra').show(); disableButtons();", $this->_params['prefix']);
      } else {
        $this->_guiParams['extraDivJS'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
      }
    } else {
      $this->_guiParams['extraDiv'] = '';
      $this->_guiParams['extraDivJS'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
      $this->_guiParams['placeHolder'] = sprintf("$('#%sflb_profile').parent()", $this->_params['prefix']);
    }

    if (in_array('close', $this->_params['buttons'])||isset($this->_params['extraDiv'])&&!strcmp($this->_params['extraDiv'],'all')) {
      $this->_guiParams['extraDivJS'] .= sprintf("
        function hideButtonsToSendPassword() {
          $('#%sflb_profile_login').hide();
          $('#%sflb_profile_registrate').hide();
          $('#%sflb_profile_registrate_prepare').hide();
          $('#%sflb_profile_password_label').hide();
          $('#%sflb_profile_password').hide();
        }", $this->_params['prefix'], $this->_params['prefix'], $this->_params['prefix'], $this->_params['prefix'], $this->_params['prefix']);
    } else {
      $this->_guiParams['extraDivJS'] .= "function hideButtonsToSendPassword() { }";
    }
  }

  private function _getLoggedHint() {
    if ($this->_app->auth->getUserId()) {
      $credit = '';
      if (in_array('credit', $this->_params['buttons'])) {
        $credit = sprintf('<div class="link" id="%sflb_profile_hint_credit">%s</div>', $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_profile_hint_credit'));
      }
      global $AJAX;
      $this->_guiParams['loggedHint'] = sprintf(
        '<div class="flb_profile_fullname_hint" id="%sflb_profile_hint">
          <div class="logo"><img src="%s/img/logo_flb_white.png"/></div><div class="title">%s</div>
          <div class="link link_logout" id="%sflb_profile_hint_logout">%s</div>
          <div class="text">%s</div>
          <div class="text">%s</div><div class="flb_clear">&nbsp;</div>
          <div class="link link_notlast" id="%sflb_profile_hint_reservation">%s</div>
          <div class="link%s" id="%sflb_profile_hint_profile">%s</div>
          %s
         </div>', $this->_params['prefix'], dirname($AJAX['adminUrl']), $this->_app->textStorage->getText('label.ajax_profile_hintTitle'),
        $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_profile_hint_logout'),
        $this->_guiParams['fullname'], $this->_guiParams['email'],
        $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_profile_hint_reservations'),
        $credit?' link_notlast':'',
        $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_profile_hint_profile'),
        $credit
        );
    } else {
      $this->_guiParams['loggedHint'] = '';
    }
  }
  
  protected function _getData() {
    $this->_guiParams['fullname'] = $this->_app->auth->getFullname();
    $this->_guiParams['email'] = $this->_app->auth->getEmail();

    if ($this->_app->auth->getUserId()) {
      if (!$this->_userData) {
        $user = new BUser($this->_app->auth->getUserId());
        $this->_userData = $user->getData();
      }

      $s = new SDocument;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->setColumnsMask(array('document_id'));
      $res = $this->_app->db->doQuery($s->toString());
      $this->_userData['documentCount'] = $this->_app->db->getRowsNumber($res);

      $this->_guiParams['phone'] = $this->_userData['phone'];
    }
    
    $this->_getButtons();
    $this->_getLoggedHint();
    $this->_getExtraDiv();
  }

  protected function _userRun() {
    // kdyz se ma zobrazit profil summary prihlaseneho uzivatele bez uplneho profilu, vynuti se doplneni profilu
    if (!$this->_app->session->getExpired()&&$this->_app->auth->getUserId()) {
      if (isset($this->_params['checkAttributeMandatory'])&&$this->_params['checkAttributeMandatory']) {
        $user = new BUser($this->_app->auth->getUserId());
        $this->_userData = $user->getData();
        $profileUpdateNeeded = !$this->_userData['firstname'] || !$this->_userData['lastname'] || !$this->_userData['email'] || !$this->_userData['phone'];

        if (!$profileUpdateNeeded) {
          $userAttributes = $user->getAttribute($this->_params['provider'], $this->_app->language->getLanguage(), 'USER');
          foreach ($userAttributes as $attr) {
            if ((!isset($this->_params['showAttribute']) || in_array($attr['category'], $this->_params['showAttribute'])) &&
              (($attr['mandatory'] == 'Y') && !$attr['value'])) {
              $profileUpdateNeeded = true;

              break;
            }
          }
        }

        if ($profileUpdateNeeded) {
          $this->_result['popup'] = $this->_app->textStorage->getText('error.ajax_profile_updateNeeded');

          $redirect = new AjaxGuiProfileDetail($this->_request);
          $redirect->setParams('detailButtons', array('profile_save'));
          $redirect->run();

          if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
            $this->_guiParams['extraDivContent'] = $redirect->getResult()['output'];
          } else {
            $this->_result = $redirect->getResult();

            // moznost refresh
            $p = $this->_params;
            unset($p['sessid']);
            unset($p['provider']);
            unset($p['extraDivContent']);
            $injection = sprintf('<input type="hidden" id="flb_guiType" value="guiProfile"/><input type="hidden" id="flb_guiParams" value="%s"/>', $this->_app->htmlspecialchars(json_encode($p)));
            $this->_result = str_replace('flb_profile">', 'flb_profile">' . $injection, $this->_result);

            return;
          }
        } else {
          // kdyz je profil ok, muze mit uzivatel vytvoreny zavazne nahradniky, ktere je potreba vyridit

          $s = new SEventAttendee;
          $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
          $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
          $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
          $s->addStatement(new SqlStatementMono($s->columns['substitute_mandatory'], "%s='Y'"));
          $s->addStatement(new SqlStatementMono($s->columns['start'], '%s>NOW()'));
          $s->setColumnsMask(array('eventattendee_id'));
          $res = $this->_app->db->doQuery($s->toString());
          if ($this->_app->db->getRowsNumber($res)) {
            $this->_result['popup'] = $this->_app->textStorage->getText('error.ajax_profile_substituteUpdateNeeded');

            $redirect = new AjaxGuiSubstituteMandatoryList($this->_request);
            $redirect->run();

            if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
              $this->_guiParams['extraDivContent'] = $redirect->getResult()['output'];
            } else {
              $this->_result = $redirect->getResult();

              return;
            }
          }
        }
      }
    }
    
    parent::_userRun();
  }
}

?>
