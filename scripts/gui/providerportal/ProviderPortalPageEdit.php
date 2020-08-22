<?php

class GuiEditProviderPortalPage extends GuiElement {

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ProviderPortalPageEdit.html');

    $validator = Validator::get('providerPortal', 'ProviderPortalValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    $this->insertTemplatevar('title', sprintf($this->_app->textStorage->getText('label.editProviderPortalPage_titleExisting'), $data['pageName'], $data['name']));
    
    $this->_app->document->addJavascriptFile('tinymce/tinymce.min.js?version=5.2.2');
    
    $this->_app->document->addJavascript("
            $(function() {
              $('#fi_helpDiv').dialog({
                autoOpen: false, 
                width: 600,
              });
              
              $('.fi_help').click(function() {
                $('#fi_helpDiv').dialog('open');
              });
              
              var mceSettings = {
                selector: '#fi_content',
                height: 300,
                extended_valid_elements: 'script[charset|defer|language|src|type],input[onclick|type|value],button[onclick],span',
                plugins: [
                  'advlist autolink lists link image charmap print preview anchor',
                  'searchreplace visualblocks code fullscreen',
                  'insertdatetime media table contextmenu paste'
                ],
                toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
              };
              
              tinymce.init(mceSettings);
              
              $('#toggle').click(function() {
                //tinymce.execCommand('mceToggleEditor', false, 'fi_content');
                
                var hidden = $('#toggleValue');
                if (hidden.val() == 'on') {
                  var editor = tinymce.EditorManager.get('fi_content');
                  editor.remove();
                  
                  hidden.val('off');
                } else {
                  var editor = tinymce.EditorManager.createEditor('fi_content', mceSettings);
                  editor.render();
                  
                  hidden.val('on');
                }
              });
            });");
  }
}

?>
