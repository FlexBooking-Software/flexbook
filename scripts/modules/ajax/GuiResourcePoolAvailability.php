<?php

class AjaxGuiResourcePoolAvailability extends AjaxGuiAction2 {
  protected $_filter;
  
  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_resource_availability', $this->_params['prefix']);
    $this->_class = 'flb_resource_availability';

    if (isset($this->_params['filter'])) $this->_filter = $this->_params['filter'];
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['resourcePoolListTemplate'])) {
      $this->_params['resourcePoolListTemplate'] = '
          <div class="photo">@@RESOURCEPOOL_PHOTO</div>
          <div class="info">
            <div class="basic">
              <div class="center">@@CENTER_NAME</div>
              <div class="address">@@CENTER_ADDRESS</div>
              <div class="name">@@RESOURCEPOOL_NAME</div>
              <div class="description">@@RESOURCEPOOL_DESCRIPTION</div>
            </div>
          </div>
          <div class="reservation">
            <div class="price">@@TOTAL_PRICE</div>
            <br/><div class="availableCount">{__label.ajax_resource_availability_reservation_availableCount} @@RESOURCE_COUNT x</div>
            <div class="button">@@RESERVATION</div>
          </div>';
    }

    if (isset($this->_params['resourcepool'])) $this->_params['resourcepool'] = str_replace($this->_params['prefix'],'',$this->_params['resourcepool']);
    if (isset($this->_params['center'])&&!is_array($this->_params['center'])) $this->_params['center'] = array($this->_params['center']);
    if (!isset($this->_params['withTime'])) $this->_params['withTime'] = false;
  }

  protected function _evaluateFilter() {
    foreach ($this->_filter as $index=>$item) {
      if (!$actualValue = ifsetor($this->_params['filterValue'][$item['id']])) {
        if (!isset($item['firstItem'])) {
          $actualValue = ifsetor($item['items'][0]['tag']);
        }
      }

      $this->_filter[$index]['value'] = $actualValue;
    }
  }

  protected function _createFilterItem($itemSettings) {
    $ret = '';

    if (isset($itemSettings['label'])&&$itemSettings['label']) {
      $ret .= sprintf('<div class="label"><span>%s:</span></div>', $itemSettings['label']);
    }
    if (!($active = !isset($itemSettings['dependOn']))) {
      $depend = sprintf(' data-dependon="%s" data-dependvalue="%s"', $itemSettings['dependOn'], $itemSettings['dependValue']);
      foreach ($this->_filter as $filterItem) {
        if ($filterItem['id']==$itemSettings['dependOn']) {
          $active = $filterItem['value'] == $itemSettings['dependValue'];
          break;
        }
      }
    }
    switch ($itemSettings['type']) {
      case 'checkbox':
        $checked = $itemSettings['value']==$itemSettings['tag']?'checked="yes"':'';
        $ret .= sprintf('<input type="checkbox" %s data-active="%s" data-id="%s"%s class="flb_resource_availability_filter_item" value="%s"/>',
          $active?'1':'0', $checked, $itemSettings['id'],
          isset($depend)?$depend:'', $itemSettings['tag']);
        break;
      case 'select':
        $items = '';
        if (isset($itemSettings['firstItem'])&&$itemSettings['firstItem']) $items = sprintf('<option value="">%s</option>', $itemSettings['firstItem']);
        foreach ($itemSettings['items'] as $selectItem) {
          $selected = $itemSettings['value']==$selectItem['tag']?'selected="yes"':'';
          $items .= sprintf('<option value="%s" %s>%s</option>', $selectItem['tag'], $selected, $selectItem['label']);
        }
        $ret .= sprintf('<select data-active="%s" data-id="%s"%s class="flb_resource_availability_filter_item">%s</select>',
          $active?'1':'0', $itemSettings['id'], isset($depend)?$depend:'', $items);
        break;
      default: return '';
    }

    return sprintf('<div class="filterItem%s">%s</div>', $active?'':' inactiveFilterItem', $ret);
  }

  protected function _createFilter() {
    $ret = '<div class="flb_resource_availability_filter">';
    $ret .= sprintf('<div class="filterItem filterRange">
              <div class="label" id="{prefix}flb_resource_availability_period_label"><span>{__label.ajax_resource_availability_period}:</span></div>
              <input type="text" id="{prefix}flb_resource_availability_period" class="flb_resource_availability_period%s" value="{period}" />
             </div>', $this->_params['withTime']?'_withtime':'');

    if ($this->_filter) {
      $this->_evaluateFilter();

      foreach ($this->_filter as $item) {
        $ret .= $this->_createFilterItem($item);
      }
    }

    $ret .= '<input type="button" class="filterButton" id="{prefix}flb_resource_availability_search" value="{__button.ajax_resource_availability_search}" /></div>';

    return $ret;
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = $this->_createFilter();

    if (isset($this->_guiParams['resourcePoolList'])) {
      $this->_guiHtml .= '<hr/><div class="flb_resource_availability_list">{resourcePoolList}</div>';
    }

    if ($this->_params['withTime']) {
      $this->_guiParams['timePicker'] = 'timePicker: true, ';
      $this->_guiParams['timeFormat'] = ' HH:mm';
    } else {
      $this->_guiParams['timePicker'] = $this->_guiParams['timeFormat'] = '';
    }

    $this->_guiHtml .= "<script>$(document).ready(function() {
          {specialJs}
  
          $('#{prefix}flb_resource_availability_period').daterangepicker({
            autoUpdateInput: false, minDate: moment(), timePicker24Hour: true, {timePicker} 
            locale: { format: 'DD.MM.YYYY{timeFormat}', cancelLabel: 'Clear' }      
          });
          $('#{prefix}flb_resource_availability_period').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD.MM.YYYY{timeFormat}') + ' - ' + picker.endDate.format('DD.MM.YYYY{timeFormat}'));
            if ({noFilter}) $('#{prefix}flb_resource_availability_search').click();  
          });
          $('#{prefix}flb_resource_availability_period').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
          });
  
          $('#{prefix}flb_resource_availability').on('click','#{prefix}flb_resource_availability_search', function() {
            var params = {params};
            var jsParams = {'period':$('#{prefix}flb_resource_availability_period').val(), filterValue: {} };
            
            $('#{prefix}flb_resource_availability .flb_resource_availability_filter_item').each(function() {
              var active = $(this).attr('data-active');
              if (active=='1') {
                var id = $(this).attr('data-id');
                var val = $(this).val();
                if ($(this).is(':checkbox')&&!$(this).is(':checked')) val = null;
                if (val) {
                  jsParams.filterValue[id] = val;
                }
              }
            });
            
            $.extend(params, jsParams);
            flbLoadHtml('guiResourcePoolAvailability', $(this).closest('.flb_output').parent(), params);            
          });
          
          $('#{prefix}flb_resource_availability').on('click','.flb_resource_availability_reservation_notlogged', function() {
            flbLoginRequired('{language}');
          });
          
          $('#{prefix}flb_resource_availability').on('change','.flb_resource_availability_filter_item', function() {
            refreshFilter();
          });
           
          $('#{prefix}flb_resource_availability').on('click','.flb_resource_availability_reservation_prepare', function() {
            var id = $(this).attr('id').replace('{prefix}flb_resource_availability_reservation_prepare_','');
            getReservationAttribute(id);
            
            if (getResourcePoolResource(id)) {            
              $(this).hide();
              $('#{prefix}flb_resource_availability_reservation_'+id).show();
            } else {
              alert('{__label.ajax_resource_availability_poolNotAvailable}');                
            }
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
          
          function refreshFilter() {
            var filterValues = {};
            $('#{prefix}flb_resource_availability .flb_resource_availability_filter_item').each(function() {
              var active = $(this).attr('data-active');
              if (active=='1') {
                filterValues[$(this).attr('data-id')] = $(this).val();
              } else {
                filterValues[$(this).attr('data-id')] = null;
              }
            });
            
            $('#{prefix}flb_resource_availability .flb_resource_availability_filter_item').each(function() {
              var dependOn = $(this).attr('data-dependon');
              if (dependOn) {
                var dependValue = $(this).attr('data-dependvalue');
                if (filterValues[dependOn] == dependValue) {
                  $(this).attr('data-active', '1');
                  $(this).parent().removeClass('inactiveFilterItem');
                } else {
                  $(this).attr('data-active', '0');
                  $(this).parent().addClass('inactiveFilterItem');
                }
              }
            });
          }
          
          function getResourcePoolResource(resourcePool) {
            //console.log('===== reservationPoolGetResource =====');
            var ret = false;
          
            $.ajax({
              type: 'GET',
              url: $('#flb_core_url').val()+'action=getResourcePoolResource',
              dataType: 'json',
              async: false,
              data: {
                  resourcePoolId : resourcePool,
                  from: $('#{prefix}flb_resource_availability_reservation_date_from').val()+' '+$('#{prefix}flb_resource_availability_reservation_from_'+resourcePool).val()+':00',
                  to: $('#{prefix}flb_resource_availability_reservation_date_to').val()+' '+$('#{prefix}flb_resource_availability_reservation_to_'+resourcePool).val()+':00'
                },
              success: function(data) {
                if (data.id) {
                  $('#{prefix}flb_resource_availability_resource_'+resourcePool).val(data.id);
                  $('#{prefix}flb_resource_availability_reservation_count_'+resourcePool).empty();
                  for (var i=1;i<=data.id.length;i++) {
                    $('#{prefix}flb_resource_availability_reservation_count_'+resourcePool).append($('<option></option>').attr('value', i).text(i));
                  }
                  
                  ret = true;
                }
              },
              error: function(error) {
                alert('{__label.ajaxError}');
              }
            });
          
            //console.log('Returning resources: '+ret);
            //console.log('=====================================');
            
            return ret;
          }
          
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
           
           function saveReservation(resourcePoolId, params) {
             if (!params) params = {};
             var ret = false;
             
             var attr = {};
             $('#{prefix}flb_resource_availability_reservation_attribute_' + resourcePoolId + ' [meaning=reservation_attribute]').each(function () {
                var idExt = $(this).attr('id');
                idExt = idExt.replace('{prefix}attr_','');
                attr[idExt] = $(this).val();
             });
             
             if ($('#flb_core_userid').val()) {
                var data = { provider: $('#flb_core_provider').val(),
                            sessid: $('#flb_core_sessionid').val(), 
                            resource: '',
                            user: $('#flb_core_userid').val(),
                            start: $('#{prefix}flb_resource_availability_reservation_date_from').val()+' '+$('#{prefix}flb_resource_availability_reservation_from_'+resourcePoolId).val()+':00',
                            end: $('#{prefix}flb_resource_availability_reservation_date_to').val()+' '+$('#{prefix}flb_resource_availability_reservation_to_'+resourcePoolId).val()+':00',
                            attribute: attr
                          };
                var allowedResource = $('#{prefix}flb_resource_availability_resource_'+resourcePoolId).val().split(',');
                var count = $('#{prefix}flb_resource_availability_reservation_count_'+resourcePoolId).val();
                for (var i=0;i<count;i++) {
                  if (data.resource) data.resource += ',';
                  data.resource += allowedResource[i];
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
      list($fromDate,$fromTime) = explode(' ', $from);
      list($toDate,$toTime) = explode(' ', $to);

      $html .= sprintf('<div class="label flb_resource_availability_reservation_from_label"><span>%s:</span></div>
        <div class="value flb_resource_availability_reservation_from">%s <input type="text" class="flb_time" id="%sflb_resource_availability_reservation_from_%s" value="%s"></div>',
        $this->_app->textStorage->getText('label.ajax_resource_availability_reservation_from'), $fromDate, $this->_params['prefix'], $lineData['id'], $fromTime);
      $html .= sprintf('<div class="label flb_resource_availability_reservation_to_label"><span>%s:</span></div>
        <div class="value flb_resource_availability_reservation_to">%s <input type="text" class="flb_time" id="%sflb_resource_availability_reservation_to_%s" value="%s"></div>',
        $this->_app->textStorage->getText('label.ajax_resource_availability_reservation_to'), $toDate, $this->_params['prefix'], $lineData['id'], $toTime);
      $html .= sprintf('<div class="label flb_resource_availability_reservation_count_label"><span>%s:</span></div>
        <div class="value flb_resource_availability_reservation_count"><select id="%sflb_resource_availability_reservation_count_%s"></select></div>',
        $this->_app->textStorage->getText('label.ajax_resource_availability_reservation_count'), $this->_params['prefix'], $lineData['id']);
      $html .= sprintf('<input type="hidden" id="%sflb_resource_availability_resource_%s" value=""/>', $this->_params['prefix'], $lineData['id']);

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

  private function _parseResourcePoolListLine($lineData) {
    foreach ($lineData as $key=>$value) $data['@@'.strtoupper($key)] = $value;

    $b = new BResource(current($lineData['resources']));
    $lineData['totalPrice'] = round($b->getPrice($lineData['from_datetime'], $lineData['to_datetime']),2);
    $data['@@TOTAL_PRICE'] = $lineData['totalPrice'];
    $data['@@TOTAL_PRICE'] .= ' '.$this->_app->textStorage->getText('label.currency_CZK');
    $data['@@RESOURCE_COUNT'] = count($lineData['resources']);
    unset($data['@@RESOURCES']);

    $data['@@RESOURCEPOOL_PHOTO'] = $this->_getPhotoThumb($lineData['id'], $lineData['url_photo']);

    $data['@@RESERVATION'] = $this->_getReservationGui($lineData);

    return str_replace(array_keys($data), $data, $this->_params['resourcePoolListTemplate']);
  }

  private function _getResourcePoolList() {
    // kdyz je definovano obdobi, kde se maji hledat zdroje
    if (isset($this->_params['period'])&&$this->_params['period']) {
      list($from,$to) = explode(' - ', $this->_params['period']);
      $fromTime = $toTime = null;
      if ($this->_app->regionalSettings->checkHumanDate($from)) {
        $from = $this->_app->regionalSettings->convertHumanToDate($from);
      } elseif ($this->_app->regionalSettings->checkHumanDateTime($from)) {
        $from = $this->_app->regionalSettings->convertHumanToDateTime($from);
        list($from,$fromTime) = explode(' ', $from);
      }
      if ($this->_app->regionalSettings->checkHumanDate($to)) {
        $to = $this->_app->regionalSettings->convertHumanToDate($to);
      } elseif ($this->_app->regionalSettings->checkHumanDateTime($to)) {
        $to = $this->_app->regionalSettings->convertHumanToDateTime($to);
        list($to,$toTime) = explode(' ', $to);
      }
      if ($from>$to) throw new ExceptionUserTextStorage('error.ajax_resource_availability_invalidPeriod');

      $s = new SResourcePool;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (isset($this->_params['center'])&&$this->_params['center']) $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf("%%s IN (%s)", $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      if (isset($this->_params['tag'])&&$this->_params['tag']) {
        $tag = $this->_params['tag'];
        foreach ($tag as $key=>$value) {
          $tag[$key] = sprintf("'%s'", $this->_app->db->escapeString($value));
        }
        $s->addStatement(new SqlStatementMono($s->columns['tag_name'], sprintf("%%s IN (%s)", implode(',',$tag))));
      }

      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
      $s->setColumnsMask(array('resourcepool_id','name','description','center','center_name','full_address','url_photo','all_tag','resource_all'));
      //throw new ExceptionUser($s->toString());
      $res = $this->_app->db->doQuery($s->toString());
      if (!$this->_app->db->getRowsNumber($res)) {
        $this->_guiParams['resourceList'] = sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData'));
      } else {
        // filter se vztahuje az na zdroje ze skupiny
        $filterTag = null;
        if (isset($this->_params['filterValue'])&&is_array($this->_params['filterValue'])) {
          $filterTag = array();
          foreach ($this->_params['filterValue'] as $filterId=>$filterValue) {
            $s1 = new STag;
            $s1->addStatement(new SqlStatementBi($s1->columns['name'], $filterValue, '%s=%s'));
            $s1->setColumnsMask(array('tag_id'));
            $res1 = $this->_app->db->doQuery($s1->toString());
            if ($row1 = $this->_app->db->fetchAssoc($res1)) {
              $filterTag[$filterId] = $row1['tag_id'];
            } else {
              $filterTag[$filterId] = -1; // kdyz tag neexistuje, nebude se zobrazovat nic
            }
          }
        }

        $html = '';
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $resources = explode(',',$row['resource_all']);

          // jeste musim zjistit, ktery zdroje jsou available
          foreach ($resources as $index=>$resId) {
            $s1  = new SResource;
            $s1->addStatement(new SqlStatementBi($s1->columns['resource_id'], $resId, '%s=%s'));
            $s1->addStatement(new SqlStatementMono($s1->columns['active'], "%s='Y'"));
            if ($filterTag&&is_array($filterTag)) {
              foreach ($filterTag as $filterTagId=>$filterTagValue) {
                $s1->addStatement(new SqlStatementMono($s1->columns['all_tag_select'], sprintf("%s IN (%%s)", $filterTagValue)));
              }
            }
            $s1->setColumnsMask(array('time_alignment_from','time_alignment_to','time_end_from','time_end_to'));
            $res1 = $this->_app->db->doQuery($s1->toString());
            if ($row1 = $this->_app->db->fetchAssoc($res1)) {
              $resourceAligment = $row1;
              $s1 = new SResourceAvailability;
              $s1->addStatement(new SqlStatementBi($s1->columns['resource'], $resId, '%s=%s'));
              $s1->addStatement(new SqlStatementTri($from.' '.ifsetor($fromTime,$row1['time_alignment_from']), $s1->columns['start'], $s1->columns['end'], '%s BETWEEN %s AND %s'));
              $s1->addStatement(new SqlStatementTri($to.' '.ifsetor($toTime,$row1['time_end_to']), $s1->columns['start'], $s1->columns['end'], '%s BETWEEN %s AND %s'));
              $s1->setColumnsMask(array('resourceavailability_id'));
              $res1 = $this->_app->db->doQuery($s1->toString());

              if ($this->_app->db->getRowsNumber($res1)!=1) unset($resources[$index]);
            } else unset($resources[$index]);
          }
          if (!count($resources)) continue;

          $data = array(
            'id'                          => $row['resourcepool_id'],
            'resourcepool_name'           => $row['name'],
            'resourcepool_description'    => $row['description'],
            'center_id'                   => $row['center'],
            'center_name'                 => $row['center_name'],
            'center_address'              => $row['full_address'],
            'url_photo'                   => $row['url_photo'],
            'all_tag'                     => $row['all_tag'],
            'resources'                   => $resources,
            'from_datetime'               => $from.' '.ifsetor($resourceAligment['time_alignment_from'],'00:00:00'),
            'to_datetime'                 => $to.' '.ifsetor($resourceAligment['time_end_to'],'24:00:00'),
            'from_time_start'             => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($resourceAligment['time_alignment_from'],'00:00:00'),'h:m'),
            'from_time_end'               => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($resourceAligment['time_alignment_to'],'24:00:00'),'h:m'),
            'to_time_start'               => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($resourceAligment['time_end_from'],'00:00:00'),'h:m'),
            'to_time_end'                 => $this->_app->regionalSettings->convertTimeToHuman(ifsetor($resourceAligment['time_end_to'],'24:00:00'),'h:m'),
          );

          $g = new GuiElement(array('template'=>$this->_parseResourcePoolListLine($data)));
          $html .= sprintf('<div class="flb_resource_availability_item flb_list_item" id="%s%s">%s</div>',
            $this->_params['prefix'],
            $row['resourcepool_id'],
            $g->render()
          );
        }

        $this->_guiParams['resourcePoolList'] = sprintf('
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
    $this->_guiParams['resourcepool'] = ifsetor($this->_params['resourcepool'],'null');
    $this->_guiParams['specialJs'] = '';
    $this->_guiParams['noFilter'] = $this->_filter?'false':'true';

    global $PAYMENT_GATEWAY;
    $this->_guiParams['paymentUrl'] = sprintf($PAYMENT_GATEWAY['initUrl'], ifsetor($this->_params['language'],'cz'), $this->_params['sessid'], $this->_params['provider']);

    $this->_getResourcePoolList();
  }
}

?>
