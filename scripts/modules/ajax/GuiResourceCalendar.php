<?php

class AjaxGuiResourceCalendar extends AjaxGuiAction2 {

  protected function _userRun() {
    $this->_params['calendarType'] = 'resource';
    $gui = new GuiCalendar(array('params'=>$this->_params));
    
    $this->_result['output'] = sprintf('<div id="%sflb_resource_calendar" class="flb_output">%s%s</div>', $this->_params['prefix'], $gui->render(), $this->_getEventHandling());
  }
}

?>
