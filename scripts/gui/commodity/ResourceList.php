<?php

class GridSettingsResource extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'resource_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listResource_provider'), 'none'));
    $this->addColumn(new GridColumn('fulltext', 'resource_id', $ts->getText('label.listResource_fulltext')));
    $this->addColumn(new GridColumn('providerName', 'provider_with_center', $ts->getText('label.listResource_provider'), 'none'));
    $this->addColumn(new GridColumn('center', 'center_id', $ts->getText('label.listResource_center'), 'none'));
    $this->addColumn(new GridColumn('centerName', 'center_name', $ts->getText('label.listEvent_center'), 'none'));  
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listResource_name'), 'none'));
    $this->addColumn(new GridColumn('address', 'full_address', $ts->getText('label.listResource_address'), 'none'));
    $this->addColumn(new GridColumn('street', 'street', $ts->getText('label.listResource_street'), 'none'));
    $this->addColumn(new GridColumn('city', 'city', $ts->getText('label.listResource_city'), 'none'));
    $this->addColumn(new GridColumn('postalCode', 'postalCode', $ts->getText('label.listResource_postalCode'), 'none'));
    $this->addColumn(new GridColumn('state', 'state', $ts->getText('label.listResource_state'), 'none'));
    $this->addColumn(new GridColumn('price', 'price', $ts->getText('label.listResource_price'), 'none'));
    $this->addColumn(new GridColumn('working', 'availabilityprofile_name', $ts->getText('label.listResource_working'), 'none'));
    $this->addColumn(new GridColumn('tag', 'tag_name', $ts->getText('label.listResource_tag'), 'customTag'));
    $this->addColumn(new GridColumn('active', 'active', $ts->getText('label.listResource_active'), 'select'));
    $this->addColumn(new GridColumn('action', 'resource_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listResource','listResourceForResourcePool'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('id', 'i', 'providerName', 'name', 'address', 'price', 'working', 'action');
      elseif ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) $this->_columnsMask = array('id', 'i', 'name', 'address', 'price', 'working', 'action');
      else $this->_columnsMask = array('i', 'name', 'address', 'price', 'working', 'action');
      
      $this->_onPage = $ONPAGE['listResource'];
      
      $this->setForcedSources(array('description','unitprofile_name'));
    } elseif ($this->_name == 'selectResource') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    if (!strcmp($this->_name,'listResource')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'eResourceEdit',
            'imgsrc'    => 'img/button_grid_detail.png',
            'label' => $ts->getText('button.grid_edit'),
            'title' => $ts->getText('button.grid_edit'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'vResourceCalendar',
            'dynamics' => array('provider'=>'provider'),
            'imgsrc'    => 'img/button_grid_calendar.png',
            'label' => $ts->getText('button.listResource_calendar'),
            'title' => $ts->getText('button.listResource_calendar'),
            'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'conditions' => array('active'=>'Y'),
            'action' => 'eResourceReservation',
            'imgsrc'    => 'img/button_grid_reserve.png',
            'label' => $ts->getText('button.grid_newReservation'),
            'title' => $ts->getText('button.grid_newReservation'),
            'varName' => 'id')));
      if ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) {
        $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
              'action' => 'eResourceCopy',
              'imgsrc'    => 'img/button_grid_copy.png',
              'label' => $ts->getText('button.grid_copy'),
              'title' => $ts->getText('button.grid_copy'),
              'varName' => 'id')));
        $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
              'action'  => 'eResourceDelete',
              'imgsrc'    => 'img/button_grid_delete.png',
              'label'   => $ts->getText('button.grid_delete'),
              'title'   => $ts->getText('button.grid_delete'),
              'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
              'varName' => 'id')));
      }
    } elseif (!strcmp($this->_name,'listResourceForResourcePool')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action' => 'eResourcePoolResourceSelect',
            'label' => $ts->getText('button.grid_add'),
            'title' => $ts->getText('button.grid_add'),
            'varName' => 'id')));
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'resource_id')));
    
    if ($app->auth->isAdministrator()) {
      $this->getColumn('provider')->setSearchType('select');
      $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    /*} else {
      $this->getColumn('center')->setSearchType('select');
      $this->getColumn('center')->setFilterDataSource(new SqlFilterDataSource('Center',array('provider'=>$app->auth->getActualProvider())));*/
    }
    $this->getColumn('fulltext')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('centerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addGuiElement(new GuiGridCellResourceDesc);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('address')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('address')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('address')->addGuiElement(new GuiGridCellCut(array('charNum'=>30)));
    $this->getColumn('price')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('price')->addGuiElement(new GuiGridCellPrice);
    $this->getColumn('price')->addElementAttribute('class', 'tdRight');
    $this->getColumn('active')->addElementAttribute('class', 'tdCenter');
    $this->getColumn('active')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('active')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
  
  public function getDefaultFilter() { return array('active'=>'Y'); }
}

class GuiGridCellResourceDesc extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $this->setTemplateString(sprintf('<span title="%s">%s</span>', htmlspecialchars($this->_rowData['description']), $this->_outputData));
  }
}

class GuiGridCellPrice extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $this->setTemplateString(sprintf('%s / %s', Application::get()->regionalSettings->convertNumberToHuman($this->_outputData,2), $this->_rowData['unitprofile_name']));
  }
}


class GuiListResource extends GuiWebGrid {

  public function __construct($name='listResource') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;
    
    global $AJAX;
    $app->document->addJavascript(sprintf("
      $(document).ready(function() {
        $('#tag').tokenInput('%s?action=getTag&commodity=resource%s%s',{
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

    $gridSettings = new GridSettingsResource($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SResource($sqlSettings);
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'resource_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementQuad($select->columns['provider_with_center'], $select->columns['full_address'], $select->columns['name'], $select->columns['description'],
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond, $likeCond)));
    }
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
    
    $multiAction = null;
    $doubleClickAction = null;
    if (!strcmp($name,'listResource')) {
      $multiAction = array('action'          => array('eResourceGroupDelete','eResourceGroupDisable','eResourceGroupEdit'),
                            'label'           => array(
                                                $app->textStorage->getText('button.listResource_multiDelete'),
                                                $app->textStorage->getText('button.listResource_multiDisable'),
                                                $app->textStorage->getText('button.listResource_multiEdit')),
                            'onclick'         => array(
                                                'return confirm(\''.$app->textStorage->getText('label.listResource_multiDelete_confirm').'\');',
                                                'return confirm(\''.$app->textStorage->getText('label.listResource_multiDisable_confirm').'\');',
                                                '',
                                                ),
                            'varName'         => 'id',
                            'column'          => 'id',
                            );
      $doubleClickAction = 'eResourceEdit';
    } elseif (!strcmp($name,'listResourceForResourcePool')) {
      $multiAction = array('action'          => array('eResourcePoolResourceSelect'),
                           'label'           => array($app->textStorage->getText('button.listResourcePool_selectResource')),
                           'onclick'         => array(''),
                           'varName'         => 'id',
                           'column'          => 'id',
                          );
    }

    parent::__construct(array('id'=>'fi_resourceList',
                              'settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager,
                              'doubleClickAction'=>$doubleClickAction, 'doubleClickColumn'=>'resource_id', 'doubleClickVarName'=>'id',
                              'multiAction'=>$multiAction,
                              ));
  }
}

?>
