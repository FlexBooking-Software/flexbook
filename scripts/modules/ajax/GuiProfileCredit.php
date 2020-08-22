<?php

class AjaxGuiProfileCredit extends AjaxGuiAction2 {
  private $_paymentGateway = array();
  
  public function __construct($request) {
    parent::__construct($request);
  
    $this->_id = sprintf('%sflb_profile_credit', $this->_params['prefix']);
    $this->_class = 'flb_profile_credit';
  }

  protected function _getPaymentGw() {
    if (BCustomer::getProviderSettings($this->_params['provider'],'disableOnline')=='N') {
      $s1 = new SProviderPaymentGateway;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s1->addStatement(new SqlStatementMono($s1->columns['active'], "%s='Y'"));
      $s1->setColumnsMask(array('providerpaymentgateway_id', 'gateway_name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        $this->_paymentGateway[] = array('name' => $row1['gateway_name']);
      }
    }
  }

  protected function _createTemplate() {
    $this->_guiHtml = '
          <div class="label flb_title"><span>{__label.ajax_profile_credit_title}</span></div>
          <div class="label flb_profile_credit_amount_label"><span>{__label.ajax_profile_credit_amount}:</span></div>
          <div class="value flb_profile_credit_amount">{creditAmount} {currency}</div>
          {ticketList}
          <div id="{prefix}flb_profile_credit_history" class="flb_profile_credit_history">
            <hr/>
            <div id="{prefix}flb_profile_credit_history_data" class="flb_profile_credit_history_data"></div>
          </div>
          <div id="{prefix}flb_profile_credit_charge" class="flb_profile_credit_charge">
            <hr/>
            <div class="group">
              <label class="label flb_profile_credit_charge_label"><span>{__label.ajax_profile_credit_chargeAmount}:</span></label>
              <input type="text" id="{prefix}flb_profile_credit_charge_amount"/>
            </div>
            <div class="flb_profile_credit_charge_button">
              {creditChargeButtons}
            </div>
            <hr/>     
          </div>  
          <div id="{prefix}flb_profile_credit_available_tickets" class="flb_profile_credit_available_ticket">
            <hr/>
            {availableTicketList}
            <hr/>
          </div>
          <div class="button">
            <input type="button" id="{prefix}flb_profile_credit_back" value="{backLabel}" />
            {creditChargeButton}
            {ticketChargeButton}
            <input type="button" id="{prefix}flb_profile_credit_history_btn" value="{__button.ajax_profile_credit_history}" />
            {prepaymentInvoiceButton}
          </div>';
          
    $this->_guiHtml .= "<script>
                     $(document).ready(function() {
                       $('#{prefix}flb_profile_credit_history').hide();
                       $('#{prefix}flb_profile_credit_charge').hide();
                       $('#{prefix}flb_profile_credit_available_tickets').hide();
                    
                       $('#{prefix}flb_profile_credit').on('click','#{prefix}flb_profile_credit_back', function() {
                          {backAction}
                       });
                       
                       $('#{prefix}flb_profile_credit').on('click','#{prefix}flb_profile_credit_history_btn', function() {
                          $.ajax({
                            type: 'GET',
                            url: $('#flb_core_url').val()+'action=getUserCreditHistory',
                            dataType: 'json',
                            data: {
                                language: '{language}',
                                provider: $('#flb_core_provider').val(),
                                user: $('#flb_core_userid').val(),
                                sessid: $('#flb_core_sessionid').val(),
                            },
                            success: function(data) {
                              if (data.error) alert(data.message);
                              else {
                                $('#{prefix}flb_profile_credit_history_data').html('');
                                $.each(data, function(index,element) {
                                  if (element.invoice) {
                                    invoice = '<a target=\"_blank\" href=\"'+$('#flb_core_url').val()+'action=vUserPrepaymentInvoice&id='+element.id+'&sessid='+$('#flb_core_sessionid').val()+'\"><img title=\"{__label.editUserCredit_historyInvoiceTitle}\" src=\"https://www.flexbook.cz/img/button_grid_download.png\"/></a>';
                                  } else invoice = '&nbsp;';
                                  $('#{prefix}flb_profile_credit_history_data').append('<div class=\"item\">'+
                                              '<div class=\"download\">'+invoice+'</div>'+
                                              '<div class=\"time\">'+element.timestamp+'</div>'+
                                              '<div class=\"type\">'+element.typeLabel+'</div>'+
                                              '<div class=\"amount\">'+element.amount+' '+element.currency+'</div>'+
                                              '<div class=\"description\">'+element.description+'</div>'+
                                              '</div>');
                                });
                              }
                            },
                            error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                          }); 
                        
                          $('#{prefix}flb_profile_credit_charge').hide();
                          $('#{prefix}flb_profile_credit_available_tickets').hide();
                          $('#{prefix}flb_profile_credit_history').show(); 
                       });
                       
                       $('#{prefix}flb_profile_credit').on('click','#{prefix}flb_profile_credit_prepaymentinvoice_btn', function() {
                          $.ajax({
                            type: 'GET',
                            url: $('#flb_core_url').val()+'action=getUserPrepaymentInvoice',
                            dataType: 'json',
                            data: {
                                language: '{language}',
                                provider: $('#flb_core_provider').val(),
                                user: $('#flb_core_userid').val(),
                                sessid: $('#flb_core_sessionid').val(),
                            },
                            success: function(data) {
                              if (data.error) alert(data.message);
                              else {
                                $('#{prefix}flb_profile_credit_history_data').html('');
                                $.each(data, function(index,element) {
                                  invoice = '<a target=\"_blank\" href=\"'+$('#flb_core_url').val()+'action=vUserPrepaymentInvoice&id='+element.id+'&sessid='+$('#flb_core_sessionid').val()+'\"><img title=\"{__label.editUserCredit_historyInvoiceTitle}\" src=\"https://www.flexbook.cz/img/button_grid_download.png\"/></a>';
                                  $('#{prefix}flb_profile_credit_history_data').append('<div class=\"item\">'+
                                              '<div class=\"download\">'+invoice+'</div>'+
                                              '<div class=\"time\">'+element.timestamp+'</div>'+
                                              '<div class=\"type\">'+element.number+'</div>'+
                                              '<div class=\"amount\">'+element.amount+' '+element.currency+'</div>'+
                                              '</div>');
                                });
                              }
                            },
                            error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                          }); 
                        
                          $('#{prefix}flb_profile_credit_charge').hide();
                          $('#{prefix}flb_profile_credit_available_tickets').hide();
                          $('#{prefix}flb_profile_credit_history').show(); 
                       });
                       
                       $('#{prefix}flb_profile_credit').on('click','#{prefix}flb_profile_credit_ticketcharge_btn', function() {
                         $('#{prefix}flb_profile_credit_history').hide();
                         $('#{prefix}flb_profile_credit_charge').hide();
                         $('#{prefix}flb_profile_credit_available_tickets').show();
                       });
                       
                       $('#{prefix}flb_profile_credit').on('click','#{prefix}flb_profile_credit_charge_btn', function() {
                         $('#{prefix}flb_profile_credit_history').hide();
                         $('#{prefix}flb_profile_credit_available_tickets').hide();
                         $('#{prefix}flb_profile_credit_charge').show();
                       });
                       
                       $('#{prefix}flb_profile_credit').on('click','.{prefix}flb_profile_credit_charge_gw', function() {
                         if ($('#{prefix}flb_profile_credit_charge_amount').val()<=0) {
                           alert('{__error.ajax_provider_credit_chargeAmount_invalid}');
                         } else {
                           var params = { provider: $('#flb_core_provider').val(), amount: $('#{prefix}flb_profile_credit_charge_amount').val() };
                           var backAction = 'flbRefresh(\'#{prefix}flb_profile_credit\');';
                           flbPaymentGateway('{paymentUrl}',$(this).attr('gw'),
                             'CREDIT',$('#flb_core_userid').val(),JSON.stringify(params),
                              null,backAction);
                         }
                       });
                       
                       $('#{prefix}flb_profile_credit').on('click','.{prefix}flb_profile_credit_ticketcharge_gw', function() {
                         var params = { user: $('#flb_core_userid').val() };
                         var backAction = 'flbRefresh(\'#{prefix}flb_profile_credit\');';
                         flbPaymentGateway('{paymentUrl}',$(this).attr('gw'),
                           'TICKET',$('#{prefix}flb_profile_credit_available_ticket_id').val(),JSON.stringify(params),
                            null,backAction);
                       });
                     });
                   </script>";
  }

  private function _getCreditChargeButtons() {
    $buttons = '';
    foreach ($this->_paymentGateway as $gw) {
      $buttons .= sprintf('<input type="button" class="%sflb_profile_credit_charge_gw" gw="%s" value="%s %s" />',
        $this->_params['prefix'], $gw['name'],
        $this->_app->textStorage->getText('button.calendar_editReservation_payGW'),
        $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gw['name']));
    }

    $this->_guiParams['creditChargeButtons'] = $buttons;
  }
  
  private function _getTicketList() {
    $template = '';
    
    $b = new BUser($this->_app->auth->getUserId());
    $ticket = $b->getAvailableTicket($this->_params['provider'], true, null, null, 1);
    if (count($ticket)) {
      $template = sprintf('<div class="label flb_profile_credit_ticket_label"><span>%s:</span></div>',
                          $this->_app->textStorage->getText('label.ajax_profile_credit_ticket'));
      foreach ($ticket as $t) {
        $valid = '';
        if ($t['validFrom']) $valid = $this->_app->regionalSettings->convertDateTimeToHuman($t['validFrom']);
        if ($t['validTo']) {
          if ($valid) $valid .= sprintf(' - %s', $this->_app->regionalSettings->convertDateTimeToHuman($t['validTo']));
          else $valid = sprintf('%s %s', $this->_app->textStorage->getText('label.ajax_profile_credit_ticketValidTo'), $t['validTo']);
        } elseif ($valid) $valid = sprintf('%s %s', $this->_app->textStorage->getText('label.ajax_profile_credit_ticketValidFrom'), $valid);
        if ($valid) $valid = sprintf('(%s: %s)', $this->_app->textStorage->getText('label.ajax_profile_credit_ticketValid'), $valid);
        
        $template .= sprintf('<div class="flb_profile_credit_ticket"><div class="description"><span class="name">%s</span><span class="valid"> %s</span>:</div><div class="value flb_profile_credit_amount">%s %s</div></div>',
                             $t['name'], $valid, $t['value'], $t['currency']);
      }
    }
    
    $this->_guiParams['ticketList'] = $template;
  }

  private function _getAvailableTicketList() {
    $template = '';

    $select = new STicket;
    $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_params['provider'], '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['active'], "%s='Y'"));
    $select->setColumnsMask(array('ticket_id','name','price','validity_count','validity_unit'));
    $res = $this->_app->db->doQuery($select->toString());
    $ticket = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $validity = '';
      if ($row['validity_count']&&$row['validity_unit']) {
        $validity = sprintf(' (%s %d %s)', $this->_app->textStorage->getText('label.ajax_profile_credit_ticketValid'), $row['validity_count'],
          $this->_app->textStorage->getText('label.'.strtolower($row['validity_unit']).'_l'));
      }
      $text = sprintf('%s%s/ %s %s', $row['name'], $validity, $this->_app->regionalSettings->convertNumberToHuman($row['price'],2), $this->_app->textStorage->getText('label.currency_CZK'));

      $ticket[] = sprintf('<option value="%d">%s</option>', $row['ticket_id'], $text);
    }

    if (count($this->_paymentGateway)) {
      if (count($ticket)) {
        $template = sprintf('
          <div class="group">
            <label class="label flb_profile_credit_available_tickets_label"><span>%s:</span></label>
            <select id="%sflb_profile_credit_available_ticket_id">
          ',
          $this->_app->textStorage->getText('label.ajax_profile_credit_ticketChoose'), $this->_params['prefix']);

        foreach ($ticket as $t) {
          $template .= $t;
        }
        $template .= '</select>';

        $buttons = '';
        foreach ($this->_paymentGateway as $gw) {
          $buttons .= sprintf('<input type="button" class="%sflb_profile_credit_ticketcharge_gw" gw="%s" value="%s %s" />',
            $this->_params['prefix'], $gw['name'],
            $this->_app->textStorage->getText('button.calendar_editReservation_payGW'),
            $this->_app->textStorage->getText('label.ajax_paymentGateway_' . $gw['name']));
        }
        $template .= sprintf('<div class="flb_profile_credit_available_tickets_button">%s</div>', $buttons);

        $template .= '</div>';
        $this->_guiParams['ticketChargeButton'] = sprintf('<input type="button" id="%sflb_profile_credit_ticketcharge_btn" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_credit_ticketCharge'));
      } else {
        $this->_guiParams['ticketChargeButton'] = '';
      }
      $this->_guiParams['creditChargeButton'] = sprintf('<input type="button" id="%sflb_profile_credit_charge_btn" value="%s" />',
        $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_credit_creditCharge'));
    } else {
      $this->_guiParams['creditChargeButton'] = '';
      $this->_guiParams['ticketChargeButton'] = '';
    }

    $this->_guiParams['availableTicketList'] = $template;
  }

  private function _getPaymentGatewayInitUrl() {
    global $PAYMENT_GATEWAY;

    $ret = sprintf($PAYMENT_GATEWAY['initUrl'], ifsetor($this->_params['language'],'cz'), $this->_params['sessid'], $this->_params['provider']);
    #$ret .= '&jsbackaction='.urlencode(sprintf("flbRefresh('#%sflb_profile_credit');", $this->_params['prefix']));

    $this->_guiParams['paymentUrl'] = $ret;
  }
  
  protected function _getData() {
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->setColumnsMask(array('credit'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.ajax_profile_credit_invalidUser');
    $this->_guiParams['creditAmount'] = $this->_app->regionalSettings->convertNumberToHuman($row['credit'],2);
    $this->_guiParams['currency'] = $this->_app->textStorage->getText('label.currency_CZK');

    $o = new OProviderSettings(array('provider'=>$this->_params['provider']));
    $oData = $o->getData();
    if ($oData['generate_accounting']=='Y') $this->_guiParams['prepaymentInvoiceButton'] = sprintf('<input type="button" id="%sflb_profile_credit_prepaymentinvoice_btn" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_profile_credit_prepaymentinvoice'));
    else $this->_guiParams['prepaymentInvoiceButton'] = '';
    
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['backAction'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
    } else {
      $this->_guiParams['backAction'] = sprintf("flbLoadHtml('guiProfile', $('#%sflb_profile_credit').parent(), %s);", $this->_params['prefix'], $this->_guiParams['params']);
    }  
    
    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.close');
    } else {
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.back');
    }

    $this->_getPaymentGw();
    $this->_getCreditChargeButtons();
    $this->_getTicketList();
    $this->_getAvailableTicketList();
    $this->_getPaymentGatewayInitUrl();
  }
  
  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    parent::_userRun();
  }
}

?>
