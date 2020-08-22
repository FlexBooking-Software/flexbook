<?php

class GridSettingsProviderAccountType extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'provideraccounttype_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listProviderAccountType_provider'), 'select'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listProviderAccountType_provider'), 'none'));    
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listProviderAccountType_name')));
    $this->addColumn(new GridColumn('action', 'provideraccounttype_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listProviderAccountType'))) {
      if (!$app->auth->isAdministrator()) $this->_columnsMask = array('i', 'name', 'action');
      else $this->_columnsMask = array('i', 'providerName', 'name', 'action');
      $this->_onPage = $ONPAGE['listProviderAccountType'];
    } elseif ($this->_name == 'selectProviderAccountType') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eProviderAccountTypeEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eProviderAccountTypeCopy',
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'  => 'eProviderAccountTypeDelete',
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'   => $ts->getText('button.grid_delete'),
          'title'   => $ts->getText('button.grid_delete'),
          'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'provideraccounttype_id')));
    
    if (!$app->auth->isAdministrator()) $this->getColumn('provider')->setSearchType('none');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiListProviderAccountType extends GuiWebGrid {

  public function __construct($name='listProviderAccountType') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsProviderAccountType($name);
    $select = new SProviderAccountType($gridSettings->getSqlSelectSettings());
    
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
