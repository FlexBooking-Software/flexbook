<?php

class ModuleMain extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('home', 'HomeValidator');
    
    // pridani/odebrani vice zdroju
    if (!strcmp($this->_app->request->getParams('commodity'),'resource')) {
      // nove tagy dam do validatoru vzdycky
      if ($tags = $this->_app->request->getParams('resourceTag')) $validator->setValues(array('resourceTag'=>$tags));
      
      $id = $this->_app->request->getParams('id');
      if (count($id)>1) {
        // kontrola, jestli vice vybranych zdroju muze byt v multi-kalendari
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
        if ($this->_app->auth->getActualCenter()) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$id)))));
        $s->setColumnsMask(array('resource_id','unitprofile'));
        $res = $this->_app->db->doQuery($s->toString());
        $unitProfile = null;
        while ($r = $this->_app->db->fetchAssoc($res)) {
          if ($unitProfile&&($unitProfile!=$r['unitprofile'])) throw new ExceptionUserTextStorage('error.calendar_multi_unitprofileConflict');
          else $unitProfile = $r['unitprofile'];
        }
        
        $validator->initValues();
      } elseif (!strcmp($id[0],'all')) {
        // kdyz se ma zobrazit kalendar vsech vyfiltrovanych zdroju
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
        if ($this->_app->auth->getActualCenter()) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
        if ($tag=$validator->getVarValue('resourceTag')) $s->addStatement(new SqlStatementMono($s->columns['tag'], sprintf('%%s IN (%s)', $this->_app->db->escapeString($tag))));
        $s->setColumnsMask(array('resource_id','unitprofile'));
        $res = $this->_app->db->doQuery($s->toString());
        $unitProfile = null;
        $selected = array();
        while ($r = $this->_app->db->fetchAssoc($res)) {
          $selected[] = $r['resource_id'];
          if ($unitProfile&&($unitProfile!=$r['unitprofile'])) throw new ExceptionUserTextStorage('error.calendar_multi_unitprofileConflict');
          else $unitProfile = $r['unitprofile'];
        }
        
        $validator->setValues(array('id'=>$selected,'commodity'=>'resource'));
      } elseif ($this->_app->request->getParams('remove')) {
        $id = $this->_app->request->getParams('id');
        foreach ($selected = $validator->getVarValue('id') as $index=>$value) {
          if ($value==$id) {
            unset($selected[$index]);
            break;
          }
        }
        
        $validator->setValues(array('id'=>$selected));
      } else {
        $validator->initValues();
      }
    } else {
      $validator->initValues();
    }
    
    if (!$id = $validator->getVarValue('id')) {
      $s = new SResource;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
      if ($this->_app->auth->getActualCenter()) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('resource_id', 'name'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) {
        $commodity = 'resource';
        $id = $row['resource_id'];
      }
      if (!$id) {
        $s = new SEvent;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
        if ($this->_app->auth->getActualCenter()) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
        $s->addOrder(new SqlStatementAsc($s->columns['start']));
        $s->setColumnsMask(array('event_id', 'start', 'name'));
        $res = $this->_app->db->doQuery($s->toString());
        if ($row = $this->_app->db->fetchAssoc($res)) {
          $commodity = 'event';
          $id = $row['event_id'];      
        }
      }
      
      if ($id) $validator->setValues(array('id'=>array($id),'commodity'=>$commodity));
    }
    
    return 'vMain';
  }
}

?>
