<?php

class GridSettingsProviderTextStorage extends WebGridSettings {

  protected function _initSettings() {
    global $ONPAGE;
    
    parent::_initSettings();
    
    $app = Application::get();
    $ts = $app->textStorage;

    $this->addColumn(new GridColumn('i', '__i', $ts->getText('label.grid_indexColumn') ,'none'));
    $this->addColumn(new GridColumn('id', 'providertextstorage_id', $ts->getText('label.grid_fulltext')));
    $this->addColumn(new GridColumn('provider', 'provider', $ts->getText('label.listProviderTextStorage_provider'), 'none'));
    $this->addColumn(new GridColumn('language', 'language', $ts->getText('label.listProviderTextStorage_language'), 'none'));
    $this->addColumn(new GridColumn('key', 'ts_key', $ts->getText('label.listProviderTextStorage_key'), 'none'));
    $this->addColumn(new GridColumn('original', 'original_value', $ts->getText('label.listProviderTextStorage_original'), 'none'));
    $this->addColumn(new GridColumn('new', 'new_value', $ts->getText('label.listProviderTextStorage_new'), 'none'));
    $this->addColumn(new GridColumn('action', 'providertextstorage_id', $ts->getText('label.grid_none'), 'none'));
    
    if (in_array($this->_name, array('listProviderTextStorage'))) {
      $this->_columnsMask = array('language', 'key', 'original', 'new', 'action');
      $this->_onPage = $ONPAGE['listProviderTextStorage'];
    }
    $this->setForcedSources(array('providertextstorage_id'));

    $this->getColumn('action')->addGuiElement(new GuiGridCellAction(array(
      'action'  => '#',
      'class'   => 'tsEditButton',
      'imgsrc'  => 'img/button_grid_detail.png',
      'label'   => $ts->getText('button.grid_edit'),
      'title'   => $ts->getText('button.grid_edit'),
      'varName' => 'id')));

    $this->getColumn('id')->addFilterParam('classInput', 'longText');
    $this->getColumn('language')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('key')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('original')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);
    $this->getColumn('new')->addHeaderGuiElement(new GuiGridCellHeaderImgOrder);

    $this->getColumn('original')->addGuiElement(new GuiGridCellProviderTextStorageOriginalValue);
    $this->getColumn('new')->addGuiElement(new GuiGridCellProviderTextStorageNewValue);

    $this->setOrder('key', 'desc');
  }
}

class GuiGridCellProviderTextStorageOriginalValue extends GuiGridCellRenderer {

  protected function _userRender() {
    $this->setTemplateString(sprintf('<span meaning="tsOriginalValue">%s</span>', $this->_outputData));
  }
}

class GuiGridCellProviderTextStorageNewValue extends GuiGridCellRenderer {

  protected function _userRender() {
    $this->setTemplateString(sprintf('<span id="%s" meaning="tsNewValue">%s</span>', $this->_rowData['providertextstorage_id'], $this->_outputData));
  }
}

class GuiListProviderTextStorage extends GuiWebGrid {

  public function __construct($name='listProviderTextStorage') {
    $app = Application::get();

    $showFilter = true;
    $showPager = true;

    $gridSettings = new GridSettingsProviderTextStorage($name);
    $sqlSettings = $gridSettings->getSqlSelectSettings();
    $select = new SProviderTextStorage($sqlSettings);

    // fulltextove vyhledavani
    $temp = $gridSettings->getGuiGridFilterSettings();
    if (isset($temp['filter']['id'])&&$temp['filter']['id']) {
      $filter =& $sqlSettings->getFilter();
      foreach ($filter as $k=>$v) {
        if ($v['source'] == 'providertextstorage_id') { unset($filter[$k]); }
      }

      $fulltext = $temp['filter']['id'];
      $likeCond = $app->db->escapeString(sprintf('%%%%%s%%%%', $fulltext));
      $select->addStatement(new SqlStatementTri($select->columns['ts_key'], $select->columns['original_value'], $select->columns['new_value'],
        sprintf("((%%s LIKE '%s') OR (%%s LIKE '%s') OR (%%s LIKE '%s'))", $likeCond, $likeCond, $likeCond)));
    }

    if (!$app->auth->isAdministrator()) $select->addStatement(new SqlStatementBi($select->columns['provider'], $app->auth->getActualProvider(), '%s=%s'));

    $dataSource = new SqlDataSource($gridSettings->getDataSourceSettings(), $select);
    
    parent::__construct(array('settings'=>$gridSettings, 'dataSource'=>$dataSource, 'showFilter'=>$showFilter, 'showPager'=>$showPager));
  }

  protected function _getNonEmptyTemplate() {
  	$ret = parent::_getNonEmptyTemplate();
  	$ret .= '
<div id="fi_editProviderTS_form" class="ajaxForm" title="{__label.listProviderTextStorage_dialogTitle}">
  <form>
    <fieldset>
      <input type="hidden" id="editProviderTS_id" name="id" value="" />
      <input type="hidden" id="editProviderTS_provider" name="provider" value="" />
      <input type="hidden" id="editProviderTS_originalValue" name="original" value="" />
      <label for="newValue">{__label.listProviderTextStorage_new}</label>
      <input type="text" id="editProviderTS_newValue" name="new" class="text ui-widget-content ui-corner-all">
    </fieldset>
  </form>
</div>';

		return $ret;
	}

  protected function _userRender() {
		parent::_userRender();

		global $AJAX;

		Application::get()->document->addJavascript(sprintf("
		  $(document).ready(function() {  
		    $('.listProviderTextStorage .tsEditButton').click(function() {
		      var spanNew = $(this).closest('tr').find('span[meaning=tsNewValue]');
		      var spanOriginal = $(this).closest('tr').find('span[meaning=tsOriginalValue]');
		      $('#editProviderTS_id').val(spanNew.attr('id'));
		      $('#editProviderTS_newValue').val(spanNew.html());
		      $('#editProviderTS_originalValue').val(spanOriginal.html());
		      $('#fi_editProviderTS_form').dialog('open');
		      $('#editProviderTS_newValue').focus();
		      return false;
		    });
		    
		    $('#fi_editProviderTS_form').dialog({
          autoOpen: false,
          height: 180,
          width: 460,
          modal: true,
          buttons: {
            '{__button.listProviderTextStorage_update}': function() {
              $.ajax({
                 type: 'POST',
                 dataType: 'json',
                 data: { provider: %s, sessid: '%s', textStorage: [{ id: $('#editProviderTS_id').val(), value: $('#editProviderTS_newValue').val() }] },
                 url: '%s?action=saveTextStorage',
                 success: function(data) {
                    if (data.error) alert(data.message);
                    else {
                      $('#'+$('#editProviderTS_id').val()).html($('#editProviderTS_newValue').val());
                      $('#fi_editProviderTS_form').dialog('close');
                    }
                 },
                 error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
              });
            },
            '{__button.listProviderTextStorage_fill}': function() {
              $('#editProviderTS_newValue').val($('#editProviderTS_originalValue').val());
            },
            '{__button.listProviderTextStorage_close}': function() {
              
              $(this).dialog('close');
            }
          }
        });
		  });", $this->_app->auth->getActualProvider(), $this->_app->session->getId(), $AJAX['adminUrl']));
	}
}

?>
