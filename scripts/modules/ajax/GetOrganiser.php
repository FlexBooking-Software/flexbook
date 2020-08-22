<?php

class AjaxGetOrganiser extends AjaxAction {

  protected function _userRun() {  
    if (isset($this->_params['provider'])) {
      $s = new SUserRegistration;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
			$s->addStatement(new SqlStatementBi($s->columns['organiser'], $s->columns['power_organiser'], "(%s='Y' OR %s='Y')"));
      $s->addOrder(new SqlStatementAsc($s->columns['fullname_reversed']));
      $s->setColumnsMask(array('user','fullname_reversed','role_center'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
				$organiserCenter = explode(',',$row['role_center']);
				if (isset($this->_params['center'])) {
					if ((strpos($row['role_center'],'ALL')===false)&&!in_array($this->_params['center'], $organiserCenter)) continue;
				}

        $row['id'] = $row['user'];
        $row['name'] = $row['fullname_reversed'];
        $this->_result[] = $this->_request->convertOutput($row);
      }
    }
  }
}

?>