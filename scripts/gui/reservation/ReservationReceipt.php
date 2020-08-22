<?php

class GuiReservationReceipt extends GuiElement {
  protected $_reservationId = null;
  protected $_number = null;
  protected $_actionDate = null;
  protected $_refund = false;
  protected $_refundAmount = false;
  
  public function __construct($params=array()) {
    parent::__construct($params);

    $this->_reservationId = $params['reservation'];
    if (isset($params['number'])) $this->_number = $params['number'];
    if (isset($params['actionDate'])) $this->_actionDate = $params['actionDate'];
    if (isset($params['refund'])) $this->_refund = $params['refund'];
    if (isset($params['refundAmount'])) $this->_refundAmount = $params['refundAmount'];
  }

  protected function _parseData($row) {
    $data = array(
      '@@RESERVATION_ID'                  => $row['reservation_id'],
      '@@RESERVATION_NUMBER'              => $row['number'],
      '@@PRICE'                           => $row['total_price'],
      '@@DISCOUNT_TEXT'                   => '',
      '@@DISCOUNT_PRICE'                  => 0,
      '@@TOTAL_PRICE'                     => $row['total_price'],
      '@@RECEIPT_NUMBER'                  => $row['receipt_number'],
      '@@USER_ID'                         => $row['user_id'],
      '@@USER_NAME'                       => $row['user_name'],
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
      '@@CENTER_NAME'                     => $row['center_name'],
      '@@CENTER_STREET'                   => $row['center_street'],
      '@@CENTER_CITY'                     => $row['center_city'],
      '@@CENTER_POSTAL_CODE'              => $row['center_postal_code'],
    );

    if (strcmp($row['provider_name'],$row['center_name'])) $data['@@CENTER_NAME'] = sprintf('%s - %s', $row['provider_name'], $row['center_name']);
    list($row['date_from'],$row['time_from']) = explode(' ', $row['start']);
    list($row['date_to'],$row['time_to']) = explode(' ', $row['end']);
    if ($row['payed']) list($row['pay_date'],$row['pay_time']) = explode(' ', $row['payed']);
    else $row['pay_date'] = $row['pay_time'] = null;

    if ($row['payed_ticket']) {
      $s = new SUserTicket;
      $s->addStatement(new SqlStatementBi($s->columns['userticket_id'], $row['payed_ticket'], '%s=%s'));
      $s->setColumnsMask(array('name','original_value','price'));
      $res = $this->_app->db->doQuery($s->toString());
      $row1 = $this->_app->db->fetchAssoc($res);

      $ticketDiscount = $row1['price']/$row1['original_value']*$row['total_price'];

      if ($data['@@DISCOUNT_TEXT']) $data['@@DISCOUNT_TEXT'] .= ',';
      $data['@@DISCOUNT_TEXT'] .= $row1['name'];
      $data['@@DISCOUNT_PRICE'] += $ticketDiscount;
      $data['@@TOTAL_PRICE'] -= $ticketDiscount;
    }

    if ($row['voucher_code']) {
      if ($data['@@DISCOUNT_TEXT']) $data['@@DISCOUNT_TEXT'] .= ',';
      $data['@@DISCOUNT_TEXT'] .= $row['voucher_code'];
      $data['@@DISCOUNT_PRICE'] += $row['voucher_discount_amount'];
      $data['@@PRICE'] += $row['voucher_discount_amount'];
    }

    $data['@@COMMODITY_PRICE'] = $data['@@PRICE'];
    $data['@@COMMODITY_BASE_PRICE'] = round($data['@@COMMODITY_PRICE']/(1+($row['provider_vat_rate']/100)),2);

    $data['@@DPH_BASE'] = round($data['@@TOTAL_PRICE']/(1+($row['provider_vat_rate']/100)),2);
    $data['@@DPH_AMOUNT'] = $data['@@TOTAL_PRICE']-$data['@@DPH_BASE'];

    if ($this->_refund) {
      $data['@@DPH_BASE'] = -$data['@@DPH_BASE'];
      $data['@@DPH_AMOUNT'] = -$data['@@DPH_AMOUNT'];
      $data['@@TOTAL_PRICE'] = -$data['@@TOTAL_PRICE'];
    }

    $s1 = new SReservationJournal;
    $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $this->_reservationId, '%s=%s'));
    $s1->addStatement(new SqlStatementMono($s1->columns['action'], "%s='PAY'"));
    $s1->setColumnsMask(array('note'));
    $res1 = $this->_app->db->doQuery($s1->toString());
    $row1 = $this->_app->db->fetchAssoc($res1);
    $noteParts = explode('|', $row1['note']);
    $data['@@PAY_TYPE'] = $this->_app->textStorage->getText('label.ajax_reservation_payment_'.$noteParts[0]);

    $data['@@DATE_FROM'] = $this->_app->regionalSettings->convertDateToHuman($row['date_from']);
    $data['@@TIME_FROM'] = $this->_app->regionalSettings->convertTimeToHuman($row['time_from'], 'h:m');
    $data['@@DATE_TO'] = $this->_app->regionalSettings->convertDateToHuman($row['date_to']);
    $data['@@TIME_TO'] = $this->_app->regionalSettings->convertTimeToHuman($row['time_to'], 'h:m');
    $data['@@PAY_DATE'] = $this->_app->regionalSettings->convertDateToHuman($row['pay_date']);
    $data['@@PAY_TIME'] = $this->_app->regionalSettings->convertTimeToHuman($row['pay_time'], 'h:m');

    $data['@@COMMODITY_BASE_PRICE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@COMMODITY_BASE_PRICE'],2);
    $data['@@COMMODITY_PRICE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@COMMODITY_PRICE'],2);
    $data['@@DPH_BASE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@DPH_BASE'],2);
    $data['@@DPH_AMOUNT'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@DPH_AMOUNT'],2);
    $data['@@PRICE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@PRICE'],2);
    $data['@@DISCOUNT_PRICE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@DISCOUNT_PRICE'],2);
    $data['@@TOTAL_PRICE'] = $this->_app->regionalSettings->convertNumberToHuman($data['@@TOTAL_PRICE'],2);

    if ($row['event']) {
      $data['@@COMMODITY_NAME'] = $row['event_name'];
      $data['@@COMMODITY_QUANTITY'] = $row['event_places'].'x';
    } elseif ($row['resource']) {
      $data['@@COMMODITY_NAME'] = $row['resource_name'];
      $data['@@COMMODITY_QUANTITY'] = sprintf('%s: %s-%s', $data['@@DATE_FROM'], $data['@@TIME_FROM'], $data['@@TIME_TO']);
    }

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
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_reservationId, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['receipt_number'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('reservation_id','provider','number','total_price','receipt_number','voucher_code','voucher_discount_amount',
      'event','event_pack','event_name','event_places',
      'resource','resource_name',
      'start','end','payed','payed_ticket',
      'user_id','user_name','user_email','user_street','user_postal_code','user_city',
      'provider','provider_name','provider_ic','provider_dic','provider_vat','provider_vat_rate',
      'provider_email','provider_www','provider_phone','provider_phone_1','provider_phone_2',
      'provider_bank_account_number','provider_bank_account_suffix',
      'center_name','center_street','center_city','center_postal_code',
      'provider_invoice_name','provider_invoice_street','provider_invoice_city','provider_invoice_postal_code'
    ));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser('Invalid reservation!');

    // tyto parametry jdou predefinovat v konstruktoru (kvuli dokladu o vraceni penez)
    if ($this->_number) $row['receipt_number'] = $this->_number;
    if ($this->_actionDate) $row['payed'] = $this->_actionDate;
    if ($this->_refundAmount) $row['total_price'] = $this->_refundAmount;

    $data = $this->_parseData($row);

    $providerSettings = BCustomer::getProviderSettings($row['provider']);
    if ($providerSettings['receiptTemplate']) $template = $providerSettings['receiptTemplate'];
    else $template = file_get_contents(dirname(__FILE__).'/ReservationReceipt.html');

    $template = str_replace(array_keys($data), $data, $template);
    $template = $this->_parseQrCode($template);

    $gui = new GuiElement(array('template'=>sprintf('<div class="reservationReceipt">%s</div>',$template)));

    $this->insert($gui);
  }
}

?>
