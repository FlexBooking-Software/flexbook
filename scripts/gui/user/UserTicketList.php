<?php

class GridSettingsUserTicket extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'userticket_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listUserTicket_provider'), 'select'));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listUserTicket_provider'), 'none'));
    $this->addColumn(new GridColumn('created', 'created', $ts->getText('label.listUserTicket_created'), 'none'));
    $this->addColumn(new GridColumn('validFor', 'userticket_id', $ts->getText('label.listUserTicket_valid'), 'none'));    
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listUserTicket_name'), 'none'));
    $this->addColumn(new GridColumn('originalValue', 'original_value', $ts->getText('label.listUserTicket_originalValue'), 'none'));
    $this->addColumn(new GridColumn('value', 'value', $ts->getText('label.listUserTicket_value'), 'none'));
    $this->addColumn(new GridColumn('active', 'userticket_id', $ts->getText('label.listUserTicket_active'), 'select'));
    $this->addColumn(new GridColumn('action', 'userticket_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listUserTicket'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('providerName', 'name', 'created', 'validFor', 'value', 'action');
      else $this->_columnsMask = array('name', 'created', 'validFor', 'value', 'action');
      
      $this->setForcedSources(array('userticket_id','user','validity_type','validity_from','validity_to','center','subject_tag','original_value'));
    } elseif ($this->_name == 'selectUserTicket') {
      $this->_columnsMask = array('id', 'name');
    }
    $this->_onPage = null;

    $this->getColumn('i')->addElementAttribute('class', 'index');
    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'userticket_id')));
    
    if (!$app->auth->isAdministrator()) $this->getColumn('provider')->setSearchType('none');
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('validFor')->addGuiElement(new GuiGridCellValidFor);
    $this->getColumn('originalValue')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('value')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('created')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('active')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('active')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('action')->addGuiElement(new GuiGridCellHistoryAction);  
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
  
  public function getDefaultFilter() { return array('active'=>'Y'); }
}

class GuiGridCellHistoryAction extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $template = '';
    if ($this->_rowData['value']>0) {
      $confirmLabel = $this->_rowData['value'] == $this->_rowData['original_value'] ? $this->_app->textStorage->getText('label.editUserCredit_confirmTicketRefund') : $this->_app->textStorage->getText('label.editUserCredit_confirmTicketPartialRefund');
      $template = sprintf('<a href="index.php?action=eUserTicketRefund&user=%s&id=%s%s" onclick="return confirm(\'%s\');"><img title="%s" src="img/button_grid_cancel_refund.png"/></a>',
        $this->_rowData['user'], $this->_rowData['userticket_id'], $this->_app->session->getTagForUrl(), $confirmLabel,
        $this->_app->textStorage->getText('label.editUserCredit_ticketRefund'));
    }
    $template .= sprintf('<a href="#" class="ticketHistoryUrl" id="%s"><img title="%s" src="img/button_grid_detail.png"/></a>', $this->_rowData['userticket_id'], $this->_app->textStorage->getText('label.editUserCredit_history'));
    
    $this->setTemplateString($template);
  }
}

class GuiListUserTicket extends GuiWebGrid {

  public function __construct($user=null, $name='listUserTicket') {
    $app = Application::get();
    $showFilter = true;
    $showPager = false;

    $gridSettings = new GridSettingsUserTicket($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SUserTicket($sqlSettings);
    
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['active'])&&$temp['filter']['active']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'userticket_id') { unset($filter[$k]); }
      }
      
      if ($temp['filter']['active']=='Y') {
        $select->addStatement(new SqlStatementPenta($select->columns['from_timestamp'],$select->columns['from_timestamp'],
                                                    $select->columns['to_timestamp'],$select->columns['to_timestamp'],
                                                    $select->columns['value'],
                                                    '((%s IS NULL OR %s<=NOW()) AND (%s IS NULL OR %s>=NOW()) AND (%s>0))'));
      } elseif ($temp['filter']['active']=='N') {
        $select->addStatement(new SqlStatementPenta($select->columns['from_timestamp'],$select->columns['from_timestamp'],
                                                    $select->columns['to_timestamp'],$select->columns['to_timestamp'],
                                                    $select->columns['value'],
                                                    '((%s IS NOT NULL AND %s>NOW()) OR (%s IS NOT NULL AND %s<NOW()) OR (%s<=0))'));
      }
    }
    
    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementTri($select->columns['center'], $select->columns['center'], $app->auth->getActualCenter(), '(%s IS NULL OR %s=%s)'));
    }
    
    if ($user) $select->addStatement(new SqlStatementBi($select->columns['user'], $user, '%s=%s'));
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
