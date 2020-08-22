<?php

// pridat moznost zapnout cas ve filtru

class AjaxGuiResourceAvailability extends AjaxGuiAction2 {
  
  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_resource_availability', $this->_params['prefix']);
    $this->_class = 'flb_resource_availability';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['resourceListTemplate'])) {
      $this->_params['resourceListTemplate'] = '
          <div class="photo">@@RESOURCE_PHOTO</div>
          <div class="info">
            <div class="basic">
              <div class="center">@@CENTER_NAME</div>
              <div class="address">@@CENTER_ADDRESS</div>
              <div class="name">@@RESOURCE_NAME</div>
              <div class="description">@@RESOURCE_DESCRIPTION</div>
            </div>
            <div class="attribute flb_resource_attribute">
              @@ATTRIBUTE
            </div>
          </div>
          <div class="reservation">
            <div class="price">@@TOTAL_PRICE</div>
            <div class="button">@@RESERVATION</div>
          </div>';
    }

    if (isset($this->_params['resource'])) $this->_params['resource'] = str_replace($this->_params['prefix'],'',$this->_params['resource']);
    if (isset($this->_params['center'])&&!is_array($this->_params['center'])) $this->_params['center'] = array($this->_params['center']);
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = '<div class="flb_resource_availability_search"><div class="label" id="{prefix}flb_resource_availability_period_label"><span>{__label.ajax_resource_availability_period}:</span></div>
             <input type="text" id="{prefix}flb_resource_availability_period" class="flb_resource_availability_period" value="{period}" />';
    $this->_guiHtml .= '<input type="button" id="{prefix}flb_resource_availability_search" value="{__button.ajax_resource_availability_search}" /></div>';

    if (isset($this->_guiParams['resourceList'])) {
      $this->_guiHtml .= '<hr/><div class="flb_resource_availability_list">{resourceList}</div>';
    }

    $this->_guiHtml .= "<script>$(document).ready(function() {
          {specialJs}
  
          $('#{prefix}flb_resource_availability_period').daterangepicker({
            autoUpdateInput: false, minDate: moment(),
            locale: { format: 'DD.MM.YYYY', cancelLabel: 'Clear' }      
          });
          $('#{prefix}flb_resource_availability_period').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
            $('#{prefix}flb_resource_availability_search').click();  
          });
          $('#{prefix}flb_resource_availability_period').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
          });
  
          $('#{prefix}flb_resource_availability').on('click','#{prefix}flb_resource_availability_search', function() {
            var params = {params};
            $.extend(params, {'period':$('#{prefix}flb_resource_availability_period').val()});
            flbLoadHtml('guiResourceAvailability', $(this).closest('.flb_output').parent(), params);            
          });
          
          $('#{prefix}flb_resource_availability').on('click','.flb_resource_availability_reservation_notlogged', function() {
            flbLoginRequired('{language}');
          });
           
          $('#{prefix}flb_resource_availability').on('click','.flb_resource_availability_reservation_prepare', function() {
            var id = $(this).attr('id').replace('{prefix}flb_resource_availability_reservation_prepare_','');
            getReservationAttribute(id);
            
            $(this).hide();
            $('#{prefix}flb_resource_availability_reservation_'+id).show();
          });
          
          $('#{prefix}flb_resource_availability').on('click','#{prefix}flb_resource_availability_button_reserve', function() {
            var id = $(this).parent().attr('id').replace('{prefix}flb_resource_availability_button_','');
            saveReservation(id, null);
          });
          
          $('#{prefix}flb_resource_availability').on('click','#{prefix}flb_resource_availability_button_reserve_pay_credit', function() {
            if (confirm('{__label.ajax_event_confirmPayment}')) {
              var id = $(this).parent().attr('id').replace('{prefix}flb_resource_availability_button_','');
              saveReservation(id, {pay:'Y',payType:'credit'});
            }
          });
          
          $('#{prefix}flb_resource_availability').on('click','#{prefix}flb_resource_availability_button_reserve_pay_ticket', function() {
            if (!$('#{prefix}flb_resource_availability_pay_ticket').val()) {
              alert('{__label.ajax_event_noTicket}');
              return;
            }
            if (confirm('{__label.ajax_event_confirmPayment}')) {
              var id = $(this).parent().attr('id').replace('{prefix}flb_resource_availability_button_','');
              saveReservation(id, {pay:'Y',payType:'ticket',payTicket:$('#{prefix}flb_resource_availability_pay_ticket').val()});
            }
          });
          
          $('#{prefix}flb_resource_availability').on('click','.{prefix}flb_resource_availability_button_reserve_pay_gw', function() {
            var id = $(this).parent().attr('id').replace('{prefix}flb_resource_availability_button_','');
            if (id=saveReservation(id, {paymentOnline:$(this).attr('gw')})) {
              flbPaymentGateway('{paymentUrl}',$(this).attr('gw'),'RESERVATION',id,null,null,null);
            }
          });
          
          function getReservationAttribute(id) {
            $.ajax({
              type: 'GET',
              url: $('#flb_core_url').val()+'action=guiReservationAttribute&sessid='+$('#flb_core_sessionid').val()+'&prefix={prefix}',
              dataType: 'json',
              data: { resourceId: id },
              success: function(data) {
                $('#{prefix}flb_resource_availability_reservation_attribute_'+id).html(data.output);
              },
              error: function(error) { alert('{__label.ajaxError}'); }
            });
           }
           
           function saveReservation(resourceId, params) {
             if (!params) params = {};
             var ret = false;
             
             var attr = {};
             $('#{prefix}flb_resource_availability_reservation_attribute_' + resourceId + ' [meaning=reservation_attribute]').each(function () {
                var idExt = $(this).attr('id');
                idExt = idExt.replace('{prefix}attr_','');
                attr[idExt] = $(this).val();
             });
             
             if ($('#flb_core_userid').val()) {
                var data = { provider: $('#flb_core_provider').val(),
                            sessid: $('#flb_core_sessionid').val(), 
                            resource: resourceId,
                            user: $('#flb_core_userid').val(),
                            start: $('#{prefix}flb_resource_availability_reservation_date_from').val()+' '+$('#{prefix}flb_resource_availability_reservation_from_'+resourceId).val()+':00',
                            end: $('#{prefix}flb_resource_availability_reservation_date_to').val()+' '+$('#{prefix}flb_resource_availability_reservation_to_'+resourceId).val()+':00',
                            attribute: attr
                          };
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
                            flbRefresh('#{prefix}flb_resource_availability');
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
      });</script>";
  }

  private function _getAttributeGui($resourceId) {
    $ret = '';

    $b = new BResource($resourceId);
    $attributes = $b->getAttribute();

    $category = '';
    foreach ($attributes as $id=>$attribute) {
      if (isset($this->_params['showAttribute'])&&$this->_params['showAttribute']) {
        if (!in_array($attribute['category'], $this->_params['showAttribute'])) continue;
      }
      // atributy jsou uzavreny do DIVu kategorie
      if (strcmp($category,$attribute['category'])) {
        if ($category) $ret .= '</div>';
        $ret .= sprintf('<div class="flb_resource_attributecategory_name">%s</div><div class="flb_resource_attributecategory" id="flb_resource_attributecategory_%s">', $attribute['category'], htmlize($attribute['category']));
      }
      switch ($attribute['type']) {
        case 'NUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value']); break;
        case 'DECIMALNUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2); break;
        case 'TIME':
          $value = $this->_app->regionalSettings->convertTimeToHuman($attribute['value'],'h:m');
          if (isset($this->_params['format']['time'])) $value = date($this->_params['format']['time'], strtotime($value));
          break;
        case 'DATETIME':
          $value = $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']);
          if (isset($this->_params['format']['datetime'])) $value = date($this->_params['format']['datetime'], strtotime($value));
          break;
        case 'DATE':
          $value = $this->_app->regionalSettings->convertDateToHuman($attribute['value']);
          if (isset($this->_params['format']['date'])) $value = date($this->_params['format']['date'], strtotime($value));
          break;
        case 'FILE':
          global $AJAX;
          $value = sprintf('<a target="_attributeFile" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), $attribute['valueId'], $attribute['value']);
          break;
        default: $value = $attribute['value'];
      }

      $attrHtml = sprintf('<div id="flb_resource_attribute_%s" class="flb_resource_attribute"><div class="label">%s:</div><div class="value flb_resource_attributevalue">%s</div></div>', $id, $attribute['name'], $value);

      $ret .= $attrHtml;

      $category = $attribute['category'];
    }
    if ($category) $ret .= '</div>';

    if (!$ret) $ret = '&nbsp;';

    return $ret;
  }

  private function _getReservationGui($lineData) {
    if ($this->_app->auth->getUserId()) {
      $html = sprintf('<input type="button" id="%sflb_resource_availability_reservation_prepare_%s" class="flb_resource_availability_reservation_prepare flb_primaryButton" name="reserve" value="%s"/>',
        $this->_params['prefix'], $lineData['id'], $this->_app->textStorage->getText('button.ajax_resource_availability_reserve'));
    } else {
      $html = sprintf('<input type="button" class="flb_resource_availability_reservation_notlogged" name="reserve" value="%s"/>', $this->_app->textStorage->getText('button.ajax_resource_availability_reserve'));
    }
    $html .= sprintf('<div style="display:none;" class="flb_resource_availability_reservation" id="%sflb_resource_availability_reservation_%s">', $this->_params['prefix'], $lineData['id']);
    $buttons = '';

    if ($user=$this->_app->auth->getUserId()) {
      list($from,$to) = explode(' - ', $this->_params['period']);

      $html .= sprintf('<div class="label flb_resource_availability_reservation_from_label"><span>%s:</span></div>
        <div class="value flb_resource_availability_reservation_from">%s <input type="text" class="flb_time" id="%sflb_resource_availability_reservation_from_%s" value="%s"></div>',
        $this->_app->textStorage->getText('label.ajax_resource_availability_reservation_from'), $from, $this->_params['prefix'], $lineData['id'], $lineData['from_time_start']);
      $html .= sprintf('<div class="label flb_resource_availability_reservation_to_label"><span>%s:</span></div>
        <div class="value flb_resource_availability_reservation_to">%s <input type="text" class="flb_time" id="%sflb_resource_availability_reservation_to_%s" value="%s"></div>',
        $this->_app->textStorage->getText('label.ajax_resource_availability_reservation_to'), $to, $this->_params['prefix'], $lineData['id'], $lineData['to_time_end']);

      $this->_guiParams['specialJs'] .= sprintf("$('#%sflb_resource_availability_reservation_from_%s').datetimepicker({
            format:'H:i', datepicker:false, step: 60,
            minTime: '%s', maxTime: '%s1'
          });", $this->_params['prefix'], $lineData['id'], $lineData['from_time_start'], substr($lineData['from_time_end'],0,4));
      $this->_guiParams['specialJs'] .= sprintf("$('#%sflb_resource_availability_reservation_to_%s').datetimepicker({
            format:'H:i', datepicker:false, step: 60,
            minTime: '%s', maxTime: '%s1'
          });", $this->_params['prefix'], $lineData['id'], $lineData['to_time_start'], substr($lineData['to_time_end'],0,4));

      // inputy pro dodatecne atributy rezervace
      $html .= sprintf('<div id="%sflb_resource_availability_reservation_attribute_%s" class="flb_reserve_attribute"></div>', $this->_params['prefix'], $lineData['id']);

      $buttons .= sprintf('<input type="button" id="%sflb_resource_availability_button_reserve" class="flb_primaryButton" value="%s" />', $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_resource_availability_reserve'));
      $availablePayment = array();
      if ($lineData['totalPrice']) {
        $providerSettings = $settings = BCustomer::getProviderSettings($this->_params['provider'],array('disableCredit','disableTicket','disableOnline'));

        // zjistim jestli muze platit z kreditu
        if ($providerSettings['disableCredit']=='N') {
          $s1 = new SUser;
          $s1->addStatement(new SqlStatementBi($s1->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
          $s1->addStatement(new SqlStatementBi($s1->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
          $s1->setColumnsMask(array('registration_credit'));
          $res1 = $this->_app->db->doQuery($s1->toString());
          if (($row1 = $this->_app->db->fetchAssoc($res1)) && ($row1['registration_credit'] >= $lineData['totalPrice'])) $availablePayment[] = 'credit';
        }

        if ($providerSettings['disableTicket']=='N') {
          if ($lineData['all_tag']) $tag = explode(',', $lineData['all_tag']);
          else $tag = array();
          $bUser = new BUser($user);
          $ticket = $bUser->getAvailableTicket($this->_params['provider'], true, $lineData['center_id'], $tag, $lineData['totalPrice']);
          if (count($ticket)) $availablePayment[] = 'ticket';
        }

        // zjistim jestli muze platit platebni branou
        if ($providerSettings['disableOnline']=='N') {
          $gateway = BCustomer::getProviderPaymentGateway($this->_params['provider'], $lineData['totalPrice']);
          if (count($gateway)) $availablePayment[] = 'gateway';
        }
      }

      if (in_array('credit',$availablePayment)) {
        $buttons .= sprintf('<input type="button" id="%sflb_resource_availability_button_reserve_pay_credit" class="flb_primaryButton" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_resource_availability_reservePayCredit'));
      }
      if (in_array('ticket',$availablePayment)) {
        $buttons .= sprintf('<input type="button" id="%sflb_resource_availability_button_reserve_pay_ticket" class="flb_primaryButton" value="%s" />',
          $this->_params['prefix'], $this->_app->textStorage->getText('button.ajax_resource_availability_reservePayTicket'));
      }
      if (in_array('gateway',$availablePayment)) {
        foreach ($gateway as $gw) {
          $buttons .= sprintf('<input type="button" class="%sflb_resource_availability_button_reserve_pay_gw flb_primaryButton" gw="%s" value="%s %s" />',
            $this->_params['prefix'], $gw['name'],
            $this->_app->textStorage->getText('button.ajax_resource_availability_reservePayGW'),
            $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gw['name']));
        }
      }

      if (in_array('ticket',$availablePayment)) {
        $ticketSelect = sprintf('<select id="%sflb_resource_availability_pay_ticket"><option value="">%s</option>',
          $this->_params['prefix'], $this->_app->textStorage->getText('label.select_choose'));
        foreach ($ticket as $t) { $ticketSelect .= sprintf('<option value="%s">%s / %s %s</option>', $t['id'], $t['name'], $t['value'], $t['currency']); }
        $ticketSelect .= '</select>';

        $html .= sprintf('<div class="label"><div class="label flb_event_ticket_label"><span>%s:</span></div>
                           %s
                          </div>',
          $this->_app->textStorage->getText('label.ajax_resource_availability_ticket'),
          $ticketSelect);
      }
    }

    $html .= sprintf('<div class="button" id="%sflb_resource_availability_button_%s">%s</div>', $this->_params['prefix'], $lineData['id'], $buttons);
    $html .= '</div>';

    return $html;
  }

  private function _parseResourceListLine($lineData) {
    foreach ($lineData as $key=>$value) $data['@@'.strtoupper($key)] = $value;

    if (strpos($this->_params['resourceListTemplate'],'RESOURCE_ATTRIBUTE')!==false) {
      // nejdriv vsechny atributy poskytovatele "vynuluju"
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute_id','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@RESOURCE_ATTRIBUTE('.$row['short_name'].')'] = ''; }
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $lineData['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute','short_name','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@RESOURCE_ATTRIBUTE('.$row['short_name'].')'] = $row['value']; }
    }

    $b = new BResource($lineData['id']);
    $lineData['totalPrice'] = round($b->getPrice($lineData['from_datetime'], $lineData['to_datetime']),2);
    $data['@@TOTAL_PRICE'] = $lineData['totalPrice'];
    $data['@@TOTAL_PRICE'] .= ' '.$this->_app->textStorage->getText('label.currency_CZK');

    if (!$lineData['url_photo']) {
      $data['@@RESOURCE_PHOTO'] = sprintf('<img class="noPhoto" src="%simg/no_photo.png"/>', $this->_app->getBaseDir());
    } else {
      $data['@@RESOURCE_PHOTO'] = sprintf('<div id="%s_fotorama" class="fotorama" data-auto="false" data-nav="thumbs" data-allowfullscreen="true">', $lineData['id']);
      foreach (explode(',',$lineData['url_photo']) as $photo) {
        if ($photo) {
          $data['@@RESOURCE_PHOTO'] .= sprintf('<img src="%s"/>', $photo);
        }
      }
      $data['@@RESOURCE_PHOTO'] .= '</div>';
      $this->_guiParams['specialJs'] .= sprintf("$('#%s_fotorama').fotorama();", $lineData['id']);
    }
    $data['@@ATTRIBUTE'] = $this->_getAttributeGui($lineData['id']);
    $data['@@RESERVATION'] = $this->_getReservationGui($lineData);

    return str_replace(array_keys($data), $data, $this->_params['resourceListTemplate']);
  }

  private function _getResourceList() {
    // kdyz je definovano obdobi, kde se maji hledat zdroje
    if (isset($this->_params['period'])&&$this->_params['period']) {
      list($from,$to) = explode(' - ', $this->_params['period']);
      $from = $this->_app->regionalSettings->convertHumanToDate($from);
      $to = $this->_app->regionalSettings->convertHumanToDate($to);
      if ($from>$to) throw new ExceptionUserTextStorage('error.ajax_resource_availability_invalidPeriod');

      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (isset($this->_params['center'])&&$this->_params['center']) $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf("%%s IN (%s)", $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      if (isset($this->_params['resourceId'])&&$this->_params['resourceId']) $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$this->_params['resourceId'])))));
      if (isset($this->_params['resourcePoolId'])&&$this->_params['resourcePoolId']) $s->addStatement(new SqlStatementMono($s->columns['resourcepool'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$this->_params['resourcePoolId'])))));
      if (isset($this->_params['tag'])&&$this->_params['tag']) {
        $tag = $this->_params['tag'];
        foreach ($tag as $key=>$value) {
          $tag[$key] = sprintf("'%s'", $this->_app->db->escapeString($value));
        }
        $s->addStatement(new SqlStatementMono($s->columns['tag_name'], sprintf("%%s IN (%s)", implode(',',$tag))));
      }

      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
      $s->setColumnsMask(array('resource_id','name','description','center','center_name','full_address','url_photo','all_tag',
        'time_alignment_from','time_alignment_to','time_end_from','time_end_to'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$this->_app->db->getRowsNumber($res)) {
        $this->_guiParams['resourceList'] = sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData'));
      } else {
        $html = '';
        while ($row = $this->_app->db->fetchAssoc($res)) {
          // jeste musim zjistit, jestli je zdroj available
          $s1 = new SResourceAvailability;
          $s1->addStatement(new SqlStatementBi($s1->columns['resource'], $row['resource_id'], '%s=%s'));
          $s1->addStatement(new SqlStatementTri($from.' '.$row['time_alignment_from'], $s1->columns['start'], $s1->columns['end'], '%s BETWEEN %s AND %s'));
          $s1->addStatement(new SqlStatementTri($to.' '.$row['time_end_to'], $s1->columns['start'], $s1->columns['end'], '%s BETWEEN %s AND %s'));
          $s1->setColumnsMask(array('resourceavailability_id'));
          $res1 = $this->_app->db->doQuery($s1->toString());
          if ($this->_app->db->getRowsNumber($res1)!=1) continue;

          $data = array(
            'id'                      => $row['resource_id'],
            'resource_name'           => $row['name'],
            'resource_description'    => $row['description'],
            'center_id'               => $row['center'],
            'center_name'             => $row['center_name'],
            'center_address'          => $row['full_address'],
            'url_photo'               => $row['url_photo'],
            'all_tag'                 => $row['all_tag'],
            'from_datetime'           => $from.' '.ifsetor($row['time_alignment_from'],'00:00:00'),
            'to_datetime'             => $to.' '.ifsetor($row['time_end_to'],'24:00:00'),
            'from_time_start'         => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($row['time_alignment_from'],'00:00:00'),'h:m'),
            'from_time_end'           => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($row['time_alignment_to'],'24:00:00'),'h:m'),
            'to_time_start'           => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($row['time_end_from'],'00:00:00'),'h:m'),
            'to_time_end'             => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($row['time_end_to'],'24:00:00'),'h:m'),
          );

          $g = new GuiElement(array('template'=>$this->_parseResourceListLine($data)));
          $html .= sprintf('<div class="flb_resource_availability_item flb_list_item" id="%s%s">%s</div>',
            $this->_params['prefix'],
            $row['resource_id'],
            $g->render()
          );
        }

        $this->_guiParams['resourceList'] = sprintf('
          <div class="flb_title" id="%sflb_resource_availability_list_title"><span>%s</span></div>
          <div class="flb_list flb_resource_availability_list" id="%sflb_resource_availability_list">
            <input type="hidden" id="%sflb_resource_availability_reservation_date_from" value="%s"/>
            <input type="hidden" id="%sflb_resource_availability_reservation_date_to" value="%s"/>
            %s
          </div>',
          $this->_params['prefix'], $this->_app->textStorage->getText('label.ajax_resource_availability_list_title'),
          $this->_params['prefix'],
          $this->_params['prefix'], $from, $this->_params['prefix'], $to,
          $html);
      }
    }
  }

  protected function _getData() {
    $this->_guiParams['period'] = ifsetor($this->_params['period']);
    $this->_guiParams['resource'] = ifsetor($this->_params['resource'],'null');
    $this->_guiParams['specialJs'] = '';

    global $PAYMENT_GATEWAY;
    $this->_guiParams['paymentUrl'] = sprintf($PAYMENT_GATEWAY['initUrl'], ifsetor($this->_params['language'],'cz'), $this->_params['sessid'], $this->_params['provider']);

    $this->_getResourceList();
  }
}

?>
