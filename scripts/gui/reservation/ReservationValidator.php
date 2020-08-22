<?php

class ReservationValidator extends Validator {

  protected function _insert() {
    $app = Application::get();
    
    $this->addValidatorVar(new ValidatorVar('fromSubstitute'));

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('number'));
    $this->addValidatorVar(new ValidatorVar('mandatory'));
    $this->addValidatorVar(new ValidatorVar('created'));
    $this->addValidatorVar(new ValidatorVar('failed'));
    $this->addValidatorVar(new ValidatorVar('cancelled'));
    $this->addValidatorVar(new ValidatorVar('payed'));
    $this->addValidatorVar(new ValidatorVar('payedTicket'));
    $this->addValidatorVar(new ValidatorVar('userId'));
    $this->addValidatorVar(new ValidatorVar('userName'));
    $this->addValidatorVar(new ValidatorVar('userNameSelected'));
    $this->addValidatorVar(new ValidatorVar('userEmail'));
    $this->addValidatorVar(new ValidatorVar('customerId'));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('customerName'));
    $this->addValidatorVar(new ValidatorVar('customerEmail'));
    $this->addValidatorVar(new ValidatorVar('commodity'));
    
    $this->addValidatorVar(new ValidatorVar('resourceId'));
    $this->addValidatorVar(new ValidatorVar('resourceName'));
    $this->addValidatorVar(new ValidatorVar('resourceDescription'));
    $this->addValidatorVar(new ValidatorVar('resourcePrice'));
    $this->addValidatorVar(new ValidatorVar('resourceUnit'));
    $this->addValidatorVar(new ValidatorVar('resourceFrom', false, new ValidatorTypeDateTime));
    $this->addValidatorVar(new ValidatorVar('resourceTo', false, new ValidatorTypeDateTime));
    
    $this->addValidatorVar(new ValidatorVar('eventId'));
    $this->addValidatorVar(new ValidatorVar('eventName'));
    $this->addValidatorVar(new ValidatorVar('eventDescription'));
    $this->addValidatorVar(new ValidatorVar('eventPlaces', false, new ValidatorTypeInteger(100)));
    $this->addValidatorVar(new ValidatorVar('eventCoAttendees'));
    $this->addValidatorVar(new ValidatorVar('eventPack'));
    $this->addValidatorVar(new ValidatorVar('eventPackId'));
    $this->addValidatorVar(new ValidatorVar('eventPackStart'));
    $this->addValidatorVar(new ValidatorVar('eventPackFailed'));
    $this->addValidatorVar(new ValidatorVar('eventPrice'));
    $this->addValidatorVar(new ValidatorVar('eventRepeatPrice'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeePersonId'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeUser'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeFirstname'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeLastname'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeEmail'));
    $this->addValidatorVar(new ValidatorVarArray('eventAttendeeObsolete'));
    
    $this->addValidatorVar(new ValidatorVarArray('attribute'));
    
    $this->addValidatorVar(new ValidatorVar('note'));
    $this->addValidatorVar(new ValidatorVar('journal'));
    
    $this->addValidatorVar(new ValidatorVar('price', false, new ValidatorTypeNumber(10,2)));
    $this->addValidatorVar(new ValidatorVar('priceComment'));
    $this->addValidatorVar(new ValidatorVar('priceManual'));
    $this->addValidatorVar(new ValidatorVar('priceNew'));

    $this->addValidatorVar(new ValidatorVar('voucher'));
    $this->addValidatorVar(new ValidatorVar('voucherCode'));
    $this->addValidatorVar(new ValidatorVar('voucherDiscount'));
    $this->addValidatorVar(new ValidatorVar('voucherDiscountType'));
    $this->addValidatorVar(new ValidatorVar('voucherDiscountValue'));
    
    $this->addValidatorVar(new ValidatorVar('confirmCancel'));
    $this->addValidatorVar(new ValidatorVar('cancelNote'));
    $this->addValidatorVar(new ValidatorVar('openOnlinepayment'));
    $this->addValidatorVar(new ValidatorVar('confirmOnlinepayment'));
    
    $this->addValidatorVar(new ValidatorVar('confirmPast'));
    $this->addValidatorVar(new ValidatorVar('pay'));
    $this->addValidatorVar(new ValidatorVar('payType'));
    $this->addValidatorVar(new ValidatorVar('payTicket'));
    $this->addValidatorVar(new ValidatorVar('payArrangeCredit'));
    $this->addValidatorVar(new ValidatorVar('payArrangeCreditAmount'));
    $this->addValidatorVar(new ValidatorVar('skipCondition'));
    
    $this->getVar('userId')->setLabel($app->textStorage->getText('label.editReservation_user'));
    $this->getVar('resourceId')->setLabel($app->textStorage->getText('label.editReservation_resource'));
    $this->getVar('resourceFrom')->setLabel($app->textStorage->getText('label.editReservation_resourceFrom'));
    $this->getVar('resourceTo')->setLabel($app->textStorage->getText('label.editReservation_resourceTo'));
    $this->getVar('eventId')->setLabel($app->textStorage->getText('label.editReservation_event'));
    $this->getVar('eventPlaces')->setLabel($app->textStorage->getText('label.editReservation_eventPlaces'));
    $this->getVar('price')->setLabel($app->textStorage->getText('label.editReservation_price'));
  }

  public function loadData($id) {
    $app = Application::get();
    
    $bReservation = new BReservation($id);
    $data = $bReservation->getData();
    
    // kvuli vybirani uzivatele z combogridu
    $data['userNameSelected'] = ifsetor($data['userName'],null);
    
    if (isset($data['created'])&&$data['created']) $data['created'] = $app->regionalSettings->convertDateTimeToHuman($data['created']);
    if (isset($data['failed'])&&$data['failed']) $data['failed'] = $app->regionalSettings->convertDateTimeToHuman($data['failed']);
    if (isset($data['cancelled'])&&$data['cancelled']) $data['cancelled'] = $app->regionalSettings->convertDateTimeToHuman($data['cancelled']);
    if (isset($data['payed'])&&$data['payed']) $data['payed'] = $app->regionalSettings->convertDateTimeToHuman($data['payed']);
    
    if (isset($data['resourceFrom'])&&$data['resourceFrom']) $data['resourceFrom'] = $app->regionalSettings->convertDateTimeToHuman($data['resourceFrom']);
    if (isset($data['resourceTo'])&&$data['resourceTo']) $data['resourceTo'] = $app->regionalSettings->convertDateTimeToHuman($data['resourceTo']);
    
    if (isset($data['eventId'])) $data['commodity'] = 'event';
    elseif (isset($data['resourceId'])) $data['commodity'] = 'resource';
    
    if (isset($data['eventAttendeePerson'])) {
      $subaccount =  BCustomer::getProviderSettings($data['providerId'], 'userSubaccount')=='Y';
      foreach ($data['eventAttendeePerson'] as $person) {
        $data['eventAttendeePersonId'][] = $person['id'];
        $data['eventAttendeeUser'][] = $person['user'];
        $data['eventAttendeeFirstname'][] = $person['firstname'];
        $data['eventAttendeeLastname'][] = $person['lastname'];
        $data['eventAttendeeEmail'][] = $person['email'];
        // kdyz se behem provozu prepne na poducty (nebo naopak), je potreba zajistit aby se zobrazovali ucastnici
        // i u rezervaci, ktere nemaji vyplnene ucastniky podle nastaveni
        $obsoleteAttendee = '';
        if ($subaccount) {
          if (!$person['user']) {
            $obsoleteAttendee = sprintf('%s %s', $person['firstname'], $person['lastname']);
            if ($person['email']) $obsoleteAttendee .= sprintf(' (%s)', $person['email']);
          }
        } else {
          if (!$person['lastname']) {
            $obsoleteAttendee = sprintf('%s %s', $person['userFirstname'], $person['userLastname']);
            if ($person['userEmail']) $obsoleteAttendee .= sprintf(' (%s)', $person['userEmail']);
          }
        }

        $data['eventAttendeeObsolete'][] = $obsoleteAttendee;
      }
    }
    
    if (isset($data['priceUser'])&&$data['priceUser']) {
      $data['priceManual'] = '1';
    }
    
    foreach ($data['attribute'] as $id=>$attr) {
      switch ($attr['type']) {
        case 'NUMBER':
                     $data['attribute'][$id] = $app->regionalSettings->convertNumberToHuman($attr['value']);
                     break;
        case 'DECIMALNUMBER':
                     $data['attribute'][$id] = $app->regionalSettings->convertNumberToHuman($attr['value'],2);
                     break;
        case 'TIME': $data['attribute'][$id] = $app->regionalSettings->convertTimeToHuman($attr['value'],'h:m');
                     break;
        case 'DATETIME':
                     $data['attribute'][$id] = $app->regionalSettings->convertDateTimeToHuman($attr['value']);
                     break;
        case 'DATE': $data['attribute'][$id] = $app->regionalSettings->convertDateToHuman($attr['value']);
                     break;
        default: $data['attribute'][$id] = $attr['value'];
      }
    }
    
    $this->setValues($data);
  }
}

?>
