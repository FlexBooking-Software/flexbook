<?php

class GuiCustomerCredit extends GuiElement {

  private function _insertCredit($customerId) {
    $s = new SCustomerRegistration;
    $s->addStatement(new SqlStatementBi($s->columns['customer'], $customerId, '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('credit_admin','list'))));
    $s->addOrder(new SqlStatementAsc($s->columns['registration_timestamp']));
    $s->setColumnsMask(array('provider','provider_name','registration_timestamp','credit'));
    $res = $this->_app->db->doQuery($s->toString());
    $template = ''; $i = 0;
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($i++%2) $class = 'Even';
      else $class = 'Odd';
      $template .= sprintf('<tr class="%s"><td>%s</td><td>%s</td><td>%s</td>
                           <td><input type="text" class="shortText" name="credit_%s">
                           <input type="submit" class="inputSubmit" name="action_eCustomerCredit?provider=%s" value="%s" onclick="return confirm(\'%s\');"/></td></tr>', $class, $row['provider_name'],
                           $this->_app->regionalSettings->convertDateTimeToHuman($row['registration_timestamp']),
                           $this->_app->regionalSettings->convertNumberToHuman($row['credit']),
                           $row['provider'], $row['provider'], $this->_app->textStorage->getText('button.editCustomerCredit_save'),
                           $this->_app->textStorage->getText('label.editCustomerCredit_confirm'));
    }
    if ($template) $template = sprintf('<div class="gridTable"><table><tr><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr>%s</table></div>',
                                       $this->_app->textStorage->getText('label.editCustomerCredit_provider'),
                                       $this->_app->textStorage->getText('label.editCustomerCredit_timestamp'),
                                       $this->_app->textStorage->getText('label.editCustomerCredit_credit'),
                                       $template);
    $this->insertTemplateVar('fi_credit', $template, false);
  }

  protected function _userRender() {
    $customerId = $this->_app->request->getParams('id');
    
    $s = new SCustomer;
    $s->addStatement(new SqlStatementBi($s->columns['customer_id'], $customerId, '%s=%s'));
    if (!$this->_app->auth->isAdministrator()) $s->addStatement(new SqlStatementMono($s->columns['registration_provider'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedProvider('credit_admin','list'))));
    $s->setColumnsMask(array('customer_id','name','street','city','postal_code','state','ic','dic','email'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->setTemplateFile(dirname(__FILE__).'/CustomerCredit.html');
      
      foreach ($row as $k => $v) {
        if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
      }
  
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editCustomerCredit_title'));
      
      $this->_insertCredit($customerId);
    }
  }
}

?>
