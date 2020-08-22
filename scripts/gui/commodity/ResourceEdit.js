$(document).ready(function() {
  var tabCookieName = 'ui-resource-tab';
  var tabCookieValue = $.cookie(tabCookieName);
  
  // u noveho zdroje nemuze byt aktivni tab rezervaci
  if ((tabCookieValue==3)&&!$('#fi_id').val()) tabCookieValue = 0;
  var tab = $('#tab').tabs({
    active : (tabCookieValue || 0),
    activate : function( event, ui ) {
      var newIndex = ui.newTab.parent().children().index(ui.newTab);
      // my setup requires the custom path, yours may not
      $.cookie(tabCookieName, newIndex);
    }
  });
  
  if ($('#fi_id').val()) $('#a-tab-4').show();
  else $('#a-tab-4').hide();
  
  $('#fi_tag').tokenInput('{ajaxUrlPath}/ajax.php?action=getTag&provider={provider}',{
    minChars: 0,
    showAllResults: true,
    queryParam: 'term', theme: 'facebook',
    tokenValue: 'name',
    preventDuplicates: true,
    hintText: '{__label.searchTag_hint}',
    searchingText: '{__label.searchTag_searching}',
    noResultsText: '{__label.searchTag_noResult}',
    onResult: function (item) {
      if ($.isEmptyObject(item)) {
        return [ { id: $('tester').text(),name: $('tester').text() } ];
      } else {
        var found = false;
        item.forEach(function(tag,index) {
          if (tag.name==$('tester').text()) found = true;
        });
        if (!found) item.push({ id: $('tester').text(),name: $('tester').text() });

        return item;
      }
    },
  });
  {tagTokenInit}
  
  {additionalEditJS}

  $('#fi_provider').change(function() {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getCenter',
        success: function(data) {
            var centerCombo = $('#fi_center').html('');
            centerCombo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              centerCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getAvailabilityProfile',
        success: function(data) {
            var centerCombo = $('#fi_availProfile').html('');
            centerCombo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              centerCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getAvailabilityExceptionProfile',
        success: function(data) {
            var centerCombo = $('#fi_availExProfile').html('');
            centerCombo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              centerCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getUnitProfile',
        success: function(data) {
            var centerCombo = $('#fi_unitProfile').html('');
            centerCombo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              centerCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getPriceList',
        success: function(data) {
            var priceListCombo = $('#fi_priceList').html('');
            priceListCombo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              priceListCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
      type: 'GET',
      dataType: 'json',
      data: { provider : $(this).val() },
      url: '{ajaxUrl}?action=getReservationCondition',
      success: function(data) {
          var combo = $('#fi_reservationCondition').html('');
          combo.append('<option value=\"\">{__label.select_choose}</option>');
          
          $.each(data, function(index,element) {
            combo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
          });
      },
      error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getNotificationTemplate',
        success: function(data) {
            var combo = $('#fi_notificationTemplate').html('');
            combo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              combo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    $.ajax({
        type: 'GET',
        dataType: 'json',
        data: { provider : $(this).val() },
        url: '{ajaxUrl}?action=getAccountType',
        success: function(data) {
            var combo = $('#fi_accountType').html('');
            combo.append('<option value=\"\">{__label.select_choose}</option>');
            
            $.each(data, function(index,element) {
              combo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
        },
        error: function(error) { alert('{__label.ajaxError}'); }
    });
    
    return false;
  });
  
  $('#fi_newAttribute_form').dialog({
    autoOpen: false,
    height: 'auto',
    width: 480,
    modal: true,
    beforeClose: function(event,ui) {
      $('#editAttribute_value_input').datetimepicker('destroy');
      uploadObj.reset();
    },
    buttons: {
        '{__button.editResourceAttribute_ok}': function() {
            var bValid = true;
                
            if (bValid) {
                var value = null;
                var fileChange = '';
                if ($('#editAttribute_type').val()=='FILE') {
                  value = $('#editAttribute_value_filename').val();
                  fileChange = ';changed:~1;fileId:~'+$('#editAttribute_value_fileid').val();
                } else if ($('#editAttribute_type').val()=='LIST') {
                  value = $('#editAttribute_value_select').val();
                } else if ($('#editAttribute_type').val()=='TEXTAREA') {
                  value = $('#editAttribute_value_textarea').val();
                } else {
                  value = $('#editAttribute_value_input').val();
                }
               
                if ($('#editAttribute_applicable').val()=='COMMODITY') {
                  var html =
                          '<td id="category">' + $('#editAttribute_category').val() + '</td>' +
                          '<td id="name">' + $('#editAttribute_name').val() + '</td>' +
                          '<td id="typeHtml">' + $('#editAttribute_typeHtml').html() + '</td>' +
                          '<td>' + value.substr(0,30) + (value.length<=30?'':' ...') + '</td>' +
                          '<input type="hidden" id="type" value="' + $('#editAttribute_type').val() + '"/>' +
                          '<input type="hidden" id="value" value="' + value + '"/>' +
                          '<input type="hidden" id="allowedValues" value="' + $('#editAttribute_allowedValues').val() + '"/>' +
                          '<input type="hidden" id="disabled" value="' + $('#editAttribute_disabled').val() + '"/>' +
                          '<td class="tdAction">[<a href="#" id="fi_attributeEdit">{__button.grid_change}</a>][<a href="#" id="fi_attributeRemove">{__button.grid_remove}</a>]</a></td>';
                  
                  html += '<input type="hidden" class="attributeHidden" name="newResourceAttribute['+$('#editAttribute_id').val()+']"'+
                          ' value="attributeId:~'+$('#editAttribute_id').val()+';category:~'+$('#editAttribute_category').val()+
                          ';name:~'+$('#editAttribute_name').val()+
                          ';type:~'+$('#editAttribute_type').val()+';allowedValues:~'+$('#editAttribute_allowedValues').val()+
                          ';value:~'+value+fileChange+
                          ';disabled:~'+$('#editAttribute_disabled').val()+'"/>';
                          
                  placeHolder = '#fi_resourceAttributeTable';
                } else {
                  if ($('#editAttribute_mandatory').val()=='Y') yesNoLabel = '{__label.yes}';
                  else yesNoLabel = '{__label.no}';
                  
                  var html =
                          '<td id="category">' + $('#editAttribute_category').val() + '</td>' +
                          '<td id="name">' + $('#editAttribute_name').val() + '</td>' +
                          '<td id="typeHtml">' + $('#editAttribute_typeHtml').html() + '</td>' +
                          '<td id="mandatory">' + yesNoLabel + '</td>' +
                          '<td class="tdAction">[<a href="#" id="fi_attributeRemove">{__button.grid_remove}</a>]</a></td>';
                  
                  html += '<input type="hidden" class="attributeHidden" name="newReservationAttribute['+$('#editAttribute_id').val()+']"'+
                          ' value="attributeId:~'+$('#editAttribute_id').val()+';category:~'+$('#editAttribute_category').val()+
                          ';name:~'+$('#editAttribute_name').val()+
                          ';type:~'+$('#editAttribute_type').val()+';allowedValues:~'+$('#editAttribute_allowedValues').val()+
                          ';mandatory:~'+$('#editAttribute_mandatory').val()+
                          ';value:~;disabled:~'+$('#editAttribute_disabled').val()+'"/>';

                  placeHolder = '#fi_reservationAttributeTable';     
                }

                if ($(placeHolder+' tbody').find('tr#'+$('#editAttribute_id').val()).length) {
                  $(placeHolder+' tbody').find('tr#'+$('#editAttribute_id').val()).html(html);
                } else {
                  $(placeHolder+' tbody').append('<tr id="'+$('#editAttribute_id_select').val()+'">'+html+'</tr>');
                }
                
                $(this).dialog('close');
            }
        },
        '{__button.editResourceAttribute_cancel}': function() {
            $(this).dialog('close');
        }
    },
    close: function() {
        
    }
  });
  
  $('#tab-2').on('click','#fi_attributeRemove', function() {
      $(this).closest('tr').remove();
      return false;
  });
  
  function attributeDialog(data) {
    $('#editAttribute_id').val(data.id);
    $('#editAttribute_category').val(data.category);
    $('#editAttribute_name').val(data.name);
    $('#editAttribute_mandatory').val(data.mandatory);
    $('#editAttribute_type').val(data.type);
    $('#editAttribute_allowedValues').val(data.allowedValues);
    $('#editAttribute_disabled').val(data.disabled);
    $('#editAttribute_typeHtml').html(data.typeHtml);
    $('#editAttribute_nameHtml').html(data.name);
      
    if ($('#editAttribute_type').val()=='LIST') {
      $('#editAttribute_value_select').css('display','block');
      $('#editAttribute_value_input').css('display','none');
      $('#editAttribute_value_textarea').css('display','none');
      $('#editAttribute_value_file').css('display','none');
      
      var arr = $('#editAttribute_allowedValues').val().split(',');
      $('#editAttribute_value_select').html('');
      $('#editAttribute_value_select').append('<option value=\"\">- vyberte -</option>');
      $.each(arr, function(index,element) {
        $('#editAttribute_value_select').append('<option value=\"'+element+'\">'+element+'</option>');
      });
      
      $('#editAttribute_value_select').val(data.value); 
    } else if ($('#editAttribute_type').val()=='FILE') {
      $('#editAttribute_value_select').css('display','none');
      $('#editAttribute_value_input').css('display','block');
      $('#editAttribute_value_textarea').css('display','none');
      $('#editAttribute_value_file').css('display','block');
      
      $('#editAttribute_value_input').val(data.value);
      $('#editAttribute_value_input').prop('disabled', true);
    } else if ($('#editAttribute_type').val()=='TEXTAREA') {
      $('#editAttribute_value_select').css('display', 'none');
      $('#editAttribute_value_input').css('display', 'none');
      $('#editAttribute_value_textarea').css('display', 'block');
      $('#editAttribute_value_file').css('display', 'none');

      $('#editAttribute_value_textarea').val(data.value);
      $('#editAttribute_value_textarea').prop('disabled', false);
    } else {
      if ($('#editAttribute_type').val()=='DATE') { $('#editAttribute_value_input').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',datepicker:true,timepicker:false}); }
      else if ($('#editAttribute_type').val()=='DATETIME')  { $('#editAttribute_value_input').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',datepicker:true,timepicker:true}); }
      else if ($('#editAttribute_type').val()=='TIME')  { $('#editAttribute_value_input').datetimepicker({format:'H:i',datepicker:false,timepicker:true}); }
  
      $('#editAttribute_value_select').css('display','none');
      $('#editAttribute_value_input').css('display','block');
      $('#editAttribute_value_textarea').css('display','none');
      $('#editAttribute_value_file').css('display','none');
      
      $('#editAttribute_value_input').val(data.value);
      $('#editAttribute_value_input').prop('disabled', false);
    }
  }
  
  $('#tab-2').on('click','#fi_attributeEdit', function() {
      var tr = $(this).closest('tr');
      
      attributeDialog({ id: tr.attr('id'), category: tr.find('#category').html(), name: tr.find('#name').html(), mandatory: tr.find('#mandatory').html(),
                      type: tr.find('#type').val(), typeHtml: tr.find('#typeHtml').html(), disabled: tr.find('#disabled').val(),
                      allowedValues: tr.find('#allowedValues').val(), value: tr.find('#value').val() });
      
      $('#editAttribute_applicable').val('COMMODITY');
      
      $('#editAttribute_header').css('display','none');
      $('#editAttribute_data').css('display','block');
      $('#fi_newAttribute_form').dialog('open');
      return false;
  });
  
  $('#fi_newResourceAttribute_button').click(function() {
      var skipId = new Array();
      $('#fi_resourceAttributeTable tr').each(function() {
        if ($(this).attr('id')) skipId.push($(this).attr('id'));
      });
      
      $.ajax({
          type: 'GET',
          dataType: 'json',
          data: { provider: [ $('#fi_provider').val() ], skip: skipId, language: '{language}', applicable: ['COMMODITY'] },
          url: '{ajaxUrl}?action=getAttribute',
          success: function(data) {
            var attributeCombo = $('#editAttribute_id_select').html('');
            attributeCombo.append('<option value=\"\">- vyberte -</option>');
            
            $.each(data, function(index,element) {
              attributeCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
          },
          error: function(error) { alert('{__label.ajaxError}'); }
      });
      
      $('#editAttribute_applicable').val('COMMODITY');
      
      $('#editAttribute_header').css('display','block');
      $('#editAttribute_data').css('display','none');
      $('#fi_newAttribute_form').dialog('open');
  });
  
  $('#fi_newReservationAttribute_button').click(function() {
      var skipId = new Array();
      $('#fi_reservationAttributeTable tr').each(function() {
        if ($(this).attr('id')) skipId.push($(this).attr('id'));
      });
      
      $.ajax({
          type: 'GET',
          dataType: 'json',
          data: { provider: [ $('#fi_provider').val() ], skip: skipId, language: '{language}', applicable: ['RESERVATION'] },
          url: '{ajaxUrl}?action=getAttribute',
          success: function(data) {
            var attributeCombo = $('#editAttribute_id_select').html('');
            attributeCombo.append('<option value=\"\">- vyberte -</option>');
            
            $.each(data, function(index,element) {
              attributeCombo.append('<option value=\"'+element.id+'\">'+element.name+'</option>');
            });
          },
          error: function(error) { alert('{__label.ajaxError}'); }
      });
      
      $('#editAttribute_applicable').val('RESERVATION');
      
      $('#editAttribute_header').css('display','block');
      $('#editAttribute_data').css('display','none');
      $('#fi_newAttribute_form').dialog('open');
  });
  
  $('#editAttribute_id_select').change(function() {
    if ($(this).val()) {
      // kdyz je vybrany atribut 
      $.ajax({
          type: 'GET',
          dataType: 'json',
          data: { id : $(this).val(), language : '{language}' },
          url: '{ajaxUrl}?action=getAttribute',
          success: function(data) {
            attributeDialog(data);
            
            // kdyz je to popisovy atribut zdroje, zobrazim elementy pro jeho vyplneni
            if ($('#editAttribute_applicable').val()=='COMMODITY') $('#editAttribute_data').css('display','block');
          },
          error: function(error) { alert('{__label.ajaxError}'); }
      });
    }
  });
  
  var uploadObj = $('#editAttribute_file').uploadFile({
          url: "{ajaxUrlPath}/uploadfile.php",
          fileName: "uploadfile",
          dragDrop: false,
          maxFileCount: 1,
          uploadStr: "{__button.editCustomerAttribute_fileUpload}",
          maxFileCountErrorStr: "{__label.editCustomerAttribute_maxCount}",
          onSuccess: function(files,data,xhr,pd) {
            if (files) {
              var data = JSON.parse(data);
              $('#editAttribute_value_filename').val(data.name);
              $('#editAttribute_value_fileid').val(data.id);
            }
          }
  });
});