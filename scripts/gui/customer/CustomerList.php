<?php

class GridSettingsCustomer extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'customer_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('fulltext', 'customer_id', $ts->getText('label.listCustomer_fulltext')));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listCustomer_name'), 'none'));
    $this->addColumn(new GridColumn('address', 'full_address', $ts->getText('label.listCustomer_address'), 'none'));
    $this->addColumn(new GridColumn('street', 'street', $ts->getText('label.listCustomer_street'), 'none'));
    $this->addColumn(new GridColumn('city', 'city', $ts->getText('label.listCustomer_city'), 'none'));
    $this->addColumn(new GridColumn('postalCode', 'postal_code', $ts->getText('label.listCustomer_postalCode'), 'none'));
    $this->addColumn(new GridColumn('state', 'state', $ts->getText('label.listCustomer_state'), 'none'));
    $this->addColumn(new GridColumn('ic', 'ic', $ts->getText('label.listCustomer_ic'), 'none'));
    $this->addColumn(new GridColumn('dic', 'dic', $ts->getText('label.listCustomer_dic'), 'none'));
    $this->addColumn(new GridColumn('email', 'email', $ts->getText('label.listCustomer_email'), 'none'));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listCustomer_provider'), 'selectNull'));
    $this->addColumn(new GridColumn('role', 'customer_id', $ts->getText('label.listCustomer_role'), 'none'));
    $this->addColumn(new GridColumn('credit', 'customer_id', $ts->getText('label.listCustomer_credit'), 'none'));
    $this->addColumn(new GridColumn('action', 'customer_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listCustomer'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('i','role','name','ic','address','email','action');
      else $this->_columnsMask = array('i','name','ic','address','email','action');
      
      $this->_onPage = $ONPAGE['listCustomer'];
      
      $this->setForcedSources(array('provider'));
    } elseif ($this->_name == 'selectCustomer') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    } elseif ($this->_name == 'listUserCustomer') {
      $this->_columnsMask = array('i','name','ic','address','email','action');
      $this->_onPage = null;
      
      $this->setForcedSources(array('provider'));
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => $this->_name=='listCustomer'?'eCustomerEdit':'eMyCustomerEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    if ($app->auth->haveRight('credit_admin','ANY')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'vCustomerCredit',
            'imgsrc'    => 'img/button_grid_credit.png',
            'label'   => $ts->getText('button.listCustomer_credit'),
            'title'   => $ts->getText('button.listCustomer_credit'),
            'varName' => 'id')));
    }
    if ($app->auth->isAdministrator()) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'eCustomerDelete',
            'imgsrc'    => 'img/button_grid_delete.png',
            'label'   => $ts->getText('button.grid_delete'),
            'title'   => $ts->getText('button.grid_delete'),
            'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
            'varName' => 'id')));
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'customer_id')));
    
    $this->getColumn('fulltext')->addFilterParam('classInput', 'longText');
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('address')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('address')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('ic')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('ic')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('email')->addFilterParam('classInput', 'mediumText');
    if (!$app->auth->isAdministrator()) {
      $this->getColumn('provider')->setSearchType('none');
    } else {
      $this->getColumn('provider')->addGuiElement(new GuiGridCellYesNo(array('strictYN'=>false)));
      $this->getColumn('provider')->setFilterDataSource(new NullNotNullFilterDataSource);
    }
    $this->getColumn('role')->addGuiElement(new GuiGridCellCustomerRole);
    $this->getColumn('credit')->addElementAttribute('class', 'tdRight');
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiGridCellCustomerRole extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $template = '';
    
    if ($this->_rowData['provider']) {
      if ($template) $template .= '&nbsp;';
      $template .= '<img title="{__label.listCustomer_roleProvider}" src="img/icon_provider.png"/>';
    }
    
    $this->setTemplateString($template);
  }
}

class GuiListCustomer extends GuiWebGrid {

  public function __construct($name='listCustomer') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsCustomer($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SCustomer($sqlSettings);
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'customer_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementPenta($select->columns['name'], $select->columns['full_address'], $select->columns['ic'], $select->columns['dic'], $select->columns['email'],
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond, $likeCond, $likeCond)));
    }
    // stav rezervace
    if (isset($temp['filter']['provider'])&&$temp['filter']['provider']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'provider') { unset($filter[$k]); }
      }
      
      $provider = $temp['filter']['provider'];
      if ($provider == 'N*O*T*N*U*L*L') $select->addStatement(new SqlStatementMono($select->columns['provider'], '%s IS NOT NULL'));
      if ($provider == 'N*U*L*L') $select->addStatement(new SqlStatementMono($select->columns['provider'], '%s IS NULL'));
    }
    
    if ($name == 'listCustomer') {
      // kdyz zobrazuju zakazniky poskytovatelu, kam je uzivatel prirazen
      if (!$app->auth->isAdministrator()) {
        $select->addStatement(new SqlStatementBi($select->columns['registration_provider'], $app->auth->getActualProvider(), '%s=%s'));
      }
    } elseif ($name == 'listUserCustomer') {
      // kdyz zobrazuju poskytovatele, kam je uzivatel prirazen
      $select->addStatement(new SqlStatementMono($select->columns['provider'], sprintf('%%s IN (%s)', $app->auth->getAllowedProvider(null,'list'))));
    }
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    if ($name == 'listCustomer') {
      $params = array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager,
                              'doubleClickAction'=>'eCustomerEdit', 'doubleClickColumn'=>'customer_id', 'doubleClickVarName'=>'id');
    } elseif ($name == 'listUserCustomer') {
      $params = array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>false, 'showPager'=>false);
    }
    
    parent::__construct($params);
  }
}

?>
