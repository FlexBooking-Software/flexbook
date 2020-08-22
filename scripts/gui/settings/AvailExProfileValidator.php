<?php

class AvailExProfileValidator extends Validator {

  protected function _insert() {
    $app = Application::get();

    $this->addValidatorVar(new ValidatorVar('id'));
    $this->addValidatorVar(new ValidatorVar('name', true, new ValidatorTypeString(255)));
    $this->addValidatorVar(new ValidatorVar('providerId'));
    $this->addValidatorVar(new ValidatorVar('term'));

    $this->addValidatorVar(new ValidatorVar('fromEvent'));
    $this->addValidatorVar(new ValidatorVar('fromResource'));
    
    $this->getVar('name')->setLabel($app->textStorage->getText('label.editAvailExProfile_name'));
    $this->getVar('providerId')->setLabel($app->textStorage->getText('label.editAvailExProfile_provider'));
  }

  public function loadData($id) {
    $app = Application::get();

    $bAvailExProfile = new BAvailabilityExceptionProfile($id);
    $data = $bAvailExProfile->getData();
    
    $data['term'] = array();
    foreach ($data['item'] as $key=>$value) {
      list($value['fromDate'],$value['fromTime']) = explode(' ', $app->regionalSettings->convertDateTimeToHuman($value['from']));
      list($value['toDate'],$value['toTime']) = explode(' ', $app->regionalSettings->convertDateTimeToHuman($value['to']));
      $type = null;
      $date = null;
      $dateFrom = null; $dateTo = null;
      $timeFrom = null; $timeTo = null;
      
      if ($value['fromDate']==$value['toDate']) {
        if (($value['fromTime']=='00:00')&&($value['toTime']=='24:00')) {
          $type = 'Date';
          $date = $value['fromDate'];
        } else {
          $type = 'TimeRange';
          $timeFrom = $value['fromDate'].' '.$value['fromTime'];
          $timeTo = $value['toDate'].' '.$value['toTime'];
        }
      } else {
        if (($value['fromTime']=='00:00')&&($value['toTime']=='24:00')) {
          $type = 'DateRange';
          $dateFrom = $value['fromDate'];
          $dateTo = $value['toDate'];
        } else {
          $type = 'TimeRange';
          $timeFrom = $value['fromDate'].' '.$value['fromTime'];
          $timeTo = $value['toDate'].' '.$value['toTime'];
        }
      }
      
      $repeatWeekday = 0;
      $repeatUntil = $app->regionalSettings->convertDateToHuman($value['repeatUntil']);
      
      $data['term'][$key] = array('termId'=>$value['itemId'],'name'=>$value['name'],'type'=>$type,'date'=>$date,'dateFrom'=>$dateFrom,'dateTo'=>$dateTo,'timeFrom'=>$timeFrom,'timeTo'=>$timeTo,
                                  'repeated'=>$value['repeated'],'repeat_cycle'=>$value['repeatCycle'],'repeat_until'=>$repeatUntil,
                                  'repeat_weekday_mon'=>ifsetor($value['repeatWeekday_mon']),'repeat_weekday_tue'=>ifsetor($value['repeatWeekday_tue']),'repeat_weekday_wed'=>ifsetor($value['repeatWeekday_wed']),
                                  'repeat_weekday_thu'=>ifsetor($value['repeatWeekday_thu']),'repeat_weekday_fri'=>ifsetor($value['repeatWeekday_fri']),'repeat_weekday_sat'=>ifsetor($value['repeatWeekday_sat']),
                                  'repeat_weekday_sun'=>ifsetor($value['repeatWeekday_sun']));
    }
    
    $this->setValues($data);
  }
}

?>
