<?php

class GridSettingsReservation extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;

    // nechceme rozlisovat sudy/lichy radek, coz je definovano ve WebGridSettings
    GridSettings::_initSettings();
    $this->addRow($rowHeader = new GridRow('header'));

    $rotates = array($rowData1 = new GridRow('data1'), $rowData2 = new GridRow('data2'));
    $this->addRow($rowData = new GridRowRotate('data', $rotates));

    $rowHeader->addElementAttribute('class','Header');
    $rowData1->addElementAttribute('class','Even');
    $rowData2->addElementAttribute('class','Even');

    $this->addGuiGridTableAttribute('class', 'gridTable');
    $this->addGuiGridFilterDivAttribute('class', 'gridFilterForm');
    $this->addGuiGridPagerDivAttribute('class', 'gridPagerForm');
    $this->addGuiGridMultiactionDivAttribute('class', 'gridMultiactionForm');

    $this->setGuiGridPagerLabel(Application::get()->textStorage->getText('label.grid_pager'));
    $this->setGuiGridPagerButtonLabel(Application::get()->textStorage->getText('button.grid_pager'));
    $this->setGuiGridFilterButtonLabelSubmit(Application::get()->textStorage->getText('button.grid_filterSubmit'));
    $this->setGuiGridFilterButtonLabelReset(Application::get()->textStorage->getText('button.grid_filterReset'));
    $this->addGuiGridPagerDivPagingAttribute('class','pages');
    
    $app = Application::get();
    $ts = $app->textStorage;
    
    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'reservation_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listReservation_provider'), 'none'));
    $this->addColumn(new GridColumn('providerName', 'provider_with_center', $ts->getText('label.listReservation_provider'), 'none'));
    $this->addColumn(new GridColumn('center', 'center_id', $ts->getText('label.listReservation_center'), 'none'));
    $this->addColumn(new GridColumn('centerName', 'center_name', $ts->getText('label.listReservation_center'), 'none'));
    $this->addColumn(new GridColumn('number', 'number', $ts->getText('label.listReservation_number')));
    $this->addColumn(new GridColumn('user', 'user_id', $ts->getText('label.listReservation_user'), 'customUser'));
    $this->addColumn(new GridColumn('userName', 'user_name', $ts->getText('label.listReservation_user'), 'none'));
    $this->addColumn(new GridColumn('userNameReversed', 'user_name_reversed', $ts->getText('label.listReservation_user'), 'none'));
    $this->addColumn(new GridColumn('customer', 'customer_id', $ts->getText('label.listReservation_customer'), 'none'));
    $this->addColumn(new GridColumn('customerName', 'customer_name', $ts->getText('label.listReservation_customer'), 'none'));
    $this->addColumn(new GridColumn('created', 'created', $ts->getText('label.listReservation_created'), 'none'));
    $this->addColumn(new GridColumn('payed', 'payed', $ts->getText('label.listReservation_payed'), 'none'));
    $this->addColumn(new GridColumn('start', 'start', $ts->getText('label.listReservation_start'), 'none'));
    $this->addColumn(new GridColumn('commodity', 'reservation_id', $ts->getText('label.listReservation_commodity'), 'none'));
    $this->addColumn(new GridColumn('event', 'event', $ts->getText('label.listReservation_event'), 'none'));
    $this->addColumn(new GridColumn('eventName', 'event_name', $ts->getText('label.listReservation_event'), 'none'));
    $this->addColumn(new GridColumn('eventPlaces', 'event_places', $ts->getText('label.listReservation_eventPlaces'), 'none'));
    $this->addColumn(new GridColumn('eventAttendeePerson', 'eventattendee_id', $ts->getText('label.listReservation_eventAttendee'), 'none'));
    $this->addColumn(new GridColumn('resource', 'resource', $ts->getText('label.listReservation_resource'), 'none'));
    $this->addColumn(new GridColumn('resourceName', 'resource_name', $ts->getText('label.listReservation_resource'), 'none'));
    $this->addColumn(new GridColumn('payId', 'onlinepayment_paymentid', $ts->getText('label.listReservation_payId')));
    $this->addColumn(new GridColumn('state', 'reservation_id', $ts->getText('label.listReservation_state'), 'select'));
    $this->addColumn(new GridColumn('price', 'total_price', $ts->getText('label.listReservation_price'), 'none'));
    $this->addColumn(new GridColumn('fulltext', 'reservation_id', $ts->getText('label.listReservation_fulltext')));
    $this->addColumn(new GridColumn('action', 'reservation_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listReservation'))) {
      if (!$app->auth->isAdministrator()) $this->_columnsMask = array('id', 'i', 'start', 'number', 'userName', 'commodity', 'price', 'action');
      else $this->_columnsMask = array('id', 'i', 'providerName', 'start', 'number', 'userName', 'commodity', 'price', 'action');
      $this->_onPage = $ONPAGE['listReservation'];
    } elseif ($this->_name == 'selectReservation') {
      $this->_columnsMask = array('id', 'number');
      $this->_onPage = null;
    } elseif ($this->_name == 'listUserReservation') {
      $this->_columnsMask = array('i', 'number', 'providerName', 'commodity', 'price', 'action');
    } elseif ($this->_name == 'listEventReservation') {
      $this->_columnsMask = array('i', 'number', 'userNameReversed', 'eventPlaces', 'eventAttendeePerson', 'price', 'action');
      $this->setForcedSources(array('eventattendee_failed'));
      $this->_onPage = null;
    } elseif ($this->_name == 'listResourceReservation') {
      $this->_columnsMask = array('i', 'number', 'userName', 'commodity', 'price', 'action');
      $this->_onPage = null;
    }
    $this->setForcedSources(array_merge($this->getForcedSources(),array('provider_id','user_name','failed','cancelled','payed','mandatory','start',
      'event','event_name','event_places','event_start','event_pack','all_event_resource_name',
      'resource','resource_name','resource_from','resource_to')));

    if ($this->_name == 'listEventReservation') {
      $this->setOrder('payed','asc');
    } else {
      $this->setOrder('start','asc');
    }
    
    $this->getColumn('i')->addElementAttribute('class', 'index');

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'action' => 'eReservationEdit',
          'imgsrc'    => 'img/button_grid_detail.png',
          'label' => $ts->getText('button.grid_edit'),
          'title' => $ts->getText('button.grid_edit'),
          'varName' => 'id')));
    /*$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('cancelled'=>null),
          'action' => 'vReservationTicket',
          'imgsrc'    => 'img/button_grid_ticket.png',
          'label' => $ts->getText('button.grid_ticket'),
          'title' => $ts->getText('button.grid_ticket'),
          'target' => '_blankTicket',
          'varName' => 'id')));*/
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('cancelled'=>null),
          'action' => 'vReservationTicketForm',
          'imgsrc'    => 'img/button_grid_ticket.png',
          'label' => $ts->getText('button.grid_ticket'),
          'title' => $ts->getText('button.grid_ticket'),
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('cancelled'=>null,'event_badge'=>'Y'),
          'dynamics' => array('provider'=>'provider_id'),
          'action' => 'vReservationBadge',
          'imgsrc'    => 'img/button_grid_badge.png',
          'label' => $ts->getText('button.grid_badge'),
          'title' => $ts->getText('button.grid_badge'),
          'target' => '_blankBadge',
          'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
      'restrictions' => array('receipt_number'=>null),
      'action' => 'vReservationReceipt',
      'imgsrc'    => 'img/button_grid_credit.png',
      'label' => $ts->getText('button.grid_receipt'),
      'title' => $ts->getText('button.grid_receipt'),
      'target' => '_blankReceipt',
      'varName' => 'id')));
    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
      'restrictions' => array('invoice_number'=>null),
      'action' => 'vReservationInvoice',
      'imgsrc'    => 'img/button_grid_receipt.png',
      'label' => $ts->getText('button.grid_invoice'),
      'title' => $ts->getText('button.grid_invoice'),
      'target' => '_blankInvoice',
      'varName' => 'id')));
    if ($app->auth->haveRight('credit_admin','ANY')) {
      $providerSettings = BCustomer::getProviderSettings($app->auth->getActualProvider(),array('disableCredit','disableTicket','disableCash'));
      if ($app->auth->isAdministrator()||($providerSettings['disableCredit']=='N')||($providerSettings['disableTicket']=='N')||($providerSettings['disableCash']=='N')) {
        $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
          'conditions' => array('payed' => null, 'cancelled' => null, 'failed' => null),
          'action' => 'eReservationPreparePay',
          'imgsrc' => 'img/button_grid_pay.png',
          'label' => $ts->getText('button.listReservation_pay'),
          'title' => $ts->getText('button.listReservation_pay'),
          'onclick' => 'return confirm(\'' . $ts->getText('label.listReservation_confirmPay') . '\');',
          'varName' => 'id'
        )));
      }
    }
    if (!strcmp($this->_name,'listEventReservation')) {
      $val = Validator::get('event', 'EventValidator');
      $data = $val->getValues();
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('cancelled'=>null,'failed'=>null,'eventattendee_failed'=>null,'event_pack'=>'Y'),
        'constants' => array('event'=>$data['id']),
        'action'  => 'eReservationChooseFail',
        'imgsrc'    => 'img/button_grid_fail.png',
        'label'   => $ts->getText('button.grid_fail'),
        'title'   => $ts->getText('button.grid_fail'),
        'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('cancelled'=>null,'failed'=>null,'eventattendee_failed'=>null),
        'restrictions' => array('event_pack'=>'Y'),
        'action'  => 'eReservationFail',
        'imgsrc'    => 'img/button_grid_fail.png',
        'label'   => $ts->getText('button.grid_fail'),
        'title'   => $ts->getText('button.grid_fail'),
        'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmFail').'\');',
        'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('cancelled'=>null,'failed'=>null,'eventattendee_failed'=>null,'event_pack'=>'Y'),
        'constants' => array('event'=>$data['id']),
        'action'  => 'eReservationChooseCancel',
        'imgsrc'    => 'img/button_grid_cancel.png',
        'label'   => $ts->getText('button.grid_cancel'),
        'title'   => $ts->getText('button.grid_cancel'),
        'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('cancelled'=>null,'failed'=>null,'eventattendee_failed'=>null),
        'restrictions' => array('event_pack'=>'Y'),
        'action'  => 'eReservationPrepareCancel',
        'imgsrc'    => 'img/button_grid_cancel.png',
        'label'   => $ts->getText('button.grid_cancel'),
        'title'   => $ts->getText('button.grid_cancel'),
        'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmCancel').'\');',
        'varName' => 'id')));
    } else {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('cancelled'=>null,'failed'=>null),
        'action'  => 'eReservationFail',
        'imgsrc'    => 'img/button_grid_fail.png',
        'label'   => $ts->getText('button.grid_fail'),
        'title'   => $ts->getText('button.grid_fail'),
        'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmFail').'\');',
        'varName' => 'id')));
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('cancelled'=>null,'failed'=>null),
        'action'  => 'eReservationPrepareCancel',
        'imgsrc'    => 'img/button_grid_cancel.png',
        'label'   => $ts->getText('button.grid_cancel'),
        'title'   => $ts->getText('button.grid_cancel'),
        'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmCancel').'\');',
        'varName' => 'id')));
    }
    /*if ($app->auth->haveRight('credit_admin','ANY')) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
            'conditions' => array('cancelled'=>null,'failed'=>null),
            'restrictions' => array('payed'=>null),
            'action'  => 'eReservationPrepareRefund',
            'imgsrc'    => 'img/button_grid_cancel_refund.png',
            'label'   => $ts->getText('button.listReservation_cancelRefund'),
            'title'   => $ts->getText('button.listReservation_cancelRefund'),
            'onclick' => 'return confirm(\''.$ts->getText('label.listReservation_confirmCancelRefund').'\');',
            'varName' => 'id')));
    }*/
    if ($app->auth->haveRight('delete_record',$app->auth->getActualProvider())) {
      $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
        'conditions' => array('payed' => null),
        'action' => 'eReservationDelete',
        'imgsrc' => 'img/button_grid_delete.png',
        'label' => $ts->getText('button.grid_delete'),
        'title' => $ts->getText('button.grid_delete'),
        'onclick' => 'return confirm(\'' . $ts->getText('label.grid_confirmDelete') . '\');',
        'varName' => 'id'
      )));
    }

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'reservation_id')));
    
    if ($app->auth->isAdministrator()) {
      $this->getColumn('provider')->setSearchType('select');
      $this->getColumn('provider')->setFilterDataSource(new SqlFilterDataSource('Provider'));
    } else {
      /*$this->getColumn('center')->setSearchType('select');
      $this->getColumn('center')->setFilterDataSource(new SqlFilterDataSource('Center',array('provider'=>$app->auth->getActualProvider())));*/
    }
    
    $this->getColumn('created')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('start')->addGuiElement(new GuiGridCellDateTime);
    $this->getColumn('start')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('number')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('number')->addFilterParam('classInput', 'mediumText');
    $this->getColumn('userName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('userNameReversed')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('fulltext')->addFilterParam('classInput', 'longText');
    $this->getColumn('customerName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('resourceName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('eventName')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    
    if (in_array($this->_name, array('listUserReservation','listReservation'))) $this->getColumn('commodity')->addGuiElement(new GuiGridCellCommodity);
    elseif ($this->_name=='listResourceReservation') $this->getColumn('commodity')->addGuiElement(new GuiGridCellResourceDates);
    
    #$this->getColumn('commodity')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    #$this->getColumn('commodity')->setOrderAsc(array(array('direction'=>'asc','source'=>'start')));
    #$this->getColumn('commodity')->setOrderDesc(array(array('direction'=>'desc','source'=>'start')));
    
    $this->getColumn('price')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('price')->addGuiElement(new GuiGridCellNumber(array('decimalPlaces'=>2,'replaceSpaces'=>'&nbsp;')));
    $this->getColumn('price')->addElementAttribute('class', 'tdRight');
    
    $ds = new HashDataSource(new DataSourceSettings, array(
                            'ACTIVE'            => $app->textStorage->getText('label.listReservation_stateACTIVE'),
                            'REALISED'          => $app->textStorage->getText('label.listReservation_stateREALISED'),
                            'CANCELLED'         => $app->textStorage->getText('label.listReservation_stateCANCELLED'),
                            'FAILED'            => $app->textStorage->getText('label.listReservation_stateFAILED'),
                            'ONLINEPAYMENT'     => $app->textStorage->getText('label.listReservation_stateONLINEPAYMENT'),
                            'PAYED'             => $app->textStorage->getText('label.listReservation_statePAYED'),
                            ));
    $this->getColumn('state')->setFilterParams(array('firstOption'=>$app->textStorage->getText('label.select_filter')));
    $this->getColumn('state')->setFilterDataSource($ds);
    
    $this->getColumn('eventAttendeePerson')->addGuiElement(new GuiGridCellEventAttendeePerson);
    
    $this->getColumn('action')->addElementAttribute('class', 'tdAction');   
  }
  
  public function getDefaultFilter() {
    $ret = array();
    if (in_array($this->_name,array('listReservation','listUserReservation','listResourceReservation'))) $ret['state'] ='ACTIVE';

    return $ret;
  }
}

class GuiGridCellCommodity extends GuiGridCellRenderer {
  
  protected function _userRender() {
    if ($this->_rowData['event']) {
      $start = '';
      if (isset($this->_rowData['event_pack'])&&($this->_rowData['event_pack']=='Y')) {
        $s = new SEventAttendee;
        $s->addStatement(new SqlStatementBi($s->columns['reservation'], $this->_rowData['reservation_id'], '%s=%s'));
        $s->addOrder(new SqlStatementAsc($s->columns['start']));
        $s->setColumnsMask(array('start'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($start) $start .= ', ';
          $start .= $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
        }
      }
      
      $this->setTemplateString(sprintf('%s - %dx<br/>%s', $this->_rowData['event_name'], $this->_rowData['event_places'], $start));
    } else {
      $from = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['resource_from']);
      if (substr($this->_rowData['resource_from'],0,10)==substr($this->_rowData['resource_to'],0,10)) {
        $to = $this->_app->regionalSettings->convertTimeToHuman(substr($this->_rowData['resource_to'],11),'h:m');
      } else {
        $to = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['resource_to']);  
      }
      $interval = sprintf('%s - %s', $from, $to);
      
      $this->setTemplateString(sprintf('%s<br/>%s', $this->_rowData['resource_name'], $interval));
    }
  }
}

class GuiGridCellResourceDates extends GuiGridCellRenderer {
  
  protected function _userRender() {
    $from = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['resource_from']);
    if (substr($this->_rowData['resource_from'],0,10)==substr($this->_rowData['resource_to'],0,10)) {
      $to = $this->_app->regionalSettings->convertTimeToHuman(substr($this->_rowData['resource_to'],11),'h:m');
    } else {
      $to = $this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['resource_to']);  
    }
    $interval = sprintf('%s - %s', $from, $to);
    
    $this->setTemplateString(sprintf('%s', $interval));
  }
}

class GuiReservationGridRow extends GuiWebGridRow {

  protected function _preRenderRow($name, &$data, &$attributes) {
    $app = Application::get();

    parent::_preRenderRow($name, $data, $attributes);
    
    if (isset($data['cancelled'])) {
      if ($data['cancelled']) $attributes['class'] .= ' cancelled';
    } elseif (isset($data['failed'])) {
      if ($data['failed']) $attributes['class'] .= ' failed';
    } elseif (isset($data['payed'])) {
      if ($data['payed']) $attributes['class'] .= ' payed';
    }

    if (isset($data['eventattendee_failed'])) {
      if ($data['eventattendee_failed']) $attributes['class'] .= ' failed';
    }
    
    if (isset($data['end'])&&($data['end']<date('Y-m-d H:i:s'))) {
      $attributes['class'] .= ' realised';
    }

    if (isset($data['mandatory'])&&($data['mandatory']=='Y')) {
      $attributes['class'] .= ' mandatory';
    }
  }
}

class GuiListReservation extends GuiWebGrid {
  
  protected function _initRow() {
    $params = $this->_getRowParams();
    $this->_guiGridRow = new GuiReservationGridRow($params);
  }

  public function __construct($name='listReservation',$eventId=null) {
    $app = Application::get();
    $showFilter = $showPager = !in_array($name,array('listEventReservation','listResourceReservation','listUserReservation'));

    $gridSettings = new GridSettingsReservation($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SReservation($sqlSettings);
    
    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['fulltext'])&&$temp['filter']['fulltext']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'reservation_id') { unset($filter[$k]); }
      }
      
      $fulltext = $temp['filter']['fulltext'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementHexa($select->columns['number'], $select->columns['user_name'], $select->columns['event_name'],
        $select->columns['resource_name'], $select->columns['eventattendeeperson_fullname'], $select->columns['eventattendeeperson_user_fullname'],
        sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))",
          $likeCond, $likeCond, $likeCond, $likeCond, $likeCond, $likeCond)));
    }
    // stav rezervace
    if (isset($temp['filter']['state'])&&$temp['filter']['state']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'reservation_id') { unset($filter[$k]); }
      }
      
      $state = $temp['filter']['state'];
      if ($state == 'PAYED') $select->addStatement(new SqlStatementMono($select->columns['payed'], '%s IS NOT NULL'));
      if ($state == 'FAILED') $select->addStatement(new SqlStatementMono($select->columns['failed'], '%s IS NOT NULL'));
      if ($state == 'CANCELLED') $select->addStatement(new SqlStatementMono($select->columns['cancelled'], '%s IS NOT NULL'));
      if ($state == 'ACTIVE') $select->addStatement(new SqlStatementTri($select->columns['failed'], $select->columns['cancelled'], $select->columns['end'], '%s IS NULL AND %s IS NULL AND %s>NOW()'));
      if ($state == 'REALISED') $select->addStatement(new SqlStatementTri($select->columns['failed'], $select->columns['cancelled'], $select->columns['end'], '%s IS NULL AND %s IS NULL AND %s<NOW()'));
      if ($state == 'ONLINEPAYMENT') $select->addStatement(new SqlStatementTri($select->columns['payed'], $select->columns['cancelled'], $select->columns['open_onlinepayment'], '%s IS NULL AND %s IS NULL AND %s IS NOT NULL'));
    }
    
    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['center'], sprintf('%%s IN (%s)', $app->auth->getAllowedCenter('list'))));
      if ($app->auth->getActualCenter()) $select->addStatement(new SqlStatementBi($select->columns['center'], $app->auth->getActualCenter(), '%s=%s'));
    }
    
    if ($name == 'listUserReservation') {
      $val = Validator::get('user', 'UserValidator');
      $data = $val->getValues();
      $select->addStatement(new SqlStatementBi($select->columns['user'], $data['id'], '%s=%s'));
    } elseif ($name == 'listEventReservation') {
      $select->addStatement(new SqlStatementBi($select->columns['eventattendee_event'], $eventId, '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['cancelled'], '%s IS NULL'));
                                               
      #$showFilter = false;
      #$showPager = false;
    } elseif ($name == 'listResourceReservation') {
      $val = Validator::get('resource', 'ResourceValidator');
      $data = $val->getValues();
      $select->addStatement(new SqlStatementBi($select->columns['resource'], $data['id'], '%s=%s'));
      $select->addStatement(new SqlStatementMono($select->columns['cancelled'], '%s IS NULL'));
      $select->addOrder(new SqlStatementAsc($select->columns['resource_from']));
                                               
      #$showFilter = false;
      #$showPager = false;
    }
    
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);
    
    $params = array('id'=>'fi_reservationList','settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager);
    
    if ($name == 'listReservation') {
      $params['doubleClickAction'] = 'eReservationEdit';
      $params['doubleClickColumn'] = 'reservation_id';
      $params['doubleClickVarName'] = 'id';
      $params['multiAction'] = array(
                                    'action'          => array('eReservationGroupDelete'),
                                    'label'           => array(
                                                        $app->textStorage->getText('button.listReservation_multiDelete'),
                                                        ),
                                    'onclick'         => array(
                                                        'return confirm(\''.$app->textStorage->getText('label.listReservation_multiDelete_confirm').'\');',
                                                        ),
                                    'varName'         => 'id',
                                    'column'          => 'id'
                              );
    }

    parent::__construct($params);
  }
}

?>
