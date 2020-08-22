<?php

class GridSettingsPortalTemplate extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn'), 'none'));
    $this->addColumn(new GridColumn('id', 'portaltemplate_id', 'ID', 'none'));  
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listPortalTemplate_name')));
    $this->addColumn(new GridColumn('pageCount', 'page_count', $ts->getText('label.listPortalTemplate_pageCount'), 'none'));
    $this->addColumn(new GridColumn('action', 'portaltemplate_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listPortalTemplate'))) {
      $this->_columnsMask = array('i', 'name', 'pageCount', 'action');
      $this->_onPortal = $ONPAGE['listPortalTemplate'];
    } elseif ($this->_name == 'selectPortalTemplate') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPortal = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    if ($app->auth->haveRight('admin')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'ePortalTemplateEdit',
            'imgsrc'    => 'img/button_grid_detail.png',
            'label' => $ts->getText('button.grid_edit'),
            'title' => $ts->getText('button.grid_edit'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'ePortalTemplateCopy',
            'imgsrc'    => 'img/button_grid_copy.png',
            'label' => $ts->getText('button.grid_copy'),
            'title' => $ts->getText('button.grid_copy'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'ePortalTemplateDelete',
            'imgsrc'    => 'img/button_grid_delete.png',
            'label'   => $ts->getText('button.grid_delete'),
            'title'   => $ts->getText('button.grid_delete'),
            'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
            'varName' => 'id')));  
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'portaltemplate_id')));
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'longText');
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiListPortalTemplate extends GuiWebGrid {

  public function __construct($name='listPortalTemplate') {
    $app = Application::get();
    $showFilter = true;
    $showPortalr = true;

    $gridSettings = new GridSettingsPortalTemplate($name);
    $select = new SPortalTemplate($gridSettings->getSqlSelectSettings());
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPortalr'=>$showPortalr));
  }
}

?>
