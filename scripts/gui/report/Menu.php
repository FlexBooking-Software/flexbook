<?php

class GuiReportMenu extends GuiElement {
  private $_section;

  private function _insertReportMenuItem(& $template, $report) {
    if (!$this->_section) $this->_section = $report;

    $class = '';

    if ($template) $template .= '<li class="separator">|</li>';
    else $class = 'first';

    if ($this->_section==$report) $class .= ($class?' ':'').'selected';

    $template .= sprintf('<li%s><a href="{%%basefile%%}?action=eReport&section=%s{%%sessionUrl%%}">{__label.reportMenu_%s}</a></li>', $class?' class="'.$class.'"':'', $report, $report);
  }

  protected function _userRender() {
    $this->_section = $this->_app->auth->getSubSection();

    $allowedReports = array();
    if ($this->_app->auth->haveRight('report_admin', $this->_app->auth->getActualProvider())) $allowedReports = array('user','attendee','reservation','credit','document','onlinepayment');
    elseif ($this->_app->auth->haveRight('report_reception', $this->_app->auth->getActualProvider())) $allowedReports = array('credit');

    $template = '';
    foreach ($allowedReports as $report) {
      $this->_insertReportMenuItem($template, $report);
    }
    $template = "<ul class=\"submenu\">\n".$template."</ul>\n";

    $this->setTemplateString($template);
  }
}

?>
