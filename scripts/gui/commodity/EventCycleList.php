<?php

class GridSettingsEventCycle extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'event_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listEvent_provider'), 'none'));
    $this->addColumn(new GridColumn('fulltext', 'event_id', $ts->getText('label.listEvent_fulltext')));
    $this->addColumn(new GridColumn('providerName', 'provider_with_center', $ts->getText('label.listEvent_provider'), 'none'));
    $this->addColumn(new GridColumn('center', 'center_id', $ts->getText('label.listEvent_center'), 'none'));
    $this->addColumn(new GridColumn('centerName', 'center_name', $ts->getText('label.listEvent_center'), 'none'));
    $this->addColumn(new GridColumn('resource', 'resource', $ts->getText('label.listEvent_resource'), 'none'));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listEvent_name'), 'none'));
    $this->addColumn(new GridColumn('nameWithResource', 'name_with_resource', $ts->getText('label.listEvent_name'), 'none'));
    $this->addColumn(new GridColumn('address', 'full_address', $ts->getText('label.listEvent_address'), 'none'));
    $this->addColumn(new GridColumn('street', 'street', $ts->getText('label.listEvent_street'), 'none'));
    $this->addColumn(new GridColumn('city', 'city', $ts->getText('label.listEvent_city'), 'none'));
    $this->addColumn(new GridColumn('postalCode', 'postalCode', $ts->getText('label.listEvent_postalCode'), 'none'));
    $this->addColumn(new GridColumn('state', 'state', $ts->getText('label.listEvent_state'), 'none'));
    $this->addColumn(new GridColumn('start', 'start', $ts->getText('label.listEvent_start'), 'none'));
    $this->addColumn(new GridColumn('end', 'end', $ts->getText('label.listEvent_end'), 'none'));
    $this->addColumn(new GridColumn('cycleTerm', 'event_id', $ts->getText('label.listEvent_term'), 'calendar'));
    $this->addColumn(new GridColumn('organiser', 'organiser_fullname', $ts->getText('label.listEvent_organiser'), 'none'));
    $this->addColumn(new GridColumn('price', 'price', $ts->getText('label.listEvent_cyclePrice'), 'none'));
    $this->addColumn(new GridColumn('maxAttendees', 'max_attendees', $ts->getText('label.listEvent_maxAttendees'), 'none'));
    $this->addColumn(new GridColumn('cycleTag', 'tag_name', $ts->getText('label.listEvent_tag'), 'customTag'));
    $this->addColumn(new GridColumn('active', 'active', $ts->getText('label.listEvent_active'), 'select'));
    $this->addColumn(new GridColumn('repeat', 'repeat_index', '&nbsp;', 'none'));
    $this->addColumn(new GridColumn('action', 'event_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listEventCycle'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('id', 'i', 'providerName', 'name', 'cycleTerm', 'organiser', 'maxAttendees', 'price', 'action');
      elseif ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) $this->_columnsMask = array('id', 'i', 'name', 'cycleTerm', 'organiser', 'maxAttendees', 'price', 'action');
      else $this->_columnsMask = array('i', 'name', 'cycleTerm', 'organiser', 'maxAttendees', 'price', 'action');
      
      $this->_onPage = $ONPAGE['listEvent'];
      
      $this->setForcedSources(array('description','free','max_substitutes','free_substitute','active','start','end','center_id','badge','provider_id','repeat_price','repeat_reservation'));
    } elseif ($this->_name == 'selectEvent') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }
    $this->setOrder('cycleTerm','asc');

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eEventEdit',
          'constants' => array('repeat'=>1),
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eEventCycleItems',
          'imgsrc'    => 'img/button_grid_items.png',
          'label' => $ts->getText('button.editEvent_cycleItems'),
          'title' => $ts->getText('button.editEvent_cycleItems'),
          'varName' => 'id')));
    if ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'eEventDelete',
            'constants' => array('repeat'=>1),
            'imgsrc'    => 'img/button_grid_delete.png',
            'label'   => $ts->getText('button.grid_delete'),
            'title'   => $ts->getText('button.grid_delete'),
            'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
            'varName' => 'id')));
    } elseif ($app->auth->haveRight('power_organiser',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('organiser'=>$app->auth->getUserId()),
        'action'  => 'eEventDelete',
        'constants' => array('repeat'=>1),
        'imgsrc'    => 'img/button_grid_delete.png',
        'label'   => $ts->getText('button.grid_delete'),
        'title'   => $ts->getText('button.grid_delete'),
        'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
        'varName' => 'id')));
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'event_id')));
    
    if ($app->auth->isAdministrator()) {
      $this->getColumn('provider')->setSearchType('select');
      $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    } else {
      /*$this->getColumn('center')->setSearchType('select');
      $this->getColumn('center')->setFilterDataSource(new SqlFilterDataSource('Center',array('provider'=>$app->auth->getActualProvider())));*/
      if ($center=$app->auth->getActualCenter()) {
        $this->getColumn('resource')->setSearchType('select');
        $this->getColumn('resource')->setFilterDataSource(new SqlFilterDataSource('Resource',array(array('source'=>'center','value'=>$center)), 'name'));
      } else {
        $this->getColumn('resource')->setSearchType('select');
        $this->getColumn('resource')->setFilterDataSource(new SqlFilterDataSource('Resource', array(array('source'=>'center','value'=>null,
          'condition'=>sprintf('%%s IN (%s)', $app->auth->getAllowedCenter('list')))), 'name'));
      }
    }
    $this->getColumn('fulltext')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    $this->getColumn('centerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addGuiElement(new GuiGridCellCycleDesc);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('address')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('address')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('address')->addGuiElement(new GuiGridCellCut(array('charNum'=>30)));
    $this->getColumn('start')->addElementAttribute('class', 'tdRight');
    $this->getColumn('start')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('start')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('start')->addFilterParam('classInput', 'shortText');
    $this->getColumn('end')->addElementAttribute('class', 'tdRight');
    $this->getColumn('end')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('end')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('end')->addFilterParam('classInput', 'shortText');
    $this->getColumn('cycleTerm')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('cycleTerm')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('cycleTerm')->setOrderAsc(array(array('source'=>'start','direction'=>'asc')));
    $this->getColumn('cycleTerm')->setOrderDesc(array(array('source'=>'start','direction'=>'desc')));
    $this->getColumn('cycleTerm')->addElementAttribute('class', 'tdCycleTerm');
    $this->getColumn('cycleTerm')->addGuiElement(new GuiGridCellCycleTerm);
    $this->getColumn('organiser')->addGuiElement(new GuiGridCellCycleOrganiser);
    $this->getColumn('maxAttendees')->addGuiElement(new GuiGridCellCycleMaxAttendees);
    $this->getColumn('maxAttendees')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('maxAttendees')->setOrderAsc(array(array('source'=>'free','direction'=>'asc')));
    $this->getColumn('maxAttendees')->setOrderDesc(array(array('source'=>'free','direction'=>'desc')));
    $this->getColumn('maxAttendees')->addElementAttribute('class', 'tdRight');
    $this->getColumn('price')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('price')->addGuiElement(new GuiGridCellCyclePrice(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('price')->addElementAttribute('class', 'tdRight');
    $this->getColumn('active')->addElementAttribute('class', 'tdCenter');
    $this->getColumn('active')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('active')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }

  public function getDefaultFilter() { return array('active'=>'Y'); }
}

class GuiGridCellCycleTerm extends GuiGridCellRenderer {

  protected function _userRender() {
    $template = '';

    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $this->_rowData['event_id'], '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['repeat_index']));
    $s->setColumnsMask(array('start','active'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $template .= sprintf('<div class="cycleItemStart%s">%s</div>', $row['active']=='N'?' inactive':'', $this->_app->regionalSettings->convertDateTimeToHuman($row['start']));
    }

    $this->setTemplateString($template);
  }
}

class GuiGridCellCycleDesc extends GuiGridCellRenderer {

  protected function _userRender() {
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['repeat_parent'], $this->_rowData['event_id'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    $s->setColumnsMask(array('name','description','organiser_fullname','repeat_reservation','repeat_price','price','max_attendees'));
    $res = $this->_app->db->doQuery($s->toString());

    if ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_rowData['cycle_active_event'] = $row;
      $this->setTemplateString(sprintf('<span title="%s">%s</span>', htmlspecialchars($row['description']), $row['name']));
    } else {
      parent::_userRender();
    }
  }
}

class GuiGridCellCycleOrganiser extends GuiGridCellRenderer {

  protected function _userRender() {
    if (isset($this->_rowData['cycle_active_event'])&&is_array($this->_rowData['cycle_active_event'])) {
      $template = $this->_rowData['cycle_active_event']['organiser_fullname'];
    } else {
      $template = $this->_outputData;
    }

    $this->setTemplateString($template);
  }
}

class GuiGridCellCycleMaxAttendees extends GuiGridCellRenderer {

  protected function _userRender() {
    if (isset($this->_rowData['cycle_active_event'])&&is_array($this->_rowData['cycle_active_event'])) {
      $template = $this->_rowData['cycle_active_event']['max_attendees'];
    } else {
      $template = $this->_outputData;
    }

    $this->setTemplateString($template);
  }
}

class GuiGridCellCyclePrice extends GuiGridCellNumber {

  protected function _userRender() {
    if (isset($this->_rowData['cycle_active_event'])&&is_array($this->_rowData['cycle_active_event'])) {
      $repeatReservation = $this->_rowData['cycle_active_event']['repeat_reservation'];
      $repeatPrice = $this->_rowData['cycle_active_event']['repeat_price'];
      $price = $this->_rowData['cycle_active_event']['price'];
    } else {
      $repeatReservation = $this->_rowData['repeat_reservation'];
      $repeatPrice = $this->_rowData['repeat_price'];
      $price = $this->_outputData;
    }

    if (!strcmp($repeatReservation,'SINGLE')) $template = '---';
    else $template = $repeatPrice?$this->_convertNumber($repeatPrice):$this->_app->textStorage->getText('label.listEvent_price_free');

    if (!strcmp($repeatReservation,'PACK')) $template .= ' (---)';
    else $template .= ' ('.($price?$this->_convertNumber():$this->_app->textStorage->getText('label.listEvent_price_free')).')';

    $this->setTemplateString($template);
  }
}

class GuiListEventCycle extends GuiWebGrid {

  public function __construct($name='listEventCycle') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;
    
    global $AJAX;
    $app->document->addJavascript(sprintf("
      $(document).ready(function() {
        $('#fi_eventCycleList #cycleTag').tokenInput('%s?action=getTag&commodity=event%s%s',{
          queryParam:'term', theme:'facebook',
          minChars:0, 
          showAllResults: true,
          preventDuplicates:true,
          hintText:'%s',
          searchingText:'%s',
          noResultsText:'%s'
         });
      });", $AJAX['url'],
      $app->auth->isAdministrator()?'':'&provider='.$app->auth->getActualProvider(),
      $app->auth->getActualCenter()?'&center='.$app->auth->getActualCenter():'',
      $app->textStorage->getText('label.searchTag_hint'),
      $app->textStorage->getText('label.searchTag_searching'),
      $app->textStorage->getText('label.searchTag_noResult')));

    $gridSettings = new GridSettingsEventCycle($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SEvent($sqlSettings);
    #$select->addStatement(new SqlStatementMono($select->columns['repeat_index'], '%s=0'));
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'event_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementHexa($select->columns['provider_with_center'], $select->columns['full_address'], $select->columns['name'], $select->columns['organiser_fullname'], $select->columns['organiser_email'], $select->columns['description'],
        sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond, $likeCond, $likeCond, $likeCond)));
    }
    // vyhledavani podle terminu
    if (isset($temp['filter']['cycleTerm'])&&$temp['filter']['cycleTerm']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'event_id') { unset($filter[$k]); }
      }

      $cond = null;
      if ($app->regionalSettings->checkHumanDate($temp['filter']['cycleTerm'])) {
        $term = $app->regionalSettings->convertHumanToDate($temp['filter']['cycleTerm']);
        $cond = '%s BETWEEN DATE(%s) AND DATE(%s)';
      } elseif ($app->regionalSettings->checkHumanDateTime($temp['filter']['cycleTerm'])) {
        $term = $app->regionalSettings->convertHumanToDateTime($temp['filter']['cycleTerm']);
        $cond = '%s BETWEEN %s AND %s';
      }
      if ($cond) {
        $select->addRepeatItemsTermCondition($term,$cond);
        $select->addStatement(new SqlStatementMono($select->columns['repeat_items_count'], '%s>0'));
      }
    }
    // vyhledavani podle tagu
    if (isset($temp['filter']['cycleTag'])&&$temp['filter']['cycleTag']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'tag_name') { unset($filter[$k]); }
      }
      $select->addStatement(new SqlStatementMono($select->columns['tag_count'], '%s>0'));
      $select->sTag->addStatement(new SqlStatementMono($select->sTag->columns['tag'], sprintf('%%s IN (%s)', $temp['filter']['cycleTag'])));
    }
    // filtr na aktivni se bude vztahovat ke vsem opakovanim
    if (isset($temp['filter']['active'])&&$temp['filter']['active']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'active') { unset($filter[$k]); }
      }
      $select->addRepeatItemsActiveCondition($temp['filter']['active']);
      $select->addStatement(new SqlStatementMono($select->columns['repeat_items_count'], '%s>0'));
    } else {
      $select->addStatement(new SqlStatementMono($select->columns['repeat_items_count'], '%s>0'));
    }
    
    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      if ($app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $app->auth->getActualCenter(), '%s=%s'));
    }
   
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('id'=>'fi_eventCycleList',
                              'settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager,
                              'doubleClickAction'=>'eEventEdit', 'doubleClickColumn'=>'event_id', 'doubleClickVarName'=>'id',
                              'multiAction'=>array(
                                    'action'          => array('eEventGroupDelete?repeat=1'),
                                    'label'           => array(
                                                        $app->textStorage->getText('button.listEvent_multiDelete'),
                                                        ),
                                    'onclick'         => array(
                                                        'return confirm(\''.$app->textStorage->getText('label.listEventCycle_multiDelete_confirm').'\');',
                                                        ),
                                    'varName'         => 'id',
                                    'column'          => 'id'
                              )));
  }
}

?>
