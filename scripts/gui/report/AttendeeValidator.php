<?php

class AttendeeReportValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('centerId'));
    $this->addValidatorVar(new ValidatorVar('eventId'));
    $this->addValidatorVar(new ValidatorVar('pastEventId'));
    $this->addValidatorVar(new ValidatorVar('tag'));
    $this->addValidatorVar(new ValidatorVar('organiserId'));
    $this->addValidatorVar(new ValidatorVar('from', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('to', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('fulltext'));
    $this->addValidatorVar(new ValidatorVar('failed'));
  
    $this->addValidatorVar(new ValidatorVar('showAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('visibleColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('groupColumn'));
    $this->addValidatorVar(new ValidatorVarArray('groupValue'));
    
    $this->getVar('from')->setLabel($app->textStorage->getText('label.report_attendee_from'));
    $this->getVar('to')->setLabel($app->textStorage->getText('label.report_attendee_to'));
  }
  
  public function loadData() {
    $app = Application::get();
    
    $visible = array('name','start','reservation_number','person_firstname','person_lastname','person_email');
    
    $label = array();
    global $REPORT_COLUMNS;     
    foreach ($REPORT_COLUMNS['attendee'] as $i=>$val) {
      $label[$val] = $app->textStorage->getText('label.report_attendee_resultCol_'.$val);
    }

    $labelAdditional = array();
    $s = new SAttribute;
    $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
    $s->setColumnsMask(array('attribute_id','all_name'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $labelAdditional[$row['attribute_id']] = sprintf('%s %s', $app->textStorage->getText('label.report_resultCol_additionalATTENDEE'), $row['all_name']);
    }
    
    $this->setValues(array('providerId'=>$app->auth->isAdministrator()?null:$app->auth->getActualProvider(),
                           'state'=>'CREATED','visibleColumn'=>$visible,'labelColumn'=>$label,
                           'labelAdditional'=>$labelAdditional,'groupColumn'=>array()));
  }
}

?>
