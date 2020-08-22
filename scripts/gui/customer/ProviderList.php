<?php

class GridSettingsCenter extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'center_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listCenter_name')));
    $this->addColumn(new GridColumn('address', 'full_address', $ts->getText('label.listCenter_address')));
    $this->addColumn(new GridColumn('street', 'street', $ts->getText('label.listCenter_street'), 'none'));
    $this->addColumn(new GridColumn('city', 'city', $ts->getText('label.listCenter_city'), 'none'));
    $this->addColumn(new GridColumn('postalCode', 'postalCode', $ts->getText('label.listCenter_postalCode'), 'none'));
    $this->addColumn(new GridColumn('state', 'state', $ts->getText('label.listCenter_state'), 'none'));
    $this->addColumn(new GridColumn('action', 'center_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listCenter'))) {
      $this->_columnsMask = array('i', 'name', 'address', 'action');
      $this->_onPage = $ONPAGE['listCenter'];
    } elseif ($this->_name == 'selectCenter') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }
  }
}

class GridSettingsProvider extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('fulltext', 'provider_id', $ts->getText('label.listCustomer_fulltext')));
    $this->addColumn(new GridColumn('id', 'provider_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listProvider_name'), 'none'));
    $this->addColumn(new GridColumn('address', 'full_address', $ts->getText('label.listProvider_address'), 'none'));
    $this->addColumn(new GridColumn('ic', 'ic', $ts->getText('label.listProvider_ic'), 'none'));
    $this->addColumn(new GridColumn('dic', 'dic', $ts->getText('label.listProvider_dic'), 'none'));
    $this->addColumn(new GridColumn('email', 'email', $ts->getText('label.listProvider_email'), 'none'));
    $this->addColumn(new GridColumn('action', 'provider_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listProvider','listProviderForSettings'))) {
      $this->_columnsMask = array('i', 'name', 'address', 'email', 'action');
      $this->_onPage = $ONPAGE['listProvider'];
    } elseif ($this->_name == 'selectProvider') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }
    
    if ($this->_name == 'listProviderForSettings') {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
              'action' => 'eSettings',
              'imgsrc'    => 'img/button_grid_detail.png',
              'label' => $ts->getText('button.grid_settings'),
              'title' => $ts->getText('button.grid_settings'),
              'varName' => 'provider')));
    }
    
    $this->getColumn('fulltext')->addFilterParam('classInput', 'longText');
  }
}

class GuiListProvider extends GuiWebGrid {

  public function __construct($name='listProvider') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsProvider($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SProvider($sqlSettings);
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'provider_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementHexa($select->columns['name'], $select->columns['name'], $select->columns['full_address'],
                                                 $select->columns['ic'], $select->columns['dic'], $select->columns['email'],
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))",
                    $likeCond, $likeCond, $likeCond, $likeCond, $likeCond, $likeCond)));
    }

    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);
    
    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
