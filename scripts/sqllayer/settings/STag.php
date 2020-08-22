<?php

class STag extends SqlSelect {
  private $_tTag;
  private $_tTagPortal;
  private $_tTagProvider;

  private $_tEventTag;
  private $_tEvent;
  private $_tResourceTag;
  private $_tResource;

  private $_sEventCount;
  private $_sResourceCount;
  
  private function _insertTagTable() {
    $this->_tTag = new SqlTable('tag', 't');
    
    $this->addColumn(new SqlColumn($this->_tTag, 'tag_id'));
    $this->addColumn(new SqlColumn($this->_tTag, 'name'));
  }
  
  private function _insertTagPortalTable() {
    $this->_tTagPortal = new SqlTable('tag_portal', 'tpor');
    
    $this->addColumn(new SqlColumn($this->_tTagPortal, 'tag', 'tpor_tag'));
    $this->addColumn(new SqlColumn($this->_tTagPortal, 'portal'));
  }
  
  private function _insertTagProviderTable() {
    $this->_tTagProvider = new SqlTable('tag_provider', 'tpro');
    
    $this->addColumn(new SqlColumn($this->_tTagProvider, 'tag', 'tpro_tag'));
    $this->addColumn(new SqlColumn($this->_tTagProvider, 'provider'));
  }

  private function _insertEventTagTable() {
    $this->_tEventTag = new SqlTable('event_tag', 'et');

    $this->addColumn(new SqlColumn($this->_tEventTag, 'event'));
    $this->addColumn(new SqlColumn($this->_tEventTag, 'tag', 'event_tag'));
  }

  private function _insertEventTable() {
    $this->_tEvent = new SqlTable('event', 'e');

    $this->addColumn(new SqlColumn($this->_tEvent, 'event_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'name', 'event_name'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'provider', 'event_provider'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'external_id', 'event_external_id'));
    $this->addColumn(new SqlColumn($this->_tEvent, 'center', 'event_center'));
  }

  private function _insertResourceTagTable() {
    $this->_tResourceTag = new SqlTable('resource_tag', 'rt');

    $this->addColumn(new SqlColumn($this->_tResourceTag, 'resource'));
    $this->addColumn(new SqlColumn($this->_tResourceTag, 'tag', 'resource_tag'));
  }

  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');

    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name', 'resource_name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'provider', 'resource_provider'));
    $this->addColumn(new SqlColumn($this->_tResource, 'external_id', 'resource_external_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'center', 'resource_center'));
  }

  private function _insertEventCountSelect() {
    $this->_sEventCount = new SqlSelect;
    $tTag = new SqlTable('event_tag', 'et');
    $this->_sEventCount->addColumn(new SqlColumn($tTag, 'event'));
    $this->_sEventCount->addColumn(new SqlColumn($tTag, 'tag'));
    $this->_sEventCount->addColumn(new SqlColumn(false, 'tag_id', 'outer_tag', false, false, true));
    $this->_sEventCount->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sEventCount->columns['tag'], 'COUNT(%s)'), 'count', true));
    $this->_sEventCount->addStatement(new SqlStatementBi($this->_sEventCount->columns['tag'], $this->_sEventCount->columns['outer_tag'], '%s=%s'));
    $this->_sEventCount->setColumnsMask(array('count'));

    $this->addColumn(new SqlColumn(false, $this->_sEventCount, 'event_count'));
  }

  private function _insertResourceCountSelect() {
    $this->_sResourceCount = new SqlSelect;
    $tTag = new SqlTable('resource_tag', 'rt');
    $this->_sResourceCount->addColumn(new SqlColumn($tTag, 'resource'));
    $this->_sResourceCount->addColumn(new SqlColumn($tTag, 'tag'));
    $this->_sResourceCount->addColumn(new SqlColumn(false, 'tag_id', 'outer_tag', false, false, true));
    $this->_sResourceCount->addColumn(new SqlColumn(false, new SqlStatementMono($this->_sResourceCount->columns['tag'], 'COUNT(%s)'), 'count', true));
    $this->_sResourceCount->addStatement(new SqlStatementBi($this->_sResourceCount->columns['tag'], $this->_sResourceCount->columns['outer_tag'], '%s=%s'));
    $this->_sResourceCount->setColumnsMask(array('count'));

    $this->addColumn(new SqlColumn(false, $this->_sResourceCount, 'resource_count'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertTagTable();
    $this->_insertTagPortalTable();
    $this->_insertTagProviderTable();

    $this->_insertEventTagTable();
    $this->_insertEventTable();
    $this->_insertResourceTagTable();
    $this->_insertResourceTable();

    $this->_insertEventCountSelect();
    $this->_insertResourceCountSelect();
    
    $this->addJoin(new SqlJoin('LEFT', $this->_tTagPortal, new SqlStatementBi($this->columns['tpor_tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tTagProvider, new SqlStatementBi($this->columns['tpro_tag'], $this->columns['tag_id'], '%s=%s')));

    $this->addJoin(new SqlJoin('LEFT', $this->_tEventTag, new SqlStatementBi($this->columns['event_tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tEvent, new SqlStatementBi($this->columns['event'], $this->columns['event_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourceTag, new SqlStatementBi($this->columns['resource_tag'], $this->columns['tag_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
  }
}

?>
