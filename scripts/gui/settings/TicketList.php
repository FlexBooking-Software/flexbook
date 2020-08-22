<?php

class GridSettingsTicket extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'ticket_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listTicket_provider'), 'select'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listTicket_provider'), 'none'));    
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listTicket_name')));
    $this->addColumn(new GridColumn('validFor', 'center', $ts->getText('label.validFor'), 'none'));
    $this->addColumn(new GridColumn('value', 'value', $ts->getText('label.listTicket_value'), 'none'));
    $this->addColumn(new GridColumn('price', 'price', $ts->getText('label.listTicket_price'), 'none'));
    $this->addColumn(new GridColumn('action', 'ticket_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listTicket'))) {
      if (!$app->auth->isAdministrator()) $this->_columnsMask = array('i', 'name', 'validFor', 'value', 'action');
      else $this->_columnsMask = array('i', 'providerName', 'name', 'validFor', 'value', 'action');
      $this->setForcedSources(array('subject_tag','validity_type','validity_unit','validity_count','validity_from','validity_to'));
      
      $this->_onPage = $ONPAGE['listTicket'];
    } elseif ($this->_name == 'selectTicket') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eTicketEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eTicketCopy',
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'  => 'eTicketDelete',
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'   => $ts->getText('button.grid_delete'),
          'title'   => $ts->getText('button.grid_delete'),
          'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'ticket_id')));
    
    if (!$app->auth->isAdministrator()) $this->getColumn('provider')->setSearchType('none');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    
    $this->getColumn('validFor')->addGuiElement(new GuiGridCellValidFor);
    
    $this->getColumn('value')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('value')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('value')->addElementAttribute('class', 'tdRight');
    
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiListTicket extends GuiWebGrid {

  public function __construct($name='listTicket') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsTicket($name);
    $select = new STicket($gridSettings->getSqlSelectSettings());
    
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
