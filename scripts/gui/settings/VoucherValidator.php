<?php

class VoucherValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('code', true, new ValidatorTypeString(50)));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('validityFrom', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('validityTo', false, new ValidatorTypeDate));
    $this->addValidatorVar(new ValidatorVar('center'));
    $this->addValidatorVar(new ValidatorVar('subjectTag'));
    $this->addValidatorVar(new ValidatorVar('discountType'));
    $this->addValidatorVar(new ValidatorVar('discountAmount'));
    $this->addValidatorVar(new ValidatorVar('discountProportion'));
    $this->addValidatorVar(new ValidatorVar('applicationTotal'));
    $this->addValidatorVar(new ValidatorVar('applicationUser'));
    $this->addValidatorVar(new ValidatorVar('active'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editVoucher_name'));
    $this->getVar('code')->setLabel($app->textStorage->getText('label.editVoucher_code'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editVoucher_provider'));
    $this->getVar('validityFrom')->setLabel($app->textStorage->getText('label.editVoucher_validityFrom'));
    $this->getVar('validityTo')->setLabel($app->textStorage->getText('label.editVoucher_validityTo'));
    $this->getVar('discountAmount')->setLabel($app->textStorage->getText('label.editVoucher_discountAmount'));
    $this->getVar('discountProportion')->setLabel($app->textStorage->getText('label.editVoucher_discountProportion'));
    $this->getVar('applicationTotal')->setLabel($app->textStorage->getText('label.editVoucher_applicationTotal'));
    $this->getVar('applicationUser')->setLabel($app->textStorage->getText('label.editVoucher_applicationUser'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $b = new BVoucher($id);
    $data = $b->getData();

    $data['validityFrom'] = $app->regionalSettings->convertDateToHuman($data['validityFrom']);
    $data['validityTo'] = $app->regionalSettings->convertDateToHuman($data['validityTo']);

    if ($data['discountAmount']) $data['discountType'] = 'AMOUNT';
    else $data['discountType'] = 'PROPORTION';
   
    $this->setValues($data);
  }
}

?>
