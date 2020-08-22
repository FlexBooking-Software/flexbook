<?php

class AjaxGuiProfileDetail extends AjaxGuiAction2 {
  protected $_accountType = 'USER';
  
  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_profile_detail', $this->_params['prefix']);
    $this->_class = 'flb_profile';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['detailButtons'])) $this->_params['detailButtons'] = array('profile_save','profile_back','profile_close');
    if (!isset($this->_params['externalAccount'])) $this->_params['externalAccount'] = 0;
  }
  
  protected function _getAttribute($mandatoryOnly=false, $userId=null) {
    global $AJAX;
    
    $ret = '';
    $js = '';

    if ($userId===null) $userId = $this->_app->auth->getUserId();
    $bU = new BUser($userId?$userId:null);
    $attributes = $bU->getAttribute($this->_params['provider'], $this->_app->language->getLanguage(), $this->_accountType);
    
    $category = null;
    foreach ($attributes as $id=>$attribute) {
      if ($mandatoryOnly&&($attribute['mandatory']=='N')) continue;

      // interni atribut se zobrazuje pouze kdyz ma hodnotu
      if (!strcmp($attribute['restricted'],'READONLY')&&(is_null($attribute['value'])||($attribute['value']===''))) continue;

      if (isset($this->_params['showAttribute'])&&$this->_params['showAttribute']) {
        if (!in_array($attribute['category'], $this->_params['showAttribute'])) continue;
      }

      $readonlyClass = $readonlyHtml = '';
      if (!strcmp($attribute['restricted'],'READONLY')||(!strcmp($attribute['restricted'],'CREATEONLY')&&!is_null($attribute['value'])&&($attribute['value']!==''))) {
        $readonlyClass = ' flb_readonly';
        $readonlyHtml = 'readonly="readonly"';
      }
      #$readonlyHtml = '';

      // atributy jsou uzavreny do DIVu kategorie
      if (strcmp($category,$attribute['category'])) {
        if ($category) $ret .= '</div>';
        if ($attribute['category']) $ret .= sprintf('<div class="flb_profile_attributecategory_name">%s</div><div class="flb_profile_attributecategory flb_profile_attributecategory_%s">', $attribute['category'], htmlize($attribute['category']));
      }
      switch ($attribute['type']) {
        case 'NUMBER':
                     $inputHtml = sprintf('<input class="text%s" type="text" meaning="attribute" attributetype="NUMBER" id="%sattr_%d" value="%s" %s/>', $readonlyClass, $this->_guiParams['prefix'], $id, $this->_app->regionalSettings->convertNumberToHuman($attribute['value']), $readonlyHtml);
                     break;
        case 'DECIMALNUMBER':
                     $inputHtml = sprintf('<input class="text%s" type="text" meaning="attribute" attributetype="DECIMALNUMBER" id="%sattr_%d" value="%s" %s/>', $readonlyClass, $this->_guiParams['prefix'], $id, $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2), $readonlyHtml);
                     break;
        case 'TEXT': $inputHtml = sprintf('<input class="text%s" type="text" meaning="attribute" attributetype="TEXT" id="%sattr_%d" value="%s" %s/>', $readonlyClass, $this->_guiParams['prefix'], $id, $attribute['value'], $readonlyHtml);
                     break;
        case 'TIME': $inputHtml = sprintf('<input class="text%s" type="text" meaning="attribute" attributetype="TIME" id="%sattr_%d" value="%s" %s/>', $readonlyClass, $this->_guiParams['prefix'], $id, $this->_app->regionalSettings->convertTimeToHuman($attribute['value'],'h:m'), $readonlyHtml);
                     if (!$readonlyHtml) $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'H:i',datepicker:false,timepicker:true,allowBlank:true,scrollInput:false}); });", $this->_guiParams['prefix'], $id);
                     break;
        case 'DATETIME':
                     $inputHtml = sprintf('<input class="text%s" type="text" meaning="attribute" attributetype="DATETIME" id="%sattr_%d" value="%s" %s/>', $readonlyClass, $this->_guiParams['prefix'], $id, $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']), $readonlyHtml);
                     if (!$readonlyHtml) $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'d.m.Y H:i',lang:'%s',dayOfWeekStart:'1',datepicker:true,timepicker:true,allowBlank:true,scrollInput:false}); });", $this->_guiParams['prefix'], $id, $this->_guiParams['language']);
                     break;
        case 'DATE': $inputHtml = sprintf('<input class="text%s" type="text" meaning="attribute" attributetype="DATE" id="%sattr_%d" value="%s" %s/>', $readonlyClass, $this->_guiParams['prefix'], $id, $this->_app->regionalSettings->convertDateToHuman($attribute['value']), $readonlyHtml);
                     if (!$readonlyHtml) $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'d.m.Y',lang:'%s',dayOfWeekStart:'1',datepicker:true,timepicker:false,allowBlank:true,scrollInput:false}); });", $this->_guiParams['prefix'], $id, $this->_guiParams['language']);
                     break;
        case 'TEXTAREA':
                     $inputHtml = sprintf('<textarea class="textarea%s" meaning="attribute" attributetype="TEXTAREA" id="%sattr_%d" %s>%s</textarea><span id="%sattr_%d_span"></span>', $readonlyClass, $this->_guiParams['prefix'], $id, $readonlyHtml, $attribute['value'], $this->_guiParams['prefix'], $id);
                     break;
        case 'LIST': $inputHtml = sprintf('<select meaning="attribute" attributetype="LIST" id="%sattr_%d" %s><option value="">%s</option>', $this->_guiParams['prefix'], $id, $readonlyHtml?'disabled="disabled"':'', $this->_app->textStorage->getText('label.select_choose'));
                     foreach (explode(',',$attribute['allowedValues']) as $value) {
                      $inputHtml .= sprintf('<option value="%s"%s>%s</option>', $value, !strcmp($value,$attribute['value'])?' selected="selected"':'', $value);
                     }
                     $inputHtml .= '</select>';
                     break;
        case 'FILE': $inputHtml = sprintf('<label id="%sattr_%d_label" class="file"><a target="_file" href="%s/getfile.php?id=%s">%s</a></label><input type="hidden" meaning="attribute" attributetype="FILE" id="%sattr_%d"/><div class="file" id="%sattribute_file_%d"></div>',
                                          $this->_guiParams['prefix'], $id, dirname($AJAX['url']), ifsetor($attribute['valueId']), $attribute['value'], $this->_guiParams['prefix'], $id, $this->_guiParams['prefix'], $id);
                     if (!$readonlyHtml) $js .= sprintf("var uploadObj = $('#%sattribute_file_%d').uploadFile({
                                url: $('#flb_core_url_path').val()+'uploadfile.php',
                                fileName: 'uploadfile',
                                dragDrop: false,
                                maxFileCount: 1,
                                uploadStr: '%s',
                                maxFileCountErrorStr: '%s&nbsp;',
                                onSuccess: function(files,data,xhr,pd) {
                                  if (files) {
                                    var data = JSON.parse(data);
                                    $('#%sattr_%d').val(data.id);
                                    $('#%sattr_%d_label').hide();
                                    $('#%sattribute_file_%d .ajax-file-upload').hide();
                                  }
                                },
                              });", $this->_guiParams['prefix'], $id,
                              $this->_app->textStorage->getText('button.ajax_profile_fileUpload'),
                              $this->_app->textStorage->getText('label.ajax_profile_fileUpload_maxCount'),
                              $this->_guiParams['prefix'], $id,
                              $this->_guiParams['prefix'], $id,
                              $this->_guiParams['prefix'], $id);
                     break;
        default: $inputHtml = 'Unknown type!';
      }
      $class = ' flb_profile_attribute_'.$id;
      $divId = sprintf('%sattr_%d_label', $this->_params['prefix'], $id);
      if ($attribute['mandatory']=='Y') {
        $class .= ' flb_profile_attribute_mandatory';
        $labelPrefix = '* ';
      } else {
        $labelPrefix = '';
      }
      $attrHtml = sprintf('<div class="group"><label class="label%s" id="%s"><span>%s%s:</span></label>%s</div>',
        $class, $divId, $labelPrefix, formatAttributeName($attribute['name'], $attribute['url']), $inputHtml);
      
      $ret .= $attrHtml;
      
      $category = $attribute['category'];
    }
    if ($category) $ret .= '</div>';
    
    $this->_guiParams['attribute'] = $ret;
    $this->_guiParams['attributeJS'] = $js;
  }
  
  protected function _getExternalAccountHtml() {
    if (!$this->_params['externalAccount']) return '';
    else return '<div id="flb_profile_account">
          <input type="button" id="{prefix}flb_profile_add_account" value="{__button.ajax_profile_connectAccount}" />
          <div id="{prefix}flb_profile_detail_account_list">
            <!--<input type="button" id="{prefix}flb_profile_add_account_facebook" value="{__button.ajax_profile_connectAccount_facebook}" />-->
            <input type="button" id="{prefix}flb_profile_add_account_google" value="{__button.ajax_profile_connectAccount_google}" />
            <input type="button" id="{prefix}flb_profile_add_account_twitter" value="{__button.ajax_profile_connectAccount_twitter}" />
          </div>
          <div class="label flb_profile_connected_account_label"><span>{__label.ajax_profile_connectedAccount}:</span></div>
          <div class="flb_profile_connected_account">
            <div class="flb_profile_connected_account_icon" id="{prefix}flb_profile_facebook_icon">
              <img src="{urlDir}/img/icon_facebook.png"/>
              <button type="button" id="{prefix}flb_profile_remove_account_facebook" class="flb_profile_closebutton ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="remove">
                 <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                 <span class="ui-button-text">close</span>
              </button>
            </div>
            <div class="flb_profile_connected_account_icon" id="{prefix}flb_profile_google_icon">
              <img src="{urlDir}/img/icon_google.png"/>
              <button type="button" id="{prefix}flb_profile_remove_account_google" class="flb_profile_closebutton ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="remove">
                 <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                 <span class="ui-button-text">close</span>
              </button>
            </div>
            <div class="flb_profile_connected_account_icon" id="{prefix}flb_profile_twitter_icon">
              <img src="{urlDir}/img/icon_twitter.png"/>
              <button type="button" id="{prefix}flb_profile_remove_account_twitter" class="flb_profile_closebutton ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button" aria-disabled="false" title="remove">
                 <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                 <span class="ui-button-text">close</span>
              </button>
            </div>
          </div>
        </div>';
  }
  
  protected function _getExternalAccountJSJQuery($guiType='profile_detail') {
    return sprintf("displayAccountIcon();
            
            $('#{prefix}flb_profile_detail_account_list').hide();
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_add_account', function() {
              $('#{prefix}flb_profile_add_account').hide();
              $('#{prefix}flb_profile_detail_account_list').show();
            });
            
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_add_account_facebook', function() {
              $('#{prefix}flb_profile_add_account').show();
              $('#{prefix}flb_profile_detail_account_list').hide();
              
              flbExternalAccount($('#flb_core_url_path').val()+'index.php?action=eFacebookCall','assignToUser','{prefix}flb_profile_facebook_id');
            });
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_remove_account_facebook', function() {
              $('#{prefix}flb_profile_facebook_id').val('');
              displayAccountIcon();
            });
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_add_account_google', function() {
              $('#{prefix}flb_profile_add_account').show();
              $('#{prefix}flb_profile_detail_account_list').hide();
              
              flbExternalAccount($('#flb_core_url_path').val()+'index.php?action=eGoogleCall','assignToUser','{prefix}flb_profile_google_id');
            });
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_remove_account_google', function() {
              $('#{prefix}flb_profile_google_id').val('');
              displayAccountIcon();
            });
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_add_account_twitter', function() {
              $('#{prefix}flb_profile_add_account').show();
              $('#{prefix}flb_profile_detail_account_list').hide();
              
              flbExternalAccount($('#flb_core_url_path').val()+'index.php?action=eTwitterCall','assignToUser','{prefix}flb_profile_twitter_id');
            });
            $('#{prefix}flb_%s').on('click','#{prefix}flb_profile_remove_account_twitter', function() {
              $('#{prefix}flb_profile_twitter_id').val('');
              displayAccountIcon();
            });", $guiType, $guiType, $guiType, $guiType, $guiType, $guiType, $guiType);
  }
  
  protected function _getStateSelect($state) {
    $sel = new SState;
    $sel->addStatement(new SqlStatementMono($sel->columns['disabled'], "%s='N'"));
    $sel->addOrder(new SqlStatementAsc($sel->columns['name']));
    $sel->setColumnsMask(array('code','name'));
    $stateSelect = new GuiFormSelect(array(
          'id'          => sprintf('%sflb_profile_state', $this->_guiParams['prefix']),
          'dataSource'  => new SqlDataSource(new DataSourceSettings, $sel),
          'value'       => $state,
          'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
          'showDiv' => false,  
          ));
    
    return $stateSelect->render();
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = sprintf('
        <div class="label flb_title"><span>{__label.ajax_profile_title}</span></div>
        <div class="flb_profile_main">
          <input type="hidden" id="{prefix}flb_profile_email" value="{email}" />
          <input type="hidden" id="{prefix}flb_profile_userid" value="{id}" />
          <input type="hidden" id="{prefix}flb_profile_registration" value="{registrationId}"/>
          <input type="hidden" id="{prefix}flb_profile_language" value="{language}" />
          <input type="hidden" id="{prefix}flb_profile_showAttribute" value="{showAttribute}" />
          <input type="hidden" id="{prefix}flb_profile_checkAttributeMandatory" value="{checkAttributeMandatory}" />
          <input type="hidden" id="{prefix}flb_profile_facebook_id" value="{facebookId}" />
          <input type="hidden" id="{prefix}flb_profile_google_id" value="{googleId}" />
          <input type="hidden" id="{prefix}flb_profile_twitter_id" value="{twitterId}" />
          <div class="group">
            <label class="label flb_profile_email_label"><span>* {__label.ajax_profile_email}:</span></label>
            <input class="text flb_readonly" type="text" id="{prefix}flb_profile_email_ro" value="{email}" readonly="readonly" />
          </div>
          <div class="group">
            <label class="label flb_profile_firstname_label"><span>* {__label.ajax_profile_firstname}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_firstname" value="{firstname}" />
          </div>
          <div class="group">
            <label class="label flb_profile_lastname_label"><span>* {__label.ajax_profile_lastname}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_lastname" value="{lastname}" />
          </div>
          <div class="group">
            <label class="label flb_profile_phone_label"><span>* {__label.ajax_profile_phone}:</span></label>
            <input class="text flb_phone" type="text" id="{prefix}flb_profile_phone" value="{phone}" />
          </div>
          <div class="group">
            <label class="label flb_profile_street_label"><span>{__label.ajax_profile_street}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_street" value="{street}" />
          </div>
          <div class="group">
            <label class="label flb_profile_city_label"><span>{__label.ajax_profile_city}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_city" value="{city}" />
          </div>
          <div class="group">  
            <label class="label flb_profile_postalcode_label"><span>{__label.ajax_profile_postalCode}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_postal_code" value="{postalCode}" />
          </div>
          <div class="group">
            <label class="label flb_profile_state_label"><span>{__label.ajax_profile_state}:</span></label>
            {state}
          </div>
        </div>
        {attribute}
        %s
        {advertising}
        <div class="button">
          {button_back}
          {button_save}
        </div>', $this->_getExternalAccountHtml());
    
    $this->_guiHtml .= sprintf("
          <script>
          function displayAccountIcon() {
            if ($('#{prefix}flb_profile_facebook_id').val()) $('#{prefix}flb_profile_facebook_icon').show();
            else $('#{prefix}flb_profile_facebook_icon').hide();
            
            if ($('#{prefix}flb_profile_google_id').val()) $('#{prefix}flb_profile_google_icon').show();
            else $('#{prefix}flb_profile_google_icon').hide();
            
            if ($('#{prefix}flb_profile_twitter_id').val()) $('#{prefix}flb_profile_twitter_icon').show();
            else $('#{prefix}flb_profile_twitter_icon').hide();
          }
          
          $(document).ready(function() {
            {attributeJS}
            
            if ($.isFunction(fitToWindow)) {
              var parent = $('#{prefix}flb_profile_detail').parent();
              fitToWindow(parent.attr('id'));
            }
            
            function saveProfile() {
              var attr = new Array();
              $('#{prefix}flb_profile_detail [meaning=attribute]').each(function () {
                var idExt = $(this).attr('id');
                idExt = idExt.replace('{prefix}attr_','');
                var aValue = $(this).val();
                if (($(this).attr('attributetype')=='FILE')&&(aValue=='')) aValue = '__no_change__';
                
                attr.push({id:idExt,value:aValue});
              });
              var phone = $('#{prefix}flb_profile_detail #{prefix}flb_profile_phone').val();
              if ($.fn.intlTelInput) {
                if (!$('#{prefix}flb_profile_detail #{prefix}flb_profile_phone').intlTelInput('isValidNumber')) {
                  alert('{__error.ajax_profile_invalidPhone}');
                  return false;
                }
                phone = $('#{prefix}flb_profile_detail #{prefix}flb_profile_phone').intlTelInput('getNumber');
              } 
              $.ajax({
                type: 'POST',
                dataType: 'json',
                data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(),
                    language:                 $('#{prefix}flb_profile_language').val(),
                    email:                    $('#{prefix}flb_profile_email').val(),
                    firstname:                $('#{prefix}flb_profile_firstname').val(),  
                    lastname:                 $('#{prefix}flb_profile_lastname').val(),
                    street:                   $('#{prefix}flb_profile_street').val(),
                    city:                     $('#{prefix}flb_profile_city').val(),
                    postalCode:               $('#{prefix}flb_profile_postal_code').val(),
                    state:                    $('#{prefix}flb_profile_state').val(),
                    phone:                    phone,
                    facebookId:               $('#{prefix}flb_profile_facebook_id').val(),
                    googleId:                 $('#{prefix}flb_profile_google_id').val(),
                    twitterId:                $('#{prefix}flb_profile_twitter_id').val(),
                    attribute:                attr,
                    checkAttributeMandatory:  $('#{prefix}flb_profile_checkAttributeMandatory').val(),
                    registration:             $('#{prefix}flb_profile_registration').val(),
                    advertising:              $('#{prefix}flb_profile_advertising').is(':checked')?'Y':'N',
                },
                url: $('#flb_core_url').val()+'action=saveUser',
                success: function(data) {
                  if (data.error) alert(data.message);
                  else {
                    //$('#{parentNode}').off('.flb');
                    
                    {backAction}
                      
                    if (data.popup) alert(data.popup);
                    else alert('{__label.ajax_profile_changeOk}');
                    
                    if (typeof flbRefresh == 'function') flbRefresh('.flb_profile');
                  }
                },
                error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
              });  
            }
            
            function enableButtons() {
              $('#{prefix}flb_profile_reservation').attr('disabled', false);
              $('#{prefix}flb_profile_change').attr('disabled', false);
              $('#{prefix}flb_profile_subaccount').attr('disabled', false);
              $('#{prefix}flb_profile_password').attr('disabled', false);
              $('#{prefix}flb_profile_credit_btn').attr('disabled', false);
              $('#{prefix}flb_profile_closebutton').show();
            }
            
            %s
            
            if ($.fn.intlTelInput) {
              $('#{prefix}flb_profile_detail #{prefix}flb_profile_phone').intlTelInput({
                preferredCountries: ['cz','sk'],
                utilsScript: $('#flb_core_url_path').val()+'jq/intlTelInputUtils.js'
              });
            }
            
            //$('#{parentNode}').on('click.flb','#{prefix}flb_profile_back', function() {
            //  $('#{parentNode}').off('.flb');
            //  
            //  {backAction}
            //});
            //$('#{parentNode}').on('click.flb','#{prefix}flb_profile_save', saveProfile);
            
            $('#{prefix}flb_profile_detail').on('click','#{prefix}flb_profile_save', saveProfile);
            $('#{prefix}flb_profile_detail').on('click','#{prefix}flb_profile_back', function() {
              {backAction}
            });
          });
          </script>", $this->_getExternalAccountJSJQuery());
  }
  
  private function _getAdvertising() {
    $found = false;
    $advertising = false;
    
    foreach ($this->_guiParams['registration'] as $reg) {
      if ($reg['providerId']==$this->_params['provider']) {
        if ($reg['advertising']=='Y') $advertising = true;
        
        $this->_guiParams['registrationId'] = $reg['registrationId'];
        
        $found = true;
        break;
      }
    }
    if (!$found) $this->_guiParams['registrationId'] = '';
    
    if (isset($this->_params['hideAdvertising'])&&$this->_params['hideAdvertising']) {
      $this->_guiParams['advertising'] = sprintf('<input style="display:none;" type="checkbox" id="%sflb_profile_advertising" value="%s" checked="yes"/>',
                                                        $this->_params['prefix'], $advertising?'Y':'N');
    } else {
      $template = sprintf('
        <div class="group">
          <label class="label flb_profile_advertising_label"><span>%s:</span></label>
          <input type="checkbox" id="%sflb_profile_advertising" value="Y" %s/>
        </div>',
        $this->_app->textStorage->getText('label.ajax_profile_advertising'),
        $this->_params['prefix'], $advertising?'checked="yes"':'');

        $this->_guiParams['advertising'] = $template;
    }
  }
  
  private function _getButtons() {
    if (in_array('profile_back', $this->_params['detailButtons'])) {
      if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
        $this->_guiParams['button_back'] = sprintf('<input type="button" id="%sflb_profile_back" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.close'));
      } else {
        $this->_guiParams['button_back'] = sprintf('<input type="button" id="%sflb_profile_back" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.back'));
      }
    } else $this->_guiParams['button_back'] = '';
    
    if (in_array('profile_save', $this->_params['detailButtons'])) {
      $this->_guiParams['button_save'] = sprintf('<input type="button" id="%sflb_profile_save" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_save'));
    } else $this->_guiParams['button_save'] = '';
  }
  
  protected function _getData() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $bU = new BUser($this->_app->auth->getUserId());
    $this->_guiParams = array_merge($this->_guiParams, $bU->getData());
    
    $this->_guiParams['state'] = $this->_getStateSelect($this->_guiParams['state']);
    
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['backAction'] = sprintf("$('#%sflb_profile_detail').remove(); $('#%sflb_profile_extra').hide(); enableButtons();", $this->_guiParams['prefix'], $this->_guiParams['prefix']);
    } else {
      $this->_guiParams['backAction'] = sprintf("flbLoadHtml('guiProfile', $('#%sflb_profile_detail').parent(), %s);", $this->_guiParams['prefix'], $this->_guiParams['params']);
    }
    
    $this->_guiParams['checkAttributeMandatory'] = ifsetor($this->_params['checkAttributeMandatory'],0);
    
    $this->_guiParams['showAttribute'] = ifsetor($this->_params['showAttribute'],'');
    
    $this->_getAttribute();
    $this->_getAdvertising();
    $this->_getButtons();
  }
}

?>
