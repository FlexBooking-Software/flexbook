<?php

class ModuleReport extends ProjectModule {

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('report_admin', $this->_app->auth->getActualProvider())&&!$this->_app->auth->haveRight('report_reception', $this->_app->auth->getActualProvider()))
      throw new ExceptionUserTextStorage('error.accessDenied');

    $this->setTemplateString('
        <div class="reportContent">
          {children}
        </div>');
    
    $subSection = $this->_app->auth->getSubSection();
    switch ($subSection) {
      case 'user': $this->insert(new GuiUserReport); break;
      case 'attendee': $this->insert(new GuiAttendeeReport); break;
      case 'reservation': $this->insert(new GuiReservationReport); break;
      case 'credit': $this->insert(new GuiCreditReport); break;
      case 'document': $this->insert(new GuiListDocument('listDocument')); break;
      case 'onlinepayment': $this->insert(new GuiListOnlinePayment); break;
      default: $this->setTemplateString('<b>Reporty</b>');
    }
  }
}

?>
