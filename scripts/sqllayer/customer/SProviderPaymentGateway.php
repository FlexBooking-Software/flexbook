<?php

class SProviderPaymentGateway extends SqlSelect {
  private $_tProviderPaymentGateway;
  
  private function _insertProviderPaymentGatewayTable() {
    $this->_tProviderPaymentGateway = new SqlTable('providerpaymentgateway', 'ppgw');

    $this->addColumn(new SqlColumn($this->_tProviderPaymentGateway, 'providerpaymentgateway_id'));
    $this->addColumn(new SqlColumn($this->_tProviderPaymentGateway, 'provider'));
    $this->addColumn(new SqlColumn($this->_tProviderPaymentGateway, 'gateway_name'));
    $this->addColumn(new SqlColumn($this->_tProviderPaymentGateway, 'active'));
    $this->addColumn(new SqlColumn($this->_tProviderPaymentGateway, 'gateway_params'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertProviderPaymentGatewayTable();
  }
}

?>
