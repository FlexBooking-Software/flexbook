<?php

class ModuleReport extends ExecModule {

  protected function _userRun() {
    $this->_app->auth->setSection('report');

    $allowedReports = array();
    if ($this->_app->auth->haveRight('report_admin', $this->_app->auth->getActualProvider())) $allowedReports = array('user','attendee','reservation','credit');
    elseif ($this->_app->auth->haveRight('report_reception', $this->_app->auth->getActualProvider())) $allowedReports = array('credit');
    
    if (!$subSection = $this->_app->request->getParams('section')) $subSection = current($allowedReports);
    $this->_app->auth->setSubSection($subSection);
    
    switch ($subSection) {
      case 'user': $validator = Validator::get('userReport','UserReportValidator',true); $validator->loadData(); break;
      case 'attendee': $validator = Validator::get('attendeeReport','AttendeeReportValidator',true); $validator->loadData(); break;
      case 'reservation': $validator = Validator::get('reservationReport','ReservationReportValidator',true); $validator->loadData(); break;
      case 'credit': $validator = Validator::get('creditReport','CreditReportValidator',true); $validator->loadData(); break;
    }
    $validator = Validator::get('result', 'ReportValidator', true);
    
    return 'vReport';
  }
}

?>
