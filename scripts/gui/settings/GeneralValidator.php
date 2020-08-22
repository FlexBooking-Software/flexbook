<?php

class SettingsValidator extends Validator {

  protected function _insert() {
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('userConfirm'));
    $this->addValidatorVar(new ValidatorVar('userSubaccount'));
    $this->addValidatorVar(new ValidatorVar('badgePhoto'));
    $this->addValidatorVar(new ValidatorVar('badgeTemplate'));
    $this->addValidatorVar(new ValidatorVar('ticketTemplate'));
    $this->addValidatorVar(new ValidatorVar('generateAccounting'));
    $this->addValidatorVar(new ValidatorVar('prepaymentInvoiceTemplate'));
    $this->addValidatorVar(new ValidatorVar('prepaymentInvoiceNumber'));
    $this->addValidatorVar(new ValidatorVar('receiptTemplate'));
    $this->addValidatorVar(new ValidatorVar('receiptNumber'));
    $this->addValidatorVar(new ValidatorVar('invoiceTemplate'));
    $this->addValidatorVar(new ValidatorVar('invoiceNumber'));
    $this->addValidatorVar(new ValidatorVar('creditnoteTemplate'));
    $this->addValidatorVar(new ValidatorVar('creditnoteNumber'));
    $this->addValidatorVar(new ValidatorVar('showCompany'));
    $this->addValidatorVar(new ValidatorVar('reservationCancelMessage'));
    $this->addValidatorVar(new ValidatorVar('allowSkipReservationCondition'));
    $this->addValidatorVar(new ValidatorVar('userReservationCondition'));
    $this->addValidatorVar(new ValidatorVar('documenttemplate'));
    $this->addValidatorVar(new ValidatorVarArray('userUnique'));
    $this->addValidatorVar(new ValidatorVarArray('subaccountUnique'));
    $this->addValidatorVar(new ValidatorVar('allowMandatoryReservation'));
    $this->addValidatorVar(new ValidatorVar('organiserMandatoryReservation'));
    $this->addValidatorVar(new ValidatorVar('organiserMandatorySubstitute'));
    $this->addValidatorVar(new ValidatorVar('allowOnlinePaymentOnly'));
   }

  public function loadData() {
    $app = Application::get();
    
    $data = array();
    
    if ($app->auth->isAdministrator()) {
      $provider = $app->request->getParams('provider');
    } else {
      $provider = $app->auth->getActualProvider();
    }
    
    if ($provider) {
      $data = BCustomer::getProviderSettings($provider);
      if (($data['disableCredit']=='Y')&&($data['disableTicket']=='Y')&&($data['disableCash']=='Y')&&($data['disableOnline']=='N')) $data['allowOnlinePaymentOnly'] = 'Y';
      else $data['allowOnlinePaymentOnly'] = 'N';
    }
    $data['providerId'] = $provider;
    
    $this->setValues($data);
  }
}

?>
