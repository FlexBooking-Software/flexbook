<?php

class SUserValidation extends MySqlSelect {
  private $_tUserValidation;
  
  private function _insertUserValidationTable() {
    $this->_tUserValidation = new SqlTable('uservalidation', 'uv');
    
    $this->addColumn(new SqlColumn($this->_tUserValidation, 'user'));
    $this->addColumn(new SqlColumn($this->_tUserValidation, 'validation_string'));
  }

  protected function _initSqlSelect() {
    $this->_insertUserValidationTable();
    
  }
}

?>
