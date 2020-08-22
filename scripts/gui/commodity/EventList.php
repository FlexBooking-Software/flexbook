<?php

class GridSettingsEvent extends WebGridSettings {

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
    $this->addColumn(new GridColumn('term', 'event_id', $ts->getText('label.listEvent_term'), 'calendar'));
    $this->addColumn(new GridColumn('organiser', 'organiser_fullname', $ts->getText('label.listEvent_organiser'), 'none'));
    $this->addColumn(new GridColumn('price', 'price', $ts->getText('label.listEvent_price'), 'none'));
    $this->addColumn(new GridColumn('maxAttendees', 'max_attendees', $ts->getText('label.listEvent_maxAttendees'), 'none'));
    $this->addColumn(new GridColumn('tag', 'tag_name', $ts->getText('label.listEvent_tag'), 'customTag'));
    $this->addColumn(new GridColumn('repeatParent', 'repeat_parent', $ts->getText('label.listEvent_cycle'), 'select'));
    $this->addColumn(new GridColumn('active', 'active', $ts->getText('label.listEvent_active'), 'select'));
    $this->addColumn(new GridColumn('repeat', 'repeat_index', '&nbsp;', 'none'));
    $this->addColumn(new GridColumn('action', 'event_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listEvent'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('id', 'i', 'providerName', 'name', 'term', 'organiser', 'maxAttendees', 'price', 'action');
      elseif ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) $this->_columnsMask = array('id', 'i', 'name', 'term', 'organiser', 'maxAttendees', 'price', 'action');
      else $this->_columnsMask = array('i', 'repeat', 'name', 'term', 'organiser', 'maxAttendees', 'price', 'action');
      
      $this->_onPage = $ONPAGE['listEvent'];
      
      $this->setForcedSources(array('description','free','max_substitutes','free_substitute','active','start','end','center_id','badge','provider_id','repeat_price','repeat_reservation'));
    } elseif ($this->_name == 'selectEvent') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }
    $this->setOrder('term','asc');

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eEventEdit',
          'constants' => array('single'=>1),
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    /*$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          //'dynamics' => array('center_id'),
          'action' => 'vEventCalendar',
          'imgsrc'    => 'img/button_grid_calendar.png',
          'label' => $ts->getText('button.listEvent_calendar'),
          'title' => $ts->getText('button.listEvent_calendar'),
          'varName' => 'event_id')));*/
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('active'=>'Y'),
          'restrictions' => array('free'=>0),
          'action' => 'eEventReservation',
          'imgsrc'    => 'img/button_grid_reserve.png',
          'label' => $ts->getText('button.grid_newReservation'),
          'title' => $ts->getText('button.grid_newReservation'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('active'=>'Y','free'=>0),
          'restrictions' => array('free_substitute'=>0),
          'dynamics' => array('event'=>'event_id'),
          'action' => 'eEventSubstituteEdit',
          'imgsrc'    => 'img/button_grid_reserve.png',
          'label' => $ts->getText('button.editEvent_newSubstitute'),
          'title' => $ts->getText('button.editEvent_newSubstitute'))));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('badge'=>'Y'),
          'dynamics' => array('provider'=>'provider_id'),
          'action' => 'vEventBadge',
          'imgsrc'    => 'img/button_grid_badge.png',
          'label' => $ts->getText('button.editEvent_badge'),
          'title' => $ts->getText('button.editEvent_badge'),
          'target' => '_blankBadge',
          'varName' => 'id')));
    if ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'eEventCopy',
            'imgsrc'    => 'img/button_grid_copy.png',
            'label' => $ts->getText('button.grid_copy'),
            'title' => $ts->getText('button.grid_copy'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'eEventDelete',
            'imgsrc'    => 'img/button_grid_delete.png',
            'label'   => $ts->getText('button.grid_delete'),
            'title'   => $ts->getText('button.grid_delete'),
            'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
            'varName' => 'id')));
    } elseif ($app->auth->haveRight('power_organiser',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('organiser'=>$app->auth->getUserId()),
        'action' => 'eEventCopy',
        'imgsrc'    => 'img/button_grid_copy.png',
        'label' => $ts->getText('button.grid_copy'),
        'title' => $ts->getText('button.grid_copy'),
        'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('organiser'=>$app->auth->getUserId()),
        'action'  => 'eEventDelete',
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
    $this->getColumn('name')->addGuiElement(new GuiGridCellEventDesc);
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
    $this->getColumn('term')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('term')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('term')->setOrderAsc(array(array('source'=>'start','direction'=>'asc')));
    $this->getColumn('term')->setOrderDesc(array(array('source'=>'start','direction'=>'desc')));
    $this->getColumn('term')->addGuiElement(new GuiGridCellTerm);
    $this->getColumn('maxAttendees')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('maxAttendees')->setOrderAsc(array(array('source'=>'free','direction'=>'asc')));
    $this->getColumn('maxAttendees')->setOrderDesc(array(array('source'=>'free','direction'=>'desc')));
    $this->getColumn('maxAttendees')->addElementAttribute('class', 'tdRight');
    $this->getColumn('maxAttendees')->addGuiElement(new GuiGridCellEventOccupy);
    $this->getColumn('price')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('price')->addGuiElement(new GuiGridCellEventPrice(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('price')->addElementAttribute('class', 'tdRight');
    $this->getColumn('active')->addElementAttribute('class', 'tdCenter');
    $this->getColumn('active')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('active')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('repeat')->addGuiElement(new GuiGridCellRepeat);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');

    // filtr na cykly se musi stavet az po nacteni hodnot filtru ze session ("active" ve filtru ovlivnuje cykly v nabidce filtru)
    $gs = new GridSettingsEventCycle('listEventCycle');
    $filter = $gs->getFilter();

    $s = new SEvent;
    if (!$app->auth->isAdministrator()) {
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $app->auth->getAllowedCenter('list'))));
      if ($app->auth->getActualCenter()) $s->addStatement(new SqlStatementBi($s->columns['center'], $app->auth->getActualCenter(), '%s=%s'));
    }
    $s->addStatement(new SqlStatementMono($s->columns['repeat_items_count'], '%s>0'));
    if (isset($filter['active'])&&$filter['active']) $s->addRepeatItemsActiveCondition($filter['active']);
    $s->setColumnsMask(array('event_id','name'));
    $res = $app->db->doQuery($s->toString());
    $hash = array(''=>$app->textStorage->getText('label.select_filter'));
    while ($row=$app->db->fetchAssoc($res)) $hash[$row['event_id']] = sprintf('%s', $row['name']);
    $this->getColumn('repeatParent')->setFilterDataSource(new HashDataSource(new DataSourceSettings, $hash));
  }

  public function getDefaultFilter() { return array('active'=>'Y'); }
}

class GuiGridCellRepeat extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $template = '';
    
    if ($this->_outputData!='') {
      $template .= '<img title="{__label.listEvent_cycle}" src="img/icon_repeat.png"/>';
    }
    
    $this->setTemplateString($template);
  }
}

class GuiGridCellEventPrice extends GuiGridCellNumber {

  protected function _userRender() {
    if (isset($this->_rowData['repeat_reservation'])) {
      if (strcmp($this->_rowData['repeat_reservation'],'PACK')) {
        $template = $this->_outputData?$this->_convertNumber():$this->_app->textStorage->getText('label.listEvent_price_free');

        if (strcmp($this->_rowData['repeat_reservation'],'SINGLE')) {
          $template .= '<br/>('.$this->_app->textStorage->getText('label.listEvent_price_cycle').': '.($this->_rowData['repeat_price']?$this->_convertNumber($this->_rowData['repeat_price']):$this->_app->textStorage->getText('label.listEvent_price_free')).')';
        }
      } else $template = $this->_app->textStorage->getText('label.listEvent_price_cycle').': '.($this->_rowData['repeat_price']?$this->_convertNumber($this->_rowData['repeat_price']):$this->_app->textStorage->getText('label.listEvent_price_free'));
    } else {
      $template = $this->_outputData?$this->_convertNumber():$this->_app->textStorage->getText('label.listEvent_price_free');
    }

    $this->setTemplateString($template);
  }
}

class GuiGridCellEventDesc extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $this->setTemplateString(sprintf('<span title="%s">%s</span>', htmlspecialchars($this->_rowData['description']), $this->_outputData));
  }
}

class GuiGridCellEventOccupy extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $title = sprintf("%s: %s %s: %s\n%s: %s %s: %s", $this->_app->textStorage->getText('label.listEvent_placesDesc'), $this->_outputData,
                     $this->_app->textStorage->getText('label.listEvent_freePlacesDesc'), $this->_rowData['free'],
                     $this->_app->textStorage->getText('label.listEvent_substitutesDesc'), $this->_rowData['max_substitutes']*1,
                     $this->_app->textStorage->getText('label.listEvent_freeSubstitutesDesc'), $this->_rowData['free_substitute']);
    $this->setTemplateString(sprintf('<span title="%s">%s&nbsp;(%s)</span>', $title, $this->_outputData, ifsetor($this->_rowData['free'],0)));
  }
}

class GuiGridCellTerm extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $interval = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['start']);
    if (substr($this->_rowData['start'],0,10)==substr($this->_rowData['end'],0,10)) {
      $interval .= '-'.$this->_app->regionalSettings->convertTimeToHuman(substr($this->_rowData['end'],11),'h:m');
    } else {
      $interval .= '<br/>'.$this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['end']);  
    }
    $interval = str_replace(' ','&nbsp;',$interval);
    
    $this->setTemplateString(sprintf('%s', $interval));
  }
}

class GuiEventGridRow extends GuiWebGridRow {

  protected function _preRenderRow($name, &$data, &$attributes) {
    $app = Application::get();

    parent::_preRenderRow($name, $data, $attributes);

    if (isset($data['active'])) {
      if ($data['active']=='N') $attributes['class'] .= ' disabled';
    }
  }
}

class GuiListEvent extends GuiWebGrid {

  protected function _initRow() {
    $params = $this->_getRowParams();
    $this->_guiGridRow = new GuiEventGridRow($params);
  }

  public function __construct($name='listEvent') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;
    
    global $AJAX;
    $app->document->addJavascript(sprintf("
      $(document).ready(function() {
        $('#fi_eventList #tag').tokenInput('%s?action=getTag&commodity=event%s%s',{
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

    $gridSettings = new GridSettingsEvent($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SEvent($sqlSettings);
    
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
    if (isset($temp['filter']['term'])&&$temp['filter']['term']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'event_id') { unset($filter[$k]); }
      }
      if ($app->regionalSettings->checkHumanDate($temp['filter']['term'])) {
        $term = $app->regionalSettings->convertHumanToDate($temp['filter']['term']);
        $select->addStatement(new SqlStatementTri($term, $select->columns['start'], $select->columns['end'], '%s BETWEEN DATE(%s) AND DATE(%s)'));
      } elseif ($app->regionalSettings->checkHumanDateTime($temp['filter']['term'])) {
        $term = $app->regionalSettings->convertHumanToDateTime($temp['filter']['term']);
        $select->addStatement(new SqlStatementTri($term, $select->columns['start'], $select->columns['end'], '%s BETWEEN %s AND %s'));
      }
    }
    // vyhledavani podle tagu
    if (isset($temp['filter']['tag'])&&$temp['filter']['tag']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'tag_name') { unset($filter[$k]); }
      }
      $select->addStatement(new SqlStatementMono($select->columns['tag_count'], '%s>0'));
      $select->sTag->addStatement(new SqlStatementMono($select->sTag->columns['tag'], sprintf('%%s IN (%s)', $app->db->escapeString($temp['filter']['tag']))));
    }
    
    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['center'], sprintf('%%s IN (%s)', $app->auth->getAllowedCenter('list'))));
      if ($app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $app->auth->getActualCenter(), '%s=%s'));
    }
   
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('id'=>'fi_eventList',
                              'settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager,
                              'doubleClickAction'=>'eEventEdit', 'doubleClickColumn'=>'event_id', 'doubleClickVarName'=>'id',
                              'multiAction'=>array(
                                    'action'          => array('eEventGroupDelete','eEventGroupDisable','eEventGroupEdit'),
                                    'label'           => array(
                                                        $app->textStorage->getText('button.listEvent_multiDelete'),
                                                        $app->textStorage->getText('button.listEvent_multiDisable'),
                                                        $app->textStorage->getText('button.listEvent_multiEdit'),
                                                        ),
                                    'onclick'         => array(
                                                        'return confirm(\''.$app->textStorage->getText('label.listEvent_multiDelete_confirm').'\');',
                                                        'return confirm(\''.$app->textStorage->getText('label.listEvent_multiDisable_confirm').'\');',
                                                        '',
                                                        ),
                                    'varName'         => 'id',
                                    'column'          => 'id'
                              )));
  }
}

?>
