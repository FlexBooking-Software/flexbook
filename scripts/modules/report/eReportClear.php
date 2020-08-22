<?php

class ModuleReportClear extends ExecModule {

  protected function _userRun() {
    $reportType = $this->_app->request->getParams('section');
    switch ($reportType) {
      case 'customer': $validator = Validator::get('customerReport','CustomerReportValidator',true); $validator->loadData(); break;
      case 'attendee': $validator = Validator::get('attendeeReport','AttendeeReportValidator',true); $validator->loadData(); break;
      case 'reservation': $validator = Validator::get('reservationReport','ReservationReportValidator',true); $validator->loadData(); break;
      case 'credit': $validator = Validator::get('creditReport','CreditReportValidator',true); $validator->loadData(); break;
    }
    
    Validator::get('result', 'ReportValidator', true);
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
