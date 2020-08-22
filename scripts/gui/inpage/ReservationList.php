<?php

class GridSettingsInPageReservationList extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'reservation_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('number', 'number', $ts->getText('label.listReservation_number')));
    $this->addColumn(new GridColumn('state', 'reservation_id', $ts->getText('label.listReservation_state'), 'select'));    
    $this->addColumn(new GridColumn('created', 'created', $ts->getText('label.listReservation_created'), 'none'));
    $this->addColumn(new GridColumn('commodity', 'reservation_id', $ts->getText('label.listReservation_commodity'), 'none'));
    $this->addColumn(new GridColumn('places', 'reservation_id', $ts->getText('label.listReservation_places'), 'none'));
    $this->addColumn(new GridColumn('event', 'event', $ts->getText('label.listReservation_event'), 'none'));
    $this->addColumn(new GridColumn('price', 'total_price', $ts->getText('label.listReservation_price'), 'none'));
    $this->addColumn(new GridColumn('action', 'reservation_id', $ts->getText('label.grid_none'), 'none'));
    
    $this->_columnsMask = array('i', 'created', 'number', 'commodity', 'places', 'price', 'action');
    $this->_onPage = null;
    
    $this->setForcedSources(array('start','event','event_name','event_places','event_start','all_event_resource_name','resource','resource_name','resource_from','resource_to','failed','cancelled','payed'));

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('cancelled'=>null,'failed'=>null,'payed'=>null),
          'restrictions' => array('total_price'=>0),
          'action'  => 'eInPageReservationPay',
          'label'   => $ts->getText('button.listReservation_pay'),
          'title'   => $ts->getText('button.listReservation_pay'),
          'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmPay').'\');',
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('cancelled'=>null,'failed'=>null,'payed'=>null),
          'action'  => 'eInPageReservationCancel',
          'label'   => $ts->getText('button.grid_cancel'),
          'title'   => $ts->getText('button.grid_cancel'),
          'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmCancel').'\');',
          'varName' => 'id')));  

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'reservation_id')));
    
    $this->getColumn('created')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('created')->addElementAttribute('class', 'tdRight');
    $this->getColumn('created')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('number')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('number')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('commodity')->addGuiElement(new GuiGridCellCommodity);
    $this->getColumn('places')->addGuiElement(new GuiGridCellPlaces);
    $this->getColumn('price')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('price')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2)));
    $this->getColumn('price')->addElementAttribute('class', 'tdRight');
    
    $ds = new HashDataSource(new DataSourceSettings, array(
                            'CREATED'   =>$app->textStorage->getText('label.listReservation_stateCREATED'),
                            'REALISED'  =>$app->textStorage->getText('label.listReservation_stateREALISED'),
                            'CANCELLED' =>$app->textStorage->getText('label.listReservation_stateCANCELLED'),
                            ));
    $this->getColumn('state')->setFilterParams(array('firstOption'=>$app->textStorage->getText('label.select_filter')));
    $this->getColumn('state')->setFilterDataSource($ds);
    
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');   
  }
  
  public function getDefaultFilter() { return array('state'=>'CREATED'); }
}

class GuiGridCellCommodity extends GuiGridCellRenderer {
  
  protected function _userRender() {
    if ($this->_rowData['event']) {
      $date = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['event_start']);
      $this->setTemplateString(sprintf('%s %s<br/>%s', $this->_rowData['event_name'],
                                       $this->_rowData['all_event_resource_name']?'('.$this->_rowData['all_event_resource_name'].')':'',
                                       $date));
    } else {
      $from = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['resource_from']);
      if (substr($this->_rowData['resource_from'],0,10)==substr($this->_rowData['resource_to'],0,10)) {
        $to = $this->_app->regionalSettings->convertTimeToHuman(substr($this->_rowData['resource_to'],11),'h:m');
      } else {
        $to = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['resource_to']);  
      }
      $interval = sprintf('%s - %s', $from, $to);
      
      $this->setTemplateString(sprintf('%s<br/>%s', $this->_rowData['resource_name'], $interval));
    }
  }
}

class GuiGridCellPlaces extends GuiGridCellRenderer {
  
  protected function _userRender() {
    if ($this->_rowData['event']) {
      $this->setTemplateString(sprintf('%s', $this->_rowData['event_places']));
    } else {
      $this->setTemplateString('1');
    }
  }
}

class GuiReservationGridRow extends GuiWebGridRow {

  protected function _preRenderRow($name, &$data, &$attributes) {
    $app = Application::get();

    parent::_preRenderRow($name, $data, $attributes);
    
    if (isset($data['cancelled'])) {
      if ($data['cancelled']) $attributes['class'] .= ' cancelled';
    }
    if (isset($data['start'])&&($data['start']<=date('Y-m-d H:i:s'))) {
      $attributes['class'] .= ' realised';
    }
    if (isset($data['payed'])) {
      if ($data['payed']) $attributes['class'] .= ' payed';
    }
  }
}

class GuiInPageReservationList extends GuiWebGrid {
  
  protected function _initRow() {
    $params = $this->_getRowParams();
    $this->_guiGridRow = new GuiReservationGridRow($params);
  }

  public function __construct($name='listReservation') {
    $app = Application::get();
    $showFilter = false;
    $showPager = false;

    $gridSettings = new GridSettingsInPageReservationList($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SReservation($sqlSettings);
    
    // omezeni na poskytovatele a zakaznika
    $validator = Validator::get('inpage', 'InPageValidator');
    $select->addStatement(new SqlStatementBi($select->columns['provider'], $validator->getVarValue('providerId'), '%s=%s'));
    $select->addStatement(new SqlStatementBi($select->columns['user'], $app->auth->getUserId(), '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['event'], '%s<>3397'));
    $select->addOrder(new SqlStatementAsc($select->columns['start']));
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'reservation_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementBi($select->columns['event_name'], $select->columns['resource_name'], 
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond)));
    }
    // stav rezervace
    if (isset($temp['filter']['state'])&&$temp['filter']['state']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'reservation_id') { unset($filter[$k]); }
      }
      
      $state = $temp['filter']['state'];
      if ($state == 'FAILED') $select->addStatement(new SqlStatementMono($select->columns['failed'], '%s IS NOT NULL'));
      if ($state == 'CANCELLED') $select->addStatement(new SqlStatementMono($select->columns['cancelled'], '%s IS NOT NULL'));
      if ($state == 'ACTIVE') $select->addStatement(new SqlStatementTri($select->columns['failed'], $select->columns['cancelled'], '%s IS NULL AND %s IS NULL AND %s>NOW()'));
    }
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
