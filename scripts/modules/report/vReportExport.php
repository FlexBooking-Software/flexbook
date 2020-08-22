<?php

class ModuleReportExport extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('report_admin', $this->_app->auth->getActualProvider())&&!$this->_app->auth->haveRight('report_reception', $this->_app->auth->getActualProvider()))
      throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->history->getBackwards(1);
    
    $subSection = $this->_app->request->getParams('section');
    switch ($subSection) {
      case 'user': $gui = new GuiUserReport; break;
      case 'attendee': $gui = new GuiAttendeeReport; break;
      case 'reservation': $gui = new GuiReservationReport; break;
      case 'credit': $gui = new GuiCreditReport; break;
    }
    
    $gui->render();
    $data = $gui->exportResult();
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-disposition: attachment; filename="export.csv"');
    echo $data;
    
    die;
  }
}

?>
