<?php

class GuiCustomerInvoice extends GuiElement {
  private $_params;

  private $_css = '<style>
      div.a4 { width: 750px; }
      div.a4 hr { margin: 0; }
      div.headline { overflow: hidden; }
      div.headline div { float: left; font-size: 0.8em; }
      div.headline div.left { width: 250px; }
      div.headline div.center { width: 250px; }
      div.headline div.right { width: 150px; }
      div.headline div.court { clear: left; }
      div.head { font-size: 0.8em; overflow: hidden; }
      div.head span.important { background-color: lightgray; font-weight: bold; }
      div.head div.left { float: left; width: 49%; overflow: hidden; }
      div.head div.left div.title { font-size: 1.1em; font-weight: bold; }
      div.head div.left div.logo { font-size: 1.5em; font-weight: bold; margin: 20px 0;}
      div.head div.left div.date { float: left; width: 50%; }
      div.head div.left div.symbol { float: left; width: 50%; }
      div.head div.left div.bank { clear: left; float: left; width: 100%; margin-top: 5px; overflow: hidden; }
      div.head div.left div.bank div.account { float: left; width: 60%; }
      div.head div.left div.bank div.suffix { float: left; width: 36%; margin-left: 4px; }
      div.head div.left div.bank div.border { text-align: center; font-weight: bold; border: 2px solid black; padding: 2px; }
      div.head div.right { float: left; width: 50%; border-left: 1px solid black; overflow: hidden; padding-left: 5px; }
      div.head div.right div.number { float: right; width: 50%; font-size: 1.4em; font-weight: bold; text-align: right; }
      div.head div.right div.customer { float: left; margin-top: 30px; width: 100%; }
      div.head div.right div.customer div.customer_name { font-size: 1.3em; font-weight: bold; margin-top: 10px; }
      div.head div.right div.customer div.customer_address { font-size: 1.1em; font-weight: bold; margin-top: 10px; }
      div.head div.right div.customer div.customer_ico { font-size: 1.1em; font-weight: bold; margin-top: 10px; }
      div.head div.info { clear: left; font-size: 0.7em; font-style: italic; }
      div.head div.itemTitle { margin-top: 20px; font-weight: bold; }
      div.item table { margin-top: 20px; font-size: 0.8em; }
      div.item table th { border-bottom: 1px solid black; }
      div.item table th.name { width: 250px; }
      div.item table th.price { width: 90px; text-align: right; }
      div.item table td.center { text-align: center; }
      div.item table td.price { text-align: right; }
      div.item table td { border-bottom: 1px dotted black; }
      div.summary { margin-top: 50px; }
      div.summary div.sum { margin-right: 30px; width: 50%; float: right; font-weight: bold; }
      div.summary div.sum div { width: 50%; float: left; }
      div.summary div.sum div.value { text-align: right; }
      div.summary div.sum div.important { background-color: lightgray; }
      div.summary div.qrcode { margin-top: 0; width: 30%; float: left; }
      div.signature { width: 30%; float: right; margin-top: 40px; }
      div.signature div.description { margin-left: 30px; font-size: 0.6em; }
    </style>';

  private $_invoiceHeaderTemplate = '<div class="head">
        <div class="left">
          <div class="title">Faktura - daňový doklad</div>
          <div class="logo">FLEXBOOK</div>
          <div class="payment">
            <b>Platba:</b> převodem
          </div>
          <div class="date">
            <b>Datum</b><br/>
            vystavení:  {createDate}<br/>
            splatnosti: <span class="important">{dueDate}</span><br/>
            zd. plnění: {accountDate}<br/>
          </div>
          <div class="symbol">
            <b>Symbol</b><br/>
            konstatní: 0308<br/>
            variabilní: <span class="important">{vs}</span><br/>
            specifický:<br/>
          </div>
          <div class="bank">
            <div class="account">
              <div class="important"><span class="important">Bankovní účet</span></div>
              <div class="border">115-5335760207</div>
            </div>
            <div class="suffix">
              <div class="important"><span class="important">KB</span></div>
              <div class="border">0100</div>
            </div>
          </div>
        </div>
        <div class="right">
          <div class="number"><span class="important">{number}</span></div>
          <div class="customer">
            Odběratel
            <div class="customer_name">{name}</div>
            <div class="customer_address">
              {street}<br/>
              {postalCode} {city}<br/>
              {state}<br/>
            </div>
            <div class="customer_ico">
              IČ:   {ic}<br/>
              DIČ:  {dic}<br/>
            </div>
          </div>
        </div>
        <div class="itemTitle">Fakturujeme Vám:</div>
      </div>';
  private $_invoiceSummaryTemplate = '<div class="summary">
        <div class="sum">
          <div class="label">Základ daně (DPH):</div><div class="value">{totalAmount} Kč</div>
          <div class="label">Sazba daně:</div><div class="value">0&nbsp;&nbsp;%</div>
          <div class="label">Celkem DPH:</div><div class="value">0 Kč</div>
          <div class="label">Celkem k ůhradě:</div><div class="value important">{totalAmount} Kč</div>
        </div>
        <div class="qrcode">
          <img src="http://api.paylibo.com/paylibo/generator/czech/image?accountPrefix=115&accountNumber=5335760207&bankCode=0100&amount={totalAmountRaw}&currency=CZK&vs={vs}&date={dueDateRaw}&message=FLEXBOOK%20{period}&size=100"/>
        </div>
      </div>';

  private $_overPaymentHeaderTemplate = '<div class="head">
        <div class="itemTitle">Variabilní symbol: <span class="important">{vs}</span></div>
        <div class="itemTitle">Vyúčtování služeb:</div>
       </div>';
  private $_overPaymentSummaryTemplate = '<div class="summary">
        <div class="sum">
          <div class="label">Celkem přeplatek:</div><div class="value important">{totalAmount} Kč</div>
        </div>
      </div>';

  private $_template = ' 
    <div class="a4">
      <div class="headline">
        <div class="left">
          <b>Flexbook s.r.o.</b><br/>
          Krásova 1027/9<br/>
          13000 Praha 3 Žižkov<br/>
          Česká republika<br/>
        </div>
        <div class="center">
           IČ: 69207603<br/>
          DIČ: CZ69207603 (nejsme plátci DPH)<br/>
        </div>
        <div class="right">
          mobil: +420 724 334 594<br/>
          www:   www.flexbook.cz<br/>
          email: info@flexbook.cz<br/>
        </div>
        <div class="court">Zápis u Městského soudu v Praze, v oddílu C, vložce c.34556.</div>
      </div>
      <hr/>
      %s
      <div class="item">
        <table>
          <tr><th class="name">Služba</th><th>Počet m.j.</th><th class="price">Cena za m.j.</th><th>Sazba</th><th class="price">Základ</th><th>DPH</th><th class="price">Celkem</th></tr>
          {item}
        </table>
      </div>
      %s
      <div class="signature">
        <img src="https://www.flexbook.cz/img/razitko.png"/>
        <div class="description">razítko a podpis</div>
      </div>
    </div>
    <div class="cleaner">&nbsp;</div>
    ';

  public function __construct($params) {
    parent::__construct(array());

    $this->_params = $params;

    $this->_params['createDate'] = $this->_app->regionalSettings->convertDateToHuman($this->_params['createDate']);
    $this->_params['accountDate'] = $this->_app->regionalSettings->convertDateToHuman($this->_params['accountDate']);
    $this->_params['dueDateRaw'] = $this->_app->regionalSettings->decreaseDate($this->_params['dueDate'],2);
    $this->_params['dueDate'] = $this->_app->regionalSettings->convertDateToHuman($this->_params['dueDate']);
    $this->_params['totalAmountRaw'] = $this->_params['totalAmount'];
    $this->_params['totalAmount'] = $this->_app->regionalSettings->convertNumberToHuman($this->_params['totalAmount'],2);
  }

  protected function _userRender() {
    $template = $this->_css;
    if ($this->_params['totalAmount']>0) $template .= sprintf($this->_template, $this->_invoiceHeaderTemplate, $this->_invoiceSummaryTemplate);
    else $template .= sprintf($this->_template, $this->_overPaymentHeaderTemplate, $this->_overPaymentSummaryTemplate);

    $this->setTemplateString($template);

    foreach ($this->_params as $key=>$value) {
      $this->insertTemplateVar($key, $value, false);
    }
    
    foreach ($this->_params['item'] as $item) {
      $itemLine = sprintf('<tr><td class="name">%s</td><td class="center">%s</td><td class="price">%s</td><td class="center">0 %%</td><td class="price">%s</td><td class="price">0,00</td><td class="price">%s</td></tr>',
        $item['label'], $item['count'], $this->_app->regionalSettings->convertNumberToHuman($item['price'],2),
        $this->_app->regionalSettings->convertNumberToHuman($item['price'],2),
        $this->_app->regionalSettings->convertNumberToHuman($item['count']*$item['price'],2));
      $this->insertTemplateVar('item', $itemLine, false);
    }
  }
}

?>
