<?php

class GuiEditReservationCondition extends GuiElement {

  private function _insertProviderSelect($data) {
    if (!$this->_app->auth->isAdministrator()) {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" id="fi_provider" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    } else {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'classLabel' => 'bold',
              'name' => 'providerId',
              'label' => $this->_app->textStorage->getText('label.editReservationCondition_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    }
  }
  
  private function _insertEvaluationSelect($data) {
    $ds = new HashDataSource(new DataSourceSettings, array(
            'ANY'=>$this->_app->textStorage->getText('label.editReservationCondition_evaluationANY'),
            'ALL'=>$this->_app->textStorage->getText('label.editReservationCondition_evaluationALL')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_evaluation',
            'classLabel' => 'bold',
            'name' => 'evaluation',
            'label' => $this->_app->textStorage->getText('label.editReservationCondition_evaluation'),
            'dataSource' => $ds,
            'value' => $data['evaluation'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'userTextStorage' => false)), 'fi_evaluation');
  }

  private function _insertCenterSelect($data) {
    $select = new SCenter;
    $select->setColumnsMask(array('center_id','name'));
    if (!$this->_app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    else $select->addStatement(new SqlStatementBi($select->columns['provider'], $data['providerId'], '%s=%s'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
      'id' => 'editCondition_center',
      'classInput' => 'left middle',
      'name' => 'conditionCenter',
      'showDiv' => false,
      'dataSource' => $ds,
      'firstOption' => Application::get()->textStorage->getText('label.select_all'),
      'userTextStorage' => false)), 'fi_center');
  }
  
  private function _insertCondition($data) {
    if (count($data['condition'])) {
      $template = sprintf('<tr><th>%s</th><th>&nbsp;</th></tr>', $this->_app->textStorage->getText('label.editReservationCondition_oneName'));
      $i=0;
      foreach ($data['condition'] as $index=>$condition) {
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        
        $formVariable = sprintf('<input type="hidden" name="newCondition[%d]" value="conditionId~%s;name~%s;from~%s;to~%s;'.
          'center~%s;centerMessage~%s;'.
          'firstTimeBeforeCount~%s;firstTimeBeforeUnit~%s;firstTimeBeforeMessage~%s;'.
          'lastTimeBeforeCount~%s;lastTimeBeforeUnit~%s;lastTimeBeforeMessage~%s;'.
          'afterStartEvent~%s;afterStartEventMessage~%s;'.
          'advancePaymentCount~%s;advancePaymentUnit~%s;advancePaymentMessage~%s;'.
          'cancelBeforeCount~%s;cancelBeforeUnit~%s;cancelBeforeMessage~%s;'.
          'cancelPayedBeforeCount~%s;cancelPayedBeforeUnit~%s;cancelPayedBeforeMessage~%s;'.
          'anonymousBeforeCount~%s;anonymousBeforeUnit~%s;anonymousBeforeMessage~%s;'.
          'quantity~%s;period~%s;type~%s;scope~%s;quantityMessage~%s;'.
          'otherScope~%s;'.
          'event~%s;eventExists~%s;eventPayed~%s;eventAll~%s;eventMessage~%s;'.
          'resource~%s;resourceExists~%s;resourcePayed~%s;resourceAll~%s;resourceMessage~%s;'.
          'totalQuantity~%s;totalQuantityPeriod~%s;totalQuantityType~%s;totalQuantityTag~%s;totalQuantityMessage~%s;'.
          'overlapQuantity~%s;overlapQuantityScope~%s;overlapQuantityTag~%s;overlapQuantityMessage~%s"/>',
          $index,$condition['conditionId'],ifsetor($condition['name']),ifsetor($condition['from']),ifsetor($condition['to']),
          ifsetor($condition['center']),ifsetor($condition['centerMessage']),
          ifsetor($condition['firstTimeBeforeCount']),ifsetor($condition['firstTimeBeforeUnit']),ifsetor($condition['firstTimeBeforeMessage']),
          ifsetor($condition['lastTimeBeforeCount']),ifsetor($condition['lastTimeBeforeUnit']),ifsetor($condition['lastTimeBeforeMessage']),
          ifsetor($condition['afterStartEvent']),ifsetor($condition['afterStartEventMessage']),
          ifsetor($condition['advancePaymentCount']),ifsetor($condition['advancePaymentUnit']),ifsetor($condition['advancePaymentMessage']),
          ifsetor($condition['cancelBeforeCount']),ifsetor($condition['cancelBeforeUnit']),ifsetor($condition['cancelBeforeMessage']),
          ifsetor($condition['cancelPayedBeforeCount']),ifsetor($condition['cancelPayedBeforeUnit']),ifsetor($condition['cancelPayedBeforeMessage']),
          ifsetor($condition['anonymousBeforeCount']),ifsetor($condition['anonymousBeforeUnit']),ifsetor($condition['anonymousBeforeMessage']),
          ifsetor($condition['quantity']),ifsetor($condition['period']),ifsetor($condition['type']),ifsetor($condition['scope']),ifsetor($condition['quantityMessage']),
          ifsetor($condition['otherScope']),
          ifsetor($condition['event']),ifsetor($condition['eventExists']),ifsetor($condition['eventPayed']),ifsetor($condition['eventAll']),ifsetor($condition['eventMessage']),
          ifsetor($condition['resource']),ifsetor($condition['resourceExists']),ifsetor($condition['resourcePayed']),ifsetor($condition['resourceAll']),ifsetor($condition['resourceMessage']),
          ifsetor($condition['totalQuantity']),ifsetor($condition['totalQuantityPeriod']),ifsetor($condition['totalQuantityType']),ifsetor($condition['totalQuantityTag']),ifsetor($condition['totalQuantityMessage']),
          ifsetor($condition['overlapQuantity']),ifsetor($condition['overlapQuantityScope']),ifsetor($condition['overlapQuantityTag']),ifsetor($condition['overlapQuantityMessage'])
        );
        $template .= sprintf('<tr class="%s" id="%d" db_id="%d"><td>%s</td><td>[<a href="#" id="fi_conditionEdit">%s</a>][<a href="#" id="fi_conditionRemove">%s</a>]</td>%s</tr>',
                             $class, $index, $condition['conditionId'], $condition['name'], 
                             $this->_app->textStorage->getText('button.grid_edit'),
                             $this->_app->textStorage->getText('button.grid_remove'),
                             $formVariable);
      }
      
      $this->insertTemplateVar('fi_condition', $template, false);
    } else $this->insertTemplateVar('fi_condition', '');
  }
  
  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ReservationConditionEdit.html');

    $validator = Validator::get('reservationCondition', 'ReservationConditionValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editReservationCondition_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editReservationCondition_titleExisting'));
    }

    if (BCustomer::getProviderSettings($this->_app->auth->getActualProvider(), 'userSubaccount')=='Y') {
      $this->insertTemplateVar('selectOption_attendee', sprintf('<option value="ATTENDEE">%s</option>',
        $this->_app->textStorage->getText('label.editReservationCondition_conditionScope_ATTENDEE')), false);
    } else $this->insertTemplateVar('selectOption_attendee', '');
    
    $this->_insertProviderSelect($data);
    $this->_insertEvaluationSelect($data);
    $this->_insertCondition($data);
    $this->_insertCenterSelect($data);
    
    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/ReservationConditionEdit.js',
                                                     array('url'=>$AJAX['adminUrl']));
  }
}

?>
