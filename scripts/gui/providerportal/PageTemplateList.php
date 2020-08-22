<?php

class GridSettingsPageTemplate extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'pagetemplate_id', 'ID', 'none'));  
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listPageTemplate_name')));
    $this->addColumn(new GridColumn('action', 'pagetemplate_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listPageTemplate'))) {
      $this->_columnsMask = array('i', 'name', 'action');
      $this->_onPage = $ONPAGE['listPageTemplate'];
    } elseif ($this->_name == 'selectPageTemplate') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    if ($app->auth->haveRight('admin')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'ePageTemplateEdit',
            'imgsrc'    => 'img/button_grid_detail.png',
            'label' => $ts->getText('button.grid_edit'),
            'title' => $ts->getText('button.grid_edit'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'ePageTemplateCopy',
            'imgsrc'    => 'img/button_grid_copy.png',
            'label' => $ts->getText('button.grid_copy'),
            'title' => $ts->getText('button.grid_copy'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'ePageTemplateDelete',
            'imgsrc'    => 'img/button_grid_delete.png',
            'label'   => $ts->getText('button.grid_delete'),
            'title'   => $ts->getText('button.grid_delete'),
            'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
            'varName' => 'id')));  
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'pagetemplate_id')));
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'longText');
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiListPageTemplate extends GuiWebGrid {

  public function __construct($name='listPageTemplate') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsPageTemplate($name);
    $select = new SPageTemplate($gridSettings->getSqlSelectSettings());
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
