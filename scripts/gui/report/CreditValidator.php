<?php

class CreditReportValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('userId'));
    $this->addValidatorVar(new ValidatorVar('from', false, new ValidatorTypeDateTime));
    $this->addValidatorVar(new ValidatorVar('to', false, new ValidatorTypeDateTime));
    
    $this->addValidatorVar(new ValidatorVar('showAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('visibleColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('groupColumn'));
    $this->addValidatorVar(new ValidatorVarArray('groupValue'));
    
    $this->getVar('from')->setLabel($app->textStorage->getText('label.report_credit_from'));
    $this->getVar('to')->setLabel($app->textStorage->getText('label.report_credit_to'));
  }
  
  public function loadData() {
    $app = Application::get();
    
    $visible = array('change_user_firstname','change_user_lastname','change_user_email','change_user_phone','change_timestamp','amount','description');
    
    $label = array();
    global $REPORT_COLUMNS;     
    foreach ($REPORT_COLUMNS['credit'] as $i=>$val) {
      $label[$val] = $app->textStorage->getText('label.report_credit_resultCol_'.$val);
    }
    
    $labelAdditional = array();
    /*$s = new SAttribute;
    $s->addOrder(new SqlStatementAsc($s->columns['category']));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
    $s->setColumnsMask(array('attribute_id','all_name'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $labelAdditional[$row['attribute_id']] = sprintf('%s %s', $app->textStorage->getText('label.report_resultCol_additional'), $row['all_name']);
    }*/
    
    $this->setValues(array('providerId'=>$app->auth->isAdministrator()?null:$app->auth->getActualProvider(),
                           'state'=>'CREATED','visibleColumn'=>$visible,'labelColumn'=>$label,
                           'labelAdditional'=>$labelAdditional,'groupColumn'=>array()));
    
    $this->setValues(array('userId'=>$app->auth->getUserId(),'from'=>date('d.m.Y 00:00'),'to'=>date('d.m.Y 23:59')));
  }
}

?>
