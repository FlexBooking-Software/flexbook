<?php

class GridSettingsOnlinePayment extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'onlinepayment_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('paymentId', 'paymentid', $ts->getText('label.listOnlinePayment_id')));
		$this->addColumn(new GridColumn('type', 'type', $ts->getText('label.listOnlinePayment_type'), 'none'));
    $this->addColumn(new GridColumn('start', 'start_timestamp', $ts->getText('label.listOnlinePayment_start'), 'none'));
		$this->addColumn(new GridColumn('end', 'end_timestamp', $ts->getText('label.listOnlinePayment_end'), 'none'));
		$this->addColumn(new GridColumn('amount', 'amount', $ts->getText('label.listOnlinePayment_amount'), 'none'));
		$this->addColumn(new GridColumn('user', 'user_fullname', $ts->getText('label.listOnlinePayment_user')));
    $this->addColumn(new GridColumn('target', 'target', $ts->getText('label.listOnlinePayment_target'), 'none'));
		$this->addColumn(new GridColumn('payed', 'payed', $ts->getText('label.listOnlinePayment_payed'), 'select'));
		$this->addColumn(new GridColumn('status', 'status', $ts->getText('label.listOnlinePayment_status'), 'none'));
    $this->addColumn(new GridColumn('action', 'onlinepayment_id', $ts->getText('label.grid_none'), 'none'));

    $this->_columnsMask = array('i','paymentId','type','start','end','amount','user','target','payed','status');
    $this->_onPage = $ONPAGE['listOnlinePayment'];
    $this->setForcedSources(array('onlinepayment_id','target_id','paymentdesc','ticket_name'));

    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'onlinepayment_id')));
    $this->getColumn('paymentId')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('paymentId')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('type')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('start')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('start')->addGuiElement(new GuiGridCellDateTime);
		$this->getColumn('end')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('end')->addGuiElement(new GuiGridCellDateTime);
		$this->getColumn('amount')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
		$this->getColumn('amount')->addElementAttribute('class', 'tdRight');
		$this->getColumn('user')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('user')->addFilterParam('classInput', 'mediumText');
		$this->getColumn('target')->addGuiElement(new GuiGridCellOnlinePaymentTarget);
		$this->getColumn('payed')->addGuiElement(new GuiGridCellYesNo);
		$this->getColumn('payed')->setFilterDataSource(new YesNoFilterDataSource);
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');

    $this->setOrder('start','desc');
  }
}

class GuiGridCellOnlinePaymentTarget extends GuiGridCellRenderer {

	protected function _userRender() {
		if (!strcmp($this->_outputData,'RESERVATION')) {
			$reservationId = array_filter(explode('|', $this->_rowData['target_id']));
			$s = new SReservation;
			$s->addStatement(new SqlStatementMono($s->columns['reservation_id'], sprintf('%%s IN (%s)', implode(',', $reservationId))));
			$s->setColumnsMask(array('number'));
			$res = $this->_app->db->doQuery($s->toString());
			$number = '';
			while ($row = $this->_app->db->fetchAssoc($res)) {
				if ($number) $number .= ',';
				$number .= $row['number'];
			}

			$this->setTemplateString(sprintf('%s<br/>%s', $this->_outputData, $number));
		} elseif (!strcmp($this->_outputData,'TICKET')) {
			$this->setTemplateString(sprintf('%s<br/>%s', $this->_outputData, $this->_rowData['ticket_name']));
		} else $this->setTemplateString($this->_outputData);
	}
}

class GuiOnlinePaymentGridRow extends GuiWebGridRow {

	protected function _preRenderRow($name, &$data, &$attributes) {
		$app = Application::get();

		parent::_preRenderRow($name, $data, $attributes);

		if (isset($data['payed'])&&($data['payed']=='Y')) $attributes['class'] .= ' payed';
	}
}

class GuiListOnlinePayment extends GuiWebGrid {

	protected function _initRow() {
		$params = $this->_getRowParams();
		$this->_guiGridRow = new GuiOnlinePaymentGridRow($params);
	}

  public function __construct($name='listOnlinePayment') {
    $app = Application::get();

    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsOnlinePayment($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SOnlinePayment($sqlSettings);

    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['user_provider'], $app->auth->getActualProvider(), '%s=%s'));
    }
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    $params = array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager);
    parent::__construct($params);
  }
}

?>