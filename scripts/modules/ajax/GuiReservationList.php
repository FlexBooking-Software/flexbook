<?php

class AjaxGuiReservationList extends AjaxGuiAction2 {

  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_reservation_list', $this->_params['prefix']);
    $this->_class = 'flb_reservation_list';
  }

  protected function _initDefaultParams() {
    if (!isset($this->_params['buttons'])) $this->_params['buttons'] = array('reservationPrint');
    elseif (!is_array($this->_params['buttons'])&&!evaluateLogicalValue($this->_params['buttons'])) $this->_params['buttons'] = array();
    if (!isset($this->_params['format']['time'])) $this->_params['format']['time'] = 'H:i';
    if (!isset($this->_params['format']['date'])) $this->_params['format']['date'] = 'd.m. Y';
    if (!isset($this->_params['format']['datetime'])) $this->_params['format']['datetime'] = 'd.m. Y H:i';
  }

  protected function _createTemplate() {
    $this->_guiHtml = "
                    <div class=\"label flb_title\"><span>{__label.ajax_reservation_title}</span></div>
                    {pastCheckbox}
                    <div id=\"{prefix}flb_reservation_list_data\" style=\"display:none;\">
                      <table id=\"{prefix}flb_reservation_list_table\" class=\"flb_reservation_list_table\">
                        <tr>
                          <th class=\"flb_reservation_list_item_start_label\"><div>{__label.ajax_reservation_start}</div></th>
                          <th class=\"flb_reservation_list_item_number_label\"><div>{__label.ajax_reservation_number}</div></th>
                          <th class=\"flb_reservation_list_item_desc_label\"><div>{__label.ajax_reservation_desc}</div></th>
                          <th class=\"flb_reservation_list_item_price_label\"><div>{__label.ajax_reservation_price}</div></th>
                          <th>&nbsp;</th>
                        </tr>
                        <tbody id=\"{prefix}flb_reservation_list_data_body\"></tbody>
                      </table>
                      <div id=\"{prefix}flb_reservation_list_nodata\" class=\"nodata\">{__label.grid_noData}</div>
                    </div>
                    <div class=\"button\">
                      {backButton}
                      {printButton}
                    </div>
                    <script>
                     $(document).ready(function() { 
                       $('#{prefix}flb_reservation_list').on('click','.flb_reservation_list_item_button', function() {
                          flbLoadHtml('guiReservationDetail', $('#{prefix}flb_reservation_list').parent(), $.extend({params}, 
                            { reservationId: this.id, showPast: $('#{prefix}flb_reservation_list_past_check').is(':checked')?1:0 }));  
                       });
                       $('#{prefix}flb_reservation_list').on('click','.flb_reservation_list_item_substitute_button', function() {
                          flbLoadHtml('guiSubstituteDetail', $('#{prefix}flb_reservation_list').parent(), $.extend({params}, 
                            { substituteId: this.id, showPast: $('#{prefix}flb_reservation_list_past_check').is(':checked')?1:0 }));  
                       });
                       
                       $('#{prefix}flb_reservation_list').on('click','#{prefix}flb_reservation_list_back', function() {
                          $('#{prefix}flb_reservation_list_data').hide();
                          $('#{prefix}flb_reservation_list_print').hide();
                          {jsBackAction}
                       });
                       
                       $('#{prefix}flb_reservation_list').on('click','.flb_reservation_list_item_button_pay', function() {
                        resId = this.id.replace('{prefix}','');
                        flbLoadHtml('guiReservationDetail', $('#{prefix}flb_reservation_list').parent(), $.extend({params}, { reservationId: resId, pay: 1 }));
                       });
                       
                       $('#{prefix}flb_reservation_list').on('click','.flb_reservation_list_item_button_cancel', function() {
                        if (confirm('{__label.ajax_reservation_confirmCancel}')) {
                          var resId = this.id.replace('{prefix}','');
                          var data = { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: resId };
                          $.ajax({
                             type: 'POST',
                             dataType: 'json',
                             data: data,
                             url: $('#flb_core_url').val()+'action=cancelReservation',
                             success: function(data) {
                                 if (data.error) alert(data.message);
                                 else {
                                   flbLoadHtml('guiReservationList', $('#{prefix}flb_reservation_list').parent(), $.extend({params}, { prefix: '{prefix}'}));
                                   $('.flb_calendar').fullCalendar('refetchEvents');
                                 }
                             },
                             error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); }
                          });
                        }
                       });
                       $('#{prefix}flb_reservation_list').on('click','.flb_reservation_list_item_button_cancelRefund', function() {
                        if (confirm('{__label.ajax_reservation_confirmCancelRefund}')) {
                          var resId = this.id.replace('{prefix}','');
                          var data = { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: resId, refund: 'Y' };
                          $.ajax({
                             type: 'POST',
                             dataType: 'json',
                             data: data,
                             url: $('#flb_core_url').val()+'action=cancelReservation',
                             success: function(data) {
                                 if (data.error) alert(data.message);
                                 else {
                                   if (typeof flbRefresh == 'function') flbRefresh('.flb_output');
                                   $('.flb_calendar').fullCalendar('refetchEvents');
                                 }
                             },
                             error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); }
                          });
                        }
                       });
                       
                       $('#{prefix}flb_reservation_list').on('click','.flb_reservation_list_item_substitute_button_cancel', function() {
                        if (confirm('{__label.ajax_reservation_confirmSubstituteCancel}')) {
                          resId = this.id.replace('{prefix}','');
                          $.ajax({
                              type: 'POST',
                              dataType: 'json',
                              data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: resId },
                              url: $('#flb_core_url').val()+'action=cancelSubstitute',
                              success: function(data) {
                                  if (data.error) alert(data.message);
                                  else {
                                    if (typeof flbRefresh == 'function') flbRefresh('.flb_output');
                                    $('.flb_calendar').fullCalendar('refetchEvents');
                                  }
                              },
                              error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                          });
                        }
                       });
                       
                       $('#{prefix}flb_reservation_list').on('click','#{prefix}flb_reservation_list_print', function() {
                          w=window.open(null,'_blank');
                          w.document.write('<style>@media print {'+
                                  'div.button { display: none; }'+
                                  'td.flb_button { display: none; }'+
                                  'div#{prefix}flb_reservation_list_past { display: none; } }'+
                                  'table { border-collapse: collapse; }'+
                                  'th { text-align: left; }'+
                                  'tr { vertical-align: top; }'+
                                  'td { border-bottom: 1px solid black; padding-right: 10px; }'+
                                  '.user { font-size: 1.2em; font-weight: bold; }'+
                                  '.title { font-weight: bold; }'+
                                  '.flb_reservation_list_item_start { width: 90px; }'+
                                  '.flb_reservation_list_item_desc { width: 450px; }'+
                                  '.flb_reservation_list_item_price { text-align: right; }'+
                                  '</style>');
                          w.document.write('<div class=\'user\'>'+$('#flb_core_username').val()+'</div>');
                          w.document.write('<div class=\'title\'>{__label.ajax_reservation_title}</div><hr/>');
                          w.document.write($('#{prefix}flb_reservation_list').html());
                          w.print();
                          w.close();
                       });
                       
                       $('#{prefix}flb_reservation_list_past_check').on('click', function() { 
                         getReservations(); 
                       });
                       
                       function getReservations() {
                         var past = 'n';
                         var center = {center};
                         if ($('#{prefix}flb_reservation_list_past_check').is(':checked')) past = 'y';
                         $.ajax({
                            type: 'GET',
                            url: $('#flb_core_url').val()+'action=getReservation',
                            data: { provider: $('#flb_core_provider').val(), center: center, sessid: $('#flb_core_sessionid').val(), past: past, format: {format} },
                            dataType: 'json',
                            success: function(data) {
                              var html = '';
                              if (data.length) {
                                $('#{prefix}flb_reservation_list_table').show();
                                $('#{prefix}flb_reservation_list_print').show();
                                $('#{prefix}flb_reservation_list_nodata').hide();
                              } else {
                                $('#{prefix}flb_reservation_list_table').hide();
                                $('#{prefix}flb_reservation_list_print').hide();
                                $('#{prefix}flb_reservation_list_nodata').show();
                              }
                              $('#{prefix}flb_reservation_list_data').show();
                              
                              $.each(data, function(index,reservation) {
                                //alert(reservation.number);
                                
                                otherButton = '';
                                if (reservation.type=='reservation') {
                                  now = moment().format('YYYY-MM-DD HH:mm:ss');
                                  if (!reservation.cancelled&&!reservation.failed&&(reservation.startRaw>now)) {
                                    if (!reservation.payed&&reservation.readyToPay) otherButton = '&nbsp;<span class=\"flb_button flb_reservation_list_item_button_pay\" id=\"{prefix}'+reservation.id+'\">{__button.ajax_pay}</span>';
                                    if (reservation.mandatory!='Y') {
                                      if (!reservation.payed) otherButton += '&nbsp;<span class=\"flb_button flb_reservation_list_item_button_cancel\" id=\"{prefix}'+reservation.id+'\">{__button.ajax_cancel}</span>';
                                      else otherButton += '&nbsp;<span class=\"flb_button flb_reservation_list_item_button_cancelRefund\" id=\"{prefix}'+reservation.id+'\">{__button.ajax_cancel}</span>';
                                    }
                                  } 
                                }
                                if (reservation.type=='reservation') { 
                                  itemClass = ''; 
                                  if (reservation.cancelled) itemClass += ' flb_reservation_list_item_cancelled';
                                  buttonClass='flb_reservation_list_item_button'; 
                                } else { 
                                  itemClass=' flb_reservation_list_item_substitute'; 
                                  buttonClass='flb_reservation_list_item_substitute_button'; 
                                }
                                html += '<tr class=\"flb_reservation_list_item flb_list_item'+itemClass+'\">'+
                                          '<td class=\"flb_reservation_list_item_start\">'+reservation.start+'</td>'+
                                          '<td class=\"flb_reservation_list_item_number'+itemClass+'\">'+reservation.number+'</td>'+
                                          '<td class=\"flb_reservation_list_item_desc\">'+reservation.commodity+'</td>'+
                                          '<td class=\"flb_reservation_list_item_price\">'+reservation.totalPrice+'</td>'+
                                          '<td> <span class=\"flb_button '+buttonClass+'\" id=\"{prefix}'+reservation.id+'\">DETAIL</span>'+otherButton+'</td>'+
                                        '</tr>';
                              });
                              $('#{prefix}flb_reservation_list_data_body').html(html);
                            },
                            error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); }
                          });
                       }
                       
                       getReservations();
                     });
                   </script>";
  }

  protected function _getButtons() {
    if (in_array('reservationBack', $this->_params['buttons'])) {
      if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) $label = $this->_app->textStorage->getText('button.close');
      else $label = $this->_app->textStorage->getText('button.back');

      $this->_guiParams['backButton'] = sprintf('<input type="button" id="%sflb_reservation_list_back" value="%s" />', $this->_params['prefix'], $label);
    } else $this->_guiParams['backButton'] = '';

    if (in_array('reservationPrint', $this->_params['buttons'])) {
      $this->_guiParams['printButton'] = sprintf('<input type="button" id="%sflb_reservation_list_print" value="%s" style="display:none;" />', $this->_params['prefix'],
        $this->_app->textStorage->getText('button.ajax_reservation_print'));
    } else $this->_guiParams['printButton'] = '';
  }
      
  protected function _getData() {
    if (isset($this->_params['disablePast'])&&$this->_params['disablePast']) $this->_guiParams['pastCheckbox'] = '';
    else {
      $this->_guiParams['pastCheckbox'] = sprintf('<div class="flb_reservation_list_past" id="%sflb_reservation_list_past">%s&nbsp;<input id="%sflb_reservation_list_past_check" type="checkbox"%s/></div>',
        $this->_params['prefix'], $this->_app->textStorage->getText('label.inpage_reservationPast'), $this->_params['prefix'],
        isset($this->_params['showPast'])&&$this->_params['showPast']?' checked="yes"':'');
    }

    $this->_getButtons();

    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['jsBackAction'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
    } else {
      $this->_guiParams['jsBackAction'] = sprintf("flbLoadHtml('guiProfile', $('#%sflb_reservation_list').parent(), %s);", $this->_params['prefix'], $this->_guiParams['params']);
    }
    
    if (isset($this->_params['format'])) $this->_guiParams['format'] = json_encode($this->_params['format']);
    if (isset($this->_params['center'])) $this->_guiParams['center'] = json_encode($this->_params['center']);
    else $this->_guiParams['center'] = 'null';
  }

  protected function _modifyTemplate() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) {
      $this->_guiHtml = sprintf('<div id="%s" class="flb_output %s">%s</div>', $this->_id, $this->_class, $this->_app->textStorage->getText('label.ajax_reservation_notLogged'));
    }
  }
}

?>
