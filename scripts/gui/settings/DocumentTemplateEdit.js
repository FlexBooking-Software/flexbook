$(function() {
    var id = $('#editItem_id'),
        index = $('#editItem_index'),
        name = $('#editItem_name'),
        code = $('#editItem_code'),
        type = $('#editItem_type'),
        number = $('#editItem_number'),
        content = $('#editItem_content'),
        
        allFields = $([]).add(id).add(name).add(code).add(type).add(number).add(content);
    
    $('#fi_newItem_form').dialog({
        autoOpen: false,
        height: 'auto',
        width: 950,
        modal: true,
        buttons: {
            '{__button.editDocumentTemplate_itemSave}': function() {
                var valid = true;
                
                if (valid&&!name.val()) { alert('{__error.editDocumentTemplate_item_missingName}'); valid = false; }
                if (valid&&!code.val()) { alert('{__error.editDocumentTemplate_item_missingCode}'); valid = false; }
                if (valid&&!type.val()) { alert('{__error.editDocumentTemplate_item_missingType}'); valid = false; }
                if (valid&&!number.val()) { alert('{__error.editDocumentTemplate_item_missingNumber}'); valid = false; }

                if (valid) {
                  var editor = tinymce.EditorManager.get('editItem_content');
                  if (editor) {
                    templateContent = editor.getContent();
                  } else {
                    templateContent = content.val();
                  }
                  templateContent = templateContent.replace(/"/g, '&quot;');
                  if (!templateContent) { alert('{__error.editDocumentTemplate_item_missingContent}'); valid = false; }
                }
                
                if (valid) {
                    var html =
                            '<td>' + name.val() + '</td>' +
                            '<td>' + code.val() + '</td>' +
                            '<td>[<a href="#" id="fi_itemEdit">{__button.grid_edit}</a>][<a href="#" id="fi_itemRemove">{__button.grid_remove}]</a></td>';
                    
                    if (index.val()) {
                      i = index.val();
                    } else {
                      i = Math.floor(Math.random()*10000);
                    }
                    
                    html += '<input type="hidden" name="newItem['+i+']" value="itemId~'+id.val()+
                            ';name~'+name.val()+';code~'+code.val()+';number~'+number.val()+';type~'+type.val()+'"/>';
                    html += '<input type="hidden" name="newItemContent['+i+']" value="'+templateContent+'"/>';
                    
                    if (index.val()) {
                      $('#fi_itemTable tbody').find('tr#'+index.val()).html(html);
                    } else {
                      $('#fi_itemTable tbody').append('<tr id="'+i+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                    //alert(html);
                }
            },
            '{__button.editDocumentTemplate_itemCancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
          var editor = tinymce.EditorManager.get('editItem_content');
          if (editor) editor.remove();

          index.val('');
          allFields.val('').removeClass('ui-state-error');
        }
    });    
    
    $('#fi_addItem').click(function() {
      if (!$('#fi_provider').val()) { alert('{__error.editDocumentTemplate_missingProvider}'); return false; }

      if (!$('#fi_target').val()) { alert('{__error.editDocumentTemplate_missingTarget}'); return false; }
      if ($('#fi_target').val()=='GENERAL') $('#editItem_type option.general').show();
      else $('#editItem_type option.general').hide();

      $('#editItem_number').val('[YYYY]-[ID]-[cccccc]');

      var editor = tinymce.EditorManager.createEditor('editItem_content', mceSettings);
      editor.render();

      $('#fi_newItem_form').dialog('open');
    });
    
    $('#editDocumentTemplate').on('click','#fi_itemRemove', function() {
      $(this).closest('tr').remove();
      return false;
    });
    
    $('#editDocumentTemplate').on('click','#fi_itemEdit', function() {
      if (!$('#fi_target').val()) { alert('{__error.editDocumentTemplate_missingTarget}'); return false; }
      if ($('#fi_target').val()=='GENERAL') $('#editItem_type option.general').show();
      else $('#editItem_type option.general').hide();

      var tr = $(this).closest('tr');
      index.val(tr.attr('id'));
      id.val(tr.attr('db_id'));
      var input = tr.find('input');
      var values = input[0].value.split(';');
      for (i=0;i<values.length;i++) {
        value = values[i].split('~');
        if (value[0]=='name') { name.val(value[1]); }
        if (value[0]=='code') { code.val(value[1]); }
        if (value[0]=='number') { number.val(value[1]); }
        if (value[0]=='type') { type.val(value[1]); }
        if (value[0]=='content') { content.val(value[1]); }
      }
      content.val(input[1].value);

      var editor = tinymce.EditorManager.createEditor('editItem_content', mceSettings);
      editor.render();

      $('#fi_newItem_form').dialog('open');
      
      return false;
    });
    
    $('#fi_helpDiv').dialog({
        autoOpen: false, 
        width: 850,
    });
    $('.help').click(function() {
        $('#fi_helpDiv').dialog('open');
    });

    $('#fi_numberHelpDiv').dialog({
      autoOpen: false,
      width: 800,
    });
    $('.numberHelp').click(function() {
      $('#fi_numberHelpDiv').dialog('open');
    });

    var mceSettings = {
      height: 200,
      extended_valid_elements: 'script[charset|defer|language|src|type],input[onclick|type|value],button[onclick]',
      plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table contextmenu paste'
      ],
      toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
    };
    tinymce.init(mceSettings);
});