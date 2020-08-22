<?php

class GridSettingsEventAttendee extends WebGridSettings {

  protected function _initSettings() {
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('event', 'event', $ts->getText('label.listEventAttendee_event'), 'none'));
    $this->addColumn(new GridColumn('user', 'user', $ts->getText('label.listEventAttendee_user'), 'none'));
    $this->addColumn(new GridColumn('fulltext', 'eventattendee_id', $ts->getText('label.listReservation_fulltext')));
    $this->addColumn(new GridColumn('userName', 'fullname', $ts->getText('label.listEventAttendee_userName')));
    $this->addColumn(new GridColumn('eventStart', 'start', $ts->getText('label.listEventAttendee_eventStart'), 'none'));
    $this->addColumn(new GridColumn('eventName', 'name', $ts->getText('label.listEventAttendee_eventName'), 'none'));
    $this->addColumn(new GridColumn('places', 'places', $ts->getText('label.listEventAttendee_places'), 'none'));
    $this->addColumn(new GridColumn('subscription', 'subscription_time', $ts->getText('label.listEventAttendee_subscription'), 'none'));
    $this->addColumn(new GridColumn('eventAttendeePerson', 'eventattendee_id', $ts->getText('label.listReservation_eventAttendee'), 'none'));
    $this->addColumn(new GridColumn('state', 'eventattendee_id', $ts->getText('label.listReservation_state'), 'select'));
    $this->addColumn(new GridColumn('action', 'eventattendee_id', $ts->getText('label.grid_none'), 'none'));

    if (!strcmp($this->_name,'listEventSubstitute')) {
      $this->_columnsMask = array('i', 'userName', 'subscription', 'places', 'eventAttendeePerson', 'action');
      $this->_onPage = null;
    } elseif (!strcmp($this->_name,'listUserSubstitute')) {
      $this->_columnsMask = array('i', 'eventName', 'subscription', 'places', 'eventAttendeePerson', 'action');
      $this->_onPage = null;
    } else {
      $this->_columnsMask = array('i', 'eventStart', 'eventName', 'userName', 'places', 'eventAttendeePerson', 'action');

      global $ONPAGE;
      $this->_onPage = $ONPAGE['listReservation'];
    }
    $this->setForcedSources(array('substitute_mandatory'));

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'varName'   => 'attendeeId',
          'dynamics'  => array('event'=>'event'),
          'action'    => 'eEventSubstituteEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label'     => $ts->getText('button.grid_edit'),
          'title'     => $ts->getText('button.grid_edit'))));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'restrictions' => array('free' => 0),
          'action' => 'eEventSubstituteReservation',
          'imgsrc'    => 'img/button_grid_reserve.png',
          'label' => $ts->getText('button.grid_newReservation'),
          'title' => $ts->getText('button.grid_newReservation'),
          'varName' => 'id')));
    if ($app->auth->haveRight('delete_record',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'dynamics' => array('event' => 'event'),
        'action' => 'eEventSubstituteDelete',
        'label' => $ts->getText('button.grid_delete'),
        'title' => $ts->getText('button.grid_delete'),
        'imgsrc' => 'img/button_grid_delete.png',
        'onclick' => 'return confirm(\'' . $ts->getText('label.grid_confirmDelete') . '\');',
        'varName' => 'id'
      )));
    }

    $this->getColumn('fulltext')->addFilterParam('classInput', 'longText');
    $this->getColumn('userName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder); 
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
    $this->getColumn('subscription')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('subscription')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('eventAttendeePerson')->addGuiElement(new GuiGridCellEventAttendeePerson);
    $this->getColumn('eventStart')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('eventStart')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('eventName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);

    $ds = new HashDataSource(new DataSourceSettings, array(
      'ACTIVE'    => $app->textStorage->getText('label.listReservation_stateACTIVE'),
      'REALISED'  => $app->textStorage->getText('label.listReservation_stateREALISED'),
    ));
    $this->getColumn('state')->setFilterParams(array('firstOption'=>$app->textStorage->getText('label.select_filter')));
    $this->getColumn('state')->setFilterDataSource($ds);

    $this->setOrder('eventStart','asc');
  }

  public function getDefaultFilter() {
    $ret = array();
    if (in_array($this->_name,array('listSubstitute'))) $ret['state'] ='ACTIVE';

    return $ret;
  }
}

class GuiGridCellEventAttendeePerson extends GuiGridCellRenderer {
  
  protected function _userRender() {
    if ($this->_outputData) {
      $template = '';
      $s = new SEventAttendeePerson;
      $s->addStatement(new SqlStatementBi($s->columns['eventattendee'], $this->_outputData, '%s=%s'));
      $s->setColumnsMask(array('firstname','lastname','email','subaccount','subaccount_firstname','subaccount_lastname','subaccount_email'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row=$this->_app->db->fetchAssoc($res)) {
        if ($template) $template .= '<br/>';
        if ($row['subaccount']) $template .= sprintf('%s %s (%s)', $row['subaccount_firstname'], $row['subaccount_lastname'], $row['subaccount_email']);
        else $template .= sprintf('%s %s (%s)', $row['firstname'], $row['lastname'], $row['email']);
      }
      
      $this->setTemplateString($template);
    }
  }
}

class GuiSubstituteGridRow extends GuiWebGridRow {

  protected function _preRenderRow($name, &$data, &$attributes) {
    if (isset($data['substitute_mandatory'])&&($data['substitute_mandatory']=='Y')) {
      $attributes['class'] .= ' mandatory';
    }
  }
}

class GuiListEventSubstitute extends GuiWebGrid {

  protected function _initRow() {
    $params = $this->_getRowParams();
    $this->_guiGridRow = new GuiSubstituteGridRow($params);
  }
  
  public function __construct($name, $params=array()) {
    $app = Application::get();

    $showFilter = !strcmp($name,'listSubstitute');
    $showPager = !strcmp($name,'listSubstitute');
    
    $gridSettings = new GridSettingsEventAttendee($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SEventAttendee($sqlSettings);

    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'eventattendee_id') { unset($filter[$k]); }
      }

      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementBi($select->columns['fullname'], $select->columns['name'],
        sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond)));
    }
    // stav rezervace
    if (isset($temp['filter']['state'])&&$temp['filter']['state']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'eventattendee_id') { unset($filter[$k]); }
      }

      $state = $temp['filter']['state'];
      if ($state == 'ACTIVE') $select->addStatement(new SqlStatementMono($select->columns['end'], '%s>NOW()'));
      if ($state == 'REALISED') $select->addStatement(new SqlStatementMono($select->columns['end'], '%s<NOW()'));
    }
    
    if (isset($params['event'])) $select->addStatement(new SqlStatementBi($select->columns['event'], $params['event'], '%s=%s'));
    if (isset($params['user'])) {
      $select->addStatement(new SqlStatementBi($select->columns['user'], $params['user'], '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['end'], '%s>=NOW()'));
    }
    $select->addStatement(new SqlStatementMono($select->columns['substitute'], "%s='Y'"));

    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['center'], sprintf('%%s IN (%s)', $app->auth->getAllowedCenter('list'))));
      if ($app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $app->auth->getActualCenter(), '%s=%s'));
    }
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
