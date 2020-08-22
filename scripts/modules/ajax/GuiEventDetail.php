<?php

class AjaxGuiEventDetail extends AjaxGuiAction2 {
  protected $_paymentNeeded;
  protected $_useUserSubaccount = false;
  
  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_event_%s', $this->_params['prefix'], $this->_params['eventId']);
    $this->_class = 'flb_event_detail';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['renderText'])) $this->_params['renderText'] = array('name','description','center','start','end','cycleItem','url','attribute','price','reservation');
    if (!isset($this->_params['backButton'])) $this->_params['backButton'] = 1;
    if (!isset($this->_params['attendeeTemplate'])) $this->_params['attendeeTemplate'] = '@@FIRSTNAME @@LASTNAME';
    if (!isset($this->_params['cycleItemTemplate'])) $this->_params['cycleItemTemplate'] = '@@EVENT_START';
    if (!isset($this->_params['showAttendeePayment'])) $this->_params['showAttendeePayment'] = 0;
    if (!isset($this->_params['showInactive'])) $this->_params['showInactive'] = 0;
    
    $this->_params['eventId'] = str_replace($this->_params['prefix'],'',$this->_params['eventId']);
  }
    
  protected function _createTemplate() {
    $this->_guiHtml = '<input type="hidden" id="{prefix}flb_event_id" value="{event_id}" />';
    $this->_guiHtml .= '<input type="hidden" id="{prefix}flb_event_user_id" value="{user_id}" />';
    
    foreach ($this->_params['renderText'] as $render) {
      switch ($render) {
        case 'name':
          $this->_guiHtml .= '<div class="label flb_event_name_label"><span>{__label.ajax_event_name}:</span></div><div class="value flb_event_name">{name}</div>';
          break;
        case 'description':
          $this->_guiHtml .= '<div class="label flb_event_description_label"><span>{__label.ajax_event_description}:</span></div><div class="value flb_event_description">{description}</div>';
          break;
        case 'resource':
          if ($this->_data['all_resource_name']) {
            $this->_guiHtml .= '<div class="label flb_event_resource_label"><span>{__label.ajax_event_resource}:</span></div><div class="value flb_event_resource">{resource}</div>';
          }
          break;
        case 'url':
          if ($this->_guiParams['url']) {
            $this->_guiHtml .= '<div class="label flb_event_url_label"><span>{__label.ajax_event_url}:</span></div><div class="value flb_event_url">{url}</div>';           
          }
          break;
        case 'photo':
          if ($this->_data['url_photo']) {
            $this->_guiHtml .= '<div class="photo">{photo}</div>';
          }
          break;
        case 'photoThumb':
          $this->_guiHtml .= '<div class="photo">{photoThumb}</div>';
          break;
        case 'organiser':
          $this->_guiHtml .= '<div class="label flb_event_organiser_label"><span>{__label.ajax_event_organiser}:</span></div><div class="value flb_event_organiser"><span class="fullname">{organiser_fullname}</span><span class="email">{organiser_email}</span><span class="phone">{organiser_phone}</span></div>';
          break;
        case 'center':
          $this->_guiHtml .= '<div class="label flb_event_center_label"><span>{__label.ajax_event_center}:</span></div><div class="value flb_event_center"><span class="name">{center_name}</span><span class="street">{center_street}</span><span class="city">{center_city}</span><span class="postal_code">{center_postal_code}</span></div>';
          break;
        case 'start':
          $this->_guiHtml .= '<div class="label flb_event_start_label"><span>{__label.ajax_event_start}:</span></div><div class="value flb_event_start">{start}</div>';
          break;
        case 'end':
          $this->_guiHtml .= '<div class="label flb_event_end_label"><span>{__label.ajax_event_end}:</span></div><div class="value flb_event_end">{end}</div>';
          break;
        case 'cycleItem':
          if ($this->_data['repeat_parent']&&strcmp($this->_data['repeat_reservation'],'SINGLE')) {
            $this->_guiHtml .= '<div class="label flb_event_repeat_start_label"><span>{__label.ajax_event_repeatStart}:</span></div><div class="value flb_event_repeat_start">{repeat_item}</div>';
          }
          break;
        case 'price':
          if ($this->_data['price']) $singlePlaceHolder = '{price} {__label.currency_CZK}';
          else $singlePlaceHolder = '<span class="flb_price_free_of_charge">{__label.ajax_price_free_of_charge}</span>';
          if ($this->_data['repeat_price']) $repeatPlaceHolder = '{repeat_price} {__label.currency_CZK}';
          else $repeatPlaceHolder = '<span class="flb_price_free_of_charge">{__label.ajax_price_free_of_charge}</span>';
          if (!$this->_data['repeat_parent']||!strcmp($this->_data['repeat_reservation'],'SINGLE')) {
            $this->_guiHtml .= sprintf('<div class="label flb_event_price_label"><span>{__label.ajax_event_price}:</span></div><div class="value flb_event_price">%s</div>', $singlePlaceHolder);
          } elseif (!strcmp($this->_data['repeat_reservation'],'PACK')) {
            $this->_guiHtml .= sprintf('<div class="label flb_event_price_label"><span>{__label.ajax_event_repeatPrice}:</span></div><div class="value flb_event_price">%s</div>', $repeatPlaceHolder);
          } else {
            $this->_guiHtml .= sprintf('<div class="label flb_event_price_label"><span>{__label.ajax_event_price}:</span></div><div class="value flb_event_price">%s</div>', $singlePlaceHolder);
            $this->_guiHtml .= sprintf('<div class="label flb_event_price_label"><span>{__label.ajax_event_repeatPrice}:</span></div><div class="value flb_event_price">%s</div>', $repeatPlaceHolder);
          }
          break;
        case 'places':
          if ($this->_data['max_attendees']) {
            $this->_guiHtml .= '<div class="label flb_event_places_label"><span>{__label.ajax_event_places}:</span></div><div class="value flb_event_places">{places}</div>';
          }
          break;
        case 'attendees':
          if ($this->_data['fe_attendee_visible']!='N') $this->_guiHtml .= '{attendees}';
          break;
        case 'attribute':
          $this->_guiHtml .= '<div class="flb_commodity_attributes">{attribute}</div>';
          break;
      }
    }

    // zobrazeni ucastniku muze byt vynuceno z backoffice
    if (!in_array('attendees', $this->_params['renderText'])) {
      if (in_array($this->_data['fe_attendee_visible'],array('Y','LOGGED_USER'))) $this->_guiHtml .= '{attendees}';
    }
    
    if (in_array('reservation',$this->_params['renderText'])) $this->_guiHtml .= '{reservationInfo}{reservationGui}<div class="button">';
    
    if ($this->_params['backButton']) {
      if (!in_array('reservation',$this->_params['renderText'])) $this->_guiHtml .= '<div class="button">';
      $this->_guiHtml .= '<input type="button" id="{prefix}flb_event_back" value="{__button.back}" />';
      if (!in_array('reservation',$this->_params['renderText'])) $this->_guiHtml .= '</div>';
    }

    if (in_array('reservation',$this->_params['renderText'])) $this->_guiHtml .= '{reservationButton}</div>';

    $this->_guiHtml .= "<script>
                       $(document).ready(function() {
                         " . $this->_getJavascript() . "
                       });
                     </script>";
  }

  protected function _getJavascript() {
    return "var userData = getUserDetail({ user: $('#{prefix}flb_event_user_id').val(), event: {event_id}, price: parseInt('{price}') });
           var useUserSubaccount = {useUserSubaccount};
           eventInputAttendeesRefresh(userData);
           
           $('#{prefix}flb_event_reserve_additional_input').hide();
           $('#{prefix}flb_event_reserve_additional_button').hide();
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_pack_N', function() {
              $('#{prefix}flb_event_pack').val('N');
              calculatePrice();
           });
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_pack_Y', function() {
              $('#{prefix}flb_event_pack').val('Y');
              calculatePrice();
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_back', function() {
              flbLoadHtml('{backGui}', $(this).closest('.flb_output').parent(), {params});            
           });
  
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_notlogged', function() {
              flbLoginRequired('{language}');
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_prepare', function() {
              $(this).hide();
              
              getReservationAttribute();
              
              $('#{prefix}flb_event_reserve_additional_input').show();
              $('#{prefix}flb_event_reserve_additional_button').show();
           });
           
           {showReservationGui}
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_pay_credit', function() {
              if (confirm('{__label.ajax_event_confirmPayment}')) {
                saveReservation({pay:'Y',payType:'credit'});
              }
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_pay_ticket', function() {
              if (!$('#{prefix}flb_event_pay_ticket').val()) {
                alert('{__label.ajax_event_noTicket}');
                return;
              }
              if (confirm('{__label.ajax_event_confirmPayment}')) {
                saveReservation({pay:'Y',payType:'ticket',payTicket:$('#{prefix}flb_event_pay_ticket').val()});
              }
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','.{prefix}flb_event_reserve_pay_gw', function() {
              if (id=saveReservation({paymentOnline:$(this).attr('gw')})) {
                flbPaymentGateway('{paymentUrl}',$(this).attr('gw'),'RESERVATION',id,null,null,null);
              }
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve', function() {
              saveReservation(null);
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_substitute', function() {
              saveSubstitute();
           });
           
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_remove_voucher', function() {
              $('#{prefix}flb_event_voucher').val('');
              $('#{prefix}flb_event_voucher_discount').val('');
              $('#{prefix}flb_event_reserve_voucher').val('');
              $('#{prefix}flb_event_reserve_voucher').attr('readonly',false);
              $('#{prefix}flb_event_reserve_apply_voucher').show();
              $('#{prefix}flb_event_reserve_remove_voucher').hide();
              
              calculatePrice();
           });
           $('#{prefix}flb_event_{event_id}').on('click','#{prefix}flb_event_reserve_apply_voucher', function() {
              var code = $('#{prefix}flb_event_reserve_voucher').val();
              $.ajax({
                type: 'GET',
                url: $('#flb_core_url').val()+'action=getVoucher&sessid='+$('#flb_core_sessionid').val()+'&prefix={prefix}',
                dataType: 'json',
                data: { provider: $('#flb_core_provider').val(), code: code, eventId: $('#{prefix}flb_event_id').val(), eventPlaces: $('#{prefix}flb_event_places').val() },
                success: function(data) {
                  if (data) {
                    $('#{prefix}flb_event_voucher').val(data.id);
                    if (data.discountType=='PROPORTION') {
                      $('#{prefix}flb_event_voucher_discount').val(data.discountValue);
                      $('#{prefix}flb_event_voucher_discount').attr('data-meaning','%');
                    } else {
                      $('#{prefix}flb_event_voucher_discount').val(data.calculatedDiscountRaw);
                      $('#{prefix}flb_event_voucher_discount').attr('data-meaning','SUM');
                    }
                    
                    $('#{prefix}flb_event_reserve_voucher').val(data.code);
                    $('#{prefix}flb_event_reserve_voucher').attr('readonly',true);
                    $('#{prefix}flb_event_reserve_apply_voucher').hide();
                    $('#{prefix}flb_event_reserve_remove_voucher').show();
                  } else {
                    $('#{prefix}flb_event_reserve_voucher').val('');
                    $('#{prefix}flb_event_reserve_voucher').attr('readonly',false);
                    $('#{prefix}flb_event_reserve_apply_voucher').show();
                    $('#{prefix}flb_event_reserve_remove_voucher').hide();
                    
                    var text = '{__label.ajax_voucher_notFound}';
                    alert(text.replace('{code}',code));
                  }
                  
                  calculatePrice();
                },
                error: function(error) { alert('{__label.ajaxError}'); }
              });
           });
           
           function getReservationAttribute() {
            $.ajax({
              type: 'GET',
              url: $('#flb_core_url').val()+'action=guiReservationAttribute&sessid='+$('#flb_core_sessionid').val()+'&prefix={prefix}',
              dataType: 'json',
              data: { eventId: $('#{prefix}flb_event_id').val() },
              success: function(data) {
                $('#{prefix}flb_event_reserve_attribute').html(data.output);
              },
              error: function(error) { alert('{__label.ajaxError}'); }
            });
           }
           
           function saveReservation(params) {
             if (!params) params = {};
             var ret = false;
             
             var attendee = {};
             if (useUserSubaccount) {
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=user]').each(function() { attendee[index] = { user: $(this).val() }; index++; });
             } else {
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=firstname]').each(function() { attendee[index] = { firstname: $(this).val() }; index++; });
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=lastname]').each(function() { attendee[index].lastname = $(this).val(); index++; });
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=email]').each(function() { attendee[index].email = $(this).val(); index++; });
             }
             
             var attr = {};
             $('#{prefix}flb_event_{event_id} [meaning=reservation_attribute]').each(function () {
                var idExt = $(this).attr('id');
                idExt = idExt.replace('{prefix}attr_','');
                attr[idExt] = $(this).val();
             });
             
             if ($('#flb_core_userid').val()) {
                var data = { provider: $('#flb_core_provider').val(),
                            sessid: $('#flb_core_sessionid').val(), 
                            event: $('#{prefix}flb_event_id').val(),
                            user: $('#{prefix}flb_event_user_id').val(),
                            places: $('#{prefix}flb_event_places').val(),
                            pack: $('#{prefix}flb_event_pack').val(),
                            attendee: JSON.stringify(attendee),
                            attribute: attr,
                          };
                if (ch = $('#{prefix}flb_event_mandatory')) {
                  if (ch.is(':checked')) data.mandatory = 'Y';
                }
                if (voucher = $('#{prefix}flb_event_voucher').val()) {
                  data.voucher = voucher;
                }
                if (params.pay) data.pay = params.pay;
                if (params.payType) data.payType = params.payType;
                if (params.payTicket) data.payTicket = params.payTicket;
                if (params.paymentOnline) data.paymentOnline = params.paymentOnline;
                $.ajax({
                    async: false,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    url: $('#flb_core_url').val()+'action=saveReservation',
                    success: function(data) {
                        if (data.error) alert(data.message);
                        else {
                          ret = data.id;
                          
                          if (data.popup) alert(data.popup);
                          
                          if (typeof flbRefresh == 'function') {
                            flbRefresh('.flb_event_detail');
                          }
                        }
                    },
                    error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                });
              } else {
                alert('{__label.calendar_loginRequired}');
              }
              
              return ret;
           }
           
           function saveSubstitute() {
             var ret = false;
             
             var attendee = {};
             if (useUserSubaccount) {
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=user]').each(function() { attendee[index] = { user: $(this).val() }; index++; });
             } else {
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=firstname]').each(function() { attendee[index] = { firstname: $(this).val() }; index++; });
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=lastname]').each(function() { attendee[index].lastname = $(this).val(); index++; });
               var index = 0;
               $('#{prefix}flb_event_reserve_attendees tr [meaning=email]').each(function() { attendee[index].email = $(this).val(); index++; });
             }
             
             var attr = {};
             $('#{prefix}flb_event_{event_id} [meaning=reservation_attribute]').each(function () {
                var idExt = $(this).attr('id');
                idExt = idExt.replace('{prefix}attr_','');
                attr[idExt] = $(this).val();
             });
             
             if ($('#flb_core_userid').val()) {
                $.ajax({
                    async: false,
                    type: 'POST',
                    dataType: 'json',
                    data: { provider: $('#flb_core_provider').val(),
                            sessid: $('#flb_core_sessionid').val(), 
                            event: $('#{prefix}flb_event_id').val(),
                            user: $('#{prefix}flb_event_user_id').val(),
                            places: $('#{prefix}flb_event_places').val(),
                            attendee: JSON.stringify(attendee),
                            attribute: attr,
                          },
                    url: $('#flb_core_url').val()+'action=saveSubstitute',
                    success: function(data) {
                        if (data.error) alert(data.message);
                        else {
                          ret = data.id;
                          
                          if (data.popup) alert(data.popup);
                          
                          if (typeof flbRefresh == 'function') flbRefresh('.flb_event_detail');
                        }
                    },
                    error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                });
              } else {
                alert('{__label.calendar_loginRequired}');
              }
              
              return ret;
           }
           
           $('#{prefix}flb_event_places').change(function() { calculatePrice(); eventInputAttendeesRefresh(null); });
           
           function calculatePrice() {
             var price = 0;
             if ($('#{prefix}flb_event_pack').val()=='Y') price = $('#{prefix}flb_event_pack_price').val();
             else price = $('#{prefix}flb_event_single_price').val();
             
             price = price*parseInt($('#{prefix}flb_event_places').val());
             
             var voucherEl = $('#{prefix}flb_event_voucher_discount');
             if (voucherEl.val()) {
               if (voucherEl.attr('data-meaning')=='SUM') price -= voucherEl.val();
               else if (voucherEl.attr('data-meaning')=='%') price -= price*voucherEl.val()/100;
             }
             
             $('#{prefix}flb_event_total_price_amount').html(price);
           }
           
           function eventInputAttendeesRefresh(userData) {
              var subaccountSelectHtml = '';
              if (useUserSubaccount) {
                if (!userData) subaccountSelectHtml = $('#{prefix}flb_event_reserve_attendee_1 select').html();
                if (!subaccountSelectHtml) {
                  $.ajax({
                    type: 'GET',
                    async: false,
                    dataType: 'json',
                    data: {
                      user : $('#{prefix}flb_event_user_id').val(),
                      sessid: $('#flb_core_sessionid').val(),
                    },
                    url: $('#flb_core_url').val()+'action=getUserSubaccount',
                    success: function(data) {
                      var selectHtml = '';
                      $.each(data, function(index,element) {
                        subaccountSelectHtml += '<option value=\"'+element.id+'\">'+element.name+'</option>';
                      });
                    },
                    error: function(error) { alert('{__label.ajaxError}'); }
                  });
                }
              }
              
              var person = parseInt($('#{prefix}flb_event_places').val())*parseInt($('#{prefix}flb_event_coAttendees').val());
              if (person>0) {
                var oldValues = new Array(); var oIndex = 0;
                $('#{prefix}flb_event_reserve_attendees [meaning=attendee]').each(function() {
                  // musim ulozit puvodni hodnoty
                  oldValues[oIndex] = {
                    user: $(this).find('[meaning=user]').val(),
                    firstname: $(this).find('[meaning=firstname]').val(),
                    lastname: $(this).find('[meaning=lastname]').val(),
                    email: $(this).find('[meaning=email]').val()
                  }
                  $(this).remove();
                  
                  oIndex++;
                });
                var count = 0;
                while (count<person) {
                  var index = count+1;
                  if (useUserSubaccount) {
                    $('#{prefix}flb_event_reserve_attendees').append('<tr meaning=\"attendee\" id=\"{prefix}flb_event_reserve_attendee_'+index.toString()+'\">'+
                          '<td><select meaning=\"user\">'+subaccountSelectHtml+'</select></td>'+
                        '</tr>');
                    // nastavim puvodni hodnotu
                    if ((typeof oldValues[count] === 'object')&&oldValues[count].user&&
                        ($('#{prefix}flb_event_reserve_attendee_'+index.toString()+' select option[value='+oldValues[count].user+']').length>0)) {
                      $('#{prefix}flb_event_reserve_attendee_'+index.toString()+' select').val(oldValues[count].user);
                    } 
                  } else {
                    var fVal='';var lVal='';var eVal='';
                    if (oIndex>count) {
                      fVal = oldValues[count].firstname;
                      lVal = oldValues[count].lastname;
                      eVal = oldValues[count].email;
                    } else if (!count&&userData) {
                      fVal = userData.firstname;
                      lVal = userData.lastname;
                      eVal = userData.email;
                    } 
                    
                    $('#{prefix}flb_event_reserve_attendees').append('<tr meaning=\"attendee\" id=\"{prefix}flb_event_reserve_attendee_'+index.toString()+'\">'+
                        '<td><input meaning=\"firstname\" type=\"text\" value=\"'+fVal+'\"/></td>'+
                        '<td><input meaning=\"lastname\" type=\"text\" value=\"'+lVal+'\"/></td>'+
                        '<td><input meaning=\"email\" class=\"email\" type=\"text\" value=\"'+eVal+'\"/></td>'+
                      '</tr>');
                  }
                  
                  count++;
                }
              }
            }";
  }
  
  protected function _getAttributeGui() {
    $ret = '';
    
    $b = new BEvent($this->_data['event_id']);
    $attributes = $b->getAttribute();
    $this->_paymentNeeded = $b->isPaymentNeeded();

    $category = '';
    foreach ($attributes as $id=>$attribute) {
      if (isset($this->_params['showAttribute'])&&$this->_params['showAttribute']) {
        if (!in_array($attribute['category'], $this->_params['showAttribute'])) continue;
      }
      // atributy jsou uzavreny do DIVu kategorie
      if (strcmp($category,$attribute['category'])) {
        if ($category) $ret .= '</div>';
        if ($attribute['category']) $ret .= sprintf('<div class="flb_commodity_attributecategory_name">%s</div><div class="flb_commodity_attributecategory" id="flb_commodity_attributecategory_%s">', $attribute['category'], htmlize($attribute['category']));
      }
      switch ($attribute['type']) {
        case 'NUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value']); break;
        case 'DECIMALNUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2); break;
        case 'TIME':
          $value = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($attribute['value']), ifsetor($this->_params['format']['time'],'H:i'));
          break;
        case 'DATETIME':
          $value = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($attribute['value']), ifsetor($this->_params['format']['datetime'],'d.m.Y H:i'));
          break;
        case 'DATE':
          $value = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($attribute['value']), ifsetor($this->_params['format']['date'],'d.m.Y'));
          break;
        case 'FILE':
          global $AJAX;
          $value = sprintf('<a target="_attributeFile" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), $attribute['valueId'], $attribute['value']);
          break;
        default: $value = $attribute['value'];
      }
      
      $attrHtml = sprintf('<div id="flb_commodity_attribute_%s" class="flb_commodity_attribute"><div class="label">%s:</div><div class="value flb_commodity_attributevalue">%s</div></div>',
        $id, formatAttributeName($attribute['name'], $attribute['url']), $value);
      
      $ret .= $attrHtml;
      
      $category = $attribute['category'];
    }
    if ($category) $ret .= '</div>';
    
    $this->_guiParams['attribute'] = $ret;
  }
  
  protected function _getAttendeeInputGui() {
    $ret = sprintf(
      '<div class="flb_event_reserve_attendee">
            <div class="label flb_event_reserve_attendee_label"><span>%s:</span></div>
            <table class="attendee">
              <tr%s>
                <th>%s</th>
                <th>%s</th>
                <th>%s</th>
              </tr>
              <tbody id="%sflb_event_reserve_attendees"></tbody>
            </table>
          </div>', $this->_app->textStorage->getText('label.ajax_event_attendees'),
      $this->_useUserSubaccount?' style="display:none;"':'',
      $this->_app->textStorage->getText('label.ajax_event_attendeeFirstname'),
      $this->_app->textStorage->getText('label.ajax_event_attendeeLastname'),
      $this->_app->textStorage->getText('label.ajax_event_attendeeEmail'),
      $this->_params['prefix']);
    
    return $ret;
  }
  
  protected function _getReservationAttributeInputGui() {
    $ret = sprintf('<div id="%sflb_event_reserve_attribute" class="flb_reserve_attribute"></div>', $this->_params['prefix']);
    
    return $ret;
  }
  
  protected function _getReservationGui() {
    $reservationInfo = '';
    $reservationGui = '';
    $reservationButton = '';
    $showReservationGui = '';

    $alreadyReserved = false;
    
    if (in_array('reservation',$this->_params['renderText'])) {
      if ($user=$this->_app->auth->getUserId()) {
        $s = new SEventAttendee;
        $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_params['eventId'], '%s=%s'));
        $s->addStatement(new SqlStatementBi($s->columns['user'], $user, '%s=%s'));
        $s->setColumnsMask(array('eventattendee_id'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($this->_app->db->getRowsNumber($res)) {
          $reservationInfo = sprintf('<div class="label flb_event_reserved"><span>%s</span></div>', $this->_app->textStorage->getText('label.ajax_event_reserved'));
          $reserveButtonLabel = 'button.ajax_event_reserveAgain';
          $alreadyReserved = true;
        } else {
          $reserveButtonLabel = 'button.ajax_event_reserve';
        }

        if ($this->_data['free']||$this->_data['free_substitute']) {
          $showTotalPrice = false;
          $showVoucher = false;
          
          // moznost vybrat cele opakovani nebo jednotliva akce
          if ($this->_data['free']&&!strcmp($this->_data['repeat_reservation'],'BOTH')) {
            $extraInput = sprintf('<div class="label flb_event_pack_label"><span>%s:</span></div>
                                   <input type="radio" id="%sflb_event_pack_N" name="pack" value="N" checked="yes"/>%s
                                   <input type="radio" id="%sflb_event_pack_Y" name="pack" value="Y"/>%s',
                                   $this->_app->textStorage->getText('label.ajax_event_reservePack'),
                                   $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_event_reservePack_single'),
                                   $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_event_reservePack_pack'));
            $extraInput .= sprintf('<input type="hidden" id="%sflb_event_pack" value="N" />', $this->_params['prefix']);
            
            $showTotalPrice = true;
          } else {
            $extraInput = sprintf('<input type="hidden" id="%sflb_event_pack" value="%s" />', $this->_params['prefix'], !strcmp($this->_data['repeat_reservation'],'PACK')?'Y':'N');
          }
          
          // moznost vice mist na rezervaci
          $extraInput .= sprintf('<input type="hidden" id="%sflb_event_coAttendees" value="%s" />', $this->_params['prefix'], $this->_data['max_coattendees']);
          if ($this->_data['reservation_max_attendees']>1) {
            $input = sprintf('<select class="flb_event_places" id="%sflb_event_places">', $this->_params['prefix']);
            $capacity = $this->_data['free']>0?$this->_data['free']:$this->_data['free_substitute'];
            for ($i=1;($i<=$this->_data['reservation_max_attendees'])&&($i<=$capacity);$i++) $input .= sprintf('<option value="%s">%s</option>', $i, $i);
            $input .= '</select>';
            $extraInput .= sprintf('<div class="label flb_event_reserve_places_label"><span>%s:</span></div>%s', $this->_app->textStorage->getText('label.ajax_event_reservePlaces'), $input);
            
            $showTotalPrice = true;
          } else {
            $extraInput .= sprintf('<input type="hidden" id="%sflb_event_places" value="1" />', $this->_params['prefix']);
          }

          // moznost voucheru
          if ($this->_data['free']&&$this->_app->auth->getUserId()) {
            $bUser = new BUser($this->_app->auth->getUserId());
            $tags = $this->_data['all_tag']?explode(',', $this->_data['all_tag']):null;
            $voucher = $bUser->getAvailableVoucher($this->_params['provider'], $this->_data['price'], $this->_data['center'], $tags);
            if (count($voucher)) {
              $showTotalPrice = true;
              $showVoucher = true;
            }
          }

          // inputy pro ucastniky
          $extraInput .= $this->_getAttendeeInputGui();

          // inputy pro dodatecne atributy rezervace
          $extraInput .= $this->_getReservationAttributeInputGui();

          $buttons = '';
          if ($this->_data['fe_quick_reservation']!='Y') {
            $buttons = sprintf('<input type="button" id="%sflb_event_reserve_prepare" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText($reserveButtonLabel));
            $buttons .= sprintf('<div id="%sflb_event_reserve_additional_button" class="flb_event_reserve_additional_button">', $this->_params['prefix']);
          }

          if ($this->_data['free']) {
            if (!$this->_paymentNeeded) $buttons .= sprintf('<input type="button" id="%sflb_event_reserve" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_reserve'));
          } else {
            $buttons .= sprintf('<input type="button" id="%sflb_event_reserve_substitute" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_reserve_substitute'));
            
            $reservationInfo .= sprintf('<div class="label flb_event_occupied"><span>%s</span></div>', $this->_app->textStorage->getText('label.ajax_event_substituteAvailable'));
          }
          
          $availablePayment = array();
          $applicablePrice = !strcmp($this->_data['repeat_reservation'],'PACK')?$this->_data['repeat_price']:$this->_data['price'];
          if ($this->_data['free']&&$applicablePrice) {
            $providerSettings = $settings = BCustomer::getProviderSettings($this->_params['provider'],array('disableCredit','disableTicket','disableOnline'));

            // zjistim jestli muze platit z kreditu
            if (($this->_data['fe_allowed_payment_credit'])&&($providerSettings['disableCredit']=='N')) {
              $s1 = new SUser;
              $s1->addStatement(new SqlStatementBi($s1->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
              $s1->addStatement(new SqlStatementBi($s1->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
              $s1->setColumnsMask(array('registration_credit'));
              $res1 = $this->_app->db->doQuery($s1->toString());
              if (($row1=$this->_app->db->fetchAssoc($res1))&&($row1['registration_credit']>=$this->_data['price'])) $availablePayment[] = 'credit';
            }
            
            // zjistim jestli muze platit permanentkou
            if (($this->_data['fe_allowed_payment_ticket'])&&($providerSettings['disableTicket']=='N')) {
              if ($user=$this->_app->auth->getUserId()) {
                if ($this->_data['all_tag']) $tag = explode(',', $this->_data['all_tag']);
                else $tag = array();
                $bUser = new BUser($user);
                $ticket = $bUser->getAvailableTicket($this->_params['provider'], true, $this->_data['center'], $tag, $this->_data['price']);
                if (count($ticket)) $availablePayment[] = 'ticket';
              }
            }
            
            // zjistim jestli muze platit platebni branou
            $gateway = array();
            if (($this->_data['fe_allowed_payment_online'])&&($providerSettings['disableOnline']=='N')) {
              $gateway = BCustomer::getProviderPaymentGateway($this->_params['provider'], $this->_data['price']);
              if (count($gateway)) $availablePayment[] = 'gateway';
            }
            
            if (in_array('credit',$availablePayment)) { 
              $buttons .= sprintf('<input type="button" id="%sflb_event_reserve_pay_credit" class=" flb_primaryButton" value="%s" />',
                                      $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_reservePayCredit'));
            }  
            if (in_array('ticket',$availablePayment)) {
              $buttons .= sprintf('<input type="button" id="%sflb_event_reserve_pay_ticket" class=" flb_primaryButton" value="%s" />',
                                      $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_reservePayTicket'));
            }
            if (in_array('gateway',$availablePayment)) {  
              foreach ($gateway as $gw) {
                $buttons .= sprintf('<input type="button" class="%sflb_event_reserve_pay_gw flb_primaryButton" gw="%s" value="%s %s" />',
                                        $this->_params['prefix'], $gw['name'],
                                        $this->_app->textStorage->getText('button.ajax_event_reservePayGW'),
                                        $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gw['name']));
              }
            }
          }

          if ($this->_data['fe_quick_reservation']!='Y') {
            $buttons .= '</div>';
          }
          
          if ($showTotalPrice) {
            $extraInput .= sprintf('<input type="hidden" id="%sflb_event_single_price" value="%s"/>
             <input type="hidden" id="%sflb_event_pack_price" value="%s"/>
             <input type="hidden" id="%sflb_event_voucher" value=""/>
             <input type="hidden" id="%sflb_event_voucher_discount" data-meaning="" value=""/>
             <div class="flb_event_reserve_price">
               <div class="label flb_event_total_price_label"><span>%s:</span></div>
               <div class="value flb_event_total_price"><span id="%sflb_event_total_price_amount">%s</span> %s</div>
             </div>',
             $this->_params['prefix'], $this->_data['price'],
             $this->_params['prefix'], $this->_data['repeat_price'],
             $this->_params['prefix'], $this->_params['prefix'],
             $this->_app->textStorage->getText('label.ajax_event_totalPrice'),
             $this->_params['prefix'],
             !strcmp($this->_data['repeat_reservation'],'PACK')?$this->_data['repeat_price']:$this->_data['price'],
             $this->_app->textStorage->getText('label.currency_CZK'));
          }
          if ($showVoucher) {
            $extraInput .= sprintf('<div id="%sflb_event_pay_additional_input_voucher" class="flb_event_pay_additional_input_voucher label">
              <div class="label flb_event_voucher_label"><span>%s:</span></div>
              <input type="text" id="%sflb_event_reserve_voucher" />
              <input type="button" class="flb_button" id="%sflb_event_reserve_apply_voucher" value="%s"/>
              <span class="flb_event_voucher_remove" style="display:none;" id="%sflb_event_reserve_remove_voucher">%s</span>
              </div>',
              $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_voucher'), $this->_params['prefix'],
              $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_voucher_apply'),
              $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_voucher_remove'));
          }
          if (in_array('ticket',$availablePayment)) {
            $ticketSelect = sprintf('<select id="%sflb_event_pay_ticket"><option value="">%s</option>',
                                    $this->_params['prefix'], $this->_app->textStorage->getText('label.select_choose'));
            foreach ($ticket as $t) { $ticketSelect .= sprintf('<option value="%s">%s / %s %s</option>', $t['id'], $t['name'], $t['value'], $t['currency']); }
            $ticketSelect .= '</select>';
            
            $extraInput .= sprintf('<div id="%sflb_event_pay_additional_input_ticket" class="flb_event_pay_additional_input_ticket label">
               <div class="label flb_event_ticket_label"><span>%s:</span></div>
               %s
               </div>',
               $this->_params['prefix'],
               $this->_app->textStorage->getText('label.ajax_event_reserveTicket'),
               $ticketSelect);
          }
          
          $reservationGui = sprintf('<div id="%sflb_event_reserve_additional_input" class="input">%s</div>', $this->_params['prefix'], $extraInput);
          $reservationButton = sprintf('%s', $buttons);
        }
        
        if (!$alreadyReserved&&isset($this->_params['reserve'])&&$this->_params['reserve']) $showReservationGui = sprintf("$('#%sflb_event_reserve_prepare').click();", $this->_params['prefix']);
      } else {
        $reservationButton = $buttons = sprintf('<input type="button" id="%sflb_event_reserve_notlogged" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_event_reserve'));
      }
    }
    
    $this->_guiParams['reservationInfo'] = $reservationInfo;
    $this->_guiParams['reservationGui'] = $reservationGui;
    $this->_guiParams['reservationButton'] = $reservationButton;
    $this->_guiParams['showReservationGui'] = $showReservationGui;
  }
  
  protected function _parseAttendeeLine($lineData, $specialLineClass='') {
    foreach ($lineData as $key=>$value) {
      if (strcmp($key,'user')) $data['@@'.strtoupper($key)] = $value;
    }
    
    if (strpos($this->_params['attendeeTemplate'],'USER_ATTRIBUTE')!==false) {
      // nejdriv vsechny atributy poskytovatele "vynuluju"
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->setColumnsMask(array('attribute_id','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@USER_ATTRIBUTE('.$row['short_name'].')'] = ''; }
      $s = new SUserAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['user'], $lineData['user'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->setColumnsMask(array('attribute','short_name','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@USER_ATTRIBUTE('.$row['short_name'].')'] = $row['value']; }  
    }
    
    $attendeeLine = str_replace(array_keys($data), $data, $this->_params['attendeeTemplate']);
    return sprintf('<div class="value flb_event_attendee %s%s">%s</div>', $specialLineClass,
      isset($lineData['failed'])&&$lineData['failed']?' flb_event_attendee_failed':'', $attendeeLine);
  }
  
  protected function _getAttendeeGui() {
    if (($this->_data['fe_attendee_visible']=='Y')||$this->_app->auth->getUserId()) {
      $template = sprintf('<div class="label flb_event_attendees_label"><span>%s:</span></div><div class="value flb_event_attendees">',
                          $this->_app->textStorage->getText('label.ajax_event_attendees'));
      $reservation = '';
      $s = new SEventAttendee;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_params['eventId'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='N'"));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
      $s->setColumnsMask(array('reservation_id','reservation_event_pack','reservation_payed','failed','user','places','fullname','email','phone',
        'person_user','person_user_firstname','person_user_lastname','person_user_email','person_firstname','person_lastname','person_email'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($this->_app->db->getRowsNumber($res)) {
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $line = array(
            'user'            => $row['user'],
            'user_fullname'   => $row['fullname'],
            'user_email'      => $row['email'],
            'user_phone'      => $row['phone'],
            'firstname'       => $row['person_user']?$row['person_user_firstname']:$row['person_firstname'],
            'lastname'        => $row['person_user']?$row['person_user_lastname']:$row['person_lastname'],
            'email'           => $row['person_user']?$row['person_user_email']:$row['person_email'],
            'places'          => $row['places']
          );

          if (strcmp($reservation,$row['reservation_id'])) {
            if ($reservation) $template .= '</div>';
            $template .= '<div class="flb_event_attendee_reservation">';
            if ($this->_data['organiser']==$this->_app->auth->getUserId()) {
              $buttons = '';

              if (!$row['failed']) {
                if (!$row['reservation_payed']&&($row['reservation_event_pack']!='Y')&&$row['user']&&($row['places']<=$this->_data['free_substitute'])) {
                  $buttons .= sprintf('<input type="button" id="%sswapAttendee_%d" class="btn_swapAttendee" title="%s" value="v"/>', $this->_params['prefix'], $row['reservation_id'],
                    $this->_app->textStorage->getText('label.ajax_event_swapAttendeeTitle'));
                }
                $buttons .= sprintf('<input type="button" id="%sattendeeFail_%d" class="btn_attendeeFail" title="%s" value="x"/>', $this->_params['prefix'], $row['reservation_id'],
                  $this->_app->textStorage->getText('label.ajax_event_attendeeFailTitle'));
              } else {
                $line['failed'] = $row['failed'];
              }

              if ($buttons) $template .= sprintf('<div class="flb_event_attendee_action">%s</div>', $buttons);
            }
          }

          $template .= $this->_parseAttendeeLine($line, $this->_params['showAttendeePayment']&&$row['reservation_payed']?'flb_event_attendee_payed':null);
          
          $reservation = $row['reservation_id'];
        }
        if ($reservation) $template .= '</div>';
        
        $ret = $template;
        
        // jeste nahradniky
        $template = sprintf('<div class="label flb_event_attendees_label flb_event_substitutes_label"><span>%s:</span></div><div class="value flb_event_attendees flb_event_substitutes">',
                          $this->_app->textStorage->getText('label.ajax_event_substitutes'));
        $user = '';
        $s = new SEventAttendee;
        $s->addStatement(new SqlStatementBi($s->columns['event'], $this->_params['eventId'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['substitute'], "%s='Y'"));
        $s->addOrder(new SqlStatementAsc($s->columns['fullname']));
        $s->setColumnsMask(array('eventattendee_id','user','places','fullname','email','phone','person_firstname','person_lastname','person_email'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($this->_app->db->getRowsNumber($res)) {
          while ($row = $this->_app->db->fetchAssoc($res)) {
            $line = array(
              'user'            => $row['user'],
              'user_fullname'   => $row['fullname'],
              'user_email'      => $row['email'],
              'user_phone'      => $row['phone'],
              'firstname'       => $row['person_firstname'],
              'lastname'        => $row['person_lastname'],
              'email'           => $row['person_email'],
              'places'          => $row['places'],
            );

            if (strcmp($user,$row['user'])) {
              if ($user) $template .= '</div>';
              $template .= '<div class="flb_event_attendee_reservation">';
              if ($this->_data['organiser']==$this->_app->auth->getUserId()) {
                $buttons = '';

                if ($row['places']<=$this->_data['free']) {
                  $buttons .= sprintf('<input type="button" id="%sswapSubstitute_%d" class="btn_swapSubstitute" title="%s" value="^"/>', $this->_params['prefix'], $row['eventattendee_id'],
                    $this->_app->textStorage->getText('label.ajax_event_swapSubstituteTitle'));
                }

                if ($buttons) $template .= sprintf('<div class="flb_event_attendee_action">%s</div>', $buttons);
              }
            }

            $template .= $this->_parseAttendeeLine($line, 'flb_event_substitute');
            
            $user = $row['user'];
          }
          if ($user) $template .= '</div>';
          
          $ret .= $template;
        }
      } else $ret = '';
    } else $ret = '';
    
    $this->_guiParams['attendees'] = $ret;
  }

  protected function _getPhotoGui() {
    $this->_guiParams['photo'] = '';
    foreach (explode(',',$this->_data['url_photo']) as $photo) {
      $this->_guiParams['photo'] .= sprintf('<img class="flb_event_photo" src="%s"/>', $photo);
    }

    $this->_guiParams['photoThumb'] = $this->_getPhotoThumb($this->_data['event_id'], $this->_data['url_photo']);
  }
  
  protected function _getUrlGui() {
    $this->_guiParams['url'] = '';
    
    if ($this->_data['url_description']) {
      if ($this->_guiParams['url']) $this->_guiParams['url'] .= '&nbsp;';
      $this->_guiParams['url'] .= sprintf('<div class="flb_event_url_item"><a target="_blank" href="%s">%s</a></div>', $this->_data['url_description'], $this->_app->textStorage->getText('label.ajax_event_url_description'));
    }
    if ($this->_data['url_price']) {
      if ($this->_guiParams['url']) $this->_guiParams['url'] .= '&nbsp;';
      $this->_guiParams['url'] .= sprintf('<div class="flb_event_url_item"><a target="_blank" href="%s">%s</a></div>', $this->_data['url_price'], $this->_app->textStorage->getText('label.ajax_event_url_price'));
    }
    if ($this->_data['url_opening']) {
      if ($this->_guiParams['url']) $this->_guiParams['url'] .= '&nbsp;';
      $this->_guiParams['url'] .= sprintf('<div class="flb_event_url_item"><a target="_blank" href="%s">%s</a></div>', $this->_data['url_opening'], $this->_app->textStorage->getText('label.ajax_event_url_opening'));
    } 
    if ($this->_data['url_photo']) {
      if ($this->_guiParams['url']) $this->_guiParams['url'] .= '&nbsp;';
      $this->_guiParams['url'] .= sprintf('<div class="flb_event_url_item"><a target="_blank" href="%s">%s</a></div>', $this->_data['url_photo'], $this->_app->textStorage->getText('label.ajax_event_url_photo'));
    }   
  }

  protected function _parseRepeatLine($lineData) {
    foreach ($lineData as $key=>$value) $data['@@'.strtoupper($key)] = $value;

    if (strpos($this->_params['cycleItemTemplate'],'EVENT_ATTRIBUTE')!==false) {
      // nejdriv vsechny atributy poskytovatele "vynuluju"
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute_id','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@EVENT_ATTRIBUTE('.$row['short_name'].')'] = ''; }
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $lineData['event_id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute','short_name','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@EVENT_ATTRIBUTE('.$row['short_name'].')'] = $row['value']; }
    }

    $ret = str_replace(array_keys($data), $data, $this->_params['cycleItemTemplate']);

    return $ret;
  }

  protected function _getRepeatGui() {
    $this->_guiParams['repeat_item'] = '';
    
    if ($this->_data['repeat_parent']) {
      $end = '';
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $this->_data['repeat_parent'], '%s=%s'));
      if (!$this->_params['showInactive']) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $s->setColumnsMask(array('event_id','start','end','street','city','region','postal_code','state','center_name','organiser_fullname'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row=$this->_app->db->fetchAssoc($res)) {
        $startDate = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['start']), isset($this->_params['format']['date'])?$this->_params['format']['date']:'d.m.Y');
        $startTime = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['start']), isset($this->_params['format']['time'])?$this->_params['format']['time']:'H:i');
        $endDate = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['date'])?$this->_params['format']['date']:'d.m.Y');
        $endTime = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['time'])?$this->_params['format']['time']:'H:i');

        $start = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['start']), isset($this->_params['format']['datetime'])?$this->_params['format']['datetime']:'d.m.Y H:i');
        if (substr($row['start'],0,10)==substr($row['end'],0,10)) {
          $end = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['time'])?$this->_params['format']['time']:'H:i');
        } else {
          $end = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['datetime'])?$this->_params['format']['datetime']:'d.m.Y H:i');
        }
        $line = array(
          'event_id'                => $row['event_id'],
          'event_center_street'     => $row['street'],
          'event_center_city'       => $row['city'],
          'event_center_region'     => $row['region'],
          'event_center_zip'        => $row['postal_code'],
          'event_center_country'    => $row['state'],
          'event_center'            => $row['center_name'],
          'event_start_date'        => $startDate,
          'event_start_time'        => $startTime,
          'event_end_date'          => $endDate,
          'event_end_time'          => $endTime,
          'event_start'             => $start,
          'event_end'               => $end,
          'event_organiser'         => $row['organiser_fullname'],
        );
        $this->_guiParams['repeat_item'] .= sprintf('<div class="flb_event_repeat_item">%s</div>', $this->_parseRepeatLine($line));

        $end = $row['end'];
      }

      if (!strcmp($this->_data['repeat_reservation'],'PACK')) $this->_guiParams['end'] = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($end), ifsetor($this->_params['format']['datetime'],'d.m.Y H:i'));
    }
  }

  protected function _getData() {
    if (isset($this->_params['reserve'])&&$this->_params['reserve']&&!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('label.calendar_loginRequired');
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_params['eventId'], '%s=%s'));
    if (!$this->_params['showInactive']) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('event_id','start','end','name','price','description','all_tag',
     'fe_attendee_visible','fe_quick_reservation','fe_allowed_payment_credit','fe_allowed_payment_ticket','fe_allowed_payment_online',
     'url_description','url_price','url_photo','url_opening','center','center_name','street','city','postal_code',
     'all_resource_name','reservation_max_attendees','max_attendees','max_coattendees','free','free_substitute',
     'organiser','organiser_fullname','organiser_email','organiser_phone',
     'repeat_parent','repeat_price','repeat_reservation'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_data=$this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser('FLB error: invalid event!');
    } else {
      $this->_guiParams['event_id'] = $this->_data['event_id'];
      $this->_guiParams['user_id'] = $this->_app->auth->getUserId();
      $this->_guiParams['name'] = $this->_data['name'];
      $this->_guiParams['description'] = formatCommodityDescription($this->_data['description']);
      $this->_guiParams['resource'] = $this->_data['all_resource_name'];
      $this->_guiParams['price'] = $this->_app->regionalSettings->convertNumberToHuman($this->_data['price'],2);
      $this->_guiParams['repeat_price'] = $this->_app->regionalSettings->convertNumberToHuman($this->_data['repeat_price'],2);
      $this->_guiParams['center_name'] = $this->_data['center_name'];
      $this->_guiParams['center_street'] = $this->_data['street'];
      $this->_guiParams['center_city'] = $this->_data['city'];
      $this->_guiParams['center_postal_code'] = $this->_data['postal_code'];
      $this->_guiParams['organiser_fullname'] = $this->_data['organiser_fullname'];
      $this->_guiParams['organiser_email'] = $this->_data['organiser_email'];
      $this->_guiParams['organiser_phone'] = $this->_data['organiser_phone'];
      
      $this->_guiParams['start'] = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($this->_data['start']), ifsetor($this->_params['format']['datetime'],'d.m.Y H:i'));
      $this->_guiParams['end'] = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($this->_data['end']), ifsetor($this->_params['format']['datetime'],'d.m.Y H:i'));
      
      $this->_guiParams['places'] = sprintf('%d (%s %d)', $this->_data['max_attendees'], $this->_app->textStorage->getText('label.ajax_event_freePlaces'), $this->_data['free']);

      $this->_guiParams['backGui'] = ifsetor($this->_params['backGui'], 'guiEventList');

      $this->_useUserSubaccount = BCustomer::getProviderSettings($this->_params['provider'],'userSubaccount')=='Y';
      $this->_guiParams['useUserSubaccount'] = $this->_useUserSubaccount?1:0;
      
      global $PAYMENT_GATEWAY;
      $this->_guiParams['paymentUrl'] = sprintf($PAYMENT_GATEWAY['initUrl'], ifsetor($this->_params['language'],'cz'), $this->_params['sessid'], $this->_params['provider']);
    
      $this->_getRepeatGui();
      $this->_getUrlGui();
      $this->_getPhotoGui();
      $this->_getAttributeGui();   
      $this->_getAttendeeGui();
      $this->_getReservationGui();
    }
  }
}

?>
