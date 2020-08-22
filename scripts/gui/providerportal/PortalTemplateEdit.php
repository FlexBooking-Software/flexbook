<?php

class GuiEditPortalTemplate extends GuiElement {

  private function _insertPreview($data) {
    if ($data['preview']) {
      global $AJAX;
      $this->insertTemplateVar('fi_preview', sprintf('<img class="preview" src="%s/getfile.php?id=%s" />', dirname($AJAX['adminUrl']), $data['previewHash']), false);
    } else $this->insertTemplateVar('fi_preview', '');
  }

  private function _insertPageList($data) {
    $this->insertTemplateVar('page', '<input type="hidden" name="page[]" value=""/>', false);
    foreach ($data['page'] as $page) {
      $pO = new OPageTemplate($page);
      $pData = $pO->getData();
      
      $pageHtml = sprintf('<div class="pageTemplate"><input type="hidden" name="page[]" value="%d"/>%s<div class="removePageTemplate">X</div></div>',
                              $page, $pData['name']);
      $this->insertTemplateVar('page', $pageHtml, false);
    }
    
    $s = new SPageTemplate;
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $this->insert(new GuiFormSelect(array(
        'name'        => 'pageTemplateSelect',
        'id'          => 'fi_pageTemplateSelect',
        'dataSource'  => $ds,
        'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
        'value'       => null,
        'showDiv'     => false,
        )), 'fi_pageTemplateSelect');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/PortalTemplateEdit.html');

    $validator = Validator::get('portalTemplate', 'PortalTemplateValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editPortalTemplate_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editPortalTemplate_titleExisting'));
    }
    
    $this->_insertPreview($data);
    $this->_insertPageList($data);
    
    $this->_app->document->addJavascript(sprintf("
                  $(document).ready(function() {
                    var tabCookieName = 'ui-portaltemplate-tab';
                    var tab = $('#tab').tabs({
                            active : ($.cookie(tabCookieName) || 0),
                            activate : function( event, ui ) {
                              var newIndex = ui.newTab.parent().children().index(ui.newTab);
                              // my setup requires the custom path, yours may not
                              $.cookie(tabCookieName, newIndex);
                            }
                    });
                  
                    $('.pageList').on('click', '.removePageTemplate', function() {
                      $(this).closest('.pageTemplate').remove();
                    });
                    
                    $('.pageList').sortable({
                      items: '.pageTemplate'
                    });
                    
                    $('#fi_newPageTemplate_form').dialog({
                      autoOpen: false,
                      height: 160,
                      width: 400,
                      modal: true,
                      buttons: {
                        '{__button.editPortalTemplate_pageTemplateSave}': function() {
                          if ($('#fi_pageTemplateSelect').val()) {
                            var newPageTemplate = $('#fi_pageTemplateSelect').val();
                            var exists = false;
                            $(\"input:hidden[name='page[]']\").each(function() {
                              if ($(this).val()==newPageTemplate) {
                                exists = true;
                                return;
                              }
                            });
                            if (exists) {
                              alert('%s');
                              return;
                            }
                            
                            var html = '<div class=\"pageTemplate\"><input type=\"hidden\" name=\"page[]\" value=\"' +
                                        newPageTemplate + '\"/>' + $('#fi_pageTemplateSelect').children(':selected').text() +
                                        '<div class=\"removePageTemplate\">X</div></div>';
                          
                            $('.pageList').append(html);
                          }
                          
                          $(this).dialog('close');
                        },
                        '{__button.editPortalTemplate_pageTemplateCancel}': function() {
                          $(this).dialog('close');
                        },
                      },
                    });
                    
                    $('#fi_newPageTemplate').click(function() {
                      $('#fi_pageTemplateSelect').val('');
                      $('#fi_newPageTemplate_form').dialog('open');
                    });
                  });", $this->_app->textStorage->getText('error.editPortalTemplate_pageTemplateNotUnique')));
  }
}

?>
