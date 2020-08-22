<?php

class AjaxGetResourcePrice extends AjaxAction {

  protected function _userRun() {
    if (isset($this->_params['resourceId'])&&isset($this->_params['from'])&&isset($this->_params['to'])) {
      $price = 0;
      $paymentNeeded = false;

      // umi to nacenit i vice zdroju najednou (to se pouziva v multikalendari pro POOL)
      $s = new SResource;
      $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', $this->_params['resourceId'])));
      $s->setColumnsMask(array('resource_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $bResource = new BResource($row['resource_id']);
        $singlePrice = $bResource->getPrice($this->_params['from'], $this->_params['to']);
        if (!$paymentNeeded) $paymentNeeded = $bResource->isPaymentNeeded($this->_params['from'], $singlePrice);

        $price += $singlePrice;
      }

      $this->_result['priceRaw'] = $price;
      $this->_result['price'] = $this->_app->regionalSettings->convertNumberToHuman($price, 2);
      $this->_result['paymentNeeded'] = $paymentNeeded;
      $this->_result['currency'] = $this->_app->textStorage->getText('label.currency_CZK');
    }
  }
}

?>
