<?php

class AjaxGetUser extends AjaxAction {

  protected function _userRun() {
    $page = $this->_params['page']; // get the requested page
    $limit = $this->_params['rows']; // get how many rows we want to have into the grid
    $sidx = $this->_params['sidx']; // get index row - i.e. user click to sort
    $sord = $this->_params['sord']; // get the direction
    $searchTerm = $this->_params['searchTerm'];
    $fake = ifsetor($this->_params['fake']);
		$scope = ifsetor($this->_params['scope']);
		$scopeEvent = ifsetor($this->_params['scopeEvent']);
		$provider = $this->_app->auth->getActualProvider();
		if (!$provider) {
			$provider = $this->_app->db->escapeString(ifsetor($this->_params['provider'],-1));
		}

    if (!$fake) {
      if (!$this->_app->auth->getUserId()) throw new ExceptionUserTextStorage('error.accessDenied');
    }

    if ($searchTerm == '') {
      $searchTerm = '%';
    } else {
      $searchTerm = '%' . $searchTerm . '%';
    }

    // nejdriv zjistim pocet odpovidajicich zaznamu
    $s = new SUser;
    $s->addStatement(new SqlStatementMono($s->columns['parent_user'], '%s IS NULL'));
    if ($searchTerm) {
    	$searchTerm = $this->_app->db->escapeString($searchTerm);
      $s->addStatement(new SqlStatementQuad($s->columns['fullname'], $searchTerm, $s->columns['email'], $searchTerm,
                                            '((%s LIKE %s) OR (%s LIKE %s))'));
    }
    if (!$fake) {
    	$s->addUserRegistrationSelectStatement(array('provider'), sprintf('%%s=%s', $provider));
			$s->addStatement(new SqlStatementMono($s->columns['provider_registration'], '%s>=1'));
			if (!$this->_app->auth->haveRight('user_admin', $provider)&&!$this->_app->auth->haveRight('organiser', $provider)) $s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
    }
    if ($scope) {
			if (!strcmp($scope,'event')) {
				if ($provider) $s->addEventAttendeeStatement(array('reservation_provider'), sprintf('%%s=\'%s\'', $provider));
				$s->addEventAttendeeStatement(array('event_all_tag_name'), sprintf('\'%s\' IN %%s', $this->_app->db->escapeString($scopeEvent)));

				$s->addStatement(new SqlStatementMono($s->columns['eventattendee'], '%s>=1'));
			} elseif (!strcmp($scope,'paidEvent')) {
				if ($provider) $s->addEventAttendeeStatement(array('reservation_provider'), sprintf('%%s=\'%s\'', $provider));
				$s->addEventAttendeeStatement(array('event_all_tag_name'), sprintf('\'%s\' IN %%s', $this->_app->db->escapeString($scopeEvent)));
				$s->addEventAttendeeStatement(array('reservation_payed'), '%s IS NOT NULL');

				$s->addStatement(new SqlStatementMono($s->columns['eventattendee'], '%s>=1'));
			} elseif (strcmp($scope,'all')) {
				$s->addStatement(new SqlStatementBi($s->columns['user_id'], $this->_app->auth->getUserId(), '%s=%s'));
			}
    }
    $s->setColumnsMask(array('user_id'));
		#error_log($s->toString());
    $res = $this->_app->db->doQuery($s->toString());
    $count = $this->_app->db->getRowsNumber($res);
    if ($count>0&&$limit) {
      $total_pages = ceil($count/$limit);
    } else {
      $total_pages = 0;
    }
    if ($page>$total_pages) $page = $total_pages;
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    
    $this->_result = (object) array();
    $this->_result->page = $page;
    $this->_result->total = $total_pages;
    $this->_result->records = $count;

    // pak ziskam odpovidajici zaznamy (serazeny dle pozadavku)
    if ($total_pages) $s->setLimit(array(intval($start),intval($limit)));
    if ($sidx&&$sord) {
    	switch ($sidx) {
				case 'name': $sidx = 'fullname';break;
				case 'address': $sidx = 'full_address';break;
			}
    	if ($sord=='asc') $s->addOrder(new SqlStatementAsc($s->columns[$sidx]));
    	elseif ($sord=='desc') $s->addOrder(new SqlStatementDesc($s->columns[$sidx]));
		}
    $s->setColumnsMask(array('user_id','fullname','firstname','lastname','email','full_address'));
    // kdyz je pozadovany nestandartni sloupec (jsou povoleny pouze nektere)
    if (isset($this->_params['customColumn'])) {
			if (in_array($this->_params['customColumn'],array('name','firstname','lastname','email','address'))) $s->addToColumnsMask($this->_params['customColumn']);
			elseif (strpos($this->_params['customColumn'],'attribute_')===0) {
				// je mozny i atribut, ten je slozitejsi
				$attributeShortName = str_replace('attribute_', '', $this->_params['customColumn']);
				$provider = $this->_app->auth->getActualProvider();
				$s->addStatement(new SqlStatementBi($s->columns['attribute_short_name'], $attributeShortName, '%s=%s'));
				$s->addStatement(new SqlStatementBi($s->columns['attribute_provider'], $provider, '%s=%s'));
				$s->addToColumnsMask(array('attribute_short_name','attribute_type','attribute_value'));
			}
		}
    $res = $this->_app->db->doQuery($s->toString(true));
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $row['id'] = $row['user_id'];
      $row['name'] = $row['fullname'];
			$row['address'] = $row['full_address'];
			if (isset($row['attribute_value'])) {
				$row['attribute_'.$row['attribute_short_name']] = $row['attribute_value'];
				unset($row['attribute_short_name']);
				unset($row['attribute_value']);
				unset($row['attribute_type']);
			}
      unset($row['full_address']);
      unset($row['fullname']);

      $this->_result->rows[] = $this->_request->convertOutput($row);
    }
  }
}

?>