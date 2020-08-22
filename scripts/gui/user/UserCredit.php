<?php

class GuiUserCredit extends GuiElement {

  private function _insertCredit($data) {
    $s = new SUserRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $data['userId'], '%s=%s'));
    #if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', implode(',',$this->_app->auth->getAllowedProvider('credit_admin')))));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['registration_timestamp']));
    $s->setColumnsMask(array('userregistration_id','provider','provider_name','registration_timestamp','credit'));
    $res = $this->_app->db->doQuery($s->toString());
    $template = ''; $i = 0;
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $s1 = new SCreditJournal;
      $s1->addStatement(new SqlStatementBi($s1->columns['userregistration'], $row['userregistration_id'], '%s=%s'));
      $s1->setColumnsMask(array('creditjournal_id'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      if ($this->_app->db->getRowsNumber($res1)) $history = sprintf('<a href="#" class="creditHistoryUrl" id="%s"><img title="%s" src="img/button_grid_detail.png"/></a>', $row['userregistration_id'], $this->_app->textStorage->getText('label.editUserCredit_history'));
      else $history = '';
      
      if ($this->_app->auth->isAdministrator()) {
        $providerTr = sprintf('<th>%s</th>', $this->_app->textStorage->getText('label.editUserCredit_provider'));
        $providerTd = sprintf('<td>%s</td>', $row['provider_name']);
      } else {
        $providerTr = '';
        $providerTd = '';
      }
      
      if ($i++%2) $class = 'Even';
      else $class = 'Odd';
      $template .= sprintf('<tr class="%s">%s<td>%s</td><td>%s</td>
                           <td class="creditChange"><input type="text" class="shortText" name="credit_%s">
                           <input type="submit" class="inputSubmit" name="action_eUserCreditSave?provider=%s" value="%s" onclick="return confirm(\'%s\');"/></td>
                           <td class="creditChange"><input type="text" class="shortText" name="creditRefund_%s">
                           <input type="submit" class="inputSubmit" name="action_eUserCreditRefund?provider=%s" value="%s" onclick="return confirm(\'%s\');"/></td>
                           <td>%s</td></tr>', $class, $providerTd,
                           $this->_app->regionalSettings->convertDateTimeToHuman($row['registration_timestamp']),
                           $this->_app->regionalSettings->convertNumberToHuman($row['credit'],2),
                           $row['provider'], $row['provider'], $this->_app->textStorage->getText('button.editUserCredit_save'),$this->_app->textStorage->getText('label.editUserCredit_confirm'),
                           $row['provider'], $row['provider'], $this->_app->textStorage->getText('button.editUserCredit_return'),$this->_app->textStorage->getText('label.editUserCredit_returnConfirm'),
                           $history);
    }
    if ($template) $template = sprintf('<div class="gridTable"><table><tr>%s<th>%s</th><th>%s</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr>%s</table></div>',
                                       $providerTr,
                                       $this->_app->textStorage->getText('label.editUserCredit_timestamp'),
                                       $this->_app->textStorage->getText('label.editUserCredit_credit'),
                                       $template);
    $this->insertTemplateVar('fi_credit', $template, false);
  }
  
  private function _insertUserTicket($data) {
    $this->insert(new GuiListUserTicket($data['userId']), 'fi_ticket');
    
    $hash = array();
    $select = new STicket;
    if (!$this->_app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementTri($select->columns['center'], $select->columns['center'], $this->_app->auth->getActualCenter(), '(%s IS NULL OR %s=%s)'));
    }
    $select->addStatement(new SqlStatementMono($select->columns['active'], "%s='Y'"));
    $select->setColumnsMask(array('ticket_id','name','price','provider_name'));
    $res = $this->_app->db->doQuery($select->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $desc = '';
      if ($this->_app->auth->isAdministrator()) {
        $desc = sprintf('%s - ', $row['provider_name']);
      }
      $desc .= sprintf('%s / %s %s', $row['name'], $this->_app->regionalSettings->convertNumberToHuman($row['price'],2),
                       $this->_app->textStorage->getText('label.currency_CZK'));
      
      $hash[$row['ticket_id']] = $desc;
    }
    
    $this->insert(new GuiFormSelect(array(
          'name'        => 'newTicket',
          'value'       => $data['newTicket'],
          'dataSource'  => new HashDataSource(new DataSourceSettings, $hash),  
          'showDiv'     => false,  
          )), 'fi_newTicket');
  }

  protected function _userRender() {
    $validator = Validator::get('userCredit', 'UserCreditValidator');
    $data = $validator->getValues();
    
    foreach ($data as $key=>$value) {
      $this->insertTemplateVar($key, $value, false);
    }
    
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['user_id'], $data['userId'], '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['registration_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('credit_admin','list'))));
    $s->setColumnsMask(array('user_id','fullname','street','city','postal_code','state','email'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->setTemplateFile(dirname(__FILE__).'/UserCredit.html');
      
      foreach ($row as $k => $v) {
        if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
      }
  
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editUserCredit_title'));
      
      $this->_insertCredit($data);
      $this->_insertUserTicket($data);
    }

    $o = new OProviderSettings(array('provider'=>$this->_app->auth->getActualProvider()));
    $oData = $o->getData();
    if ($oData['generate_accounting']=='Y') {
      $this->insertTemplateVar('fb_prepaymentInvoiceList', sprintf('<input class="button" id="fb_prepaymentInvoice" type="button" value="%s" />', $this->_app->textStorage->getText('button.editUserCredit_prepaymentInvoice')), false);
    } else $this->insertTemplateVar('fb_prepaymentInvoiceList', '');
    
    global $AJAX;
    $this->_app->document->addJavascript(sprintf("
                      $(document).ready(function() {
                        $('#fi_creditHistory_form').dialog({
                          autoOpen: false,
                          height: 350,
                          width: 920,
                          modal: true,
                          buttons: {
                              '{__button.close}': function() { $(this).dialog('close'); },
                          },
                        });
                        
                        $('.creditHistoryUrl').click(function() {
                          $.ajax({
                            type: 'GET',
                            dataType: 'json',
                            data: { sessid : '%s', registration: $(this).attr('id') },
                            url: '%s?action=getUserCreditHistory',
                            success: function(data) {
                                var item = $('#fi_creditHistoryItem').html('');
                                
                                $.each(data, function(index,element) {
                                  item.append('<div class=\"item\">'+
                                              '<div class=\"time\">'+element.timestamp+'</div>'+
                                              '<div class=\"amount\">'+element.amount+' '+element.currency+'</div>'+
                                              '<div class=\"description\">'+element.description+'</div>'+
                                              '</div>');
                                });
                            },
                            error: function(error) { alert('{__label.ajaxError}'); }
                          });
                          
                          $('#fi_creditHistory_form').dialog('option', 'title', '%s');
                          $('#fi_creditHistory_form').dialog('open');
                          
                          return false;
                        });
                        
                        $('.ticketHistoryUrl').click(function() {
                          $.ajax({
                            type: 'GET',
                            dataType: 'json',
                            data: { sessid : '%s', ticket: $(this).attr('id') },
                            url: '%s?action=getUserCreditHistory',
                            success: function(data) {
                                var item = $('#fi_creditHistoryItem').html('');
                                
                                $.each(data, function(index,element) {
                                  item.append('<div class=\"item\">'+
                                              '<div class=\"time\">'+element.timestamp+'</div>'+
                                              '<div class=\"amount\">'+element.amount+' '+element.currency+'</div>'+
                                              '<div class=\"description\">'+element.description+'</div>'+
                                              '</div>');
                                });
                            },
                            error: function(error) { alert('{__label.ajaxError}'); }
                          });
                          
                          $('#fi_creditHistory_form').dialog('option', 'title', '%s');
                          $('#fi_creditHistory_form').dialog('open');
                          
                          return false;
                        });
                        
                        $('#fb_activity').click(function() {
                          $.ajax({
                            type: 'GET',
                            dataType: 'json',
                            data: { sessid : '%s', user: %s, provider: %s },
                            url: '%s?action=getUserCreditHistory',
                            success: function(data) {
                                var item = $('#fi_creditHistoryItem').html('');
                                
                                $.each(data, function(index,element) {
                                  if (element.invoice) {
                                    invoice = '<a target=\"_blank\" href=\"%s?action=vUserPrepaymentInvoice&id='+element.id+'&sessid=%s\"><img title=\"%s\" src=\"img/button_grid_download.png\"/></a>';
                                  } else invoice = '&nbsp;';
                                  item.append('<div class=\"item\">'+
                                              '<div class=\"download\">'+invoice+'</div>'+
                                              '<div class=\"time\">'+element.timestamp+'</div>'+
                                              '<div class=\"type\">'+element.typeLabel+'</div>'+
                                              '<div class=\"amount\">'+element.amount+' '+element.currency+'</div>'+
                                              '<div class=\"description\">'+element.description+'</div>'+
                                              '</div>');
                                });
                            },
                            error: function(error) { alert('{__label.ajaxError}'); }
                          });
                          
                          $('#fi_creditHistory_form').dialog('option', 'title', '%s');
                          $('#fi_creditHistory_form').dialog('open');
                          
                          return false;
                        });
                        
                        $('#fb_prepaymentInvoice').click(function() {
                          $.ajax({
                            type: 'GET',
                            dataType: 'json',
                            data: { sessid : '%s', user: %s, provider: %s },
                            url: '%s?action=getUserPrepaymentInvoice',
                            success: function(data) {
                                var item = $('#fi_creditHistoryItem').html('');
                                
                                $.each(data, function(index,element) {
                                  invoice = '<a target=\"_blank\" href=\"%s?action=vUserPrepaymentInvoice&id='+element.id+'&sessid=%s\"><img title=\"%s\" src=\"img/button_grid_download.png\"/></a>';
                                  item.append('<div class=\"item\">'+
                                              '<div class=\"download\">'+invoice+'</div>'+
                                              '<div class=\"time\">'+element.timestamp+'</div>'+
                                              '<div class=\"type\">'+element.number+'</div>'+
                                              '<div class=\"amount\">'+element.amount+' '+element.currency+'</div>'+
                                              '</div>');
                                });
                            },
                            error: function(error) { alert('{__label.ajaxError}'); }
                          });
                          
                          $('#fi_creditHistory_form').dialog('option', 'title', '%s');
                          $('#fi_creditHistory_form').dialog('open');
                          
                          return false;
                        });
                      });
                      ",$this->_app->session->getId(), $AJAX['adminUrl'], $this->_app->textStorage->getText('label.editUserCredit_historyCreditTitle'),
                        $this->_app->session->getId(), $AJAX['adminUrl'], $this->_app->textStorage->getText('label.editUserCredit_historyTicketTitle'),
                        $this->_app->session->getId(), $data['userId'], $this->_app->auth->getActualProvider(), $AJAX['adminUrl'],
                        $AJAX['adminUrl'], $this->_app->session->getId(),
                        $this->_app->textStorage->getText('label.editUserCredit_historyInvoiceTitle'),
                        $this->_app->textStorage->getText('label.editUserCredit_historyAllTitle'),
                        $this->_app->session->getId(), $data['userId'], $this->_app->auth->getActualProvider(), $AJAX['adminUrl'],
                        $AJAX['adminUrl'], $this->_app->session->getId(),
                        $this->_app->textStorage->getText('label.editUserCredit_historyInvoiceTitle'),
                        $this->_app->textStorage->getText('label.editUserCredit_historyAllInvoicesTitle')));
  }
}

?>
