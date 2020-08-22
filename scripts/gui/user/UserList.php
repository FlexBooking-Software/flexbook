<?php

class GridSettingsUser extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('fulltext', 'user_id', $ts->getText('label.grid_fulltext')));
    $this->addColumn(new GridColumn('id', 'user_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('role', 'user_id', $ts->getText('label.listUser_role'), 'none'));
    $this->addColumn(new GridColumn('firstname', 'firstname', $ts->getText('label.listUser_firstname'), 'none'));
    $this->addColumn(new GridColumn('lastname', 'lastname', $ts->getText('label.listUser_lastname'), 'none'));
    $this->addColumn(new GridColumn('fullname', 'fullname', $ts->getText('label.listUser_fullname'), 'none'));
    $this->addColumn(new GridColumn('address', 'full_address', $ts->getText('label.listUser_address'), 'none'));
    $this->addColumn(new GridColumn('username', 'username', $ts->getText('label.listUser_username'), 'none'));
    $this->addColumn(new GridColumn('email', 'email', $ts->getText('label.listUser_email'), 'none'));
    $this->addColumn(new GridColumn('phone', 'phone', $ts->getText('label.listUser_phone'), 'none'));
    $this->addColumn(new GridColumn('validated', 'validated', $ts->getText('label.listUser_validated'), 'selectNull'));
		$this->addColumn(new GridColumn('parent', 'parent_user', $ts->getText('label.listUser_accountType'), 'none'));
		$this->addColumn(new GridColumn('reservationCondition', 'reservationcondition', $ts->getText('label.listUser_reservationCondition'), 'select'));
    $this->addColumn(new GridColumn('disabled', 'disabled', $ts->getText('label.listUser_disabled'), 'select'));
    $this->addColumn(new GridColumn('action', 'user_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listUser'))) {
      if ($app->auth->isAdministrator()) $this->_columnsMask = array('i','id','role','firstname','lastname','email','phone','action');
      else $this->_columnsMask = array('i','id','firstname','lastname','email','phone','action');
      $this->_onPage = $ONPAGE['listUser'];
    } elseif (in_array($this->_name, array('listUserSubaccount'))) {
			if ($app->auth->isAdministrator()) $this->_columnsMask = array('i','id','role','firstname','lastname','email','action');
			else $this->_columnsMask = array('i','id','firstname','lastname','email','action');
			$this->_onPage = $ONPAGE['listUser'];
		} elseif ($this->_name == 'selectUser') {
      $this->_columnsMask = array('id', 'fullname');
      $this->_onPage = null;
    }
    $this->setForcedSources(array('parent_user','admin','registration_power_organiser','registration_organiser','registration_admin','registration_reception'));

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
			'conditions' => array('parent_user'=>null),
			'action' => 'eUserEdit',
			'imgsrc'    => 'img/button_grid_detail.png',
			'label' => $ts->getText('button.grid_edit'),
			'title' => $ts->getText('button.grid_edit'),
			'varName' => 'id'
		)));
		$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
			'restrictions' => array('parent_user'=>null),
			'action' => 'eUserEdit',
			'constants' => array('subaccount'=>1),
			'imgsrc' => 'img/button_grid_detail.png',
			'label' => $ts->getText('button.grid_edit'),
			'title' => $ts->getText('button.grid_edit'),
			'varName' => 'id'
		)));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('validated'=>null),
          'action' => 'eUserValidate',
          'imgsrc'    => 'img/button_grid_validate.png',
          'label' => $ts->getText('button.listUser_validate'),
          'title' => $ts->getText('button.listUser_validate'),
          'onclick' => 'return confirm(\''.$ts->getText('label.listUser_confirmValidate').'\');',
          'varName' => 'id')));
    if ($app->auth->haveRight('credit_admin','ANY')) {
			$providerSettings = BCustomer::getProviderSettings($app->auth->getActualProvider(),array('disableCredit','disableTicket','disableCash'));
			if ($app->auth->isAdministrator()||($providerSettings['disableCredit']=='N')||($providerSettings['disableTicket']=='N')||($providerSettings['disableCash']=='N')) {
				$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
					'conditions' => array('parent_user' => null),
					'action' => 'eUserCredit',
					'imgsrc' => 'img/button_grid_credit.png',
					'label' => $ts->getText('button.listUser_credit'),
					'title' => $ts->getText('button.listUser_credit'),
					'varName' => 'id'
				)));
			}
    }
		$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
						'conditions' => array('disabled'=>'N'/*,'parent_user'=>null*/),
						'action' => 'eUserDisable',
						'imgsrc'    => 'img/button_grid_disable.png',
						'label' => $ts->getText('button.grid_disable'),
						'title' => $ts->getText('button.grid_disable'),
						'varName' => 'id')));
		$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
						'conditions' => array('disabled'=>'Y'/*,'parent_user'=>null*/),
						'action' => 'eUserEnable',
						'imgsrc'    => 'img/button_grid_enable.png',
						'label' => $ts->getText('button.grid_enable'),
						'title' => $ts->getText('button.grid_enable'),
						'varName' => 'id')));
		if ($app->auth->haveRight('delete_record',$app->auth->getActualProvider())) {
			$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
				'action' => 'eUserDelete',
				'imgsrc' => 'img/button_grid_delete.png',
				'label' => $ts->getText('button.grid_delete'),
				'title' => $ts->getText('button.grid_delete'),
				'onclick' => 'return confirm(\'' . $ts->getText('label.grid_confirmDelete') . '\');',
				'varName' => 'id'
			)));
		}

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'user_id')));
    $this->getColumn('id')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('fulltext')->addFilterParam('classInput', 'longText');
    
    $this->getColumn('username')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('username')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('firstname')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('lastname')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('fullname')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('fullname')->addFilterParam('classInput', 'mediumText');
    
    $this->getColumn('role')->addGuiElement(new GuiGridCellRole);

    $this->getColumn('validated')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('validated')->setFilterDataSource(new NullNotNullFilterDataSource);

    if (BCustomer::getProviderSettings($app->auth->getActualProvider(),'userSubaccount')=='Y') {
			$this->getColumn('parent')->setSearchType('select');
			$this->getColumn('parent')->setFilterDataSource(new HashDataSource(new DataSourceSettings,
				array(''=>$app->textStorage->getText('label.select_filter'),'PRIMARY'=>$app->textStorage->getText('label.listUser_accountType_main'),'SUBACCOUNT'=>$app->textStorage->getText('label.listUser_accountType_sub'))));
		}

    if ($app->auth->getActualProvider()) $this->getColumn('reservationCondition')->setFilterDataSource(new SqlFilterDataSource('ReservationCondition', array(array('source'=>'provider','value'=>$app->auth->getActualProvider())), 'name'));
    else $this->getColumn('reservationCondition')->setFilterDataSource(new SqlFilterDataSource('ReservationCondition', null, 'name'));

    $this->getColumn('disabled')->addElementAttribute('class', 'tdCenter');
    $this->getColumn('disabled')->addGuiElement(new GuiGridCellYesNo);
    $this->getColumn('disabled')->setFilterDataSource(new YesNoFilterDataSource);
    
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
  
  public function getDefaultFilter() {
  	$app = Application::get();

  	$ret = array();

  	if ($this->_name=='listUser') {
  		$ret = array('disabled'=>'N');
  		if (BCustomer::getProviderSettings($app->auth->getActualProvider(),'userSubaccount')=='Y') $ret['parent'] = 'PRIMARY';
  	} else {
			if (BCustomer::getProviderSettings($app->auth->getActualProvider(),'userSubaccount')=='Y') $ret['parent'] = 'SUBACCOUNT';
		}

  	return $ret;
  }
}

class GuiGridCellRole extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $template = '';
    
    if ($this->_rowData['admin']=='Y') {
      if ($template) $template .= '&nbsp;';
      $template .= '<img title="{__label.listUser_roleAdministrator}" src="img/icon_administrator.png"/>';
    }
    if (($this->_rowData['registration_power_organiser']=='Y')||($this->_rowData['registration_organiser']=='Y')) {
      if ($template) $template .= '&nbsp;';
      $template .= '<img title="{__label.listUser_roleOrganiser}" src="img/icon_organiser.png"/>';
    }
    if (($this->_rowData['registration_admin']=='Y')||($this->_rowData['registration_reception']=='Y')) {
      if ($template) $template .= '&nbsp;';
      $template .= '<img title="{__label.listUser_roleProvider}" src="img/icon_provider.png"/>';
    }
    
    $this->setTemplateString($template);
  }
}

class GuiUserGridRow extends GuiWebGridRow {

	protected function _preRenderRow($name, &$data, &$attributes) {
		parent::_preRenderRow($name, $data, $attributes);

		if (isset($data['parent_user'])) {
			if ($data['parent_user']) $attributes['class'] .= ' realised';
		}
	}
}

class GuiListUser extends GuiWebGrid {

	protected function _initRow() {
		if ($this->_settings->getName()=='listUser') {
			$params = $this->_getRowParams();
			$this->_guiGridRow = new GuiUserGridRow($params);
		} else parent::_initRow();
	}

  public function __construct($name='listUser') {
    $app = Application::get();
    $showFilter = $name=='listUser';
    $showPager = $name=='listUser';

    $gridSettings = new GridSettingsUser($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SUser($sqlSettings);

    // seznam poductu v editaci hlavniho uctu
		if ($name=='listUserSubaccount') {
			$validator = Validator::get('user', 'UserValidator');
			$data = $validator->getValues();
			$select->addStatement(new SqlStatementBi($select->columns['parent_user'], $data['id'], '%s=%s'));
		}
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'user_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementQuad($select->columns['fullname'], $select->columns['email'], $select->columns['phone'], $select->columns['full_address'],
            sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond, $likeCond)));
    }

    if (isset($temp['filter']['parent'])) {
			$filter =& $sqlSettings->getFilter();
			foreach ($filter as $k=>$v) {
				if ($v['source'] == 'parent_user') { unset($filter[$k]); }
			}

			if ($temp['filter']['parent']=='PRIMARY') $select->addStatement(new SqlStatementMono($select->columns['parent_user'], '%s IS NULL'));
			elseif ($temp['filter']['parent']=='SUBACCOUNT') $select->addStatement(new SqlStatementMono($select->columns['parent_user'], '%s IS NOT NULL'));
		}

    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['registration_provider'], $app->auth->getActualProvider(), '%s=%s'));
      #$select->addStatement(new SqlStatementBi($select->columns['user_id'], $app->auth->getUserId(), '%s<>%s'));
    }
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    $params = array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager);
    if ($name=='listUser') $params = array_merge($params,array('doubleClickAction'=>'eUserEdit', 'doubleClickColumn'=>'user_id', 'doubleClickVarName'=>'id'));
    parent::__construct($params);
  }
}

?>