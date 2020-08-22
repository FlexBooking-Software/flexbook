$(function () {
  var tabCookieName = 'ui-user-tab';
  var tab = $('#tab').tabs({
    active: ($.cookie(tabCookieName) || 0),
    activate: function (event, ui) {
      var newIndex = ui.newTab.parent().children().index(ui.newTab);
      // my setup requires the custom path, yours may not
      $.cookie(tabCookieName, newIndex);
    }
  });

  var tips = $('.validateTips');

  registrationIndex = $('#editRegistration_index'),
    registrationId = $('#editRegistration_id'),
    registrationProviderId = $('#editRegistration_providerId'),
    registrationProviderName = $('#editRegistration_providerName'),
    registrationTimestamp = $('#editRegistration_timestamp'),
    registrationAdvertising = $('#editRegistration_advertising'),
    registrationCredit = $('#editRegistration_credit'),
    registrationRole = $('#editRegistration_role'),

    allFields = $([]).add(registrationProviderId).add(registrationAdvertising);

  function updateTips(t) {
    tips.text(t).addClass('ui-state-highlight');

    setTimeout(function () {
      tips.removeClass('ui-state-highlight', 1500);
    }, 500);
  }

  function checkLength(o, n, min, max) {
    if ((o.val().length > max) || (o.val().length < min)) {
      o.addClass('ui-state-error');
      updateTips('{__error.invalid_value}: ' + n);
      return false;
    } else {
      return true;
    }
  }

  $('#fb_eUserSave').click(function() {
    if ($('#fi_phone').val()) {
      if (!$("#fi_phone").intlTelInput('isValidNumber')) {
        alert('{__error.editUser_invalidPhone}');
        return false;
      }
      $("#fi_phone").val($("#fi_phone").intlTelInput('getNumber'));
    }
  });

  $('#fi_subaccountUser').click(function() { showHideSubaccountSelect(); });

  $("#fi_phone").intlTelInput({
    preferredCountries: ['cz', 'sk'],
    //nationalMode: false,
    utilsScript: "jq/intlTelInputUtils.js"
  });

  $('#fi_newRegistration_form').dialog({
    autoOpen: false,
    height: 250,
    width: 350,
    modal: true,
    buttons: {
      '{__button.editUserRegistration_ok}': function () {
        allFields.removeClass('ui-state-error');
        bValid = checkLength(registrationProviderId, '{__label.editUserRegistration_provider}', 1, 255);

        // rozdeleni hodnoty "provider" na id a nazev
        var prov = registrationProviderId.val().split('#');
        // registrace muze byt jedna na providera
        //var allRegistrationString = new String($('.registrationHidden').map(function(){ return $(this).val() }).get());
        //if (allRegistrationString.indexOf('providerId:'+prov[0])!==-1) {
        //    alert('{__error.editCustomerRegistration_exists}');
        //    bValid = false;
        //}

        if (bValid) {
          // kdyz se zaklada nova registrace
          if (!registrationIndex.val()) {
            registrationCredit.val(0);
            var d = new Date;
            var day = d.getDate();
            var month = d.getMonth() + 1;
            var year = d.getFullYear();
            registrationTimestamp.val(day + '.' + month + '.' + year);
          }

          if (registrationAdvertising.is(':checked')) {
            var advertising = '{__label.yes}';
            var advertisingDb = 'Y';
          } else {
            var advertising = '{__label.no}';
            var advertisingDb = 'N';
          }

          var html =
            '<td id="providerName">' + prov[1] + '</td>' +
            '<td id="timestamp">' + registrationTimestamp.val() + '</td>' +
            '<td id="advertising">' + advertising + '</td>' +
            '<td id="credit">' + registrationCredit.val() + ' {__label.currency_CZK}</td>' +
            '<td id="role">' + registrationRole.val() + '</td>' +
            '<input type="hidden" id="providerId" value="' + prov[0] + '"/>' +
            '<td class="tdAction">[<a href="#" id="fi_registrationEdit">{__button.grid_edit}</a>][<a href="#" id="fi_registrationRemove">{__button.grid_remove}]</a></td>';

          var tmp;
          if (registrationIndex.val()) {
            tmp = registrationIndex.val();
          } else {
            tmp = Math.floor(Math.random() * 10000);
          }
          ;

          html += '<input type="hidden" class="registrationHidden" name="newRegistration[' + tmp + ']" value="registrationId:' + registrationId.val() + ';providerId:' + prov[0] + ';providerName:' +
            prov[1] + ';timestamp:' + registrationTimestamp.val() + ';advertising:' + advertisingDb + ';credit:' + registrationCredit.val() + '"/>';

          if (registrationIndex.val()) {
            $('#fi_registrationTable tbody').find('tr#' + tmp).html(html);
          } else {
            $('#fi_registrationTable tbody').append('<tr id="' + tmp + '" db_id="">' + html + '</tr>');
          }

          $(this).dialog('close');
        }
      },
      '{__button.editCustomerRegistration_cancel}': function () {
        $(this).dialog('close');
      }
    },
    close: function () {
      registrationIndex.val('');
      registrationId.val('');
      registrationProviderId.val('');
      registrationProviderName.val('');
      registrationAdvertising.prop('checked', false);
      registrationRole.val('');
      allFields.val('').removeClass('ui-state-error');
    }
  });

  $('#tab-2').on('click', '#fi_registrationRemove', function () {
    $(this).closest('tr').remove();
    return false;
  });

  $('#tab-2').on('click', '#fi_registrationEdit', function () {
    var tr = $(this).closest('tr');
    registrationIndex.val(tr.attr('id'));
    registrationId.val(tr.attr('db_id'));
    if (tr.find('#advertising').html() == '{__label.yes}') {
      registrationAdvertising.prop('checked', true);
    } else {
      registrationAdvertising.prop('checked', false);
    }
    registrationProviderId.val(tr.find('#providerId').val() + '#' + tr.find('#providerName').html());
    registrationTimestamp.val(tr.find('#timestamp').html());
    registrationCredit.val(tr.find('#credit').html());
    registrationRole.val(tr.find('#role').html());

    $('#fi_newRegistration_form').dialog('open');

    return false;
  });

  $('#fi_newRegistration_button').click(function () {
    $('#fi_newRegistration_form').dialog('open');
  });

  $('#fi_newAttribute_form').dialog({
    autoOpen: false,
    height: 325,
    width: 650,
    modal: true,
    beforeClose: function (event, ui) {
      $('#editAttribute_value_input').datetimepicker('destroy');
      uploadObj.reset();
    },
    buttons: {
      '{__button.editUserAttribute_ok}': function () {
        var bValid = true;

        if (bValid) {
          var value = null;
          var fileChange = '';
          if ($('#editAttribute_type').val() == 'FILE') {
            value = $('#editAttribute_value_filename').val();
            fileChange = ';changed:~1;fileId:~' + $('#editAttribute_value_fileid').val();
          } else if ($('#editAttribute_type').val() == 'LIST') {
            value = $('#editAttribute_value_select').val();
          } else if ($('#editAttribute_type').val() == 'TEXTAREA') {
            value = $('#editAttribute_value_textarea').val();
          } else {
            value = $('#editAttribute_value_input').val();
          }

          var html =
            '<td id="category">' + $('#editAttribute_category').val() + '</td>' +
            '<td id="name">' + $('#editAttribute_name').val() + '</td>' +
            '<td id="typeHtml">' + $('#editAttribute_typeHtml').html() + '</td>' +
            '<td>' + value.substr(0, 30) + (value.length <= 30 ? '' : ' ...') + '</td>' +
            '<input type="hidden" id="providerId" value="' + $('#editAttribute_provider').val() + '"/>' +
            '<input type="hidden" id="type" value="' + $('#editAttribute_type').val() + '"/>' +
            '<input type="hidden" id="value" value="' + value + '"/>' +
            '<input type="hidden" id="allowedValues" value="' + $('#editAttribute_allowedValues').val() + '"/>' +
            '<input type="hidden" id="disabled" value="' + $('#editAttribute_disabled').val() + '"/>' +
            '<td class="tdAction">[<a href="#" id="fi_attributeEdit">{__button.grid_change}</a>][<a href="#" id="fi_attributeRemove">{__button.grid_remove}</a>]</a></td>';

          html += '<input type="hidden" class="attributeHidden" name="newAttribute[' + $('#editAttribute_id').val() + ']"' +
            ' value="attributeId:~' + $('#editAttribute_id').val() + ';providerId:~' + $('#editAttribute_provider').val() +
            ';providerName:~' + $('#editAttribute_providerName').val() + ';category:~' + $('#editAttribute_category').val() +
            ';name:~' + $('#editAttribute_name').val() + ';mandatory:~' + $('#editAttribute_mandatory').val() +
            ';type:~' + $('#editAttribute_type').val() + ';allowedValues:~' + $('#editAttribute_allowedValues').val() +
            ';value:~' + value + fileChange +
            ';disabled:~' + $('#editAttribute_disabled').val() + '"/>';

          if ($('#fi_attributeTable tbody').find('tr#' + $('#editAttribute_id').val()).length) {
            $('#fi_attributeTable tbody').find('tr#' + $('#editAttribute_id').val()).html(html);
          } else {
            $('#fi_attributeTable tbody').append('<tr id="' + $('#editAttribute_id_select').val() + '">' + html + '</tr>');
          }

          $(this).dialog('close');
        }
      },
      '{__button.editUserAttribute_cancel}': function () {
        $(this).dialog('close');
      }
    },
    close: function () {

    }
  });

  $('#tab-3').on('click', '#fi_attributeRemove', function () {
    $(this).closest('tr').remove();
    return false;
  });

  function attributeDialog(data) {
    $('#editAttribute_id').val(data.id);
    $('#editAttribute_provider').val(data.providerId);
    $('#editAttribute_providerName').val(data.providerName);
    $('#editAttribute_category').val(data.category);
    $('#editAttribute_name').val(data.name);
    $('#editAttribute_mandatory').val(data.mandatory);
    $('#editAttribute_type').val(data.type);
    $('#editAttribute_allowedValues').val(data.allowedValues);
    $('#editAttribute_disabled').val(data.disabled);
    $('#editAttribute_typeHtml').html(data.typeHtml);
    $('#editAttribute_nameHtml').html(data.name);

    if ($('#editAttribute_type').val() == 'LIST') {
      $('#editAttribute_value_select').css('display', 'block');
      $('#editAttribute_value_input').css('display', 'none');
      $('#editAttribute_value_textarea').css('display', 'none');
      $('#editAttribute_value_file').css('display', 'none');

      var arr = $('#editAttribute_allowedValues').val().split(',');
      $('#editAttribute_value_select').html('');
      $('#editAttribute_value_select').append('<option value=\"\">- vyberte -</option>');
      $.each(arr, function (index, element) {
        $('#editAttribute_value_select').append('<option value=\"' + element + '\">' + element + '</option>');
      });

      $('#editAttribute_value_select').val(data.value);
    } else if ($('#editAttribute_type').val() == 'FILE') {
      $('#editAttribute_value_select').css('display', 'none');
      $('#editAttribute_value_input').css('display', 'block');
      $('#editAttribute_value_textarea').css('display', 'none');
      $('#editAttribute_value_file').css('display', 'block');

      $('#editAttribute_value_input').val(data.value);
      $('#editAttribute_value_input').prop('disabled', true);
    } else if ($('#editAttribute_type').val() == 'TEXTAREA') {
      $('#editAttribute_value_select').css('display', 'none');
      $('#editAttribute_value_input').css('display', 'none');
      $('#editAttribute_value_textarea').css('display', 'block');
      $('#editAttribute_value_file').css('display', 'none');

      $('#editAttribute_value_textarea').val(data.value);
      $('#editAttribute_value_textarea').prop('disabled', false);
    } else {
      if ($('#editAttribute_type').val() == 'DATE') {
        $('#editAttribute_value_input').datetimepicker({
          format: 'd.m.Y',
          dayOfWeekStart: '1',
          datepicker: true,
          timepicker: false
        });
      } else if ($('#editAttribute_type').val() == 'DATETIME') {
        $('#editAttribute_value_input').datetimepicker({
          format: 'd.m.Y H:i',
          dayOfWeekStart: '1',
          datepicker: true,
          timepicker: true
        });
      } else if ($('#editAttribute_type').val() == 'TIME') {
        $('#editAttribute_value_input').datetimepicker({format: 'H:i', datepicker: false, timepicker: true});
      }

      $('#editAttribute_value_select').css('display', 'none');
      $('#editAttribute_value_input').css('display', 'block');
      $('#editAttribute_value_textarea').css('display', 'none');
      $('#editAttribute_value_file').css('display', 'none');

      $('#editAttribute_value_input').val(data.value);
      $('#editAttribute_value_input').prop('disabled', false);
    }
  }

  $('#tab-3').on('click', '#fi_attributeEdit', function () {
    var tr = $(this).closest('tr');

    attributeDialog({
      id: tr.attr('id'), providerId: tr.find('#providerId').val(), providerName: tr.find('#providerName').html(),
      category: tr.find('#category').html(), name: tr.find('#name').html(), mandatory: tr.find('#mandatory').html(),
      type: tr.find('#type').val(), typeHtml: tr.find('#typeHtml').html(), disabled: tr.find('#disabled').val(),
      allowedValues: tr.find('#allowedValues').val(), value: tr.find('#value').val()
    });

    $('#editAttribute_header').css('display', 'none');
    $('#editAttribute_data').css('display', 'block');
    $('#fi_newAttribute_form').dialog('open');
    return false;
  });

  $('#fi_newAttribute_button').click(function () {
    var skipId = new Array();
    $('#fi_attributeTable tr').each(function () {
      if ($(this).attr('id')) skipId.push($(this).attr('id'));
    });

    $.ajax({
      type: 'GET',
      dataType: 'json',
      data: {provider: [{allowedProvider}], skip: skipId, language: '{language}', applicable: ['USER'], applicableType: '{userType}' },
      url: '{ajaxUrl}?action=getAttribute',
      success: function (data) {
        var attributeCombo = $('#editAttribute_id_select').html('');
        attributeCombo.append('<option value=\"\">- vyberte -</option>');

        $.each(data, function (index, element) {
          attributeCombo.append('<option value=\"' + element.id + '\">' + element.name + '</option>');
        });
      },
      error: function (error) {
        alert('{__label.ajaxError}');
      }
    });

    $('#editAttribute_header').css('display', 'block');
    $('#editAttribute_data').css('display', 'none');
    $('#fi_newAttribute_form').dialog('open');
  });

  $('#editAttribute_id_select').change(function () {
    if ($(this).val()) {
      $.ajax({
        type: 'GET',
        dataType: 'json',
        data: {id: $(this).val(), language: '{language}'},
        url: '{ajaxUrl}?action=getAttribute',
        success: function (data) {
          attributeDialog(data);

          $('#editAttribute_data').css('display', 'block');
        },
        error: function (error) {
          alert('{__label.ajaxError}');
        }
      });

    }
  });

  var uploadObj = $('#editAttribute_file').uploadFile({
    url: "{url}/uploadfile.php",
    fileName: "uploadfile",
    dragDrop: false,
    maxFileCount: 1,
    uploadStr: "{__button.editCustomerAttribute_fileUpload}",
    maxFileCountErrorStr: "{__label.editCustomerAttribute_maxCount}",
    onSuccess: function (files, data, xhr, pd) {
      if (files) {
        var data = JSON.parse(data);
        $('#editAttribute_value_filename').val(data.name);
        $('#editAttribute_value_fileid').val(data.id);
      }
    }
  });

  showHideSubaccountSelect();
});

function showHidePassword() {
  var select = $('#fi_authentication');
  if (select.val() != undefined) {
    if (select.val() == 'FLEXBOOK') $('#fi_passwordDiv').show();
    else $('#fi_passwordDiv').hide();
  }
}

function showHideSubaccountSelect() {
  var checkbox = $('#fi_subaccountUser');
  var select = $('#fi_parent');

  if (checkbox.is(':checked')) select.show();
  else select.hide();
}