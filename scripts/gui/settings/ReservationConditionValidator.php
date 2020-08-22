<?php

class  ReservationConditionValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('evaluation', true));
    $this->addValidatorVar(new ValidatorVar('description'));
    $this->addValidatorVar(new ValidatorVar('condition'));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editReservationCondition_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editReservationCondition_provider'));
    $this->getVar('evaluation')->setLabel($app->textStorage->getText('label.editReservationCondition_evaluation'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bReservationCondition = new BReservationCondition($id);
    $data = $bReservationCondition->getData();
    
    $data['condition'] = array();
    foreach ($data['item'] as $key=>$value) {
      
      $from = $app->regionalSettings->convertDateTimeToHuman($value['timeFrom']);
      $to = $app->regionalSettings->convertDateTimeToHuman($value['timeTo']);
      $firstTimeUnit = 'min'; $firstTimeCount = $value['limitFirstTimeBeforeStart'];
      if ($firstTimeCount&&($firstTimeCount%1440 === 0)) { $firstTimeCount = $firstTimeCount/1440; $firstTimeUnit = 'day'; }
      elseif ($firstTimeCount&&($firstTimeCount%60 === 0)) { $firstTimeCount = $firstTimeCount/60; $firstTimeUnit = 'hour'; }
      $lastTimeUnit = 'min'; $lastTimeCount = $value['limitLastTimeBeforeStart'];
      if ($lastTimeCount&&($lastTimeCount%1440 === 0)) { $lastTimeCount = $lastTimeCount/1440; $lastTimeUnit = 'day'; }
      elseif ($lastTimeCount&&($lastTimeCount%60 === 0)) { $lastTimeCount = $lastTimeCount/60; $lastTimeUnit = 'hour'; }
      $advancePaymentUnit = 'min'; $advancePaymentCount = $value['advancePayment'];
      if ($advancePaymentCount&&($advancePaymentCount%1440 === 0)) { $advancePaymentCount = $advancePaymentCount/1440; $advancePaymentUnit = 'day'; }
      elseif ($advancePaymentCount&&($advancePaymentCount%60 === 0)) { $advancePaymentCount = $advancePaymentCount/60; $advancePaymentUnit = 'hour'; }
      $cancelBeforeUnit = 'min'; $cancelBeforeCount = $value['cancelBefore'];
      if ($cancelBeforeCount&&($cancelBeforeCount%1440 === 0)) { $cancelBeforeCount = $cancelBeforeCount/1440; $cancelBeforeUnit = 'day'; }
      elseif ($cancelBeforeCount&&($cancelBeforeCount%60 === 0)) { $cancelBeforeCount = $cancelBeforeCount/60; $cancelBeforeUnit = 'hour'; }
      $cancelPayedBeforeUnit = 'min'; $cancelPayedBeforeCount = $value['cancelPayedBefore'];
      if ($cancelPayedBeforeCount&&($cancelPayedBeforeCount%1440 === 0)) { $cancelPayedBeforeCount = $cancelPayedBeforeCount/1440; $cancelPayedBeforeUnit = 'day'; }
      elseif ($cancelPayedBeforeCount&&($cancelPayedBeforeCount%60 === 0)) { $cancelPayedBeforeCount = $cancelPayedBeforeCount/60; $cancelPayedBeforeUnit = 'hour'; }
      $anonymousUnit = 'min'; $anonymousCount = $value['limitAnonymousBeforeStart'];
      if ($anonymousCount&&($anonymousCount%1440 === 0)) { $anonymousCount = $anonymousCount/1440; $anonymousUnit = 'day'; }
      elseif ($anonymousCount&&($anonymousCount%60 === 0)) { $anonymousCount = $anonymousCount/60; $anonymousUnit = 'hour'; }
      
      $data['condition'][$key] = array(
        'conditionId'=>$value['itemId'],'name'=>$value['name'],
        'from'=>$from,'to'=>$to,
        'center'=>$value['limitCenter'],
        'centerMessage'=>$value['limitCenterMessage'],
        'quantity'=>$value['limitQuantity'],'period'=>$value['limitQuantityPeriod'],'type'=>$value['limitQuantityType'],'scope'=>$value['limitQuantityScope'],
        'quantityMessage'=>$value['limitQuantityMessage'],
        'otherScope'=>$value['limitOtherScope'],
        'totalQuantity'=>$value['limitTotalQuantity'],'totalQuantityPeriod'=>$value['limitTotalQuantityPeriod'],'totalQuantityType'=>$value['limitTotalQuantityType'],
        'totalQuantityTag'=>$value['limitTotalQuantityTag'],'totalQuantityMessage'=>$value['limitTotalQuantityMessage'],
        'overlapQuantity'=>$value['limitOverlapQuantity'],'overlapQuantityScope'=>$value['limitOverlapQuantityScope'],'overlapQuantityTag'=>$value['limitOverlapQuantityTag'],
        'overlapQuantityMessage'=>$value['limitOverlapQuantityMessage'],
        'firstTimeBeforeCount'=>$firstTimeCount,'firstTimeBeforeUnit'=>$firstTimeUnit,
        'firstTimeBeforeMessage'=>$value['limitFirstTimeBeforeMessage'],
        'lastTimeBeforeCount'=>$lastTimeCount,'lastTimeBeforeUnit'=>$lastTimeUnit,
        'lastTimeBeforeMessage'=>$value['limitLastTimeBeforeMessage'],
        'afterStartEvent'=>$value['limitAfterStartEvent'],
        'afterStartEventMessage'=>$value['limitAfterStartEventMessage'],
        'advancePaymentCount'=>$advancePaymentCount,'advancePaymentUnit'=>$advancePaymentUnit,
        'advancePaymentMessage'=>$value['advancePaymentMessage'],
        'cancelBeforeCount'=>$cancelBeforeCount,'cancelBeforeUnit'=>$cancelBeforeUnit,
        'cancelBeforeMessage'=>$value['cancelBeforeMessage'],
        'cancelPayedBeforeCount'=>$cancelPayedBeforeCount,'cancelPayedBeforeUnit'=>$cancelPayedBeforeUnit,
        'cancelPayedBeforeMessage'=>$value['cancelPayedBeforeMessage'],
        'anonymousBeforeCount'=>$anonymousCount,'anonymousBeforeUnit'=>$anonymousUnit,
        'anonymousBeforeMessage'=>$value['limitAnonymousBeforeMessage'],
        'event'=>$value['requiredEvent'],'eventExists'=>$value['requiredEventExists'],'eventPayed'=>$value['requiredEventPayed'],'eventAll'=>$value['requiredEventAll'],
        'eventMessage'=>$value['requiredEventMessage'],
        'resource'=>$value['requiredResource'],'resourceExists'=>$value['requiredResourceExists'],'resourcePayed'=>$value['requiredResourcePayed'],'resourceAll'=>$value['requiredResourceAll'],
        'resourceMessage'=>$value['requiredResourceMessage']
      );
    }
    
    $this->setValues($data);
  }
}

?>
