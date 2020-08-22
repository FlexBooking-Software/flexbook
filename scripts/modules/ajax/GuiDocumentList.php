<?php

class AjaxGuiDocumentList extends AjaxGuiAction2 {

  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_profile_document_list', $this->_params['prefix']);
    $this->_class = 'flb_profile_document_list';
  }

  protected function _initDefaultParams() {
    if (!isset($this->_params['format']['time'])) $this->_params['format']['time'] = 'H:i';
    if (!isset($this->_params['format']['date'])) $this->_params['format']['date'] = 'd.m. Y';
    if (!isset($this->_params['format']['datetime'])) $this->_params['format']['datetime'] = 'd.m. Y H:i';
  }

  protected function _createTemplate() {
    $this->_guiHtml = "
                    <div class=\"label flb_title\"><span>{__label.ajax_profile_document_title}</span></div>
                    <div class=\"flb_profile_document_data\">{documentList}</div>
                    <div class=\"button\">
                      <input type=\"button\" id=\"{prefix}flb_profile_document_list_back\" value=\"{backLabel}\" />
                    </div>
                    <script>
                     $(document).ready(function() { 
                       $('#{prefix}flb_profile_document_list').on('click','.flb_profile_document_list_item_button_detail', function() {
                          window.open($('#flb_core_url_path').val()+'getfile.php?id='+$(this).attr('data-hash'));
                          return false;  
                       });
                       
                       $('#{prefix}flb_profile_document_list').on('click','#{prefix}flb_profile_document_list_back', function() {
                          $('#{prefix}flb_profile_document_list_data').hide();
                          {jsBackAction}
                       });
                     });
                   </script>";
  }

  protected function _getData() {
    $documents = '';
    $s = new SDocument;
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addOrder(new SqlStatementDesc($s->columns['created']));
    $s->setColumnsMask(array('document_id','number','created','file_hash'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $documents .= sprintf('<div class="flb_profile_document_list_item flb_list_item" id="%s%s"><div class="flb_profile_document_list_item_desc">%s (%s)</div>
                            <div class="flb_button flb_profile_document_list_item_button flb_profile_document_list_item_button_detail" data-hash="%s"><span>%s</span></div>
                            </div>',
        $this->_params['prefix'], $row['document_id'], $row['number'], $this->_app->regionalSettings->convertDateTimeToHuman($row['created']),
        $row['file_hash'], strtoupper($this->_app->textStorage->getText('button.ajax_download')));
    }
    if ($documents) {
      $this->_guiParams['documentList'] = sprintf('<div class="flb_profile_document_list flb_list">%s</div>', $documents);
    } else $this->_guiParams['documentList'] = sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData'));

    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['jsBackAction'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.close');
    } else {
      $this->_guiParams['jsBackAction'] = sprintf("flbLoadHtml('guiProfile', $('#%sflb_profile_document_list').parent(), %s);", $this->_params['prefix'], $this->_guiParams['params']);
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.back');
    }
    
    if (isset($this->_params['format'])) $this->_guiParams['format'] = json_encode($this->_params['format']);
  }
}

?>
