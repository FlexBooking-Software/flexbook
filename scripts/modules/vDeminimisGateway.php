<?php

class ModuleDeminimisGateway extends DocumentModule {
  
  protected function _projectInsert() {
    global $PAYMENT_GATEWAY;

    $this->_app->document->addMeta(array('http-equiv'=>'X-UA-Compatible','content'=>'IE=11'));
    
    global $HTTPS;
    if ($HTTPS) { Application::get()->setProtocol('https'); }

    // deminimis je jenom na placeni rezervaci
    // nactu data o rezervaci
    $reservation = str_replace('|','',$this->_app->session->get('payment_targetId'));
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $reservation, '%s=%s'));
    $s->setColumnsMask(array('number','total_price','user','user_name','description','eventattendeeperson_user'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    if ($row['eventattendeeperson_user']) {
      $oU = new OUser($row['eventattendeeperson_user']);
      if ($oUData = $oU->getData()) {
        $row['subaccount_name'] = sprintf('%s %s', $oUData['firstname'], $oUData['lastname']);
      }
    }
    if (!$row['description']) $row['description'] = $row['number'];

    // nactu atribut, kde ma parent user IC
    $s = new SUserAttribute;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $row['user'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['short_name'], $PAYMENT_GATEWAY['source']['deminimis']['attr_ic'], '%s=%s'));
    $s->setColumnsMask(array('value'));
    $res = $this->_app->db->doQuery($s->toString());
    $row1 = $this->_app->db->fetchAssoc($res);

    if (!$row['description']||!$row['total_price']||!$row['eventattendeeperson_user']||!$row1||!$row1['value']) {
    #if (true) {
      echo "Zadost o platbu nelze podat, kontaktujte prosim podporu CzT.<br/><hr/><br/>";
      echo "Reservation detail:\n";
      adump($row);
      echo "User attribute detail:\n";
      adump($row1);

      die('Internal error!');
    }

    $row['ic'] = $row1['value'];

    $this->setTitle('Deminimis');
    $this->setTemplateString('
      <style>
        div.title { font-weight: bold; font-size: 1.3em; margin: 10px; }
        div.description { font-size: 1.1em; }
        div.form { overflow: hidden; margin-top: 20px; }
        div.button {
          color: black;
          border: 1px solid #333333;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
          padding: 6px 15px 5px 15px;
          margin-top: 3px;
          margin-right: 3px;
          border-radius: 3px;
          cursor: pointer;
          transition: all 0.2s;
          font-size: 14px;
          float: left;
        }
        div#sendButton { 
          color: white;
          background-color: #42A861 !important;
        }
        hr { clear: left; margin-top: 10px; margin-bottom: 20px; }
      </style>
      <div class="title">Žádost o platbu z registru Deminimis</div>
      <div class="description">
        Kliknutím na tlačítko "Podat žádost" potvrzujete odeslání žádosti o platbu školení prostřednictvím registru Deminimis.<br/>
        Zpracování žádosti bude vyřízeno v rámci několika pracovních dní.<br/>
        <br/>
        <b>Data žádosti:</b><br/>
        Celé jméno hlavního účtu: {user_name}<br/>
        IČ: {ic}<br/>
        Celé jméno účastníka: {subaccount_name}<br/>
        Částka: {total_price} CZK<br/>
        Popis: {description}<br/>
      </div>
      <div class="form">
        <div class="button" id="backButton"><span>ZPĚT</span></div>
        <div class="button" id="sendButton"><span>POSLAT ŽÁDOST</span></div>
      </div>
      <script type="text/javascript">
        document.getElementById("backButton").onclick = function() { location.href = "{backUrl}"; };
        document.getElementById("sendButton").onclick = function() { location.href = "{sendUrl}"; };
      </script>
      <hr/>
    ');
    foreach ($row as $key=>$val) $this->insertTemplateVar($key, $val);

    global $PAYMENT_GATEWAY;
    $this->insertTemplateVar('backUrl', sprintf($PAYMENT_GATEWAY['backUrl'], $this->_app->session->getId()), false);
    $this->insertTemplateVar('sendUrl', sprintf('%s://%s%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['SERVER_NAME'], $_SERVER['SCRIPT_NAME']) . '?action=eDeminimisGateway&sessid='.$this->_app->session->getId(), false);
  }
}

?>
