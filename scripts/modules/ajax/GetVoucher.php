<?php

class AjaxGetVoucher extends AjaxAction {

  protected function _userRun() {
    if (!isset($this->_params['provider'])) return;

    $center = $price = $tag = null;
		if (isset($this->_params['reservationId'])) {
			$s = new SReservation;
			$s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
			$s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $this->_params['reservationId'], '%s=%s'));
			$s->setColumnsMask(array('center','total_price','all_resource_tag','all_event_tag'));
			$res = $this->_app->db->doQuery($s->toString());
			if ($row = $this->_app->db->fetchAssoc($res)) {
				$center = $row['center'];
				$price = $row['total_price'];
				$tag = $row['all_resource_tag']?$row['all_resource_tag']:$row['all_event_tag'];
				$tag = $tag?explode(',',$tag):null;
			}
		} elseif (isset($this->_params['eventId'])) {
    	$s = new SEvent;
    	$s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
			$s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_params['eventId'], '%s=%s'));
			$s->setColumnsMask(array('center','price','all_tag'));
			$res = $this->_app->db->doQuery($s->toString());
			if ($row = $this->_app->db->fetchAssoc($res)) {
				$center = $row['center'];
				$price = $row['price'];
				$tag = $row['all_tag']?explode(',',$row['all_tag']):null;
			}
			if (isset($this->_params['eventPlaces'])) $price = $price*$this->_params['eventPlaces'];
		}

    $bUser = new BUser($this->_app->auth->getUserId());
    $voucher = $bUser->getAvailableVoucher($this->_params['provider'], $price, $center, $tag);

    if ($this->_params['code']) {
			foreach ($voucher as $v) {
				if (!strcmp($v['code'],strtoupper($this->_params['code']))) {
					$this->_result = $v;

					break;
				}
			}
		}
  }
}

?>