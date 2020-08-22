$(function() {
    var tabCookieName = 'ui-customer-tab';
    var tab = $('#tab').tabs({
            active : ($.cookie(tabCookieName) || 0),
            activate : function( event, ui ) {
              var newIndex = ui.newTab.parent().children().index(ui.newTab);
              // my setup requires the custom path, yours may not
              $.cookie(tabCookieName, newIndex);
            }
        });

    var subaccountEnabled = {subaccountEnabled};
    
     var index = $('#editCenter_index'),
         id = $('#editCenter_id'),
         name = $('#editCenter_name'),
         street = $('#editCenter_street'),
         city = $('#editCenter_city'),
         region = $('#editCenter_region'),
         postalCode = $('#editCenter_postalCode'),
         state = $('#editCenter_state'),
         paymentInfo = $('#editCenter_paymentInfo'),
         tips = $('.validateTips');
         
         registrationIndex = $('#editRegistration_index'),
         registrationId = $('#editRegistration_id'),
         registrationProviderId = $('#editRegistration_providerId'),
         registrationProviderName = $('#editRegistration_providerName'),
         registrationTimestamp = $('#editRegistration_timestamp'),
         registrationAdvertising = $('#editRegistration_advertising'),
         registrationCredit = $('#editRegistration_credit'),
         
         employeeIndex = $('#editEmployee_index'),
         employeeId = $('#editEmployee_id'),
         employeeUserId = $('#editEmployee_user'),
         employeeUserName = $('#editEmployee_userName'),
         employeeUserEmail = $('#editEmployee_userEmail'),
         employeeCredit = $('#editEmployee_credit'),
         
         coworkerIndex = $('#editCoworker_index'),
         coworkerId = $('#editCoworker_id'),
         coworkerUserId = $('#editCoworker_user'),
         coworkerUserName = $('#editCoworker_userName'),
         coworkerUserEmail = $('#editCoworker_userEmail'),
         coworkerAdmin = $('#editCoworker_admin'),
         coworkerSupervisor = $('#editCoworker_supervisor'),
         coworkerReception = $('#editCoworker_reception'),
         coworkerOrganiser = $('#editCoworker_organiser'),
         coworkerPowerOrganiser = $('#editCoworker_powerOrganiser'),
         
         attributeIndex = $('#editAttribute_index'),
         attributeId = $('#editAttribute_id'),
         attributeRestricted = $('#editAttribute_restricted'),
         attributeShort = $('#editAttribute_short'),
         attributeName = $('#editAttribute_name'),
         attributeUrl = $('#editAttribute_url'),
         attributeCategory = $('#editAttribute_category'),
         attributeMandatory = $('#editAttribute_mandatory'),
         attributeType = $('#editAttribute_type'),
         attributeAllowedValues = $('#editAttribute_allowedValues'),
         attributeDisabled = $('#editAttribute_disabled'),
         attributeApplicable = $('#editAttribute_applicable'),
         attributeApplicableType = $('#editAttribute_applicableType'),
         
         fileIndex = $('#editFile_index'),
         fileId = $('#editFile_id'),
         fileName = $('#editFile_name'),
         fileShort = $('#editFile_short'),
         fileSourceId = $('#editFile_sourceId'),
         fileSourceName = $('#editFile_sourceName'),
         fileSourceLink = $('#editFile_sourceLink'),
         fileNewSource = $('#editFile_newSource'),
         fileLength = $('#editFile_length'),
         
         allFields = $([]).add(name).add(street).add(city).add(region).add(postalCode).add(state).add(paymentInfo).
                     add(registrationProviderId).add(registrationAdvertising).add(attributeName).add(attributeCategory).add(attributeUrl).
                     add(attributeRestricted).add(attributeType).add(attributeAllowedValues).add(attributeDisabled).add(attributeShort).
                     add(attributeApplicableType);

    function updateTips(t) {
        tips.text(t).addClass('ui-state-highlight');
        
        setTimeout(function() { tips.removeClass('ui-state-highlight', 1500 ); }, 500 );
    }
         
    function checkLength(o, n, min, max ) {
        if ((o.val().length > max)||(o.val().length < min)) {
            o.addClass('ui-state-error');
            updateTips('{__error.invalid_value}: ' + n);
            return false;
        } else {
            return true;
        }
    }
    
    $('#fb_eCustomerSave').click(function() {
        if (!$("#fi_phone").intlTelInput('isValidNumber')) {
            alert('{__error.editUser_invalidPhone}');
            return false;
        }
        $("#fi_phone").val($("#fi_phone").intlTelInput('getNumber'));
    });
    
    $("#fi_phone").intlTelInput({
        preferredCountries: ['cz','sk'],
        //nationalMode: false, 
        utilsScript: "jq/intlTelInputUtils.js"
    });
    
    $('#fi_userAttributeTableBody').sortable({ placeholder: 'ui-state-highlight' });
    $('#fi_commodityAttributeTableBody').sortable({ placeholder: 'ui-state-highlight' });
    $('#fi_reservationAttributeTableBody').sortable({ placeholder: 'ui-state-highlight' });
    
    $('#fi_newCenter_form').dialog({
        autoOpen: false,
        height: 410,
        width: 350,
        modal: true,
        buttons: {
            '{__button.editCustomerCenter_ok}': function() {
                var bValid = true;
                allFields.removeClass('ui-state-error');
                bValid = bValid && checkLength(name, '{__label.editCustomerCenter_name}', 1, 255);
                bValid = bValid && checkLength(street, '{__label.editCustomer_street}', 1, 255);
                bValid = bValid && checkLength(city, '{__label.editCustomer_city}', 1, 255);
                bValid = bValid && checkLength(region, '{__label.editCustomer_region}', 1, 255);
                bValid = bValid && checkLength(postalCode, '{__label.editCustomer_postalCode}', 1, 255);
                bValid = bValid && checkLength(state, '{__label.editCustomer_state}', 2, 2);
                if (bValid) {
                    var html =
                            '<td>' + id.val() + '</td>' +
                            '<td id="centerName">' + name.val() + '</td>' +
                            '<td id="centerStreet">' + street.val() + '</td>' +
                            '<td id="centerCity">' + city.val() + '</td>' +
                            '<td id="centerPostalCode">' + postalCode.val() + '</td>' +
                            '<td id="centerState">' + state.val() + '</td>' +
                            '<input type="hidden" id="centerPaymentInfo" value="' + paymentInfo.val() + '"/>' +
                            '<input type="hidden" id="centerRegion" value="' + region.val() + '"/>' +
                            '<td class="tdAction">[<a href="#" id="fi_centerEdit">{__button.grid_edit}</a>][<a href="#" id="fi_centerRemove">{__button.grid_remove}]</a></td>';
                    if (index.val()) {
                        html += '<input type="hidden" name="newCenter['+index.val()+']" value="centerId:'+id.val()+';name:'+name.val()+';street:'+street.val()+';city:'+city.val()+';region:'+region.val()+';postalCode:'+postalCode.val()+';state:'+state.val()+';paymentInfo:'+paymentInfo.val()+'"/>';

                        $('#fi_centerTable tbody').find('tr#'+index.val()).html(html);
                    } else {
                        var tmp = Math.floor(Math.random()*10000);
                        html += '<input type="hidden" name="newCenter['+tmp+']" value="centerId:'+id.val()+';name:'+name.val()+';street:'+street.val()+';city:'+city.val()+';region:'+region.val()+';postalCode:'+postalCode.val()+';state:'+state.val()+';paymentInfo:'+paymentInfo.val()+'"/>';
                        
                        $('#fi_centerTable tbody').append('<tr id="'+tmp+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editCustomerCenter_cancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
            index.val('');
            id.val('');
            allFields.val('').removeClass('ui-state-error');
        }
    });
    
    function providerClick() {
      if ($('#fi_providerCheck').is(':checked')) {
        $('#fi_providerDiv').css({display:'block'});
        
        $('#li-tab-5').css({display:'block'});
        $('#li-tab-6').css({display:'block'});
        $('#li-tab-7').css({display:'block'});
        $('#li-tab-8').css({display:'block'});
        $('#li-tab-9').css({display:'block'});
        $('#li-tab-10').css({display:'block'});
        
        if ($('#fi_providerId').val()) {
          $('#fi_providerIdDiv').css({display:'block'});
        } else {
          $('#fi_providerIdDiv').css({display:'none'});
        }
      } else {
        $('#fi_providerDiv').css({display:'none'});
        
        $('#li-tab-5').css({display:'none'});
        $('#li-tab-6').css({display:'none'});
        $('#li-tab-7').css({display:'none'});
        $('#li-tab-8').css({display:'none'});
        $('#li-tab-9').css({display:'none'});
        $('#li-tab-10').css({display:'none'});
      }
    }  
    
    function invoiceAddressClick() {
      if ($('#fi_providerInvoiceAddressCheck').is(':checked')) {
        $('#fi_providerInvoiceAddressDiv').css({display:'block'});
      } else {
        $('#fi_providerInvoiceAddressDiv').css({display:'none'});
      }
    }
    
    $('#fi_providerCheck').change(function() { providerClick(); });
    $('#fi_providerInvoiceAddressCheck').change(function() { invoiceAddressClick(); });
    
    $('#fi_newRegistration_form').dialog({
        autoOpen: false,
        height: 250,
        width: 350,
        modal: true,
        buttons: {
            '{__button.editCustomerRegistration_ok}': function() {
                allFields.removeClass('ui-state-error');
                bValid = checkLength(registrationProviderId, '{__label.editCustomerRegistration_provider}', 1, 255);
                
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
                        var month = d.getMonth()+1;
                        var year = d.getFullYear();
                        registrationTimestamp.val(day+'.'+month+'.'+year);
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
                            '<input type="hidden" id="providerId" value="' + prov[0] + '"/>' +
                            '<td class="tdAction">[<a href="#" id="fi_registrationEdit">{__button.grid_edit}</a>][<a href="#" id="fi_registrationRemove">{__button.grid_remove}]</a></td>';
                    
                    var tmp;
                    if (registrationIndex.val()) { tmp = registrationIndex.val(); }
                    else { tmp = Math.floor(Math.random()*10000); };
                    
                    html += '<input type="hidden" class="registrationHidden" name="newRegistration['+tmp+']" value="registrationId:'+registrationId.val()+';providerId:'+prov[0]+';providerName:'+
                             prov[1]+';timestamp:'+registrationTimestamp.val()+';advertising:'+advertisingDb+';credit:'+registrationCredit.val()+'"/>';

                    if (registrationIndex.val()) {
                        $('#fi_registrationTable tbody').find('tr#'+tmp).html(html);
                    } else {
                        $('#fi_registrationTable tbody').append('<tr id="'+tmp+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editCustomerRegistration_cancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
            registrationIndex.val('');
            registrationId.val('');
            registrationProviderId.val('');
            registrationProviderName.val('');
            registrationAdvertising.prop('checked', false);
            allFields.val('').removeClass('ui-state-error');
        }
    });
    
    $('#tab-3').on('click','#fi_registrationRemove', function() {
        $(this).closest('tr').remove();
        return false;
    });
    
    $('#tab-3').on('click','#fi_registrationEdit', function() {
        var tr = $(this).closest('tr');
        registrationIndex.val(tr.attr('id'));
        registrationId.val(tr.attr('db_id'));
        if (tr.find('#advertising').html()=='{__label.yes}') { registrationAdvertising.prop('checked', true); }
        else { registrationAdvertising.prop('checked', false); }
        registrationProviderId.val(tr.find('#providerId').val()+'#'+tr.find('#providerName').html());
        registrationTimestamp.val(tr.find('#timestamp').html());
        registrationCredit.val(tr.find('#credit').html());
        
        $('#fi_newRegistration_form').dialog('open');
        
        return false;
    });
    
    $('#fi_newRegistration_button').click(function() {
        $('#fi_newRegistration_form').dialog('open');
    });
    
    $('#fi_newCoworker_form').dialog({
        autoOpen: false,
        height: 'auto',
        width: 550,
        modal: true,
        buttons: {
            '{__button.editCustomerCoworker_ok}': function() {
                allFields.removeClass('ui-state-error');
                bValid = checkLength(coworkerUserId, '{__label.editCustomerCoworker_user}', 1, 255);
                    
                role = '';
                if (bValid) {
                    if (coworkerAdmin.is(':checked')) {
                        role += '{__label.editCustomerCoworker_admin}';
                        var adminDb = 'Y';
                    } else { var adminDb = 'N'; }
                    if (coworkerSupervisor.is(':checked')) {
                        if (role) role += ', ';
                        role += '{__label.editCustomerCoworker_supervisor}';
                        var supervisorDb = 'Y';
                    } else { var supervisorDb = 'N'; }
                    if (coworkerReception.is(':checked')) {
                        if (role) role += ', ';
                        role += '{__label.editCustomerCoworker_reception}';
                        var receptionDb = 'Y';
                    } else { var receptionDb = 'N'; }
                    if (coworkerOrganiser.is(':checked')) {
                        if (role) role += ',';
                        role += '{__label.editCustomerCoworker_organiser}';
                        var organiserDb = 'Y';
                    } else { var organiserDb = 'N'; }
                    if (coworkerPowerOrganiser.is(':checked')) {
                        if (role) role += ',';
                        role += '{__label.editCustomerCoworker_powerOrganiser}';
                        var powerOrganiserDb = 'Y';
                    } else { var powerOrganiserDb = 'N'; }

                    var roleCenter = '';
                    $('#fi_newCoworker_form input[meaning=roleCenter]').each(function() {
                        if ($(this).is(':checked')) {
                            if (roleCenter) roleCenter += ',';
                            roleCenter += $(this).val();
                        }
                    });
                    
                    var html =
                            '<td id="coworkerFullname">' + coworkerUserName.val() + '</td>' +
                            '<td id="coworkerEmail">' + coworkerUserEmail.val() + '</td>' +
                            '<td id="coworkerRole">' + role + '</td>' +
                            '<input type="hidden" id="coworkerUser" value="' + coworkerUserId.val() + '"/>' +
                            '<input type="hidden" id="coworkerRoleCenter" value="' + roleCenter + '"/>' +
                            '<td class="tdAction">[<a href="#" id="fi_coworkerEdit">{__button.grid_edit}</a>]';
                    if (parseInt(coworkerUserId.val())!=parseInt($('#fi_authUser_id').val())) {
                        html += '[<a href="#" id="fi_coworkerRemove">{__button.grid_remove}]</a></td>';
                    }
                    
                    var tmp;
                    if (coworkerIndex.val()) { tmp = coworkerIndex.val(); }
                    else { tmp = Math.floor(Math.random()*10000); };
                    
                    html += '<input type="hidden" class="coworkerHidden" name="newCoworker['+tmp+']" value="coworkerId:'+coworkerId.val()+';userId:'+coworkerUserId.val()+';fullname:'+
                             coworkerUserName.val()+';email:'+coworkerUserEmail.val()+';admin:'+adminDb+';supervisor:'+supervisorDb+';reception:'+receptionDb+
                             ';organiser:'+organiserDb+';powerOrganiser:'+powerOrganiserDb+';roleCenter:'+roleCenter+'"/>';

                    if (coworkerIndex.val()) {
                        $('#fi_coworkerTable tbody').find('tr#'+tmp).html(html);
                    } else {
                        $('#fi_coworkerTable tbody').append('<tr id="'+tmp+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editCustomerCoworker_cancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
            coworkerIndex.val('');
            coworkerId.val('');
            coworkerUserId.val('');
            coworkerUserName.val('');
            coworkerUserEmail.val('');
            coworkerAdmin.prop('checked', false);
            coworkerSupervisor.prop('checked', false);
            coworkerReception.prop('checked', false);
            coworkerOrganiser.prop('checked', false);
            coworkerPowerOrganiser.prop('checked', false);
            $('#fi_newCoworker_form input[meaning=roleCenter]').prop('checked', false);
            allFields.val('').removeClass('ui-state-error');
        }
    });
    
    $('#fi_newEmployee_form').dialog({
        autoOpen: false,
        height: 220,
        width: 450,
        modal: true,
        buttons: {
            '{__button.editCustomerEmployee_ok}': function() {
                allFields.removeClass('ui-state-error');
                bValid = checkLength(employeeUserId, '{__label.editCustomerEmployee_user}', 1, 255);
                
                //// rozdeleni hodnoty "user" na id a jmeno a email
                //var user = employeeUserId.val().split('#');
                    
                if (bValid) {
                    if (employeeCredit.is(':checked')) {
                        var credit = '{__label.yes}';
                        var creditDb = 'Y';
                    } else {
                        var credit = '{__label.no}';
                        var creditDb = 'N';
                    }
                    
                    var html =
                            '<td id="employeeFullname">' + employeeUserName.val() + '</td>' +
                            '<td id="employeeEmail">' + employeeUserEmail.val() + '</td>' +
                            '<td id="employeeCredit">' + credit + '</td>' +
                            '<input type="hidden" id="employeeUser" value="' + employeeUserId.val() + '"/>' +
                            '<td class="tdAction">[<a href="#" id="fi_employeeEdit">{__button.grid_edit}</a>][<a href="#" id="fi_employeeRemove">{__button.grid_remove}]</a></td>';
                    
                    var tmp;
                    if (employeeIndex.val()) { tmp = employeeIndex.val(); }
                    else { tmp = Math.floor(Math.random()*10000); };
                    
                    html += '<input type="hidden" class="employeeHidden" name="newEmployee['+tmp+']" value="employeeId:'+employeeId.val()+';userId:'+employeeUserId.val()+';fullname:'+
                             employeeUserName.val()+';email:'+employeeUserEmail.val()+';creditAccess:'+creditDb+'"/>';

                    if (employeeIndex.val()) {
                        $('#fi_employeeTable tbody').find('tr#'+tmp).html(html);
                    } else {
                        $('#fi_employeeTable tbody').append('<tr id="'+tmp+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editCustomerEmployee_cancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
            employeeIndex.val('');
            employeeId.val('');
            employeeUserId.val('');
            employeeUserName.val('');
            employeeUserEmail.val('');
            employeeCredit.prop('checked', false);
            allFields.val('').removeClass('ui-state-error');
        }
    });
    
    $('#fi_newAttribute_form').dialog({
        autoOpen: false,
        width: 650,
        modal: true,
        buttons: {
            '{__button.editCustomerAttribute_ok}': function() {
                var bValid = true;
                allFields.removeClass('ui-state-error');
                bValid = bValid && checkLength(attributeName, '{__label.editCustomerAttribute_name}', 1, 1000);
                bValid = bValid && checkLength(attributeCategory, '{__label.editCustomerAttribute_category}', 0, 255);
                bValid = bValid && checkLength(attributeUrl, '{__label.editCustomerAttribute_url}', 0, 255);
                bValid = bValid && checkLength(attributeType, '{__label.editCustomerAttribute_type}', 1, 255);
                if (bValid) {
                    if (attributeMandatory.is(':checked')) {
                        var mandatory = '{__label.yes}';
                        var mandatoryDb = 'Y';
                    } else {
                        var mandatory = '{__label.no}';
                        var mandatoryDb = 'N';
                    }
                    if (attributeType.val()) { 
                      var attributeTypeHtml = $("option:selected", attributeType).text();
                    } else {
                      var attributeTypeHtml = '';
                    }
                    if (attributeDisabled.val()=='Y') {
                      var attributeSpecialAction = '[<a href="#" id="fi_attributeEnable">{__button.grid_restore}</a>]';
                    } else {
                      var attributeSpecialAction = '[<a href="#" id="fi_attributeDisable">{__button.grid_disable}</a>]';
                    }
                    
                    var html =
                            '<td id="attributeCategory">' + attributeCategory.val() + '</td>' +
                            '<td id="attributeShort">' + attributeShort.val() + '</td>' +
                            '<td id="attributeName">' + attributeName.val() + '</td>';
                    if (attributeApplicable.val()!='COMMODITY') html += '<td id="attributeMandatory">' + mandatory + '</td>';
                    html +=
                            '<td id="attributeTypeHTML">' + attributeTypeHtml + '</td>' +
                            '<input type="hidden" id="attributeUrl" value="' + attributeUrl.val() + '"/>' +
                            '<input type="hidden" id="attributeApplicable" value="' + attributeApplicable.val() + '"/>' +
                            '<input type="hidden" id="attributeApplicableType" value="' + attributeApplicableType.val() + '"/>' +
                            '<input type="hidden" id="attributeRestricted" value="' + attributeRestricted.val() + '"/>' +
                            '<input type="hidden" id="attributeType" value="' + attributeType.val() + '"/>' +
                            '<input type="hidden" id="attributeAllowedValues" value="' + attributeAllowedValues.val() + '"/>' +
                            '<input type="hidden" id="attributeDisabled" value="' + attributeDisabled.val() + '"/>' +
                            '<td class="tdAction">[<a href="#" id="fi_attributeEdit">{__button.grid_edit}</a>][<a href="#" id="fi_attributeRemove">{__button.grid_remove}</a>]'+
                            '<span id="attributeSpecialAction">'+attributeSpecialAction+'</span></td>';
                            
                    var tmp;
                    if (attributeIndex.val()) { tmp = attributeIndex.val(); }
                    else { tmp = Math.floor(Math.random()*10000); };
                    
                    if (attributeApplicable.val()=='USER') {
                        varName = 'newUserAttribute';
                        placeHolder = '#fi_userAttributeTable';
                    } else if (attributeApplicable.val()=='COMMODITY') {
                        varName = 'newCommodityAttribute';
                        placeHolder = '#fi_commodityAttributeTable';
                    } else if (attributeApplicable.val()=='RESERVATION') {
                        varName = 'newReservationAttribute';
                        placeHolder = '#fi_reservationAttributeTable';
                    }
                    
                    html += '<input type="hidden" id="attributeString" name="'+varName+'['+tmp+']" value="attributeId_:_'+attributeId.val()+'_;_restricted_:_'+attributeRestricted.val()+
                            '_;_short_:_'+attributeShort.val()+'_;_name_:_'+attributeName.val()+'_;_mandatory_:_'+mandatoryDb+'_;_url_:_'+attributeUrl.val()+'_;_category_:_'+attributeCategory.val()+
                            '_;_type_:_'+attributeType.val()+'_;_allowedValues_:_'+attributeAllowedValues.val()+'_;_disabled_:_'+attributeDisabled.val()+'_;_applicable_:_'+attributeApplicable.val()+
                            '_;_applicableType_:_'+attributeApplicableType.val()+'"/>';
                    if (attributeIndex.val()) {
                        $(placeHolder+' tbody').find('tr#'+tmp).html(html);
                    } else {
                        $(placeHolder+' tbody').append('<tr id="'+tmp+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editCustomerAttribute_cancel}': function() {
                $(this).dialog('close');
            }
        },
        open: function() {
            var tmp = $('#editAttribute_restricted').val();
            
            if (attributeApplicable.val()=='COMMODITY') {
                $(this).dialog('option','height','490');
                $(this).dialog('option','title','{__label.editCustomerAttribute_commodityTitle}');
                
                $('#editAttribute_restricted').html('<option value="">{__label.select_choose}</option><option value="INTERNAL">{__label.editCustomerAttribute_restrictedINTERNAL}</option>');
                $('#editAttribute_mandatoryField').hide();
                $('#editAttribute_applicableTypeField').hide();
            } else if (attributeApplicable.val()=='USER') {
                $(this).dialog('option','height','560');
                $(this).dialog('option','title','{__label.editCustomerAttribute_userTitle}');

                $('#editAttribute_restricted').html('<option value="">{__label.editCustomerAttribute_restrictedNORMAL}</option><option value="CREATEONLY">{__label.editCustomerAttribute_restrictedCREATEONLY}</option><option value="READONLY">{__label.editCustomerAttribute_restrictedREADONLY}</option><option value="INTERNAL">{__label.editCustomerAttribute_restrictedINTERNAL}</option>');
                if (subaccountEnabled) $('#editAttribute_applicableTypeField').show();
                else $('#editAttribute_applicableTypeField').hide();
                $('#editAttribute_mandatoryField').show();
            } else {
                $(this).dialog('option','height','520');
                $(this).dialog('option','title','{__label.editCustomerAttribute_reservationTitle}');
                
                $('#editAttribute_restricted').html('<option value="">{__label.select_choose}</option><option value="READONLY">{__label.editCustomerAttribute_restrictedREADONLY}</option><option value="INTERNAL">{__label.editCustomerAttribute_restrictedINTERNAL}</option>');
                $('#editAttribute_applicableTypeField').hide();
                $('#editAttribute_mandatoryField').show();
            }
            
            $('#editAttribute_restricted').val(tmp);
        },
        close: function() {
            attributeIndex.val('');
            attributeId.val('');
            allFields.val('').removeClass('ui-state-error');
            $('#editAttribute_allowedValues_div').css({display:'none'});
        }
    });
    
    $('#editAttribute_type').change(function() {
      if ($(this).val()=='LIST') {
        $('#editAttribute_allowedValues_div').css({display:'block'});
      } else {
        $('#editAttribute_allowedValues_div').css({display:'none'});
      }
    })
    
    $('#tab-8').on('click','#fi_attributeDisable', function() {
        var tr = $(this).closest('tr');
        tr.addClass('disabled');
        
        attributeDisabled.val('Y');
        var tmp = tr.find('#attributeString').val().replace('disabled_:_N','disabled_:_Y');
        tr.find('#attributeString').val(tmp);
        
        tr.find('#attributeSpecialAction').html('[<a href="#" id="fi_attributeEnable">{__button.grid_restore}</a>]');
        
        return false;
    });
    $('#tab-8').on('click','#fi_attributeEnable', function() {
        var tr = $(this).closest('tr');
        tr.removeClass('disabled');
        
        attributeDisabled.val('N');
        var tmp = tr.find('#attributeString').val().replace('disabled_:_Y','disabled_:_N');
        tr.find('#attributeString').val(tmp);
        
        tr.find('#attributeSpecialAction').html('[<a href="#" id="fi_attributeDisable">{__button.grid_disable}</a>]');
        
        return false;
    });
    
    $('#tab-8').on('click','#fi_attributeRemove', function() {
        $(this).closest('tr').remove();
        return false;
    });
    
    $('#tab-8').on('click','#fi_attributeEdit', function() {
        var tr = $(this).closest('tr');
        attributeIndex.val(tr.attr('id'));
        attributeId.val(tr.attr('db_id'));
        if (tr.find('#attributeMandatory').html()=='{__label.yes}') { attributeMandatory.prop('checked', true); }
        else { attributeMandatory.prop('checked', false); }
        attributeShort.val(tr.find('#attributeShort').html());
        attributeName.val(tr.find('#attributeName').html());
        attributeUrl.val(tr.find('#attributeUrl').val());
        attributeCategory.val(tr.find('#attributeCategory').html());
        attributeRestricted.val(tr.find('#attributeRestricted').val());
        attributeApplicable.val(tr.find('#attributeApplicable').val());
        attributeApplicableType.val(tr.find('#attributeApplicableType').val());
        attributeType.val(tr.find('#attributeType').val());
        if (attributeType.val()=='LIST') $('#editAttribute_allowedValues_div').css({display:'block'});
        attributeAllowedValues.val(tr.find('#attributeAllowedValues').val());
        
        $('#fi_newAttribute_form').dialog('open');
        
        return false;
    });
    
    $('#fi_newUserAttribute_button').click(function() {
        attributeApplicable.val('USER');
        $('#fi_newAttribute_form').dialog('open');
        return false;
    });
    $('#fi_newCommodityAttribute_button').click(function() {
        attributeApplicable.val('COMMODITY');
        $('#fi_newAttribute_form').dialog('open');
        return false;
    });
    $('#fi_newReservationAttribute_button').click(function() {
        attributeApplicable.val('RESERVATION');
        $('#fi_newAttribute_form').dialog('open');
        return false;
    });

    $('#editAttribute_category').devbridgeAutocomplete({ serviceUrl: '{url}?action=getAttributeCategory&provider={providerId}',});
    
    $('#fi_newFile_form').dialog({
        autoOpen: false,
        height: 320,
        width: 450,
        modal: true,
        beforeClose: function(event,ui) {
          uploadObj.reset();
        },
        buttons: {
            '{__button.editCustomerFile_ok}': function() {
                var bValid = true;
                allFields.removeClass('ui-state-error');
                bValid = bValid && checkLength(fileShort, '{__label.editCustomerFile_short}', 1, 255);
                bValid = bValid && checkLength(fileName, '{__label.editCustomerFile_name}', 1, 255);
                    
                if (bValid) {
                    var html =
                            '<td id="short">' + fileShort.val() + '</td>' +
                            '<td id="name">' + fileName.val() + '</td>' +
                            '<td id="source">' + fileSourceName.val() + '</td>' +
                            '<td id="length">' + fileLength.val() + '</td>' +
                            '<input type="hidden" id="sourceId" value="' + fileSourceId.val() + '"/>' +
                            '<input type="hidden" id="sourceLink" value="' + fileSourceLink.val() + '"/>' +
                            '<td class="tdAction">[<a href="#" id="fi_fileEdit">{__button.grid_edit}</a>][<a href="#" id="fi_fileRemove">{__button.grid_remove}]</a></td>';
                    
                    var tmp;
                    if (fileIndex.val()) { tmp = fileIndex.val(); }
                    else { tmp = Math.floor(Math.random()*10000); };
                  
                    html += '<input type="hidden" class="fileHidden" name="newFile['+tmp+']" value="id:'+fileId.val()+';short:'+fileShort.val()+';name:'+
                             fileName.val()+';length:'+fileLength.val()+';sourceName:'+fileSourceName.val()+';sourceId:'+fileSourceId.val()+';sourceLink:'+fileSourceLink.val()+';newSource:'+fileNewSource.val()+'"/>';

                    if (fileIndex.val()) {
                        $('#fi_fileTable tbody').find('tr#'+tmp).html(html);
                    } else {
                        $('#fi_fileTable tbody').append('<tr id="'+tmp+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editCustomerFile_cancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
            fileIndex.val('');
            fileId.val('');
            fileShort.val('');
            fileName.val('');
            fileLength.val('');
            fileSourceId.val('');
            fileSourceName.val('');
            fileSourceLink.val('');
            fileNewSource.val('');
            allFields.val('').removeClass('ui-state-error');
        }
    });
    
    var uploadObj = $('#editFile_source').uploadFile({
            url: "{urlDir}/uploadfile.php",
            fileName: "uploadfile",
            dragDrop: false,
            maxFileCount: 1,
            uploadStr: "{__button.editCustomerFile_fileUpload}",
            maxFileCountErrorStr: "{__label.editCustomerFile_maxCount}",
            onSuccess: function(files,data,xhr,pd) {
              if (files) {
                var data = JSON.parse(data);
                $('#editFile_sourceName').val(data.name);
                $('#editFile_sourceLink').val(data.id);
                $('#editFile_newSource').val('1');
              }
            }
    });
    
    $('#tab-9').on('click','#fi_fileRemove', function() {
        $(this).closest('tr').remove();
        return false;
    });
    
    $('#tab-9').on('click','#fi_fileEdit', function() {
        var tr = $(this).closest('tr');
        fileIndex.val(tr.attr('id'));
        fileId.val(tr.attr('db_id'));
        fileShort.val(tr.find('#short').html());
        fileName.val(tr.find('#name').html());
        fileSourceName.val(tr.find('#source').html());
        fileSourceLink.val(tr.find('#sourceLink').val());
        fileSourceId.val(tr.find('#sourceId').val());
        
        $('#fi_newFile_form').dialog('open');
        
        return false;
    });
    
    $('#fi_newFile_button').click(function() {
        $('#fi_newFile_form').dialog('open');
        return false;
    });
    
    $('#tab-2').on('click','#fi_employeeRemove', function() {
        $(this).closest('tr').remove();
        return false;
    });
    
    $('#tab-2').on('click','#fi_employeeEdit', function() {
        var tr = $(this).closest('tr');
        employeeIndex.val(tr.attr('id'));
        employeeId.val(tr.attr('db_id'));
        if (tr.find('#employeeCredit').html()=='{__label.yes}') { employeeCredit.prop('checked', true); }
        else { employeeCredit.prop('checked', false); }
        employeeUserId.val(tr.find('#employeeUser').val());
        employeeUserName.val(tr.find('#employeeFullname').html());
        employeeUserEmail.val(tr.find('#employeeEmail').html());
        
        $('#fi_newEmployee_form').dialog('open');
        
        return false;
    });
    
    $('#tab-6').on('click','#fi_newCenter_button',function() {
        $('#fi_newCenter_form').dialog('open');
        return false;
    });
    
    $('#tab-6').on('click','#fi_centerRemove', function() {
        $(this).closest('tr').remove();
        return false;
    });
    
    $('#tab-6').on('click','#fi_centerEdit', function() {
        var tr = $(this).closest('tr');
        index.val(tr.attr('id'));
        id.val(tr.attr('db_id'));
        name.val(tr.find('#centerName').html());
        street.val(tr.find('#centerStreet').html());
        city.val(tr.find('#centerCity').html());
        region.val(tr.find('#centerRegion').val());
        postalCode.val(tr.find('#centerPostalCode').html());
        state.val(tr.find('#centerState').html());
        paymentInfo.val(tr.find('#centerPaymentInfo').val());
        
        $('#fi_newCenter_form').dialog('open');
        
        return false;
    });
    
    $('#tab-7').on('click','#fi_coworkerRemove', function() {
        $(this).closest('tr').remove();
        return false;
    });
    
    $('#tab-7').on('click','#fi_coworkerEdit', function() {
        var tr = $(this).closest('tr');
        coworkerIndex.val(tr.attr('id'));
        coworkerId.val(tr.attr('db_id'));
        coworkerUserId.val(tr.find('#coworkerUser').val());
        coworkerUserName.val(tr.find('#coworkerFullname').html());
        coworkerUserEmail.val(tr.find('#coworkerEmail').html());

        if (tr.find('#coworkerRole').html().indexOf('{__label.editCustomerCoworker_admin}')!=-1) { coworkerAdmin.prop('checked', true); }
        else { coworkerAdmin.prop('checked', false); }
        if (parseInt(coworkerUserId.val())==parseInt($('#fi_authUser_id').val())) { coworkerAdmin.prop('disabled', true); }
        else { coworkerAdmin.prop('disabled', false); }

        if (tr.find('#coworkerRole').html().indexOf('{__label.editCustomerCoworker_supervisor}')!=-1) { coworkerSupervisor.prop('checked', true); }
        else { coworkerSupervisor.prop('checked', false); }
        if (tr.find('#coworkerRole').html().indexOf('{__label.editCustomerCoworker_reception}')!=-1) { coworkerReception.prop('checked', true); }
        else { coworkerReception.prop('checked', false); }
        if (tr.find('#coworkerRole').html().indexOf('{__label.editCustomerCoworker_organiser}')!=-1) { coworkerOrganiser.prop('checked', true); }
        else { coworkerOrganiser.prop('checked', false); }
        if (tr.find('#coworkerRole').html().indexOf('{__label.editCustomerCoworker_powerOrganiser}')!=-1) { coworkerPowerOrganiser.prop('checked', true); }
        else { coworkerPowerOrganiser.prop('checked', false); }

        var center = tr.find('#coworkerRoleCenter').val();
        if (center) {
            center.split(',').forEach(function (id) {
                $('#editCoworker_center_' + id).prop('checked', true);
            });
        }
        
        $('#fi_newCoworker_form').dialog('open');
        
        return false;
    });
    
    $('#fi_newCoworker_button').click(function() {
        $('#fi_newCoworker_form').dialog('open');
        return false;
    });
    
    $('#editCoworker_userName').combogrid({
      url: '{url}?action=getUser&provider={providerId}&sessid={%sessid%}',
      debug: true,
      //replaceNull: true,
      colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                 {'columnName':'name','width':'30','label':'Jméno','align':'left'},
                 {'columnName':'address','width':'40','label':'Adresa','align':'left'},
                 {'columnName':'email','width':'30','label':'Email','align':'left'}],
      select: function(event,ui) {
        $('#editCoworker_userName').val(ui.item.name);
        $('#editCoworker_user').val(ui.item.id);
        $('#editCoworker_userEmail').val(ui.item.email);
        return false;
      }
    });
    
    $('#fi_newEmployee_button').click(function() {
        $('#fi_newEmployee_form').dialog('open');
    });
    
    $('#editEmployee_userName').combogrid({
      url: '{url}?action=getUser&sessid={%sessid%}',
      debug: true,
      //replaceNull: true,
      colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                 {'columnName':'name','width':'30','label':'Jméno','align':'left'},
                 {'columnName':'address','width':'40','label':'Adresa','align':'left'},
                 {'columnName':'email','width':'30','label':'Email','align':'left'}],
      select: function(event,ui) {
        $('#editEmployee_userName').val(ui.item.name);
        $('#editEmployee_user').val(ui.item.id);
        $('#editEmployee_userEmail').val(ui.item.email);
        return false;
      }
    });
    
    $('#fi_newAttribute_button').click(function() {
        $('#fi_newAttribute_form').dialog('open');
        return false;
    });

    $('#fi_smtpSecure').change(function() {
      var secure = $(this).val();
      if (secure=='tls') $('#fi_smtpPort').val('25');
      if (secure=='ssl') $('#fi_smtpPort').val('465');
    });

    $('#fi_invoiceAccountFrom').datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',lang:'cz',timepicker:false});

    $('#fi_helpAttributeUrlDiv').dialog({
        autoOpen: false,
        width: 450,
    });

    $('#fi_helpAttributeUrl').click(function() {
        $('#fi_helpAttributeUrlDiv').dialog('open');
    });

    providerClick();
    invoiceAddressClick();

});