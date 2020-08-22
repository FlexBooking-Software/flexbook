<?php

class ModuleReportSearch extends ExecModule {
  protected $_formValidator;
  protected $_formValidatorData;
  
  protected $_resultValidator;

  protected function _search($data) { return array('data'=>array(),'summary'=>array()); }
  
  protected function _getColumnsMask(& $select) {
    $columnsMask = array();
    
    if ($this->_formValidatorData['groupValue']&&count($this->_formValidatorData['groupValue'])) {
      // uprava seznamu sloupcu na zaklade agregace
      foreach ($this->_formValidatorData['visibleColumn'] as $index=>$column) {
        if (isset($this->_formValidatorData['groupValue'][$column])&&$this->_formValidatorData['groupValue'][$column]) {
          switch ($this->_formValidatorData['groupValue'][$column]) {
            case 'sum': $select->addColumn(new SqlColumn(false, new SqlStatementMono($select->columns[$column], 'SUM(%s)'), 'sum__'.$column, true)); break;
            case 'count': $select->addColumn(new SqlColumn(false, new SqlStatementMono($select->columns[$column], 'COUNT(%s)'), 'count__'.$column, true)); break;
            case 'list': $select->addColumn(new SqlColumn(false, new SqlStatementMono($select->columns[$column], 'GROUP_CONCAT(DISTINCT %s SEPARATOR \'_NEWLINE_\')'), 'list__'.$column, true)); break;
            default: $this->_formValidatorData['groupValue'][$column] = '';
          }
          
          if ($this->_formValidatorData['groupValue'][$column]) {
            if (in_array($column,$this->_formValidatorData['groupColumn'])) $columnsMask[] = $column;
            $columnsMask[] = sprintf('%s__%s', $this->_formValidatorData['groupValue'][$column], $column);
          } else $columnsMask[] = $column;
        } else $columnsMask[] = $column;
      }  
    } else $columnsMask = $this->_formValidatorData['visibleColumn'];
    
    return $columnsMask;
  }
  
  protected function _getHeaderLine($columnsMask) {
    // popisovy radek vystupu
    $headerLine = array();
    
    foreach ($columnsMask as $val) {
      $cuttedVal = str_replace(array('sum__','count__','list__'), '', $val);
      $label = $this->_formValidatorData['labelColumn'][$cuttedVal];
      
      if (strpos($val,'__')!==false) {
        list($agr,$x) = explode('__', $val);
        $label .= sprintf(' - %s', $this->_app->textStorage->getText('label.report_resultGroup_'.$agr));
      }
      $headerLine[] = $label;
    }
    
    return $headerLine;
  }
  
  protected function _addGroupToSelect(& $select) {
    if ($this->_formValidatorData['groupColumn']&&count($this->_formValidatorData['groupColumn'])) {
      foreach ($select->columnsMask as $columnId) {
        $column =& $select->columns[$columnId];
        $column->needGroup = true;
      }
      foreach ($this->_formValidatorData['groupColumn'] as $val) {
        $select->addGroup($select->columns[$val]);
      }
    }
  }
  
  protected function _addUserAttributeToResult(& $resultLine, $user) {
    if ($this->_formValidatorData['providerId']) {
      $s1 = new SUserAttribute;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_formValidatorData['providerId'], '%s=%s'));
      $s1->addStatement(new SqlStatementBi($s1->columns['user'], $user, '%s=%s'));
      $s1->addOrder(new SqlStatementAsc($s1->columns['category']));
      $s1->addOrder(new SqlStatementAsc($s1->columns['sequence']));
      $s1->setColumnsMask(array('attribute','value'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        if (isset($resultLine['additional_'.$row1['attribute']])) {
          $resultLine['additional_'.$row1['attribute']] = $row1['value'];
        }
      }
    }
  }
     
  protected function _addReservationAttributeToResult(& $resultLine, $reservation) {
    if ($this->_formValidatorData['providerId']) {
      $s1 = new SReservationAttribute;
      $s1->addStatement(new SqlStatementBi($s1->columns['provider'], $this->_formValidatorData['providerId'], '%s=%s'));
      $s1->addStatement(new SqlStatementBi($s1->columns['reservation'], $reservation, '%s=%s'));
      $s1->addOrder(new SqlStatementAsc($s1->columns['category']));
      $s1->addOrder(new SqlStatementAsc($s1->columns['sequence']));
      $s1->setColumnsMask(array('attribute','value'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        if (isset($resultLine['additional_'.$row1['attribute']])) {
          $resultLine['additional_'.$row1['attribute']] = $row1['value'];
        }
      }
    }
  }

  protected function _userRun() {
    $this->_formValidator->initValues();
    $this->_formValidator->validateValues();
    $this->_formValidatorData = $this->_formValidator->getValues();
    
    $this->_resultValidator = Validator::get('result', 'ReportValidator', true);
    
    $ret = $this->_search($this->_formValidatorData);
    $summary = array_merge(array(sprintf($this->_app->textStorage->getText('label.report_resultSummary'),count($ret['data'])-1)),$ret['summary']);
    
    $this->_resultValidator->setValues(array(
              'loaded'=>1,
              'resultSummary'=>$summary,
              'result'=>$ret['data']));
    
    $this->_app->response->addParams(array('backwards'=>1));
    return 'eBack';
  }
}

?>
