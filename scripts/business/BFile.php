<?php

class BFile extends BusinessObject {
  private $_directory;

  public function __construct($params=array()) {
    parent::__construct($params);
    
    global $TMP_DIR;
    $this->_directory = $TMP_DIR;
  }
  
  protected function _load() {
    if ($this->_id) {
      $s = new SFile;
      $s->addStatement(new SqlStatementBi($s->columns['file_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('hash','name','mime','length'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $this->_data = array(
            'hash'    => $row['hash'],
            'name'    => $row['name'],
            'mime'    => $row['mime'],
            'length'  => $row['length'],
            );
      }
    }
  }

  public function export() {
    $this->_load();

    $s = new SFile;
    $s->addStatement(new SqlStatementBi($s->columns['file_id'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('content'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);

    $linkName = tempnam($this->_directory,'');
    unlink($linkName);
    $linkName = basename($linkName);
    $fileName = $linkName.'_'.str_replace(' ','<space>',$this->_data['name']);

    file_put_contents($this->_directory.$fileName, $row['content']);
    symlink($this->_directory.$fileName, $this->_directory.$linkName);

    return $linkName;
  }

  public function saveFromString($params) {
    $tempFileName = tempnam($this->_directory,'');
    $tempName = basename($tempFileName);

    file_put_contents($tempFileName, ifsetor($params['content']));

    $newParams = array(
      'name'  => ifsetor($params['name'],$tempName),
      'file'  => $tempName,
    );
    return $this->save($newParams);
  }
  
  public function save($params) {
    $realFile = $this->_directory.$params['file'];

    $this->_app->db->doQuery('SET CHARACTER SET binary');

    $oFile = new OFile($this->_id);
    $oFileData = array('name'     => $params['name'],
                       'length'   => filesize($realFile),
                       'mime'     => mime_content_type($realFile),
                       'content'  => file_get_contents($realFile));
    $oFile->setData($oFileData);
    $oFile->save();

    global $DB;
    if (isset($DB['encoding'])) $this->_app->db->doQuery(sprintf('SET NAMES "%s"', $DB['encoding']));

    if (!$this->_id) {
      $oFile->setData(array('hash'=>md5($oFile->getId())));
      $oFile->save();
    }
    
    if (!isset($params['keepFile'])||!$params['keepFile']) @unlink($realFile);
    
    return $oFile->getId();
  }
  
  public function getSize() {
    $this->_load();
    
    return $this->_data['length'];
  }

  public function getFileFromLink($link, $deleteLink=false) {
    $ret = null;

    $realFile = @readlink($this->_directory.$link);
    if (is_file($realFile)) {
      $ret = array(
        'file' => basename($realFile),
        'name' => str_replace(array(basename($link).'_','<space>'), array('',' '), basename($realFile))
      );

      if ($deleteLink) @unlink($this->_directory.$link);
    }

    return $ret;
  }
  
  public function delete() {
    $o = new OFile($this->_id);
    $o->delete();
  }
}

?>
