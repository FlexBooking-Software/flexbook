<?php

class XMLActionTag extends XMLAction {
  private $_portal;
  
  private $_item = array();
  
  private function _readRequest() {
    $portal = $this->_reqDoc->getElementsByTagName('portal');
    if ($portal->length) {
      $this->_portal = $this->_convertInput($portal->item(0)->nodeValue);
    }
  }

  protected function _prepareResponse() {
    $this->_readRequest();
    
    if (!$this->_portal) throw new ExceptionUser('50');
    
    $s = new STag;
    $s->addStatement(new SqlStatementBi($s->columns['portal'], $this->_portal, '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('tag_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_item[] = array('id'=>$row['tag_id'],'name'=>$row['name']);
    }
    
    $response = $this->_getResponseDataTag();
    foreach ($this->_item as $i) {
      $itemNode = $this->_respDoc->createElement('item');
      $response->appendChild($itemNode);
      $node = $this->_respDoc->createElement('id', $i['id']);
      $itemNode->appendChild($node);
      $node = $this->_respDoc->createElement('name', $this->_convertOutput($i['name']));
      $itemNode->appendChild($node);
    }
  }
}

?>