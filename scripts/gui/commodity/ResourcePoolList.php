<?php

class GridSettingsResourcePool extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'resourcepool_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider_id', $ts->getText('label.listResourcePool_provider'), 'none'));
    $this->addColumn(new GridColumn('fulltext', 'resourcepool_id', $ts->getText('label.listResourcePool_fulltext')));
    $this->addColumn(new GridColumn('providerName', 'provider_name', $ts->getText('label.listResourcePool_provider'), 'none'));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listResourcePool_name'), 'none'));
    $this->addColumn(new GridColumn('poolTag', 'tag_name', $ts->getText('label.listResourcePool_tag'), 'customTag'));
    $this->addColumn(new GridColumn('resource', 'resource_all', $ts->getText('label.listResourcePool_resource'), 'none'));
    $this->addColumn(new GridColumn('resourceCount', 'resource_count', $ts->getText('label.listResourcePool_resourceCount'), 'none'));
    $this->addColumn(new GridColumn('active', 'active', $ts->getText('label.listResourcePool_active'), 'select'));
    $this->addColumn(new GridColumn('action', 'resourcepool_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listResourcePool'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('id', 'i', 'providerName', 'name', 'resourceCount', 'action');
      elseif ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) $this->_columnsMask = array('id', 'i', 'name', 'resourceCount', 'action');
      else $this->_columnsMask = array('i', 'name', 'resource', 'action');
      
      $this->_onPage = $ONPAGE['listResource'];
    
      $this->setForcedSources(array('description'));
    } elseif ($this->_name == 'selectResource') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eResourcePoolEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    if ($app->auth->haveRight('commodity_admin',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'action'  => 'eResourcePoolDelete',
            'imgsrc'    => 'img/button_grid_delete.png',
            'label'   => $ts->getText('button.grid_delete'),
            'title'   => $ts->getText('button.grid_delete'),
            'onclick' => 'return confirm(\''.$ts->getText('label.grid_confirmDelete').'\');',
            'varName' => 'id')));
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'resourcepool_id')));
    
    if ($app->auth->isAdministrator()) {
      $this->getColumn('provider')->setSearchType('select');
      $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    }
    $this->getColumn('fulltext')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('providerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addGuiElement(new GuiGridCellResourcePoolDesc);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('active')->addElementAttribute('class', 'tdCenter');
    $this->getColumn('active')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('active')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('resource')->addGuiElement(new GuiGridCellResourcePoolResource);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
  
  public function getDefaultFilter() { return array('active'=>'Y'); }
}

class GuiGridCellResourcePoolDesc extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $this->setTemplateString(sprintf('<span title="%s">%s</span>', htmlspecialchars($this->_rowData['description']), $this->_outputData));
  }
}

class GuiGridCellResourcePoolResource extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $output = '';
    $s = new SResourcePoolItem;
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_rowData['resourcepool_id'], '%s=%s'));
    $s->setColumnsMask(array('resource_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($output) $output .= ',';
      $output .= $row['resource_name'];
    }
    
    $this->setTemplateString($output);
  }
}

class GuiListResourcePool extends GuiWebGrid {

  public function __construct($name='listResourcePool') {
    $app = Application::get();
    $showFilter = true;
    $showPager = true;
    
    global $AJAX;
    $app->document->addJavascript(sprintf("
          $(document).ready(function() {
            $('#poolTag').tokenInput('%s?action=getTag%s',{
              minChars:3, queryParam:'term', theme:'facebook',
              preventDuplicates:true,
              hintText:'%s',
              searchingText:'%s',
              noResultsText:'%s'
             });
          });", $AJAX['url'],
          $app->auth->isAdministrator()?'':'&provider='.$app->auth->getActualProvider(),
          $app->textStorage->getText('label.searchTag_hint'),
          $app->textStorage->getText('label.searchTag_searching'),
          $app->textStorage->getText('label.searchTag_noResult')));

    $gridSettings = new GridSettingsResourcePool($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SResourcePool($sqlSettings);
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'resourcepool_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementTri($select->columns['provider_name'], $select->columns['resource_name'], $select->columns['name'], 
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond)));
    }
    if (isset($temp['filter']['poolTag'])&&$temp['filter']['poolTag']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'tag_name') { unset($filter[$k]); }
      }
      $select->addStatement(new SqlStatementMono($select->columns['tag_count'], '%s>0'));
      $select->sTag->addStatement(new SqlStatementMono($select->sTag->columns['tag'], sprintf('%%s IN (%s)', $app->db->escapeString($temp['filter']['poolTag']))));
    }
    
    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['center'], sprintf('%%s IN (%s)', $app->auth->getAllowedCenter('list'))));
      if ($app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $app->auth->getActualCenter(), '%s=%s'));
    }
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('id'=>'fi_resourcePoolList',
                              'settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager,
                              'doubleClickAction'=>'eResourcePoolEdit', 'doubleClickColumn'=>'resourcepool_id', 'doubleClickVarName'=>'id',
                              'multiAction'=>array(
                                    'action'          => array('eResourcePoolGroupDelete','eResourcePoolGroupDisable'),
                                    'label'           => array(
                                                        $app->textStorage->getText('button.listResourcePool_multiDelete'),
                                                        $app->textStorage->getText('button.listResourcePool_multiDisable'),
                                                        ),
                                    'onclick'         => array(
                                                        'return confirm(\''.$app->textStorage->getText('label.listResourcePool_multiDelete_confirm').'\');',
                                                        'return confirm(\''.$app->textStorage->getText('label.listResourcePool_multiDisable_confirm').'\');',
                                                        ),
                                    'varName'         => 'id',
                                    'column'          => 'id',
                                    )));
  }
}

?>
