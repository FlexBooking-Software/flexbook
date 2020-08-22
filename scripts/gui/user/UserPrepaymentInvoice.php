<?php

class GuiUserPrepaymentInvoice extends GuiElement {
  protected $_creditJournalId = null;
  protected $_number = null;

  public function __construct($params=array()) {
    parent::__construct($params);

    $this->_creditJournalId = $params['creditJournal'];
    if (isset($params['number'])) $this->_number = $params['number'];
  }

  protected function _parseData($row) {
    $data = array(
      '@@PREPAYMENT_INVOICE_NUMBER'       => $row['prepayment_invoice_number'],
      '@@PRICE'                           => $row['amount'],
      '@@USER_ID'                         => $row['user_id'],
      '@@USER_NAME'                       => $row['user_fullname'],
      '@@USER_EMAIL'                      => $row['user_email'],
      '@@USER_STREET'                     => $row['user_street'],
      '@@USER_CITY'                       => $row['user_city'],
      '@@USER_POSTAL_CODE'                => $row['user_postal_code'],
      '@@PROVIDER_NAME'                   => $row['provider_invoice_name'],
      '@@PROVIDER_STREET'                 => $row['provider_invoice_street'],
      '@@PROVIDER_CITY'                   => $row['provider_invoice_city'],
      '@@PROVIDER_POSTAL_CODE'            => $row['provider_invoice_postal_code'],
      '@@PROVIDER_IC'                     => $row['provider_ic'],
      '@@PROVIDER_DIC'                    => $row['provider_dic'],
      '@@PROVIDER_VAT_RATE'               => $row['provider_vat_rate'],
      '@@PROVIDER_EMAIL'                  => $row['provider_email'],
      '@@PROVIDER_WWW'                    => $row['provider_www'],
      '@@PROVIDER_PHONE'                  => $row['provider_phone'],
      '@@PROVIDER_PHONE_1'                => $row['provider_phone_1'],
      '@@PROVIDER_PHONE_2'                => $row['provider_phone_2'],
      '@@PROVIDER_BANK_ACCOUNT_NUMBER'    => $row['provider_bank_account_number'],
      '@@PROVIDER_BANK_ACCOUNT_SUFFIX'    => $row['provider_bank_account_suffix'],
    );

    list($row['pay_date'],$row['pay_time']) = explode(' ', $row['change_timestamp']);
    $data['@@PAY_DATE'] = $this->_app->regionalSettings->convertDateToHuman($row['pay_date']);
    $data['@@PAY_TIME'] = $this->_app->regionalSettings->convertTimeToHuman($row['pay_time'], 'h:m');

    $data['@@PRICE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@PRICE'],2);

    $data['@@PAY_TYPE'] = $this->_app->textStorage->getText('label.ajax_profile_creditCharge_'.$row['type']);

    return $data;
  }

  protected function _parseQrCode($template) {
    global $AJAX;
    $qrCodeUrl = sprintf('<img src="%s/qr.php?code=', dirname($AJAX['url']));
    while ($i = strpos($template, '@@QR_CODE(')) {
      $template = substr_replace($template, $qrCodeUrl, $i, 10);
      $j = strpos($template, ')', $i);
      $template = substr_replace($template, '"/>', $j, 1);
    }

    return $template;
  }

  protected function _userRender() {
    $s = new SCreditJournal();
    $s->addStatement(new SqlStatementBi($s->columns['creditjournal_id'], $this->_creditJournalId, '%s=%s'));
    $s->setColumnsMask(array('amount','change_timestamp','type',
      'user_id','user_fullname','user_email','user_street','user_postal_code','user_city',
      'provider','provider_name','provider_ic','provider_dic','provider_vat','provider_vat_rate',
      'provider_email','provider_www','provider_phone','provider_phone_1','provider_phone_2',
      'provider_bank_account_number','provider_bank_account_suffix',
      'provider_invoice_name','provider_invoice_street','provider_invoice_city','provider_invoice_postal_code'
    ));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid creditjournal record!');

    // tyto parametry jdou predefinovat v konstruktoru
    if ($this->_number) $row['prepayment_invoice_number'] = $this->_number;

    $data = $this->_parseData($row);

    $providerSettings = BCustomer::getProviderSettings($row['provider']);
    if ($providerSettings['prepaymentInvoiceTemplate']) $template = $providerSettings['prepaymentInvoiceTemplate'];
    else $template = file_get_contents(dirname(__FILE__).'/UserPrepaymentInvoice.html');

    $template = str_replace(array_keys($data), $data, $template);
    $template = $this->_parseQrCode($template);

    $gui = new GuiElement(array('template'=>sprintf('<div class="userPrepaymentInvoice">%s</div>',$template)));

    $this->insert($gui);
  }
}

?>
