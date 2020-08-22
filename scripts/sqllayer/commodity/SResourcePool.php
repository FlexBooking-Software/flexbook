<?php

class SResourcePool extends SqlSelect {
  private $_tResourcePool;
  private $_tResourcePoolItem;
  private $_tProvider;
  private $_tCenter;
  private $_tAddress;
  private $_tCustomer;
  private $_tResourcePoolTag;
  private $_tResource;
  private $_tResourceTag;
  private $_tTag;
  
  public $sTag;
  public $sPoolItem;
  
  private function _insertResourcePoolTable() {
    $this->_tResourcePool = new SqlTable('resourcepool', 'rp');
    
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'resourcepool_id'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'provider'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'center'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'external_id'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'name'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'description'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'url_photo'));
    $this->addColumn(new SqlColumn($this->_tResourcePool, 'active'));
  }
  
  protected function _insertProviderTable() {
    $this->_tProvider = new SqlTable('provider', 'prov');
    
    $this->addColumn(new SqlColumn($this->_tProvider, 'provider_id'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_1', 'provider_phone_1'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'phone_2', 'provider_phone_2'));
    $this->addColumn(new SqlColumn($this->_tProvider, 'www', 'provider_www'));
  }

  protected function _insertCenterTable() {
    $this->_tCenter = new SqlTable('center', 'cen');

    $this->addColumn(new SqlColumn($this->_tCenter, 'center_id'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'name', 'center_name'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'address'));
    $this->addColumn(new SqlColumn($this->_tCenter, 'payment_info', 'center_payment_info'));
  }

  protected function _insertAddressTable() {
    $this->_tAddress = new SqlTable('address', 'a');

    $this->addColumn(new SqlColumn($this->_tAddress, 'address_id'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'street'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'city'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'postal_code'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'state'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_latitude'));
    $this->addColumn(new SqlColumn($this->_tAddress, 'gps_longitude'));

    $this->addColumn(new SqlColumn(false, new SqlStatementTri($this->columns['street'], $this->columns['city'], $this->columns['postal_code'], "CONCAT(%s,' ',%s,', ',%s)"), 'full_address'));
  }

  private function _insertCustomerTable() {
    $this->_tCustomer = new SqlTable('customer', 'c');

    $this->addColumn(new SqlColumn($this->_tCustomer, 'customer_id'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'name', 'provider_name'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'ic', 'provider_ic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'dic', 'provider_dic'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'email', 'provider_email'));
    $this->addColumn(new SqlColumn($this->_tCustomer, 'provider', 'customer_provider'));
  }

  private function _insertResourcePoolTagTable() {
    $this->_tResourcePoolTag = new SqlTable('resourcepool_tag', 'rpt');

    $this->addColumn(new SqlColumn($this->_tResourcePoolTag, 'resourcepool', 'tag_resourcepool'));
    $this->addColumn(new SqlColumn($this->_tResourcePoolTag, 'tag'));

    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['tag'], 'GROUP_CONCAT(DISTINCT %s)'), 'all_tag', true));
  }
  
  private function _insertResourcePoolItemTable() {
    $this->_tResourcePoolItem = new SqlTable('resourcepoolitem', 'rpi');
    
    $this->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resourcepool'));
    $this->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resource'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['resource'], 'GROUP_CONCAT(DISTINCT %s)'), 'resource_all', true));
    $this->addColumn(new SqlColumn(false, new SqlStatementMono($this->columns['resource'], 'COUNT(DISTINCT %s)'), 'resource_count', true));
  }
  
  private function _insertResourceTable() {
    $this->_tResource = new SqlTable('resource', 'r');
    
    $this->addColumn(new SqlColumn($this->_tResource, 'resource_id'));
    $this->addColumn(new SqlColumn($this->_tResource, 'name', 'resource_name'));
    $this->addColumn(new SqlColumn($this->_tResource, 'center', 'resource_center'));
  }
  
  private function _insertTagTable() {
    $this->_tTag = new SqlTable('tag', 't');
    
    $this->addColumn(new SqlColumn($this->_tTag, 'tag_id'));
    $this->addColumn(new SqlColumn($this->_tTag, 'name', 'tag_name'));
  }
  
  private function _insertTagSelect() {
    $this->sTag = new SqlSelect;
    $tTag = new SqlTable('resource_tag', 'rt');
    $this->sTag->addColumn(new SqlColumn($tTag, 'resource'));
    $this->sTag->addColumn(new SqlColumn($tTag, 'tag'));
    $this->sTag->addColumn(new SqlColumn($this->_tResourcePoolItem, 'resource', 'outer_resource', false, false, true));
    $this->sTag->addColumn(new SqlColumn(false, new SqlStatementMono($this->sTag->columns['tag'], 'COUNT(%s)'), 'count', true));
    $this->sTag->addStatement(new SqlStatementBi($this->sTag->columns['resource'], $this->sTag->columns['outer_resource'], '%s=%s'));
    $this->sTag->setColumnsMask(array('count'));
    
    $this->addColumn(new SqlColumn(false, $this->sTag, 'tag_count'));
  }
  
  private function _insertResourceSelect() {
    $this->sPoolItem = new SResourcePoolItem;
    $this->sPoolItem->addColumn(new SqlColumn($this->_tResourcePool, 'resourcepool_id', 'outer_resourcepool', false, false, true));
    $this->sPoolItem->addColumn(new SqlColumn(false, new SqlStatementMono($this->sPoolItem->columns['resource'], 'GROUP_CONCAT(%s)'), 'all_resource', true));
    $this->sPoolItem->addStatement(new SqlStatementBi($this->sPoolItem->columns['resourcepool'], $this->sPoolItem->columns['outer_resourcepool'], '%s=%s'));
    $this->sPoolItem->setColumnsMask(array('all_resource'));
    
    $this->addColumn(new SqlColumn(false, $this->sPoolItem, 's_resource_all'));
  }
  
  protected function _initSqlSelect() {
    $this->_insertResourcePoolTable();
    $this->_insertResourcePoolItemTable();
    $this->_insertProviderTable();
    $this->_insertCenterTable();
    $this->_insertAddressTable();
    $this->_insertResourcePoolTagTable();
    $this->_insertCustomerTable();
    $this->_insertResourceTable();
    $this->_insertTagTable();
    
    $this->_insertTagSelect();
    $this->_insertResourceSelect();
    
    $this->addJoin(new SqlJoin(false, $this->_tProvider, new SqlStatementBi($this->columns['provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tCenter, new SqlStatementBi($this->columns['center'], $this->columns['center_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tAddress, new SqlStatementBi($this->columns['address'], $this->columns['address_id'], '%s=%s')));
    $this->addJoin(new SqlJoin(false, $this->_tCustomer, new SqlStatementBi($this->columns['customer_provider'], $this->columns['provider_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourcePoolTag, new SqlStatementBi($this->columns['tag_resourcepool'], $this->columns['resourcepool_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResourcePoolItem, new SqlStatementBi($this->columns['resourcepool'], $this->columns['resourcepool_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tResource, new SqlStatementBi($this->columns['resource'], $this->columns['resource_id'], '%s=%s')));
    $this->addJoin(new SqlJoin('LEFT', $this->_tTag, new SqlStatementBi($this->columns['tag'], $this->columns['tag_id'], '%s=%s')));
  }
}

?>
