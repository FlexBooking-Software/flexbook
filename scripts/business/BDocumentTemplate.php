<?php

class BDocumentTemplate extends BusinessObject {

  private function _checkAccess($params=array()) {
    return true;
    $this->_load();
    
    $provider = ifsetor($params['providerId'],$this->_data['providerId']);
    
    $ret = $this->_app->auth->haveRight('commodity_admin', $provider);
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    if (!$this->_id) {
			if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyProvider');
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyName');
			if (!isset($params['target'])) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyTarget');
    }
    
    // tyto nesmi byt prazdny nikdy
		if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyProvider');
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyName');
    if (isset($params['target'])&&!$params['target']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyTarget');

    $knownNumber = array();
		foreach ($params['item'] as $item) {
			if (!$item['name']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyItemName');
			if (!$item['type']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyItemType');
			if (!$item['code']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyItemCode');
			if (!$item['number']) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_emptyItemNumber');
			if (!strcmp($params['target'],'COMMODITY')&&isset($params['item'])) {
				// pokud je target COMMODITY, musi byt notifikace pouze pro rezervace
				if (in_array($item['type'], array('U_CREATE'))) throw new ExceptionUserTextStorage('error.saveDocumentTemplate_targetItemConflict');
			}

			// nesmi existovat jiny dokument se stejnym format cisla (vznikaly by pak duplicity)
			if (in_array($item['number'], $knownNumber)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveDocumentTemplate_numberItemConflict'), $item['number']));
			if (strpos($item['number'],'[ID]')===false) $knownNumber[] = $item['number'];
			$s = new SDocumentTemplateItem;
			$s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $this->_id, '%s<>%s'));
			$s->addStatement(new SqlStatementBi($s->columns['documenttemplateitem_id'], $item['itemId'], '%s<>%s'));
			$s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
			$s->addStatement(new SqlStatementTri($s->columns['number'], $s->columns['number'], $item['number'], "LOCATE('[ID]',%s)=0 AND %s=%s"));
			$res = $this->_app->db->doQuery($s->toString());
			if ($this->_app->db->getRowsNumber($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveDocumentTemplate_numberItemConflict'), $item['number']));
		}
  }
  
  private function _checkBeforeDelete() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('resource_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteDocumentTemplate_resourceExists'), $row['name']));
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('event_id','name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteDocumentTemplate_eventExists'), $row['name']));

		$s = new SProviderSettings;
		$s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $this->_id, '%s=%s'));
		$s->setColumnsMask(array('provider_id','name'));
		$res = $this->_app->db->doQuery($s->toString());
		if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.deleteDocumentTemplate_providerExists'), $row['name']));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oDocumentTemplate = new ODocumentTemplate($this->_id);
      $data = $oDocumentTemplate->getData();
      $returnData['id'] = $data['documenttemplate_id'];
      $returnData['name'] = $data['name'];
			$returnData['target'] = $data['target'];
      $returnData['description'] = $data['description'];
      
      $returnData['providerId'] = $data['provider'];
      if ($data['provider']) {
        $s = new SProvider;
        $s->addStatement(new SqlStatementBi($s->columns['provider_id'], $data['provider'], '%s=%s'));
        $s->setColumnsMask(array('name','email','phone_1','phone_2','www'));
        $res = $this->_app->db->doQuery($s->toString());
        $oPData = $this->_app->db->fetchAssoc($res);
        
        $returnData['providerName'] = $oPData['name'];
        $returnData['providerEmail'] = $oPData['email'];
        $returnData['providerPhone1'] = $oPData['phone_1'];
        $returnData['providerPhone2'] = $oPData['phone_2'];
        $returnData['providerWww'] = $oPData['www'];
      }
      
      $returnData['item'] = array();
      $s = new SDocumentTemplateItem;
      $s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('documenttemplateitem_id','name','code','type','number','content'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {  
        $item = array(
          'itemId'=>$row['documenttemplateitem_id'],
          'name'=>$row['name'],'code'=>$row['code'],'type'=>$row['type'],'number'=>$row['number'],'content'=>$row['content'],
        );
        
        $returnData['item'][$row['documenttemplateitem_id']] = $item;
      }

      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  public function getData() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    return $this->_data;
  }
  
  private function _saveItem($params) {
    if (isset($params['item'])) {
      $ids = array();
      
      foreach ($params['item'] as $item) {
				$oData = array(
          'documenttemplate'=>$this->_id,
          'name'=>$item['name'],'code'=>$item['code'],'type'=>$item['type'],'number'=>$item['number']?$item['number']:null,'content'=>$item['content']
        );
        
        $o = new ODocumentTemplateItem($item['itemId']?$item['itemId']:null);
        $o->setData($oData);
        $o->save();
        
        $ids[] = $o->getId();
      }
      
      $ids = implode(',',$ids);
      $s = new SDocumentTemplateItem;
      $s->addStatement(new SqlStatementBi($s->columns['documenttemplate'], $this->_id, '%s=%s'));
      if ($ids) $s->addStatement(new SqlStatementMono($s->columns['documenttemplateitem_id'], sprintf('%%s NOT IN (%s)', $ids)));
      $s->setColumnsMask(array('documenttemplateitem_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new ODocumentTemplateItem($row['documenttemplateitem_id']);
        $o->delete();
      }
    }
  }
  
  private function _save($params) {
    $this->_load();

    $this->_app->db->beginTransaction();

    $o = new ODocumentTemplate($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['name'])) $oData['name'] = $params['name'];
		if (isset($params['target'])) $oData['target'] = $params['target'];
    if (isset($params['description'])) $oData['description'] = $params['description'];
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveItem($params);
    
    $this->_app->db->commitTransaction();
  }

  public function save($params) {
    if (!$this->_checkAccess($params)) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new ODocumentTemplate($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

		$this->_load();
  
    $this->_checkBeforeDelete();
    
    $ret = $this->_data['name'];
  
    $this->_delete();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
    
    $newDocumentTemplate = new BDocumentTemplate;
    $this->_data['name'] .= ' (kopie)';
    foreach ($this->_data['item'] as $index=>$item) {
      $this->_data['item'][$index]['itemId'] = null;
			$this->_data['item'][$index]['number'] = $item['number'].'_COPY';
    }
    $newDocumentTemplate->save($this->_data);
    
    return $ret;
  }

	public static function generate($params) {
		$bNot = new BDocumentTemplate;
		$bNot->generateDocument($params);
	}

	private function _getDocumentsFromTemplateItem(& $document, $select, $documentType) {
		$select->addStatement(new SqlStatementBi($select->columns['documenttemplateitem_type'], $documentType, '%s=%s'));
		$select->setColumnsMask(array('documenttemplateitem_id','documenttemplateitem_code','documenttemplateitem_type','documenttemplateitem_number','documenttemplateitem_content'));
		$res = $this->_app->db->doQuery($select->toString());
		while ($row = $this->_app->db->fetchAssoc($res)) {
			$document[] = array(
				'itemId' 		=> $row['documenttemplateitem_id'],
				'type' 			=> $row['documenttemplateitem_type'],
				'code' 			=> $row['documenttemplateitem_code'],
				'number' 		=> $row['documenttemplateitem_number'],
				'content' 	=> $row['documenttemplateitem_content'],
			);
		}
	}

	public function generateDocument($params) {
		$document = array();

		// nejdriv pridam globalni dokumenty poskytovatele
		$s = new SProviderSettings;
		$s->addStatement(new SqlStatementBi($s->columns['provider'], $params['providerId'], '%s=%s'));
		$this->_getDocumentsFromTemplateItem($document, $s, $params['type']);

		// pak pridam dokumenty pro zdroje/akce rezervace
		if (isset($params['reservationId'])) {
			$s = new SReservation;
			$s->addStatement(new SqlStatementBi($s->columns['reservation_id'], $params['reservationId'], '%s=%s'));
			$s->setColumnsMask(array('event','resource'));
			$res = $this->_app->db->doQuery($s->toString());
			$row = $this->_app->db->fetchAssoc($res);
			if ($row['event']) {
				$s = new SEvent;
				$s->addStatement(new SqlStatementBi($s->columns['event_id'], $row['event'], '%s=%s'));
			} else {
				$s = new SResource;
				$s->addStatement(new SqlStatementBi($s->columns['resource_id'], $row['resource'], '%s=%s'));
			}
			$this->_getDocumentsFromTemplateItem($document, $s, $params['type']);
		}

		foreach ($document as $index=>$doc) {
			$dParams = array(
				'providerId'            	=> $params['providerId'],
				'documentTemplateItemId'	=> $doc['itemId'],
				'code'										=> $doc['code'],
				'type'										=> $doc['type'],
				'contentTemplate'       	=> $doc['content'],
			);
			if (isset($params['userId'])) $dParams['userId'] = $params['userId'];
			if (isset($params['reservationId'])) $dParams['reservationId'] = $params['reservationId'];

			$b = new BDocument;
			$b->save($dParams);
		}
	}
}

?>