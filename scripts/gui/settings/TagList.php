<?php

class GridSettingsTag extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'tag_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('checkbox', 'tag_id', '&nbsp;', 'none'));
    $this->addColumn(new GridColumn('name', 'name', $ts->getText('label.listTag_name')));
    $this->addColumn(new GridColumn('used', 'tag_id', $ts->getText('label.listTag_used'), 'none'));
    $this->addColumn(new GridColumn('usedDetailed', 'tag_id', $ts->getText('label.listTag_used'), 'none'));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listTag_provider'), 'none'));
    $this->addColumn(new GridColumn('portal', 'portal', $ts->getText('label.listTag_portal'), 'select'));
    $this->addColumn(new GridColumn('action', 'tag_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listTag'))) {
      $this->_columnsMask = array('i', 'name', 'used', 'action');
      $this->_onPage = $ONPAGE['listTag'];
    } elseif (in_array($this->_name, array('listSimilarTag'))) {
      $this->_columnsMask = array('checkbox', 'i', 'name', 'usedDetailed','action');
      $this->_onPage = $ONPAGE['listSimilarTag'];
      $this->getColumn('portal')->setSearchType('none');
    } elseif ($this->_name == 'selectTag') {
      $this->_columnsMask = array('id', 'name');
      $this->_onPage = null;
    }

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eTagEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    if (!strcmp($this->_name,'listTag')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'action' => 'eTagCopy',
        'imgsrc' => 'img/button_grid_copy.png',
        'label' => $ts->getText('button.grid_copy'),
        'title' => $ts->getText('button.grid_copy'),
        'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'action' => 'eTagDelete',
        'imgsrc' => 'img/button_grid_delete.png',
        'label' => $ts->getText('button.grid_delete'),
        'title' => $ts->getText('button.grid_delete'),
        'onclick' => 'return confirm(\'' . $ts->getText('label.grid_confirmDelete') . '\');',
        'varName' => 'id')));
    } elseif (!strcmp($this->_name,'listSimilarTag')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'action' => 'eTagPrepareCommodityCopy',
        'imgsrc' => 'img/button_grid_copy.png',
        'label' => $ts->getText('button.editTag_copyCommodity'),
        'title' => $ts->getText('button.editTag_copyCommodity'),
        'varName' => 'id')));
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'tag_id')));
    $this->getColumn('checkbox')->addElementAttribute('class', 'checkbox');
    $this->getColumn('checkbox')->addGuiElement(new GuiGridCellTagCheckbox);
    
    $this->getColumn('name')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('name')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('used')->addGuiElement(new GuiGridCellTagUsage);
    $this->getColumn('usedDetailed')->addGuiElement(new GuiGridCellTagUsageDetailed);
    $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    $this->getColumn('portal')->setFilterDataSource(new SqlFilterDataSource('Portal'));
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiGridCellTagCheckbox extends GuiGridCellRenderer {

  protected function _userRender() {
    $this->setTemplateString(sprintf('<input type="checkbox" class="checkbox" meaning="tag" name="tag[]" value="%s"/>', $this->_outputData));
  }
}

class GuiGridCellTagUsage extends GuiGridCellRenderer {

  protected function _userRender() {
    $s = new SEventTag;
    $s->addColumn(new SqlColumn(false, new SqlStatementMono($s->columns['event_provider'], 'GROUP_CONCAT(DISTINCT %s)'), 'provider_count'));
    $s->addColumn(new SqlColumn(false, new SqlStatementMono($s->columns['event'], 'COUNT(%s)'), 'event_count'));
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_outputData, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['event_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->setColumnsMask(array('provider_count','event_count'));
    $res = $this->_app->db->doQuery($s->toString());
    $row1 = $this->_app->db->fetchAssoc($res);

    $s = new SResourceTag;
    $s->addColumn(new SqlColumn(false, new SqlStatementMono($s->columns['resource_provider'], 'GROUP_CONCAT(DISTINCT %s)'), 'provider_count'));
    $s->addColumn(new SqlColumn(false, new SqlStatementMono($s->columns['resource'], 'COUNT(%s)'), 'resource_count'));
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_outputData, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resource_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->setColumnsMask(array('provider_count','resource_count'));
    $res = $this->_app->db->doQuery($s->toString());
    $row2 = $this->_app->db->fetchAssoc($res);

    $s = new SResourcePoolTag();
    $s->addColumn(new SqlColumn(false, new SqlStatementMono($s->columns['resourcepool_provider'], 'GROUP_CONCAT(DISTINCT %s)'), 'provider_count'));
    $s->addColumn(new SqlColumn(false, new SqlStatementMono($s->columns['resourcepool'], 'COUNT(%s)'), 'resourcepool_count'));
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_outputData, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->setColumnsMask(array('provider_count','resourcepool_count'));
    $res = $this->_app->db->doQuery($s->toString());
    $row3 = $this->_app->db->fetchAssoc($res);

    $this->setTemplateString(sprintf('%s: %s<br/>%s: %s<br/>%s: %s',
      $this->_app->textStorage->getText('label.listTag_used_event'), $row1['event_count'],
      $this->_app->textStorage->getText('label.listTag_used_resource'), $row2['resource_count'],
      $this->_app->textStorage->getText('label.listTag_used_resourcePool'), $row3['resourcepool_count']));
  }
}

class GuiGridCellTagUsageDetailed extends GuiGridCellRenderer {

  protected function _userRender() {
    $commodity = array();
    $s = new SEventTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_outputData, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['event_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['event_name']));
    $s->setColumnsMask(array('event_name','provider_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $commodity[$row['provider_name']]['event'][] = $row['event_name'];
    }

    $s = new SResourceTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_outputData, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resource_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['resource_name']));
    $s->setColumnsMask(array('resource_name','provider_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $commodity[$row['provider_name']]['resource'][] = $row['resource_name'];
    }

    $s = new SResourcePoolTag;
    $s->addStatement(new SqlStatementBi($s->columns['tag'], $this->_outputData, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['resourcepool_name']));
    $s->setColumnsMask(array('resourcepool_name','provider_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $commodity[$row['provider_name']]['resourcePool'][] = $row['resourcepool_name'];
    }

    $template = '';
    foreach ($commodity as $provider=>$c) {
      if (isset($c['event'])) $template .= sprintf('<span class="bold">%s:</span> %s', $this->_app->textStorage->getText('label.listTag_usedDetailed_event'), implode(', ',$c['event']));
      if (isset($c['resource'])) {
        if (isset($c['event'])) $template .= ' ';
        $template .= sprintf('<span class="bold">%s:</span> %s', $this->_app->textStorage->getText('label.listTag_usedDetailed_resource'), implode(', ',$c['resource']));
      }
      if (isset($c['resourcePool'])) {
        if (isset($c['event'])||isset($c['resource'])) $template .= ' ';
        $template .= sprintf('<span class="bold">%s:</span> %s', $this->_app->textStorage->getText('label.listTag_usedDetailed_resourcePool'), implode(', ',$c['resourcePool']));
      }
      $template .= '<br/>';
    }
    $this->setTemplateString($template);
  }
}

class GuiListTag extends GuiWebGrid {

  public function __construct($name='listTag') {
    $app = Application::get();

    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsTag($name);
    $select = new STag($gridSettings->getSqlSelectSettings());
    $select->setDistinct(true);
    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    //if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementMono($select->columns['provider'], sprintf('%%s IN (%s)', implode(',',$app->auth->getAllowedProvider()))));

    if (!strcmp($name,'listSimilarTag')) {
      $validator = Validator::get('tag','TagValidator');
      $select->addStatement(new SqlStatementBi($select->columns['tag_id'], $validator->getVarValue('id'), '%s<>%s'));
    }

    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }
}

?>
