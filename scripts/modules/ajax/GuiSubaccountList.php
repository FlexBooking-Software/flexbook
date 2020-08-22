<?php

class AjaxGuiSubaccountList extends AjaxGuiAction2 {

  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_profile_subaccount_list', $this->_params['prefix']);
    $this->_class = 'flb_profile_subaccount_list';
  }

  protected function _initDefaultParams() {
    if (!isset($this->_params['format']['time'])) $this->_params['format']['time'] = 'H:i';
    if (!isset($this->_params['format']['date'])) $this->_params['format']['date'] = 'd.m. Y';
    if (!isset($this->_params['format']['datetime'])) $this->_params['format']['datetime'] = 'd.m. Y H:i';
  }

  protected function _createTemplate() {
    $this->_guiHtml = "
                    <div class=\"label flb_title\"><span>{__label.ajax_profile_subaccount_title}</span></div>
                    <div class=\"flb_profile_subaccount_data\">{subaccountList}</div>
                    <div class=\"button\">
                      <input type=\"button\" id=\"{prefix}flb_profile_subaccount_list_back\" value=\"{backLabel}\" />
                      <input type=\"button\" id=\"{prefix}flb_profile_subaccount_list_new\" value=\"{__label.ajax_profile_subaccount_new}\" />
                    </div>
                    <script>
                     $(document).ready(function() { 
                       $('#{prefix}flb_profile_subaccount_list').on('click','.flb_profile_subaccount_list_item_button_detail', function() {
                          flbLoadHtml('guiSubaccountDetail', $('#{prefix}flb_profile_subaccount_list').parent(), $.extend({params}, 
                            { id: $(this).parent().attr('id').replace('{prefix}','') }));  
                       });
                       
                       $('#{prefix}flb_profile_subaccount_list').on('click','.flb_profile_subaccount_list_item_button_delete', function() {
                        if (confirm('{__label.ajax_profile_subaccount_confirmDelete}')) {
                          var accountId = $(this).parent().attr('id').replace('{prefix}','');
                          var data = { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), id: accountId };
                          $.ajax({
                             type: 'POST',
                             dataType: 'json',
                             data: data,
                             url: $('#flb_core_url').val()+'action=deleteSubaccount',
                             success: function(data) {
                                 if (data.error) alert(data.message);
                                 else {
                                   flbLoadHtml('guiSubaccountList', $('#{prefix}flb_profile_subaccount_list').parent(), $.extend({params}, { prefix: '{prefix}'}));
                                 }
                             },
                             error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); }
                          });
                        }
                       });
                       
                       $('#{prefix}flb_profile_subaccount_list').on('click','#{prefix}flb_profile_subaccount_list_back', function() {
                          $('#{prefix}flb_profile_subaccount_list_data').hide();
                          {jsBackAction}
                       });
                       
                       $('#{prefix}flb_profile_subaccount_list').on('click','#{prefix}flb_profile_subaccount_list_new', function() {
                          flbLoadHtml('guiSubaccountDetail', $('#{prefix}flb_profile_subaccount_list').parent(), $.extend({params}, { id: null, prefix: '{prefix}' }));  
                       });
                     });
                   </script>";
  }

  private function _parseSubaccountLine($data) {
    $ret = sprintf('%s %s', $data['lastname'], $data['firstname']);
    if ($data['email']) $ret .= sprintf(' (%s)', $data['email']);

    return $ret;
  }
      
  protected function _getData() {
    $subAcccounts = '';
    $s = new SUser;
    $s->addStatement(new SqlStatementBi($s->columns['parent_user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['registration_provider'], $this->_params['provider'], '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['lastname']));
    $s->addOrder(new SqlStatementAsc($s->columns['firstname']));
    $s->setColumnsMask(array('user_id','firstname','lastname','email'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $dataString = $this->_parseSubaccountLine($row);
      $subAcccounts .= sprintf('<div class="flb_profile_subaccount_list_item flb_list_item" id="%s%s"><div class="flb_profile_subaccount_list_item_desc">%s</div>
                            <div class="flb_button flb_profile_subaccount_list_item_button flb_profile_subaccount_list_item_button_detail"><span>%s</span></div>
                            <div class="flb_button flb_profile_subaccount_list_item_button flb_profile_subaccount_list_item_button_delete"><span>%s</span></div>
                            </div>',
        $this->_params['prefix'], $row['user_id'], $dataString, strtoupper($this->_app->textStorage->getText('button.ajax_detail')),
        strtoupper($this->_app->textStorage->getText('button.ajax_remove')));
    }
    if ($subAcccounts) {
      $this->_guiParams['subaccountList'] = sprintf('<div class="flb_profile_subaccount_list flb_list">%s</div>', $subAcccounts);
    } else $this->_guiParams['subaccountList'] = sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData'));

    if (isset($this->_params['extraDiv'])&&$this->_params['extraDiv']) {
      $this->_guiParams['jsBackAction'] = sprintf("$('#%sflb_profile_extra').hide();", $this->_params['prefix']);
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.close');
    } else {
      $this->_guiParams['jsBackAction'] = sprintf("flbLoadHtml('guiProfile', $('#%sflb_profile_subaccount_list').parent(), %s);", $this->_params['prefix'], $this->_guiParams['params']);
      $this->_guiParams['backLabel'] = $this->_app->textStorage->getText('button.back');
    }
    
    if (isset($this->_params['format'])) $this->_guiParams['format'] = json_encode($this->_params['format']);
  }
}

?>
