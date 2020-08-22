<?php

class GuiLogin extends GuiElement {
  
  protected function _userRender() {
    $validator = Validator::get('login', 'LoginValidator');
    $data = $validator->getValues();

    if (!$data['login_accounts']) {
      $this->setTemplateFile(dirname(__FILE__) . '/Login.html');

      foreach ($data as $key => $value) {
        $this->insertTemplateVar($key, $value);
      }

      // kdyz se prihlasuje pro konkretniho poskytovatele
      if ($data['login_provider']) {
        $s = new SProvider;
        $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $data['login_provider'], '%s=%s'));
        $s->setColumnsMask(array('short_name','name'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);

        $this->insertTemplateVar('fi_provider', sprintf('<span id="fi_provider_block"><div class="label">%s</div><input class="text" type="test" value="%s - %s" />
          <img id="fi_provider_remove" src="img/button_jq_close_gray.png" onclick="document.getElementById(\'fi_provider\').value=null;document.getElementById(\'fi_provider_block\').remove();"/>
          </span><input type="hidden" name="login_provider" id="fi_provider" value="%s"/>',
          $this->_app->textStorage->getText('label.login_provider'), $row['short_name'], $row['name'], $data['login_provider']), false);
      } else $this->insertTemplateVar('fi_provider', '');
    } else {
      $this->setTemplateFile(dirname(__FILE__) . '/LoginAccounts.html');

      $this->insertTemplateVar('login_username', $data['login_username']);

      usort($data['login_accounts'], function ($a,$b) { return strcmp($a['providerName'], $b['providerName']); });
      foreach ($data['login_accounts'] as $account) {
        $clickAction = $account['authenticated']?
          sprintf("document.getElementById('fi_provider').value = '%s';document.getElementById('form').submit();", $account['providerId']?$account['providerId']:'NULL'):
          sprintf("document.getElementById('fi_provider').value = '%s';document.getElementById('fi_username').value = '%s';document.getElementById('fi_relogin').click();",
            $account['providerId']?$account['providerId']:'NULL', $account['username']);

        $button = sprintf('<div class="buttonAccount%s%s" type="submit" title="%s" onclick="%s"><div class="shortName">%s</div><div class="name">%s</div></div>',
          $account['providerName']?'':' buttonAdminAccount',
          $account['authenticated']?' buttonAuthenticatedAccount':' buttonNotAuthenticatedAccount',
          $account['authenticated']?$this->_app->textStorage->getText('label.login_authenticatedTitle'):$this->_app->textStorage->getText('label.login_notAuthenticatedTitle'),
          $clickAction,
          $account['providerShortName']?$account['providerShortName']:$this->_app->textStorage->getText('label.login_account_admin'),
          $account['providerName']?$account['providerName']:'');

        $this->insertTemplateVar('accounts', $button, false);
      }
    }
  }
}

?>
