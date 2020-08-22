<?php

class UserReportValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('name', false, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('type'));
    $this->addValidatorVar(new ValidatorVar('registrationFrom', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('registrationTo', false, new ValidatorTypeDate));
    
    $this->addValidatorVar(new ValidatorVar('showAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('visibleColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelColumn'));
    $this->addValidatorVar(new ValidatorVarArray('labelAdditional'));
    $this->addValidatorVar(new ValidatorVarArray('groupColumn'));
    $this->addValidatorVar(new ValidatorVarArray('groupValue'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.report_user_name'));
    $this->getVar('registrationFrom')->setLabel($app->textStorage->getText('label.report_user_registrationFrom'));
    $this->getVar('registrationTo')->setLabel($app->textStorage->getText('label.report_user_registrationTo'));
  }
  
  public function loadData() {
    $app = Application::get();
    
    $visible = array('lastname','firstname','email','registration_timestamp');
    
    $label = array();
    global $REPORT_COLUMNS;     
    foreach ($REPORT_COLUMNS['user'] as $i=>$val) {
      $label[$val] = $app->textStorage->getText('label.report_user_resultCol_'.$val);
    }

    if ($app->auth->getActualProvider()&&(BCustomer::getProviderSettings($app->auth->getActualProvider(),'userSubaccount')!='Y')) unset($label['parent_user']);
    
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
    
    $this->setValues(array('providerId'=>$app->auth->isAdministrator()?null:$app->auth->getActualProvider(),
                           'visibleColumn'=>$visible,'labelColumn'=>$label,
                           'labelAdditional'=>$labelAdditional,'groupColumn'=>array()));
  }
}

?>
