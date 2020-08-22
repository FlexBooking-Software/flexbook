<?php

class AjaxSaveReservation extends AjaxAction {

  protected function _userRun() {
    if ($this->_app->session->getExpired()||!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.ajax_sessionExpired');
    
    $reservation = ifsetor($this->_params['id']);

    $resources = array();
    $params = array();
    if (isset($this->_params['user'])) $params['userId'] = $this->_params['user'];
    if (isset($this->_params['resource'])&&(isset($this->_params['start'])||isset($this->_params['end']))) {
      if (!$reservation) {
        $resources = explode(',',$this->_params['resource']);
        $params['resourceParams'] = array('resourceId' => null);
      } else $params['resourceParams'] = array('resourceId' => $this->_params['resource']);
      if (isset($this->_params['start'])) $params['resourceParams']['resourceFrom'] = $this->_params['start'];
      if (isset($this->_params['end'])) $params['resourceParams']['resourceTo'] = $this->_params['end'];
    } elseif (isset($this->_params['event'])&&isset($this->_params['places'])) {
      $params['eventParams'] = array('eventId'=>$this->_params['event']);
      if (isset($this->_params['places'])) $params['eventParams']['eventPlaces'] = $this->_params['places'];
      if (isset($this->_params['pack'])) $params['eventParams']['eventPack'] = $this->_params['pack'];
      if (isset($this->_params['attendee'])) $params['eventParams']['eventAttendeePerson'] = json_decode($this->_params['attendee'], true);
    }
    if (isset($this->_params['paymentOnline'])) $params['paymentOnline'] = $this->_params['paymentOnline'];
    if (isset($this->_params['pay'])) {
      $params['pay'] = $this->_params['pay'];
      $params['payType'] = ifsetor($this->_params['payType'],'credit');
      if (isset($this->_params['payTicket'])) $params['payTicket'] = $this->_params['payTicket'];
      if (isset($this->_params['payArrangeCredit'])) $params['payArrangeCredit'] = $this->_params['payArrangeCredit'];
      if (isset($this->_params['payArrangeCreditAmount'])) $params['payArrangeCreditAmount'] = $this->_params['payArrangeCreditAmount'];
    }
    if (isset($this->_params['attribute'])) $params['attribute'] = $this->_params['attribute'];
    if (isset($this->_params['pool'])) $params['pool'] = $this->_params['pool'];
    if (isset($this->_params['note'])) $params['note'] = $this->_params['note'];
    if (isset($this->_params['skipCondition'])) $params['skipCondition'] = $this->_params['skipCondition'];
    if (isset($this->_params['mandatory'])) $params['mandatory'] = $this->_params['mandatory'];
    if (isset($this->_params['voucher'])) $params['voucher'] = $this->_params['voucher'];
    #adump($params);die;

    if (count($resources)) {
      // zdroju lze rezervovat vice najednou (ale jenom vytvaret rezervace)
      $this->_app->db->beginTransaction();

      $number = $id = '';
      foreach ($resources as $res) {
        $params['resourceParams']['resourceId'] = $res;
        $b = new BReservation;
        $new = $b->save($params);

        if ($number) {
          $number .= ',';
          $id .= ',';
        }
        $number .= $new;
        $id .= $b->getId();
      }

      $this->_app->db->commitTransaction();
    } else {
      $b = new BReservation($reservation);
      $number = $b->save($params);

      $id = $b->getId();
    }

    $this->_result = array('error'=>false,'popup'=>sprintf($this->_app->textStorage->getText('info.editReservation_saveOk'), $number), 'number'=>$number, 'id'=>$id);
  }
}

?>
