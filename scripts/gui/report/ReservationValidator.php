<?php

class ReservationReportValidator extends Validator {

  protected function _insert() {
    $app = Application::get();
  
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('centerId'));
    $this->addValidatorVar(new ValidatorVar('resourceId'));
    $this->addValidatorVar(new ValidatorVar('eventId'));
    $this->addValidatorVar(new ValidatorVar('pastEventId'));
    $this->addValidatorVar(new ValidatorVar('mandatory'));
    $this->addValidatorVar(new ValidatorVar('tag'));
    $this->addValidatorVar(new ValidatorVar('state'));
    $this->addValidatorVar(new ValidatorVar('payed'));
    $this->addValidatorVar(new ValidatorVar('paymentType'));
    $this->addValidatorVar(new ValidatorVar('priceManual'));
    $this->addValidatorVar(new ValidatorVar('accountTypeId'));
    $this->addValidatorVar(new ValidatorVar('from', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('to', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('realiseFrom', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('realiseTo', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('cancelledFrom', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('cancelledTo', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('fulltext'));
    
    $this->addValidatorVar(new ValidatorVar('showAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('visibleColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('groupColumn'));
    $this->addValidatorVar(new ValidatorVarArray('groupValue'));
    
    $this->getVar('from')->setLabel($app->textStorage->getText('label.report_reservation_from'));
    $this->getVar('to')->setLabel($app->textStorage->getText('label.report_reservation_to'));
    $this->getVar('realiseFrom')->setLabel($app->textStorage->getText('label.report_reservation_realiseFrom'));
    $this->getVar('realiseTo')->setLabel($app->textStorage->getText('label.report_reservation_realiseTo'));
    $this->getVar('cancelledFrom')->setLabel($app->textStorage->getText('label.report_reservation_cancelledFrom'));
    $this->getVar('cancelledTo')->setLabel($app->textStorage->getText('label.report_reservation_cancelledTo'));
  }
  
  public function loadData() {
    $app = Application::get();
    
    $visible = array('number','created','total_price','payed','user_firstname','user_lastname','user_email','mixed_resource_name','event_name');
    
    $label = array();
    global $REPORT_COLUMNS;     
    foreach ($REPORT_COLUMNS['reservation'] as $i=>$val) {
      $label[$val] = $app->textStorage->getText('label.report_reservation_resultCol_'.$val);
    }
    
    $labelAdditional = array();
    $s = new SAttribute;
    $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
    $s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','all_name'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $labelAdditional[$row['attribute_id']] = sprintf('%s %s', $app->textStorage->getText('label.report_resultCol_additionalUSER'), $row['all_name']);
    }
    $s = new SAttribute;
    $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
    $s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','all_name'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $labelAdditional[$row['attribute_id']] = sprintf('%s %s', $app->textStorage->getText('label.report_resultCol_additionalRESERVATION'), $row['all_name']);
    }
    
    $this->setValues(array('providerId'=>$app->auth->isAdministrator()?null:$app->auth->getActualProvider(),
                           'visibleColumn'=>$visible,'labelColumn'=>$label,
                           'labelAdditional'=>$labelAdditional,'groupColumn'=>array()));
  }
}

?>
