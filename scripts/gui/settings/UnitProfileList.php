<?php

class GridSettingsUnitProfile extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'unitprofile_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listUnitProfile_provider'), 'select'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listUnitProfile_provider'), 'none'));    
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listUnitProfile_name')));
    $this->addColumn(new GridColumn('unit', 'unit', $ts->getText('label.listUnitProfile_unit'), 'none'));
    $this->addColumn(new GridColumn('minUnit', 'minimum_quantity', $ts->getText('label.listUnitProfile_minUnit'), 'none'));
    $this->addColumn(new GridColumn('maxUnit', 'maximum_quantity', $ts->getText('label.listUnitProfile_maxUnit'), 'none'));
    $this->addColumn(new GridColumn('action', 'unitprofile_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listUnitProfile'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('i', 'providerName', 'name', 'unit', 'minUnit', 'maxUnit', 'action');
      else $this->_columnsMask = array('i', 'name', 'unit', 'minUnit', 'maxUnit', 'action');
      $this->_onPage = $ONPAGE['listUnitProfile'];

      $this->setForcedSources(array('unit_rounding'));
    } elseif ($this->_name == 'selectUnitProfile') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eUnitProfileEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eUnitProfileCopy',
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'  => 'eUnitProfileDelete',
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'   => $ts->getText('button.grid_delete'),
          'title'   => $ts->getText('button.grid_delete'),
          'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'unitprofile_id')));
    $this->getColumn('unit')->addGuiElement(new GuiGridCellUnit);
    
    if (!$app->auth->isAdministrator()) $this->getColumn('provider')->setSearchType('none');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiGridCellUnit extends GuiGridCellRenderer {
  
  public function _userRender() {
    if (!strcmp($this->_rowData['unit_rounding'],'day')) $output = sprintf('%s d', $this->_outputData/1440);
    elseif (!strcmp($this->_rowData['unit_rounding'],'night')) $output = sprintf('%s n', $this->_outputData/1440);
    elseif ($this->_outputData%60 === 0) $output = sprintf('%s h', $this->_outputData/60);
    else $output = sprintf('%s min', $this->_outputData);
    
    $this->setTemplateString($output);
  }
}


class GuiListUnitProfile extends GuiWebGrid {

  public function __construct($name='listUnitProfile') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsUnitProfile($name);
    $select = new SUnitProfile($gridSettings->getSqlSelectSettings());
    
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
