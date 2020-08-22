<?php

class GridSettingsDocument extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'document_id', 'ID', 'none'));
    $this->addColumn(new GridColumn('number', 'number', $ts->getText('label.listDocument_number')));
    $this->addColumn(new GridColumn('code', 'code', $ts->getText('label.listDocument_code'), 'none'));
		$this->addColumn(new GridColumn('user', 'fullname', $ts->getText('label.listDocument_user')));
    $this->addColumn(new GridColumn('reservation', 'reservation_number', $ts->getText('label.listDocument_reservation')));
    $this->addColumn(new GridColumn('created', 'created', $ts->getText('label.listDocument_created'), 'none'));
    $this->addColumn(new GridColumn('action', 'document_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listUserDocument'))) {
      $this->_columnsMask = array('i','number','code','reservation','created','action');
      $this->_onPage = $ONPAGE['listDocument'];
    } elseif (in_array($this->_name, array('listDocument'))) {
			$this->_columnsMask = array('i','number','code','user','reservation','created','action');
			$this->_onPage = $ONPAGE['listDocument'];
		}
    $this->setForcedSources(array('file_hash'));

    $this->getColumn('i')->addElementAttribute('class', 'index');

    global $NODE_URL;
		$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
			'url' => sprintf('%s/getfile.php', $NODE_URL),
			'target'	=> '_blank',
			'imgsrc'	=> 'img/button_grid_download.png',
			'label' => $ts->getText('button.grid_download'),
			'title' => $ts->getText('button.grid_download'),
			'dynamics'	=> array('id'=>'file_hash'))));
		if ($app->auth->haveRight('delete_record',$app->auth->getActualProvider())) {
			$this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
				'action' => 'eUserDocumentDelete',
				'imgsrc' => 'img/button_grid_delete.png',
				'label' => $ts->getText('button.grid_delete'),
				'title' => $ts->getText('button.grid_delete'),
				'onclick' => 'return confirm(\'' . $ts->getText('label.grid_confirmDelete') . '\');',
				'varName' => 'id'
			)));
		}

    $this->getColumn('i')->addGuiElement(new GuiGridCellID(array('columnName'=>'document_id')));
    $this->getColumn('id')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('number')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('number')->addFilterParam('classInput', 'mediumText');
		$this->getColumn('code')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('reservation')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('reservation')->addFilterParam('classInput', 'mediumText');
		$this->getColumn('user')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
		$this->getColumn('user')->addFilterParam('classInput', 'mediumText');
		$this->getColumn('created')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('created')->addGuiElement(new GuiGridCellDateTime());

    $this->getColumn('action')->addElementAttribute('class', 'tdAction');
  }
}

class GuiListDocument extends GuiWebGrid {

  public function __construct($name='listUserDocument') {
    $app = Application::get();

    $showFilter = $name=='listDocument';
    $showPager = true;

    $gridSettings = new GridSettingsDocument($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SDocument($sqlSettings);

    // seznam dokumentu uzivatele
		if ($name=='listUserDocument') {
			$validator = Validator::get('user', 'UserValidator');
			$data = $validator->getValues();
			$select->addStatement(new SqlStatementBi($select->columns['user'], $data['id'], '%s=%s'));
		}

    if (!$app->auth->isAdministrator()) {
      $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));
    }
    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);

    $params = array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager);
    parent::__construct($params);
  }
}

?>