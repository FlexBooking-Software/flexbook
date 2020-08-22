<?php

class GridSettingsProviderPortal extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn'), 'none'));
    $this->addColumn(new GridColumn('id', 'providerportal_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listProviderPortal_provider'), 'none'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listProviderPortal_provider'), 'none'));
    $this->addColumn(new GridColumn('urlName', 'url_name', $ts->getText('label.listProviderPortal_urlName')));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listProviderPortal_name')));
    $this->addColumn(new GridColumn('active', 'active', $ts->getText('label.listProviderPortal_active'), 'select'));
    $this->addColumn(new GridColumn('pageCount', 'page_count', $ts->getText('label.listProviderPortal_pageCount'), 'none'));
    $this->addColumn(new GridColumn('action', 'providerportal_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listProviderPortal'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('i', 'providerName', 'urlName', 'name', 'pageCount', 'active', 'action');
      else $this->_columnsMask = array('i', 'urlName', 'name', 'pageCount', 'active', 'action');
      $this->_onPortal = $ONPAGE['listProviderPortal'];
      $this->setForcedSources(array('provider_short_name'));
    } elseif ($this->_name == 'selectProviderPortal') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPortal = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eProviderPortalEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eProviderPortalCopy',
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'target'  => '_blank',
          'action' => 'vProviderPortalView',
          'imgsrc'    => 'img/button_grid_view.png',
          'label' => $ts->getText('button.grid_preview'),
          'title' => $ts->getText('button.grid_preview'),
          'dynamics' => array('id' => 'url_name','provider'=>'provider_short_name'),
          'constants' => array('preview'=>1),
          'varName' => 'portal_id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'  => 'eProviderPortalDelete',
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'   => $ts->getText('button.grid_delete'),
          'title'   => $ts->getText('button.grid_delete'),
          'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'providerportal_id')));
    
    if ($app->auth->isAdministrator()) {
      $this->getColumn('provider')->setSearchType('select');
      $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    }
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'longText');
    $this->getColumn('active')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('active')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
  
  //public function getDefaultFilter() { return array('active'=>'Y'); }
}

class GuiListProviderPortal extends GuiWebGrid {

  public function __construct($name='listProviderPortal') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsProviderPortal($name);
    $select = new SProviderPortal($gridSettings->getSqlSelectSettings());
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);
    
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
