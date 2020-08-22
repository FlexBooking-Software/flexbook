<?php

class ModuleReservationCancelEventPackItem extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $eventPackItem = $this->_app->request->getParams('eventPackItem');
      
      $bReservation = new BReservation($id);
      $bReservation->cancelEventPackItem($eventPackItem);
      $bData = $bReservation->getData();
      
      $o = new OEvent($eventPackItem);
      $oData = $o->getData();
      $cancelledEventDateTime = $this->_app->regionalSettings->convertDateTimeToHuman($oData['start']);
      
      // kdyz je inicializovany validator rezervace, tak tam aktualizuju data
      $validator = Validator::get('reservation','ReservationValidator');
      $validator->initValues();
      $valData = $validator->getValues();
      if ($valData['eventPackId']) {
        foreach ($valData['eventPackId'] as $index=>$id) {
          if ($id==$eventPackItem) {
            unset($valData['eventPackId'][$index]);
            unset($valData['eventPackStart'][$id]);
            
            break;
          }
        }
        $validator->setValues(array('price'=>$bData['price'],'priceManual'=>1,'priceComment'=>$bData['priceComment'],
                                    'eventPackId'=>$valData['eventPackId'],'eventPackStart'=>$valData['eventPackStart']));
      }
      
      if ($bData['payed']) $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editReservation_cancelRefundEventPackItem'), $cancelledEventDateTime));
      else $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editReservation_cancelEventPackItem'), $cancelledEventDateTime));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
