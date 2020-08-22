<?php

class AjaxGetAttribute extends AjaxAction {

  protected function _userRun() {
    if (isset($this->_params['id'])) {
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['attribute_id'], $this->_params['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s='N'"));
      $s->setColumnsMask(array('attribute_id','provider','customer_name','category','mandatory','type','allowed_values','disabled','applicable'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $s1 = new SAttributeName;
      $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
      $s1->setColumnsMask(array('lang','name'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $a = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $a[$row1['lang']] = $row1['name']; }
      if (isset($this->_params['language'])&&isset($a[$this->_params['language']])) {
        $name = $a[$this->_params['language']];
      } else {
        $name = array_values($a)[0];
      }
      
      $this->_result = array(
                'id'            => $row['attribute_id'],
                'providerId'    => $row['provider'],
                'providerName'  => $row['customer_name'],
                'category'      => $row['category'],
                'name'          => $name,
                'mandatory'     => $row['mandatory'],
                'type'          => $row['type'],
                'typeHtml'      => $this->_app->textStorage->getText('label.editCustomerAttribute_type'.$row['type']),
                'allowedValues' => $row['allowed_values'],
                'disabled'      => $row['disabled'],
                'applicable'    => $row['applicable'],
                );
    } else {
      $s = new SAttribute;
      if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementMono($s->columns['provider'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$this->_params['provider'])))));
      if (isset($this->_params['skip'])) $s->addStatement(new SqlStatementMono($s->columns['attribute_id'], sprintf('%%s NOT IN (%s)', $this->_app->db->escapeString(implode(',',$this->_params['skip'])))));
      if (isset($this->_params['applicable'])) {
        if (!is_array($this->_params['applicable'])) $this->_params['applicable'] = array($this->_params['applicable']);
        $applicableCond = '';
        foreach ($this->_params['applicable'] as $a) {
          if ($applicableCond) $applicableCond .= ',';
          $applicableCond .= sprintf("'%s'", $this->_app->db->escapeString($a));
        }
        $s->addStatement(new SqlStatementMono($s->columns['applicable'], sprintf('%%s IN (%s)', $applicableCond)));
      }
			if (isset($this->_params['applicableType'])) $s->addStatement(new SqlStatementTri($s->columns['applicable_type'], $s->columns['applicable_type'], $this->_params['applicableType'], '(%s IS NULL OR %s=%s)'));
      $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s='N'"));
      $s->addOrder(new SqlStatementAsc($s->columns['provider']));
      $s->addOrder(new SqlStatementAsc($s->columns['category']));
      $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
      $s->setColumnsMask(array('attribute_id','customer_name','category'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $s1 = new SAttributeName;
        $s1->addStatement(new SqlStatementBi($s1->columns['attribute'], $row['attribute_id'], '%s=%s'));
        $s1->setColumnsMask(array('lang','name'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $a = array(); while ($row1 = $this->_app->db->fetchAssoc($res1)) { $a[$row1['lang']] = $row1['name']; }
        if (isset($this->_params['language'])&&isset($a[$this->_params['language']])) {
          $name = $a[$this->_params['language']];
        } else {
          $name = array_values($a)[0];
        }
        
        $name = sprintf('%s - %s', $row['customer_name'], $name);
        if ($row['category']) $name .= sprintf(' (%s)', $row['category']);
        $line = array('id' => $row['attribute_id'],'name' => $name);
        $this->_result[] = $this->_request->convertOutput($line);
      }
    }
    //adump($this->_result);die;
  }
}

?>