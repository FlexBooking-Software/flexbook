<?php

class PriceListValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId', true));
    $this->addValidatorVar(new ValidatorVar('season'));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editPriceList_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editPriceList_provider'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bPriceList = new BPriceList($id);
    $data = $bPriceList->getData();
    
    foreach ($data['season'] as $index=>$s) {
      $data['season'][$index]['start'] = $app->regionalSettings->convertDateToHuman($s['start'], 'd.m.');
      $data['season'][$index]['end'] = $app->regionalSettings->convertDateToHuman($s['end'], 'd.m.');
      $data['season'][$index]['basePrice'] = $app->regionalSettings->convertNumberToHuman($s['basePrice']);
      $data['season'][$index]['monPrice'] = str_replace('"','&quot;',$data['season'][$index]['monPrice']);
      $data['season'][$index]['tuePrice'] = str_replace('"','&quot;',$data['season'][$index]['tuePrice']);
      $data['season'][$index]['wedPrice'] = str_replace('"','&quot;',$data['season'][$index]['wedPrice']);
      $data['season'][$index]['thuPrice'] = str_replace('"','&quot;',$data['season'][$index]['thuPrice']);
      $data['season'][$index]['friPrice'] = str_replace('"','&quot;',$data['season'][$index]['friPrice']);
      $data['season'][$index]['satPrice'] = str_replace('"','&quot;',$data['season'][$index]['satPrice']);
      $data['season'][$index]['sunPrice'] = str_replace('"','&quot;',$data['season'][$index]['sunPrice']);
    }
    
    $this->setValues($data);
  }
}

?>
