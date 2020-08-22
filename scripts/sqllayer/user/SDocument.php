<?php

class SDocument extends SqlSelect {
  private $_tDocument;
  private $_tUser;
  private $_tReservation;
  private $_tFile;

  private function _insertDocumentTable() {
    $this->_tDocument = new SqlTable('document', 'd');

    $this->addColumn(new SqlColumn($this->_tDocument, 'document_id'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'provider'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'code'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'type'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'number'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'user'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'reservation'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'content'));
    $this->addColumn(new SqlColumn($this->_tDocument, 'created'));
  }
  
  private function _insertUserTable() {
    $this->_tUser = new SqlTable('user', 'u');

    $this->addColumn(new SqlColumn($this->_tUser, 'user_id'));
    $this->addColumn(new SqlColumn($this->_tUser, 'firstname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'lastname'));
    $this->addColumn(new SqlColumn($this->_tUser, 'email'));
    $this->addColumn(new SqlColumn($this->_tUser, 'username'));
    $this->addColumn(new SqlColumn($this->_tUser, 'password'));
    $this->addColumn(new SqlColumn($this->_tUser, 'organiser'));
    $this->addColumn(new SqlColumn($this->_tUser, 'disabled'));
    
    $this->addColumn(new SqlColumn(false, new SqlStatementBi($this->columns['firstname'], $this->columns['lastname'], "CONCAT(%s,' ',%s)"), 'fullname'));
  }

  private function _insertReservationTable() {
    $this->_tReservation = new SqlTable('reservation', 'r');

    $this->addColumn(new SqlColumn($this->_tReservation, 'reservation_id'));
    $this->addColumn(new SqlColumn($this->_tReservation, 'number', 'reservation_number'));
  }

  private function _insertFileTable() {
    $this->_tFile = new SqlTable('file', 'f');

    $this->addColumn(new SqlColumn($this->_tFile, 'file_id'));
    $this->addColumn(new SqlColumn($this->_tFile, 'hash', 'file_hash'));
    $this->addColumn(new SqlColumn($this->_tFile, 'name', 'file_name'));
    $this->addColumn(new SqlColumn($this->_tFile, 'mime', 'file_mime'));
    $this->addColumn(new SqlColumn($this->_tFile, 'length', 'file_length'));
    $this->addColumn(new SqlColumn($this->_tFile, 'content', 'file_content'));
  }

  protected function _initSqlSelect() {
    $this->_insertDocumentTable();
    $this->_insertUserTable();
    $this->_insertReservationTable();
    $this->_insertFileTable();

    $this->addJoin(new SqlJoin(false, $this->_tUser, new SqlStatementBi($this->columns['user_id'], $this->columns['user'], "%s=%s")));
    $this->addJoin(new SqlJoin('LEFT', $this->_tReservation, new SqlStatementBi($this->columns['reservation_id'], $this->columns['reservation'], "%s=%s")));
    $this->addJoin(new SqlJoin(false, $this->_tFile, new SqlStatementBi($this->columns['file_id'], $this->columns['content'], "%s=%s")));
  }
}

?>
