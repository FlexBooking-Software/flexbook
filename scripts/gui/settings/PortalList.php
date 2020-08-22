<?php

class GridSettingsPortal extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'portal_id', 'ID', 'none'));  
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listPortal_name')));
    
    $this->_columnsMask = array('id', 'name');
    $this->_onPage = null;
    
    $this->getColumn('i')->addElementAttribute('class', 'index');
  }
}

?>
