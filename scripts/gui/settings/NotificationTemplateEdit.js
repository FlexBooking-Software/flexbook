$(function() {
    var id = $('#editItem_id'),
        index = $('#editItem_index'),
        name = $('#editItem_name'),
        offsetCount = $('#editItem_offsetCount'),
        offsetUnit = $('#editItem_offsetUnit'),
        type = $('#editItem_type'),
        toProvider = $('#editItem_toProvider'),
        toUser = $('#editItem_toUser'),
        toOrganiser = $('#editItem_toOrganiser'),
        toAttendee = $('#editItem_toAttendee'),
        toSubstitute = $('#editItem_toSubstitute'),
        fromAddress = $('#editItem_fromAddress'),
        ccAddress = $('#editItem_ccAddress'),
        bccAddress = $('#editItem_bccAddress'),
        contentType = $('#editItem_contentType'),
        subject = $('#editItem_subject'),
        body = $('#editItem_body'),
        
        allFields = $([]).add(id).add(name).add(offsetCount).add(offsetUnit).add(type).
                          add(toProvider).add(toUser).add(toOrganiser).add(toAttendee).add(toSubstitute).
                          add(fromAddress).add(ccAddress).add(bccAddress).
                          add(contentType).add(subject).add(body);
                          
    $('#editItem_type').change(function() { showHideOffset(); });
    
    $('#fi_newItem_form').dialog({
        autoOpen: false,
        height: 'auto',
        width: 950,
        modal: true,
        buttons: {
            '{__button.editNotificationTemplate_itemSave}': function() {
                var valid = true;
                
                if (valid&&!name.val()) { alert('{__error.editNotificationTemplate_item_missingName}'); valid = false; }
                if (valid&&!type.val()) { alert('{__error.editNotificationTemplate_item_missingType}'); valid = false; }
                if (valid&&!subject.val()) { alert('{__error.editNotificationTemplate_item_missingSubject}'); valid = false; }

                if (valid) {
                  var editor = tinymce.EditorManager.get('editItem_body');
                  if (editor) {
                    templateBody = editor.getContent();
                  } else {
                    templateBody = body.val();
                  }
                  templateBody = templateBody.replace(/"/g, '&quot;');
                  if (!templateBody) { alert('{__error.editNotificationTemplate_item_missingBody}'); valid = false; }
                }
                
                if (valid) {
                    var html =
                            '<td>' + name.val() + '</td>' +
                            '<td>[<a href="#" id="fi_itemEdit">{__button.grid_edit}</a>][<a href="#" id="fi_itemRemove">{__button.grid_remove}]</a></td>';
                    
                    if (index.val()) {
                      i = index.val();
                    } else {
                      i = Math.floor(Math.random()*10000);
                    }
                    
                    toProviderVal = toProvider.is(':checked')?'Y':'N';
                    toUserVal = toUser.is(':checked')?'Y':'N';
                    toOrganiserVal = toOrganiser.is(':checked')?'Y':'N';
                    toAttendeeVal = toAttendee.is(':checked')?'Y':'N';
                    toSubstituteVal = toSubstitute.is(':checked')?'Y':'N';
                    
                    html += '<input type="hidden" name="newItem['+i+']" value="itemId~'+id.val()+
                            ';name~'+name.val()+';offsetCount~'+offsetCount.val()+';offsetUnit~'+offsetUnit.val()+';type~'+type.val()+
                            ';toProvider~'+toProviderVal+';toUser~'+toUserVal+';toOrganiser~'+toOrganiserVal+';toAttendee~'+toAttendeeVal+';toSubstitute~'+toSubstituteVal+
                            ';fromAddress~'+fromAddress.val()+';ccAddress~'+ccAddress.val()+';bccAddress~'+bccAddress.val()+
                            ';contentType~'+contentType.val()+';subject~'+subject.val()+';body~'+templateBody+
                            '"/>';
                    
                    if (index.val()) {
                      $('#fi_itemTable tbody').find('tr#'+index.val()).html(html);
                    } else {
                      $('#fi_itemTable tbody').append('<tr id="'+i+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                    //alert(html);
                }
            },
            '{__button.editNotificationTemplate_itemCancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
          var editor = tinymce.EditorManager.get('editItem_body');
          if (editor) editor.remove();

          index.val('');
          allFields.val('').removeClass('ui-state-error');
          toProvider.prop('checked', false);
          toUser.prop('checked', false);
          toOrganiser.prop('checked', false);
          toAttendee.prop('checked', false);
          toSubstitute.prop('checked', false);
          offsetUnit.val('min');
          contentType.val('text/plain');
        }
    });    
    
    $('#fi_addItem').click(function() {
      if (!$('#fi_provider').val()) { alert('{__error.editNotificationTemplate_missingProvider}'); return false; }

      if (!$('#fi_target').val()) { alert('{__error.editNotificationTemplate_missingTarget}'); return false; }
      if ($('#fi_target').val()=='GENERAL') $('#editItem_type option.general').show();
      else $('#editItem_type option.general').hide();

      $('#fi_newItem_form').dialog('open');
    });
    
    $('#editNotificationTemplate').on('click','#fi_itemRemove', function() {
      $(this).closest('tr').remove();
      return false;
    });
    
    $('#editNotificationTemplate').on('click','#fi_itemEdit', function() {
      if (!$('#fi_target').val()) { alert('{__error.editNotificationTemplate_missingTarget}'); return false; }
      if ($('#fi_target').val()=='GENERAL') $('#editItem_type option.general').show();
      else $('#editItem_type option.general').hide();

      var tr = $(this).closest('tr');
      index.val(tr.attr('id'));
      id.val(tr.attr('db_id'));
      var input = tr.find('input');
      var values = input.val().split(';');
      for (i=0;i<values.length;i++) {
        value = values[i].split('~');
        if (value[0]=='name') { name.val(value[1]); }
        if (value[0]=='offsetCount') { offsetCount.val(value[1]); }
        if (value[0]=='offsetUnit') { offsetUnit.val(value[1]); }
        if (value[0]=='type') { type.val(value[1]); }
        if (value[0]=='toProvider') { if (value[1]=='Y') toProvider.prop('checked', true); }
        if (value[0]=='toUser') { if (value[1]=='Y') toUser.prop('checked', true); }
        if (value[0]=='toOrganiser') { if (value[1]=='Y') toOrganiser.prop('checked', true); }
        if (value[0]=='toAttendee') { if (value[1]=='Y') toAttendee.prop('checked', true); }
        if (value[0]=='toSubstitute') { if (value[1]=='Y') toSubstitute.prop('checked', true); }
        if (value[0]=='fromAddress') { fromAddress.val(value[1]); }
        if (value[0]=='ccAddress') { ccAddress.val(value[1]); }
        if (value[0]=='bccAddress') { bccAddress.val(value[1]); }
        if (value[0]=='contentType') { contentType.val(value[1]); }
        if (value[0]=='subject') { subject.val(value[1]); }
        if (value[0]=='body') { body.val(value[1]); }
      }
      
      showHideOffset();

      toggleEditor();
      
      $('#fi_newItem_form').dialog('open');
      
      return false;
    });
    
    $('#fi_helpDiv').dialog({
        autoOpen: false, 
        width: 800,
    });
      
    $('.help').click(function() {
        $('#fi_helpDiv').dialog('open');
    });
    
    function showHideOffset() {
        var value = $('#editItem_type').val();
        if ((value.indexOf('START')!=-1)||(value.indexOf('END')!=-1)) $('#editItem_offset').show();
        else {
          $('#editItem_offset').hide();
          $('#editItem_offsetCount').val('');
        }
    }

    function toggleEditor() {
      //tinymce.execCommand('mceToggleEditor', false, 'fi_content');

      if ($('#editItem_contentType').val() == 'text/plain') {
        var editor = tinymce.EditorManager.get('editItem_body');
        if (editor) editor.remove();
      } else {
        var editor = tinymce.EditorManager.createEditor('editItem_body', mceSettings);
        if (editor) editor.render();
      }
    }

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

    $('#editItem_contentType').change(function() {
      toggleEditor();
    });
});