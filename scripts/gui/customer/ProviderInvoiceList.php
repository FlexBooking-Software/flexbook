<?php

class GridSettingsProviderInvoice extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'providerinvoice_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('providerName', 'customer_name', $ts->getText('label.listProviderInvoice_provider'), 'none'));
    $this->addColumn(new GridColumn('number', 'number', $ts->getText('label.listProviderInvoice_number'), 'none'));
    $this->addColumn(new GridColumn('createDate', 'create_date', $ts->getText('label.listProviderInvoice_createDate'), 'none'));
    $this->addColumn(new GridColumn('dueDate', 'due_date', $ts->getText('label.listProviderInvoice_dueDate'), 'none'));
    $this->addColumn(new GridColumn('vs', 'vs', $ts->getText('label.listProviderInvoice_vs'), 'none'));
    $this->addColumn(new GridColumn('amount', 'total_amount', $ts->getText('label.listProviderInvoice_amount'), 'none'));
    $this->addColumn(new GridColumn('paid', 'paid', $ts->getText('label.listProviderInvoice_paid'), 'none'));
    $this->addColumn(new GridColumn('action', 'file_hash', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listProviderInvoice'))) {
      $this->_columnsMask = array('i', 'number', 'createDate', 'dueDate', 'vs', 'amount', 'action');
      $this->_onPage = $ONPAGE['listProviderInvoice'];
    } elseif ($this->_name == 'listInvoice') {
      $this->_columnsMask = array('i', 'number', 'providerName', 'createDate', 'dueDate', 'vs', 'amount', 'paid', 'action');
      $this->_onPage = $ONPAGE['listInvoice'];
    }elseif ($this->_name == 'selectProviderInvoice') {
      $this->_columnsMask = array('id', 'number');
      $this->_onPage = null;
    }
    $this->setForcedSources(array('file'));

    $this->getColumn('createDate')->addGuiElement(new GuiGridCellDate);
    $this->getColumn('dueDate')->addGuiElement(new GuiGridCellDate);
    $this->getColumn('amount')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('amount')->addElementAttribute('class', 'tdRight');
    $this->getColumn('paid')->addGuiElement(new GuiGridCellDateTime);

    $this->getColumn('number')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('createDate')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('vs')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('amount')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('paid')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'restrictions' => array('file'=>null),
            'url' => 'getfile.php',
            'action' => 'xxx',
            'target' => '_blank',
            'imgsrc'    => 'img/button_grid_download.png',
            'label' => $ts->getText('button.grid_download'),
            'title' => $ts->getText('button.grid_download'),
            'varName' => 'id')));

    $this->setOrder('vs', 'desc');
  }
}

class GuiListProviderInvoice extends GuiWebGrid {

  public function __construct($name='listProviderInvoice') {
    $showFilter = false;
    $showPager = true;

    $gridSettings = new GridSettingsProviderInvoice($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SProviderInvoice($sqlSettings);

    if (!strcmp($name,'listProviderInvoice')) {
      $validator = Validator::get('customer', 'CustomerValidator');
      $provider = $validator->getVarValue('providerId');
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $provider, '%s=%s'));
    }

    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);
    
    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
