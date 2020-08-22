<?php

class GridSettingsVoucher extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'voucher_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listVoucher_provider'), 'select'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listVoucher_provider'), 'none'));    
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listVoucher_name')));
    $this->addColumn(new GridColumn('code', 'code', $ts->getText('label.listVoucher_code')));
    $this->addColumn(new GridColumn('validFor', 'center', $ts->getText('label.validFor'), 'none'));
    $this->addColumn(new GridColumn('discount', 'discount_amount', $ts->getText('label.listVoucher_discount'), 'none'));
    $this->addColumn(new GridColumn('action', 'voucher_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listVoucher'))) {
      if (!$app->auth->isAdministrator()) $this->_columnsMask = array('i', 'name', 'code', 'validFor', 'discount', 'action');
      else $this->_columnsMask = array('i', 'providerName', 'name', 'code', 'validFor', 'discount', 'action');
      $this->setForcedSources(array('subject_tag','validity_type','validity_from','validity_to','discount_proportion'));
      
      $this->_onPage = $ONPAGE['listVoucher'];
    } elseif ($this->_name == 'selectVoucher') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eVoucherEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eVoucherCopy',
          'imgsrc'    => 'img/button_grid_copy.png',
          'label' => $ts->getText('button.grid_copy'),
          'title' => $ts->getText('button.grid_copy'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action'  => 'eVoucherDelete',
          'imgsrc'    => 'img/button_grid_delete.png',
          'label'   => $ts->getText('button.grid_delete'),
          'title'   => $ts->getText('button.grid_delete'),
          'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'voucher_id')));
    
    if (!$app->auth->isAdministrator()) $this->getColumn('provider')->setSearchType('none');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('code')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('code')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('validFor')->addGuiElement(new GuiGridCellValidFor);
    $this->getColumn('discount')->addGuiElement(new GuiGridCellDiscount);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiGridCellDiscount extends GuiGridCellRenderer {

  protected function _userRender() {
    if ($this->_rowData['discount_amount']) {
      $output = sprintf('%s %s', $this->_rowData['discount_amount'], $this->_app->textStorage->getText('label.currency_CZK'));
    } elseif ($this->_rowData['discount_proportion']) {
      $output = sprintf('%s %%', $this->_rowData['discount_proportion']);
    } else {
      $output = '';
    }

    $this->setTemplateString($output);
  }
}

class GuiListVoucher extends GuiWebGrid {

  public function __construct($name='listVoucher') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsVoucher($name);
    $select = new SVoucher($gridSettings->getSqlSelectSettings());
    
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
