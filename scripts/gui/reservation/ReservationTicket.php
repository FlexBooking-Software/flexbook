<?php

class GuiReservationTicket extends GuiElement {
  private $_reservationId;
  private $_skipACL = false;

  public function __construct($params=array()) {
    parent::__construct($params);

    if (isset($params['reservation'])) $this->_reservationId = $params['reservation'];
    if (isset($params['skipACL'])) $this->_skipACL = $params['skipACL'];
  }

  private function _insertTicket($id) {
    $data = array();

    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $id, '%s=%s'));
    if (!$this->_skipACL) {
      if (!$this->_app->auth->isAdministrator()&&!$this->_app->auth->isProvider()) $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    }
    $s->setColumnsMask(array('reservation_id','provider','number','total_price','event','resource','start','end',
      'user_id','user_name','user_email','user_state_code','user_state_name',
      'provider','provider_name','center_name','center_street','center_city','center_postal_code',
      'event_name','resource_name'
    ));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) die('Invalid reservation!');

    foreach ($row as $key=>$val) {
      $data[sprintf('@@%s', strtoupper($key))] = $val;
    }

    $data['@@RESERVATION_NUMBER'] = $row['number'];
    $data['@@PRICE'] = $row['total_price'];
    if (strcmp($row['provider_name'],$row['center_name'])) $data['@@CENTER_NAME'] = sprintf('%s - %s', $row['provider_name'], $row['center_name']);
    list($row['date_from'],$row['time_from']) = explode(' ', $row['start']);
    list($row['date_to'],$row['time_to']) = explode(' ', $row['end']);
    $data['@@DATE_FROM'] = $this->_app->regionalSettings->convertDateToHuman($row['date_from']);
    $data['@@TIME_FROM'] = $this->_app->regionalSettings->convertTimeToHuman($row['time_from'], 'h:m');
    $data['@@DATE_TO'] = $this->_app->regionalSettings->convertDateToHuman($row['date_to']);
    $data['@@TIME_TO'] = $this->_app->regionalSettings->convertTimeToHuman($row['time_to'], 'h:m');
    if ($row['event']) {
      $data['@@COMMODITY_NAME'] = $row['event_name'];
    } elseif ($row['resource']) {
      $data['@@COMMODITY_NAME'] = $row['resource_name'];
    }
    #adump($data);

    $providerSettings = BCustomer::getProviderSettings($row['provider']);
    if ($providerSettings['ticketTemplate']) $template = $providerSettings['ticketTemplate'];
    else $template = file_get_contents(dirname(__FILE__).'/ReservationTicket.html');

    $template = str_replace(array_keys($data), $data, $template);
    // qr code replacing
    global $AJAX;
    $qrCodeUrl = sprintf('<img src="%s/qr.php?code=', dirname($AJAX['url']));
    while ($i = strpos($template, '@@QR_CODE(')) {
      $template = substr_replace($template, $qrCodeUrl, $i, 10);
      $j = strpos($template, ')', $i);
      $template = substr_replace($template, '"/>', $j, 1);
    }

    $gui = new GuiElement(array('template'=>sprintf('<div class="reservationTicket">%s</div>',$template)));

    $this->insert($gui);
  }

  protected function _userRender() {
    if (!$this->_reservationId) $this->_reservationId = $this->_app->request->getParams('id');

    if ($this->_reservationId) {
      if (!is_array($this->_reservationId)) $this->_reservationId = array($this->_reservationId);

      foreach ($this->_reservationId as $i) $this->_insertTicket($i);
    }
  }
}

?>
