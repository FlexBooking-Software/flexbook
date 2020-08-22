<?php

class AjaxGuiSubaccountDetail extends AjaxGuiProfileDetail {
  protected $_accountType = 'SUBACCOUNT';
  
  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_profile_subaccount_detail', $this->_params['prefix']);
    $this->_class = 'flb_profile_subaccount_detail flb_profile';
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = sprintf('
        <div class="label flb_title"><span>{__label.ajax_profile_subaccount_title}</span></div>
        <div class="flb_profile_main">
          <input type="hidden" id="{prefix}flb_profile_userid" value="{id}" />
          <input type="hidden" id="{prefix}flb_profile_language" value="{language}" />
          <input type="hidden" id="{prefix}flb_profile_showAttribute" value="{showAttribute}" />
          <input type="hidden" id="{prefix}flb_profile_checkAttributeMandatory" value="{checkAttributeMandatory}" />
          <div class="group">
            <label class="label flb_profile_firstname_label"><span>* {__label.ajax_profile_firstname}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_firstname" value="{firstname}" />
          </div>
          <div class="group">
            <label class="label flb_profile_lastname_label"><span>* {__label.ajax_profile_lastname}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_lastname" value="{lastname}" />
          </div>
          <div class="group">
            <label class="label flb_profile_email_label"><span>{__label.ajax_profile_email}:</span></label>
            <input class="text" type="text" id="{prefix}flb_profile_email" value="{email}" />
          </div>
          <div class="group">
            <label class="label flb_profile_phone_label"><span>{__label.ajax_profile_phone}:</span></label>
            <input class="text flb_phone" type="text" id="{prefix}flb_profile_phone" value="{phone}" />
          </div>
        </div>
        {attribute}
        <div class="button">
          <input type="button" id="{prefix}flb_profile_back" value="{__button.back}" />
          <input type="button" id="{prefix}flb_profile_save" class="flb_primaryButton" value="{__button.ajax_profile_save}" />
        </div>');
    
    $this->_guiHtml .= sprintf("
          <script>
          $(document).ready(function() {
            {attributeJS}
            
            function saveProfile() {
              var attr = new Array();
              $('#{prefix}flb_profile_subaccount_detail [meaning=attribute]').each(function () {
                var idExt = $(this).attr('id');
                idExt = idExt.replace('{prefix}attr_','');
                var aValue = $(this).val();
                if (($(this).attr('attributetype')=='FILE')&&(aValue=='')) aValue = '__no_change__';
                
                attr.push({id:idExt,value:aValue});
              });
              var phone = $('#{prefix}flb_profile_subaccount_detail #{prefix}flb_profile_phone').val();
              if ($.fn.intlTelInput) {
                if ($('#{prefix}flb_profile_subaccount_detail #{prefix}flb_profile_phone').val()&&!$('#{prefix}flb_profile_subaccount_detail #{prefix}flb_profile_phone').intlTelInput('isValidNumber')) {
                  alert('{__error.ajax_profile_invalidPhone}');
                  return false;
                }
                phone = $('#{prefix}flb_profile_subaccount_detail #{prefix}flb_profile_phone').intlTelInput('getNumber');
              } 
              $.ajax({
                type: 'POST',
                dataType: 'json',
                data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(),
                    id:                       $('#{prefix}flb_profile_userid').val(),
                    language:                 $('#{prefix}flb_profile_language').val(),
                    email:                    $('#{prefix}flb_profile_email').val(),
                    firstname:                $('#{prefix}flb_profile_firstname').val(),  
                    lastname:                 $('#{prefix}flb_profile_lastname').val(),
                    phone:                    phone,
                    attribute:                attr,
                    checkAttributeMandatory:  $('#{prefix}flb_profile_checkAttributeMandatory').val(),
                },
                url: $('#flb_core_url').val()+'action=saveSubaccount',
                success: function(data) {
                  if (data.error) alert(data.message);
                  else {
                    flbLoadHtml('guiSubaccountList', $('#{prefix}flb_profile_subaccount_detail').parent(), $.extend({params}, { prefix: '{prefix}'}));
                      
                    if (data.popup) alert(data.popup);
                    else alert('{__label.ajax_profile_subaccount_saveOk}');
                  }
                },
                error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
              });  
            }
            
            if ($.fn.intlTelInput) {
              $('#{prefix}flb_profile_subaccount_detail #{prefix}flb_profile_phone').intlTelInput({
                preferredCountries: ['cz','sk'],
                utilsScript: $('#flb_core_url_path').val()+'jq/intlTelInputUtils.js'
              });
            }
            
            $('#{prefix}flb_profile_subaccount_detail').on('click','#{prefix}flb_profile_back', function() {
              flbLoadHtml('guiSubaccountList', $('#{prefix}flb_profile_subaccount_detail').parent(), $.extend({params}, { prefix: '{prefix}'}));
            });
            
            $('#{prefix}flb_profile_subaccount_detail').on('click','#{prefix}flb_profile_save', saveProfile);
          });
          </script>");
  }
  
  protected function _getData() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');

    if ($accountId = ifsetor($this->_params['id'],false)) {
      $bU = new BUser($accountId);
      $this->_guiParams = array_merge($this->_guiParams, $bU->getData());
    } else {
      $this->_guiParams['id'] = '';
      $this->_guiParams['firstname'] = '';
      $this->_guiParams['lastname'] = '';
      $this->_guiParams['email'] = '';
      $this->_guiParams['phone'] = '';
    }
    
    $this->_guiParams['checkAttributeMandatory'] = ifsetor($this->_params['checkAttributeMandatory'],0);
    
    $this->_guiParams['showAttribute'] = ifsetor($this->_params['showAttribute'],'');
    
    $this->_getAttribute(false, $accountId);
  }
}

?>
