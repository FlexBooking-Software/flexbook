<?php

class AjaxGetResource extends AjaxAction {

  protected function _userRun() {
    if (($id = ifsetor($this->_params['id']))||($asset = ifsetor($this->_params['assetId']))) {
      $s = new SResource;
      if ($id) $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $id, '%s=%s'));
      elseif ($asset) $s->addStatement(new SqlStatementBi($s->columns['external_id'], $asset, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->setColumnsMask(array('name','external_id','description','organiser','price','unit'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$row = $this->_app->db->fetchAssoc($res)) return;
      
      $base = $this->_app->textStorage->getText('label.minute');
      $multiplier = $row['unit'];
      if ($row['unit']%1440 === 0) { $multiplier = $row['unit']/1440; $base = $this->_app->textStorage->getText('label.day'); }
      elseif ($row['unit']%60 === 0) { $multiplier = $row['unit']/60; $base = $this->_app->textStorage->getText('label.hour'); }
      $unit = sprintf('%s %s', $multiplier, $base);
      
      $output = array(
        'id'                  => $id,
        'assetId'             => $row['external_id'],
        'organiser'						=> $row['organiser'],
        'name'                => $row['name'],
        'description'         => formatCommodityDescription($row['description']),
        'price'               => $row['price'],
        'unit'                => $row['unit'],
        'priceHtml'           => sprintf('%s %s / %s', $row['price'], $this->_app->textStorage->getText('label.currency_CZK'), $unit),
        );
      
      $this->_result = $this->_request->convertOutput($output);
    } elseif (isset($this->_params['center'])||isset($this->_params['provider'])) {
      $s = new SResource;
			if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      if (isset($this->_params['center'])) {
      	if (is_array($this->_params['center'])) {
      		$expr = '';
      		foreach ($this->_params['center'] as $center) {
      			if ($expr) $expr .= ',';
      			$expr .= $this->_app->db->escapeString($center);
					}
      		$s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $expr)));
				} else $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_params['center'], '%s=%s'));
			}
      if (isset($this->_params['name'])) $s->addStatement(new SqlStatementMono($s->columns['name'], sprintf("LOWER(%%s) LIKE '%%%%%s%%%%'", $this->_app->db->escapeString($this->_params['name']))));
      #if (isset($this->_params['tag'])) $s->addStatement(new SqlStatementMono($s->columns['tag'], sprintf('%%s IN (%s)', $this->_params['tag'])));
      if (isset($this->_params['tag'])) {
        $s->addStatement(new SqlStatementMono($s->columns['tag_count'], '%s>0'));
        $s->sTag->addStatement(new SqlStatementMono($s->sTag->columns['tag'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($this->_params['tag']))));
      }
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('resource_id','name'));
      $res = $this->_app->db->doQuery($s->toString());
      $this->_result = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $row['id'] = $row['resource_id'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>