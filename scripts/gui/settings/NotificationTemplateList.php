<?php

class GridSettingsNotificationTemplate extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'notificationtemplate_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listNotificationTemplate_provider'), 'select'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listNotificationTemplate_provider'), 'none'));    
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listNotificationTemplate_name')));
    $this->addColumn(new GridColumn('target', 'target', $ts->getText('label.listNotificationTemplate_target')));
    $this->addColumn(new GridColumn('description', 'description', $ts->getText('label.listNotificationTemplate_description'), 'none'));
    $this->addColumn(new GridColumn('action', 'notificationtemplate_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listNotificationTemplate'))) {
      if (!$app->auth->isAdministrator()) $this->_columnsMask = array('i', 'name', 'target', 'description', 'action');
      else $this->_columnsMask = array('i', 'providerName', 'name', 'target', 'description', 'action');
      $this->_onPage = $ONPAGE['listNotificationTemplate'];
    } elseif ($this->_name == 'selectNotificationTemplate') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eNotificationTemplateEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eNotificationTemplateCopy',
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'  => 'eNotificationTemplateDelete',
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'   => $ts->getText('button.grid_delete'),
          'title'   => $ts->getText('button.grid_delete'),
          'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'notificationtemplate_id')));
    
    if (!$app->auth->isAdministrator()) $this->getColumn('provider')->setSearchType('none');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('target')->addGuiElement(new GuiGridCellTarget);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiGridCellTarget extends GuiGridCellRenderer {

  protected function _userRender() {
    $this->setTemplateString($this->_app->textStorage->getText('label.listNotificationTemplate_target_'.$this->_outputData));
  }
}

class GuiListNotificationTemplate extends GuiWebGrid {

  public function __construct($name='listNotificationTemplate') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsNotificationTemplate($name);
    $select = new SNotificationTemplate($gridSettings->getSqlSelectSettings());
    
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
