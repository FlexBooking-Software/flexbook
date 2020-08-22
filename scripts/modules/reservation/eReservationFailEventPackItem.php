<?php

class ModuleReservationFailEventPackItem extends ExecModule {

  protected function _userRun() {
    if ($id = $this->_app->request->getParams('id')) {
      $eventPackItem = $this->_app->request->getParams('eventPackItem');
      
      $bReservation = new BReservation($id);
      $bReservation->failEventPackItem($eventPackItem);
      
      $o = new OEvent($eventPackItem);
      $oData = $o->getData();
      $failedEventDateTime = $this->_app->regionalSettings->convertDateTimeToHuman($oData['start']);
      
      // kdyz je inicializovany validator rezervace, tak tam aktualizuju data
      $validator = Validator::get('reservation','ReservationValidator');
      $validator->initValues();
      $valData = $validator->getValues();
      if ($valData['eventPackId']) {
        foreach ($valData['eventPackId'] as $id) {
          if ($id==$eventPackItem) {
            $valData['eventPackFailed'][$id] = date('Y-m-d H:i:s');
            
            break;
          }
        }
        $validator->setValues(array('eventPackFailed'=>$valData['eventPackFailed']));
      }
      
      $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editReservation_failEventPackItem'), $failedEventDateTime));
    }

    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
