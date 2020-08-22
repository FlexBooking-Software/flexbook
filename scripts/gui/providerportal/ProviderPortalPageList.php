<?php

class GridSettingsProviderPortalPage extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn'), 'none'));
    $this->addColumn(new GridColumn('id', 'providerportalpage_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('shortName', 'short_name', $ts->getText('label.editProviderPortal_pageShortName')));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.editProviderPortal_pageName')));
    $this->addColumn(new GridColumn('action', 'providerportalpage_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listProviderPortalPage'))) {
      $this->_columnsMask = array('i', 'shortName', 'name', 'action');
      $this->_onPortal = $ONPAGE['listProviderPortalPage'];
    } elseif ($this->_name == 'selectProviderPortalPage') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPortal = null;
    }
    $this->setForcedSources(array('providerportal'));

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eProviderPortalPageEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'page')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eProviderPortalPageCopy',
          'dynamics'  => array('portal'=>'providerportal'),
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'page')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'    => 'eProviderPortalPageDelete',
          'dynamics'  => array('portal'=>'providerportal'),
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'     => $ts->getText('button.grid_delete'),
          'title'     => $ts->getText('button.grid_delete'),
          'onclick'   => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName'   => 'page')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'providerportalpage_id')));
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'longText');
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiListProviderPortalPage extends GuiWebGrid {

  public function __construct($name='listProviderPortalPage') {
    $app = Application::get();
    $showFilter = false;
    $showPager = false;
    
    $validator = Validator::get('providerPortal', 'ProviderPortalValidator');
    $providerPortal = $validator->getVarValue('id');

    $gridSettings = new GridSettingsProviderPortalPage($name);
    $select = new SProviderPortalPage($gridSettings->getSqlSelectSettings());
    $select->addStatement(new SqlStatementBi($select->columns['providerportal'], $providerPortal, '%s=%s'));
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
