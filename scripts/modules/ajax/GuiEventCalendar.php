<?php

class AjaxGuiEventCalendar extends AjaxGuiAction {

  protected function _userRun() {
    $this->_params['calendarType'] = 'event';
    $gui = new GuiCalendar(array('params'=>$this->_params));
    
    $this->_result['output'] = sprintf('<div id="%sflb_resource_calendar" class="flb_output">%s</div>', $this->_params['prefix'], $gui->render());
  }
}

?>
