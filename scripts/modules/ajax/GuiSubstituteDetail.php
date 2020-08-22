<?php

class AjaxGuiSubstituteDetail extends AjaxGuiAction2 {
  protected $_showBackButton = true;
  protected $_finishAction = 'flbLoadHtml(\'guiReservationList\', $(\'#{prefix}flb_substitute_{substitute_id}\').parent(), {params});';
  
  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_substitute_%s', $this->_params['prefix'], $this->_params['substituteId']);
    $this->_class = 'flb_reservation_detail';
  }
  
  protected function _initDefaultParams() {
    $this->_params['substituteId'] = str_replace($this->_params['prefix'],'',$this->_params['substituteId']);
  }

  protected function _getJavascript() {
    return sprintf("$(document).ready(function() {
                  %s
                  $('#%s').on('click','.flb_substitute_swap_button', function() {
                    var button = $(this);
                    if (confirm('{__label.ajax_reservation_confirmSubstituteSwap}')) {
                      $.ajax({
                          type: 'POST',
                          dataType: 'json',
                          data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), event: button.attr('data-event-id'), substitute: button.attr('data-substitute-id') },
                          url: $('#flb_core_url').val()+'action=swapSubstitute',
                          success: function(data) {
                              if (data.error) alert(data.message);
                              else {
                                if (data.popup) alert(data.popup);
                                %s
                              }
                          },
                          error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                      });
                    }
                  });
                  
                  $('#%s').on('click','.flb_substitute_cancel_button', function() {
                    var button = $(this);
                    if (confirm('{__label.ajax_reservation_confirmSubstituteCancel}')) {
                      $.ajax({
                          type: 'POST',
                          dataType: 'json',
                          data: { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: button.attr('data-substitute-id') },
                          url: $('#flb_core_url').val()+'action=cancelSubstitute',
                          success: function(data) {
                              if (data.error) alert(data.message);
                              else {
                                %s
                              }
                          },
                          error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
                      });
                    }
                  });
                });", $this->_showBackButton?"$('#{prefix}flb_substitute_{substitute_id}').on('click','.flb_substitute_back_button', function() {
                     flbLoadHtml('guiReservationList', $('#{prefix}flb_substitute_{substitute_id}').parent(), {params});            
                  });":'', $this->_id, $this->_finishAction, $this->_id, $this->_finishAction);
  }

  protected function _getSubstituteHtml() {
    return sprintf("<div class=\"label flb_reservation_number_label\"><span>{__label.ajax_reservation_number}:</span></div>
             <div class=\"value flb_reservation_number\">{__label.ajax_reservation_numberSubstitute}</div>
             <div class=\"label flb_reservation_commodity_label\"><span>{__label.ajax_reservation_commodity}:</span></div>
             <div class=\"value flb_reservation_commodity\">{commodity}</div>
             <div class=\"label flb_reservation_created_label\"><span>{__label.ajax_reservation_created}:</span></div>
             <div class=\"value flb_reservation_created\">{created}</div>
             {eventAttendee}
             {attribute}
             <div class=\"label flb_reservation_price_label\"><span>{__label.ajax_reservation_price}:</span></div>
             <div class=\"value flb_reservation_price\">{total_price} {__label.currency_CZK}</div>
             <div class=\"button\">
             %s
             {buttons}
             </div>", $this->_showBackButton?'<input type="button" class="flb_substitute_back_button" value="{__button.back}" />':'');
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = sprintf('<script>%s</script>%s', $this->_getJavascript(), $this->_getSubstituteHtml());
  }
  
  protected function _getAttributeGui($event, $attendee) {
    $b = new BEvent($event);
    $bData = $b->getSubstitute($attendee);
    $attribute = $bData['attribute'];
    
    $template = '';
    foreach ($attribute as $id=>$attr) {
      $attr['name'] = ifsetor($attr['name'][$this->_app->language->getLanguage()], array_values($attr['name'])[0]);
      switch ($attr['type']) {
        case 'NUMBER':
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
             
    return $template;
  }
  
  protected function _getData() {
    $s = new SEventAttendee;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['eventattendee_id'], $this->_params['substituteId'], '%s=%s'));
    $s->setColumnsMask(array('eventattendee_id','subscription_time','price',
                             'event','start','name','places'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_data=$this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser('FLB error: invalid substitute!');
    } else {
      // kdyz je na akci volne misto, pridam tlacitko na preklopeni na rezervaci
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_data['event'], '%s=%s'));
      $s->setColumnsMask(array('start','free'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $this->_guiParams['buttons'] = '';
      if ($row['start']>=date('Y-m-d H:i:s')) {
        if ($row['free']) {
          $this->_guiParams['buttons'] .= sprintf('<input type="button" class="flb_primaryButton flb_substitute_swap_button" value="%s" data-event-id="%s" data-substitute-id="%s" />',
            $this->_app->textStorage->getText('button.ajax_substitute_swap'), $this->_data['event'], $this->_data['eventattendee_id']);
        }
        $this->_guiParams['buttons'] .= sprintf('<input type="button" class="flb_substitute_cancel_button" value="%s" data-substitute-id="%s" />',
          $this->_app->textStorage->getText('button.ajax_substitute_cancel'), $this->_data['eventattendee_id']);
      }
      
      $start = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['start']);
      if (isset($this->_params['format']['datetime'])) $start = date($this->_params['format']['datetime'], strtotime($start));
      $this->_guiParams['commodity'] = sprintf('%s - %dx (%s)', $this->_data['name'], $this->_data['places'], $start);
      
      $person = '';
      $s = new SEventAttendeePerson;
      $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $this->_data['eventattendee_id'], '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['firstname']));
      $s->setColumnsMask(array('firstname','lastname','email'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        if ($person) $person .= ', ';
        $person .= sprintf('%s %s', $row['firstname'], $row['lastname']);
        if ($row['email']) $person .= sprintf(' (%s)', $row['email']);
      }
      $this->_guiParams['eventAttendee'] = sprintf(
          '<div class="label flb_reservation_attendee_label"><span>%s:</span></div>
           <div class="value flb_reservation_attendee">%s</div>',
           $this->_app->textStorage->getText('label.ajax_reservation_attendee'), $person);
      
      $this->_guiParams['attribute'] = $this->_getAttributeGui($this->_data['event'], $this->_data['eventattendee_id']);
      
      $this->_guiParams['event_id'] = $this->_data['event'];
      $this->_guiParams['substitute_id'] = $this->_data['eventattendee_id'];
      $this->_guiParams['created'] = $this->_app->regionalSettings->convertDateTimeToHuman($this->_data['subscription_time']);
      if (isset($this->_params['format']['datetime'])) $this->_guiParams['created'] = date($this->_params['format']['datetime'], strtotime($this->_guiParams['created']));
      $this->_guiParams['payed'] = '---';
      $this->_guiParams['total_price'] = $this->_app->regionalSettings->convertNumberToHuman($this->_data['price']);
    }
  }
}

?>
