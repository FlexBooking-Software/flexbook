<?php

class GuiReservationInvoice extends GuiReservationReceipt {

  protected function _parseData($row) {
    $data = parent::_parseData($row);

    $data['@@INVOICE_NUMBER'] = $row['invoice_number'];

    return $data;
  }

  protected function _userRender() {
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_reservationId, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['payed'], '%s IS NOT NULL'));
    $s->setColumnsMask(array('reservation_id','provider','number','total_price','receipt_number','invoice_number','voucher_code','voucher_discount_amount',
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

    $data = $this->_parseData($row);

    $providerSettings = BCustomer::getProviderSettings($row['provider']);
    if ($providerSettings['invoiceTemplate']) $template = $providerSettings['invoiceTemplate'];
    else $template = file_get_contents(dirname(__FILE__).'/ReservationInvoice.html');

    $template = str_replace(array_keys($data), $data, $template);
    $template = $this->_parseQrCode($template);

    $gui = new GuiElement(array('template'=>sprintf('<div class="reservationInvoice">%s</div>',$template)));

    $this->insert($gui);
  }
}

?>
