<?php

class AjaxGuiReservationDetail extends AjaxGuiAction2 {
  
  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_reservation_%s', $this->_params['prefix'], $this->_params['reservationId']);
    $this->_class = 'flb_reservation_detail';
  }
  
  protected function _initDefaultParams() {
    $this->_params['reservationId'] = str_replace($this->_params['prefix'],'',$this->_params['reservationId']);
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = "<script>
                $(document).ready(function() {
                  $('#{prefix}flb_reservation_detail_pay_additional_input').hide();
                  $('#{prefix}flb_reservation_detail_pay_additional_button').hide();
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_back', function() {
                     flbLoadHtml('guiReservationList', $('#{prefix}flb_reservation_{reservation_id}').parent(), {params});            
                  });
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_pay', function() {
                     $(this).hide();
                     $('#{prefix}flb_reservation_detail_pay_additional_input').show();
                     $('#{prefix}flb_reservation_detail_pay_additional_button').show();
                  });
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','.{prefix}flb_reservation_detail_pay_gw', function() {
                    var response = saveReservationVoucher();
                    var backAction = 'flbRefresh(\'#{prefix}flb_reservation_{reservation_id}\');';
                    if (!response.payed) {
                      var gw = $(this).attr('gw');
                      $.ajax({
                         type: 'POST',
                         dataType: 'json',
                         data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: $('#{prefix}flb_reservation_id').val() },
                         url: $('#flb_core_url').val()+'action=getReservation',
                         success: function(data) {
                            if (data.error) alert(data.message);
                            else if (!data.id) alert('{__error.payReservation_alreadyCancelled}');
                            else if (data.payed) alert('{__error.payReservation_alreadyPayed}');
                            else flbPaymentGateway('{paymentUrl}',gw,'RESERVATION',data.id,null,null,backAction);
                         },
                         error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                      });
                    } else eval(backAction);
                  });
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_pay_credit', function() {
                     if (confirm('{__label.ajax_reservation_confirmPayment}')) {
                       var response = saveReservationVoucher();
                       var backAction = 'flbRefresh(\'#{prefix}flb_reservation_{reservation_id}\');';
                       if (!response.payed) {
                         $.ajax({
                             type: 'POST',
                             dataType: 'json',
                             data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: $('#{prefix}flb_reservation_id').val() },
                             url: $('#flb_core_url').val()+'action=payReservation',
                             success: function(data) {
                                 if (data.error) alert(data.message);
                                 else eval(backAction);
                             },
                             error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                         });
                       } else eval(backAction);
                     }
                  });
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_pay_ticket', function() {
                    if (!$('#{prefix}flb_reservation_detail_ticket').val()) {
                      alert('{__label.ajax_reservation_noTicket}');
                      return;
                    }
                    if (confirm('{__label.ajax_reservation_confirmPayment}')) {
                       var response = saveReservationVoucher();
                       var backAction = 'flbRefresh(\'#{prefix}flb_reservation_{reservation_id}\');';
                       if (!response.payed) {
                          $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: $('#{prefix}flb_reservation_id').val(), type: 'ticket', ticket: $('#{prefix}flb_reservation_detail_ticket').val() },
                            url: $('#flb_core_url').val()+'action=payReservation',
                            success: function(data) {
                                if (data.error) alert(data.message);
                                else eval(backAction);
                            },
                            error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                          });
                       } else eval(backAction);
                    }
                  });
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_cancel', function() {
                    if (confirm('{__label.ajax_reservation_confirmCancel}')) {
                      $.ajax({
                         type: 'POST',
                         dataType: 'json',
                         data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: $('#{prefix}flb_reservation_id').val() },
                         url: $('#flb_core_url').val()+'action=cancelReservation',
                         success: function(data) {
                             if (data.error) alert(data.message);
                             else {
                               flbLoadHtml('guiReservationDetail', $('#{prefix}flb_reservation_{reservation_id}').parent(), $.extend({params}, { reservationId: $('#{prefix}flb_reservation_id').val(), prefix: '{prefix}'}));
                               $('.flb_calendar').fullCalendar('refetchEvents');
                             }
                         },
                         error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                      });
                    }
                  });
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_cancelRefund', function() {
                    if (confirm('{__label.ajax_reservation_confirmCancelRefund}')) {
                      $.ajax({
                         type: 'POST',
                         dataType: 'json',
                         data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: $('#{prefix}flb_reservation_id').val(), refund: 'Y' },
                         url: $('#flb_core_url').val()+'action=cancelReservation',
                         success: function(data) {
                             if (data.error) alert(data.message);
                             else {
                               flbLoadHtml('guiReservationDetail', $('#{prefix}flb_reservation_{reservation_id}').parent(), $.extend({params}, { reservationId: $('#{prefix}flb_reservation_id').val(), prefix: '{prefix}'}));
                               $('.flb_calendar').fullCalendar('refetchEvents');
                             }
                         },
                         error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                      });
                    }
                  });
                  
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_receipt', function() {
                    var w = 900;
                    var h = 650;
                    var left = (screen.width/2)-(w/2);
                    var top = (screen.height/2)-(h/2);
                    
                    var newWindow = window.open('/','_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
                    newWindow.location.href = 'about:blank';
                    newWindow.location.href = $('#flb_core_url').val()+'action=vReservationReceipt&id={reservation_id}&sessid='+$('#flb_core_sessionid').val();
                  });
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_invoice', function() {
                    var w = 900;
                    var h = 650;
                    var left = (screen.width/2)-(w/2);
                    var top = (screen.height/2)-(h/2);
                    
                    var newWindow = window.open('/','_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
                    newWindow.location.href = 'about:blank';
                    newWindow.location.href = $('#flb_core_url').val()+'action=vReservationInvoice&id={reservation_id}&sessid='+$('#flb_core_sessionid').val();
                  });
                  $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_ticket_btn', function() {
                    var w = 900;
                    var h = 650;
                    var left = (screen.width/2)-(w/2);
                    var top = (screen.height/2)-(h/2);
                    
                    var newWindow = window.open('/','_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
                    newWindow.location.href = 'about:blank';
                    newWindow.location.href = $('#flb_core_url').val()+'action=vReservationTicket&id={reservation_id}&sessid='+$('#flb_core_sessionid').val();
                  });
                  
                   $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_remove_voucher', function() {
                      $('#{prefix}flb_reservation_voucher').val('');
                      $('#{prefix}flb_reservation_voucher_discount').val('');
                      $('#{prefix}flb_reservation_detail_voucher').val('');
                      $('#{prefix}flb_reservation_detail_voucher').attr('readonly',false);
                      $('#{prefix}flb_reservation_detail_voucher_apply').show();
                      $('#{prefix}flb_reservation_detail_remove_voucher').hide();
                      
                      calculatePrice();
                   });
                   $('#{prefix}flb_reservation_{reservation_id}').on('click','#{prefix}flb_reservation_detail_voucher_apply', function() {
                      var code = $('#{prefix}flb_reservation_detail_voucher').val();
                      $.ajax({
                        type: 'GET',
                        url: $('#flb_core_url').val()+'action=getVoucher&sessid='+$('#flb_core_sessionid').val()+'&prefix={prefix}',
                        dataType: 'json',
                        data: { provider: $('#flb_core_provider').val(), code: code, reservationId: {reservation_id} },
                        success: function(data) {
                          if (data) {
                            $('#{prefix}flb_reservation_voucher').val(data.id);
                            if (data.discountType=='PROPORTION') {
                              $('#{prefix}flb_reservation_voucher_discount').val(data.discountValue);
                              $('#{prefix}flb_reservation_voucher_discount').attr('data-meaning','%');
                            } else {
                              $('#{prefix}flb_reservation_voucher_discount').val(data.calculatedDiscountRaw);
                              $('#{prefix}flb_reservation_voucher_discount').attr('data-meaning','SUM');
                            }
                            
                            $('#{prefix}flb_reservation_detail_voucher').val(data.code);
                            $('#{prefix}flb_reservation_detail_voucher').attr('readonly',true);
                            $('#{prefix}flb_reservation_detail_voucher_apply').hide();
                            $('#{prefix}flb_reservation_detail_remove_voucher').show();
                          } else {
                            $('#{prefix}flb_reservation_detail_voucher').val('');
                            $('#{prefix}flb_reservation_detail_voucher').attr('readonly',false);
                            $('#{prefix}flb_reservation_detail_voucher_apply').show();
                            $('#{prefix}flb_reservation_detail_remove_voucher').hide();
                            
                            var text = '{__label.ajax_voucher_notFound}';
                            alert(text.replace('{code}',code));
                          }
                          
                          calculatePrice();
                        },
                        error: function(error) { alert('{__label.ajaxError}'); }
                      });
                   });
                  
                  function calculatePrice() {
                     var price = $('#{prefix}flb_reservation_price').val();
                     
                     var voucherEl = $('#{prefix}flb_reservation_voucher_discount');
                     if (voucherEl.val()) {
                       if (voucherEl.attr('data-meaning')=='SUM') price -= voucherEl.val();
                       else if (voucherEl.attr('data-meaning')=='%') price -= price*voucherEl.val()/100;
                     }
                     
                     $('#{prefix}flb_reservation_price_amount').html(price);
                   }
                   
                  function saveReservationVoucher() {
                    var ret = {error: false, payed: false};
                    
                    if ($('#{prefix}flb_reservation_voucher').val()) {
                      $.ajax({
                        async: false,
                        type: 'POST',
                        dataType: 'json',
                        data: { id: {reservation_id}, voucher: $('#{prefix}flb_reservation_voucher').val(), sessid: $('#flb_core_sessionid').val() },
                        url: $('#flb_core_url').val()+'action=saveReservationVoucher',
                        success: function(data) {
                          if (data.error) {
                            alert(data.message);
                            ret.error = true;
                          } else if (data.payed) {
                            ret.payed = true;
                          }
                        },
                        error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                      });  
                    }
                     
                    return ret;    
                  }
                  
                  {showPaymentGui}
                });
             </script>
             <input type=\"hidden\" id=\"{prefix}flb_reservation_id\" value=\"{reservation_id}\" />
             <input type=\"hidden\" id=\"{prefix}flb_reservation_price\" value=\"{total_price}\" />
             <input type=\"hidden\" id=\"{prefix}flb_reservation_voucher\" value=\"\"/>
             <input type=\"hidden\" id=\"{prefix}flb_reservation_voucher_discount\" data-meaning=\"\" value=\"\"/>
             <div class=\"label flb_reservation_number_label\"><span>{__label.ajax_reservation_number}:</span></div>
             <div class=\"value flb_reservation_number\">{number}</div>
             <div class=\"label flb_reservation_commodity_label\"><span>{__label.ajax_reservation_commodity}:</span></div>
             <div class=\"value flb_reservation_commodity\">{commodity}</div>
             <div class=\"label flb_reservation_created_label\"><span>{__label.ajax_reservation_created}:</span></div>
             <div class=\"value flb_reservation_created\">{created}</div>
             {eventAttendee}
             {attribute}
             <div class=\"label flb_reservation_payed_label\"><span>{__label.ajax_reservation_payed}:</span></div>
             <div class=\"value flb_reservation_payed\">{payed}</div>
             <div class=\"label flb_reservation_cancelled_label\"><span>{__label.ajax_reservation_cancelled}:</span></div>
             <div class=\"value flb_reservation_cancelled\">{cancelled}</div>
             <div class=\"label flb_reservation_price_label\"><span>{__label.ajax_reservation_price}:</span></div>
             <div class=\"value flb_reservation_price\"><span id=\"{prefix}flb_reservation_price_amount\">{total_price}</span> {__label.currency_CZK}</div>
             {voucherInfo}
             {payInput}
             <div class=\"button\">
             <input type=\"button\" id=\"{prefix}flb_reservation_detail_back\" value=\"{__button.back}\" />
             {payButton}
             {cancelButton}
             {ticketButton}
             {receiptButton}
             {invoiceButton}
             </div>
              ";
  }
  
  private function _getAttributeGui() {
    $b = new BReservation($this->_data['reservation_id']);
    $attribute = $b->getAttribute();
    
    $template = '';
    foreach ($attribute as $id=>$attr) {
      $attr['name'] = ifsetor($attr['name'][$this->_app->language->getLanguage()], array_values($attr['name'])[0]);
      switch ($attr['type']) {
        case 'NUMBER':
          $attr['value'] = $this->_app->regionalSettings->convertNumberToHuman($attr['value']);
          break;
        case 'DECIMALNUMBER':
          $attr['value'] = $this->_app->regionalSettings->convertNumberToHuman($attr['value'],2);
          break;
        case 'DATE':
          $attr['value'] = $this->_app->regionalSettings->convertDateToHuman($attr['value']);
          if (isset($this->_params['format']['date'])) $attr['value'] = date($this->_params['format']['date'], strtotime($attr['value']));
          break;
        case 'DATETIME':
          $attr['value'] = $this->_app->regionalSettings->convertDateTimeToHuman($attr['value']);
          if (isset($this->_params['format']['datetime'])) $attr['value'] = date($this->_params['format']['datetime'], strtotime($attr['value']));
          break;
        case 'TIME':
          $attr['value'] = $this->_app->regionalSettings->convertTimeToHuman($attr['value']);
          if (isset($this->_params['format']['time'])) $attr['value'] = date($this->_params['format']['time'], strtotime($attr['value']));
          break;
        case 'FILE':
          global $AJAX;
          $attr['value'] = sprintf('<a target="_attributeFile" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), $attr['valueId'], $attr['value']);
          break;
      }
      
      $template .= sprintf('<div class="label flb_reservation_attribute_label"><span>%s:</span></div>
                           <div class="value flb_reservation_attribute">%s</div>', $attr['name'], $attr['value']);
    }
             
    $this->_guiParams['attribute'] = $template;
  }
  
  private function _getPayGui() {
    $inputTemplate = '';
    $buttonTemplate = '';
    
    if (!$this->_data['cancelled']&&!$this->_data['payed']&&$this->_data['total_price']&&($this->_data['start']>date('Y-m-d H:i:s'))) {
      $availablePayment = array();

      $providerSettings = BCustomer::getProviderSettings($this->_params['provider'],array('disableCredit','disableTicket','disableOnline'));
      
      // zjistim jestli muze platit z kreditu
      if (($this->_data['fe_allowed_payment_credit'])&&($providerSettings['disableCredit']=='N')) {
        $s1 = new SUser;
        $s1->addStatement(new SqlStatementBi($s1->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
        $s1->addStatement(new SqlStatementBi($s1->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
        $s1->setColumnsMask(array('registration_credit'));
        $res = $this->_app->db->doQuery($s1->toString());
        if (($row=$this->_app->db->fetchAssoc($res))&&($row['registration_credit']>=$this->_data['total_price'])) $availablePayment[] = 'credit';
      }
      
      // zjistim jestli muze platit permanentkou
      if (($this->_data['fe_allowed_payment_ticket'])&&($providerSettings['disableTicket']=='N')) {
        if ($user=$this->_app->auth->getUserId()) {
          if ($this->_data['event']) $allTag = $this->_data['all_event_tag'];
          else $allTag = $this->_data['all_resource_tag'];
          if ($allTag) $tag = explode(',', $allTag);
          else $tag = null;
          $bUser = new BUser($user);
          $ticket = $bUser->getAvailableTicket($this->_params['provider'], true, $this->_data['center'], $tag, $this->_data['total_price']);
          if (count($ticket)) $availablePayment[] = 'ticket';
        }
      }
          
      // zjistim jestli muze platit platebni branou
      $gateway = array();
      if (($this->_data['fe_allowed_payment_online'])&&($providerSettings['disableOnline']=='N')) {
        $gateway = BCustomer::getProviderPaymentGateway($this->_params['provider'], $this->_data['total_price']);
        if (count($gateway)) $availablePayment[] = 'gateway';
      }
      
      if ((count($availablePayment)>1)||(count($gateway)>1)) {
        $buttonTemplate .= sprintf('<input type="button" id="%sflb_reservation_detail_pay" value="%s" />
                                 <div id="%sflb_reservation_detail_pay_additional_button" class="flb_reservation_detail_pay_additional_button">',
                                 $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_pay'), $this->_params['prefix']);
      }
      if (in_array('credit',$availablePayment)) {
        $buttonTemplate .= sprintf('<input type="button" id="%sflb_reservation_detail_pay_credit" value="%s" />',
                                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_payCredit'));
      }  
      if (in_array('ticket',$availablePayment)) {
        $buttonTemplate .= sprintf('<input type="button" id="%sflb_reservation_detail_pay_ticket" value="%s" />',
                                  $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_payTicket'));
      }
      if (in_array('gateway',$availablePayment)) {  
        foreach ($gateway as $gw) {
          $buttonTemplate .= sprintf('<input type="button" class="%sflb_reservation_detail_pay_gw" gw="%s" value="%s %s" />',
                                  $this->_params['prefix'], $gw['name'],
                                  $this->_app->textStorage->getText('button.ajax_reservation_payGW'),
                                  $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gw['name']));
        }
      }
      if ((count($availablePayment)>1)||(count($gateway)>1)) $buttonTemplate .= '</div>';

      if (!$this->_data['voucher_code']) {
        // kdyz neni u rezervace slevovy kod, pujde jeste pridat
        $bUser = new BUser($this->_app->auth->getUserId());
        $tags = $this->_data['all_event_tag']?$this->_data['all_event_tag']:$this->_data['all_resource_tag'];
        $tags = $tags?explode(',', $tags):null;
        $voucher = $bUser->getAvailableVoucher($this->_params['provider'], $this->_data['total_price'], $this->_data['center'], $tags);
        if (count($voucher)) {
          $inputTemplate .= sprintf('<div class="label flb_reservation_detail_voucher_label"><span>%s:</span></div>
              <input type="text" id="%sflb_reservation_detail_voucher" class="flb_reservation_detail_voucher"/>
              <input type="button" class="flb_button flb_reservation_detail_voucher_apply" id="%sflb_reservation_detail_voucher_apply" value="%s"/>
              <div class="flb_reservation_detail_voucher_remove" style="display:none;" id="%sflb_reservation_detail_remove_voucher"><span>%s</span></div>',
            $this->_app->textStorage->getText('label.ajax_voucher'), $this->_params['prefix'],
            $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_voucher_apply'),
            $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_voucher_remove'));
        }
      }
      
      if (in_array('ticket',$availablePayment)) {
        $ticketSelect = sprintf('<select id="%sflb_reservation_detail_ticket"><option value="">%s</option>',
                                $this->_params['prefix'], $this->_app->textStorage->getText('label.select_choose'));
        foreach ($ticket as $t) { $ticketSelect .= sprintf('<option value="%s">%s / %s %s</option>', $t['id'], $t['name'], $t['value'], $t['currency']); }
        $ticketSelect .= '</select>';

        $inputTemplate .= sprintf('<div class="label flb_reservation_detail_ticket_label"><span>%s:</span></div>%s',
          $this->_app->textStorage->getText('label.ajax_event_reserveTicket'),
          $ticketSelect);

        if (count($availablePayment)>1) {
          $inputTemplate = sprintf('<div id="%sflb_reservation_detail_pay_additional_input" class="flb_event_pay_additional_input label">%s</div>',
            $this->_params['prefix'], $inputTemplate);
        }
      }
    }
    
    $this->_guiParams['payInput'] = $inputTemplate;
    $this->_guiParams['payButton'] = $buttonTemplate;

    if (isset($this->_params['pay'])&&$this->_params['pay']) {
      $this->_guiParams['showPaymentGui'] = sprintf("$('#%sflb_reservation_detail_pay').click();", $this->_params['prefix']);
    } else $this->_guiParams['showPaymentGui'] = '';
  }
  
  private function _getPaymentGatewayUrl() {
    global $PAYMENT_GATEWAY;
    
    $ret = sprintf($PAYMENT_GATEWAY['initUrl'], ifsetor($this->_params['language'],'cz'), $this->_params['sessid'], $this->_params['provider']);
    #$this->_data['paymentUrl'] .= '&placeholder='.$this->_params['prefix'].'flb_reservation_'.$this->_data['reservation_id'];
    #$ret .= '&jsbackaction='.urlencode(sprintf("flbRefresh('#%sflb_reservation_%s');", $this->_params['prefix'], $this->_data['reservation_id']));
      
    $this->_guiParams['paymentUrl'] = $ret;
  }
  
  protected function _getData() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_params['reservationId'], '%s=%s'));
    //$s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['failed'], '%s IS NULL'));
    $s->setColumnsMask(array('reservation_id','center','number','receipt_number','invoice_number',
      'created','failed','payed','cancelled','notification','total_price','event_pack',
      'mandatory','start','end','voucher_code',
      'fe_allowed_payment_credit', 'fe_allowed_payment_ticket', 'fe_allowed_payment_online',
      'event','event_start','event_name','event_places','all_event_tag',
      'resource','resource_from','resource_to','resource_name','all_resource_tag'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_data=$this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser('FLB error: invalid reservation!');
    } else {
      if ($this->_data['event']) {
        if ($this->_data['event_pack']=='Y') {
          $start = '';
          $s = new SEventAttendee;
          $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_data['reservation_id'], '%s=%s'));
          $s->addOrder(new SqlStatementAsc($s->columns['start']));
          $s->setColumnsMask(array('start','failed'));
          $res = $this->_app->db->doQuery($s->toString());
          while ($row = $this->_app->db->fetchAssoc($res)) {
            $s = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
            if (isset($this->_params['format']['datetime'])) $s = date($this->_params['format']['datetime'], strtotime($s));
            
            if ($start) $start .= ', ';
            if ($row['failed']) $start .= sprintf('<span class="failed">%s</span>', $s);
            else $start .= $s;
          }
        } else { 
          $start = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['event_start']);
          if (isset($this->_params['format']['datetime'])) $start = date($this->_params['format']['datetime'], strtotime($start));
        }
        $this->_guiParams['commodity'] = sprintf('%s - %dx (%s)', $this->_data['event_name'], $this->_data['event_places'], $start);
        
        $person = '';
        $s = new SEventAttendeePerson;
        $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_data['reservation_id'], '%s=%s'));
        $s->addOrder(new SqlStatementAsc($s->columns['firstname']));
        $s->setDistinct(true);
        $s->setColumnsMask(array('firstname','lastname','email','subaccount','subaccount_firstname','subaccount_lastname','subaccount_email'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($row['subaccount']) {
            $firstname = $row['subaccount_firstname'];
            $lastname = $row['subaccount_lastname'];
            $email = $row['subaccount_email'];
          } else {
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $email = $row['email'];
          }

          if ($person) $person .= ', ';
          $person .= sprintf('%s %s', $firstname, $lastname);
          if ($email) $person .= sprintf(' (%s)', $email);
        }
        $this->_guiParams['eventAttendee'] = sprintf(
            '<div class="label flb_reservation_attendee_label"><span>%s:</span></div>
             <div class="value flb_reservation_attendee">%s</div>',
             $this->_app->textStorage->getText('label.ajax_reservation_attendee'), $person);
      } else {
        $from = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['resource_from']);
        if (isset($this->_params['format']['datetime'])) $from = date($this->_params['format']['datetime'], strtotime($from));
        if (substr($this->_data['resource_from'],0,10)==substr($this->_data['resource_to'],0,10)) {
          $to = $this->_app->regionalSettings->convertTimeToHuman(substr($this->_data['resource_to'],11),'h:m');
          if (isset($this->_params['format']['time'])) $to = date($this->_params['format']['time'], strtotime($to));
        } else {
          $to = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['resource_to']);
          if (isset($this->_params['format']['datetime'])) $to = date($this->_params['format']['datetime'], strtotime($to));
        }
        $this->_guiParams['commodity'] = sprintf('%s %s - %s', $this->_data['resource_name'], $from, $to);
        $this->_guiParams['eventAttendee'] = '';
      }
      
      $this->_getAttributeGui();
      $this->_getPayGui();
      $this->_getPaymentGatewayUrl();
      
      if (!$this->_data['cancelled']&&!$this->_data['failed']&&($this->_data['start']>date('Y-m-d H:i:s'))&&($this->_data['mandatory']!='Y')) {
        if (!$this->_data['payed']) $this->_guiParams['cancelButton'] = sprintf('<input type="button" id="%sflb_reservation_detail_cancel" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_cancel'));
        else $this->_guiParams['cancelButton'] = sprintf('<input type="button" id="%sflb_reservation_detail_cancelRefund" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_cancel'));
      } else $this->_guiParams['cancelButton'] = '';

      if ($this->_data['payed']&&!$this->_data['cancelled']&&!$this->_data['failed']) {
        $this->_guiParams['ticketButton'] = sprintf('<input type="button" id="%sflb_reservation_detail_ticket_btn" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_ticket'));
      } else $this->_guiParams['ticketButton'] = '';

      $this->_guiParams['receiptButton'] = $this->_guiParams['invoiceButton'] = '';
      if ($this->_data['receipt_number']) {
        $this->_guiParams['receiptButton'] = sprintf('<input type="button" id="%sflb_reservation_detail_receipt" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_receipt'));
      }
      if ($this->_data['invoice_number']) {
        $this->_guiParams['invoiceButton'] = sprintf('<input type="button" id="%sflb_reservation_detail_invoice" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_reservation_invoice'));
      }
      if ($this->_data['voucher_code']) {
        $this->_guiParams['voucherInfo'] = sprintf('<div class="label flb_reservation_voucher_label"><span>%s:</span></div>
             <div class="value flb_reservation_voucher">%s</div>', $this->_app->textStorage->getText('label.ajax_voucher'), $this->_data['voucher_code']);
      } else $this->_guiParams['voucherInfo'] = '';
      
      $this->_guiParams['reservation_id'] = $this->_data['reservation_id'];
      $this->_guiParams['number'] = $this->_data['number'];
      $this->_guiParams['created'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['created']);
      if (isset($this->_params['format']['datetime'])) $this->_guiParams['created'] = date($this->_params['format']['datetime'], strtotime($this->_data['created']));
      if ($this->_data['payed']) {
        $this->_guiParams['payed'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['payed']);
        if (isset($this->_params['format']['datetime'])) $this->_guiParams['payed'] = date($this->_params['format']['datetime'], strtotime($this->_data['payed']));
        
        // pridam jeste zpusob platby
        $s1 = new SReservationJournal;
        $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $this->_data['reservation_id'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['action'], "%s='PAY'"));
        $s1->setColumnsMask(array('note'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        if ($row1 = $this->_app->db->fetchAssoc($res1)) {
          $parts = explode('|', $row1['note']);
          $this->_guiParams['payed'] .= sprintf(' (%s)', $this->_app->textStorage->getText('label.ajax_reservation_payment_'.$parts[0]));
        }
      } else $this->_guiParams['payed'] = '---';
      if ($this->_data['cancelled']) {
        $this->_guiParams['cancelled'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['cancelled']);
        if (isset($this->_params['format']['datetime'])) $this->_guiParams['cancelled'] = date($this->_params['format']['datetime'], strtotime($this->_data['cancelled']));
      } else $this->_guiParams['cancelled'] = '---';
      if ($this->_data['failed']) {
        $this->_guiParams['failed'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['failed']);
        if (isset($this->_params['format']['datetime'])) $this->_guiParams['failed'] = date($this->_params['format']['datetime'], strtotime($this->_data['failed']));
      } else $this->_guiParams['failed'] = '---';
      $this->_guiParams['total_price'] = $this->_app->regionalSettings->convertNumberToHuman($this->_data['total_price']);
    }
  }

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');

    parent::_userRun();
  }
}

?>
