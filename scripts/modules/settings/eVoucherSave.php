<?php

class ModuleVoucherSave extends ExecModule {
  
  protected function _userRun() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $validator = Validator::get('voucher','VoucherValidator');
    $validator->initValues();
    
    $validator->validateValues();
    $valData = $validator->getValues();

    if ($valData['discountType']=='PROPORTION') {
      if (!$valData['discountProportion']) throw new ExceptionUserTextStorage('error.editVoucher_missingDiscount');
    } elseif ($valData['discountType']=='AMOUNT') {
      if (!$valData['discountAmount']) throw new ExceptionUserTextStorage('error.editVoucher_missingDiscount');
    }

    $id = $validator->getVarValue('id');
    
    $bVoucher = new BVoucher($id?$id:null);
    $bVoucher->save(array(
      'name'                  => $valData['name'],
      'code'                  => $valData['code'],
      'providerId'            => $valData['providerId'],
      'validityFrom'          => $valData['validityFrom']?$this->_app->regionalSettings->convertHumanToDate($valData['validityFrom']):'',
      'validityTo'            => $valData['validityTo']?$this->_app->regionalSettings->convertHumanToDate($valData['validityTo']):'',
      'center'                => $valData['center'],
      'subjectTag'            => $valData['subjectTag'],
      'applicationTotal'      => $valData['applicationTotal'],
      'applicationUser'       => $valData['applicationUser'],
      'discountAmount'        => $valData['discountAmount'],
      'discountProportion'    => $valData['discountProportion'],
      'active'                => $valData['active'],
    ));

    $this->_app->messages->addMessage('userInfo', sprintf($this->_app->textStorage->getText('info.editVoucher_saveOk'), $valData['name']));

    return 'eBack';
  }
}

?>
