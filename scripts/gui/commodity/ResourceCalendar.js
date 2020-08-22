$(document).ready(function() {
    
    $.extend($.ui.dialog.prototype, {
        'addbutton': function (buttonName, func) {
            var buttons = this.element.dialog('option', 'buttons');
            buttons[buttonName] = func;
            this.element.dialog('option', 'buttons', buttons);
        }
    });
    $.extend($.ui.dialog.prototype, {
        'removebutton': function (buttonName) {
            var buttons = this.element.dialog('option', 'buttons');
            delete buttons[buttonName];
            this.element.dialog('option', 'buttons', buttons);
        }
    });
    
    var calendar = $('#fi_calendar').fullCalendar({
        buttonText: { today: '{__label.calendar_today}' },
        dayNames: [{__label.calendar_dayNames}],
        dayNamesShort: [{__label.calendar_dayLabels}],
        monthNames: [{__label.calendar_monthLabels}],
        monthNamesShort: [{__label.calendar_monthLabels}],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: false,
            Xright: 'month,basicWeek,basicDay',
        },
        titleFormat: { week: "d.[ MMM][ yyyy]{ '&#8212;' d. MMM yyyy}" },
        columnFormat: { week: 'dddd d.M.' },
        axisFormat: 'HH:mm',
        timeFormat: { agenda: 'H:mm{ - H:mm}' },
        defaultView: 'agendaWeek',
        weekNumbers: true,
        firstDay: 1,
        minTime: {minTime},
        maxTime: {maxTime},
        allDaySlot: false,
        selectable: true,
        selectHelper: true,
        editable: true,
        eventSources: [{
            url: '{url}?action=getResourceCalendar',
            type: 'GET',
            data: { id: '{resourceId}', showEvent: 1, showReservation: 1, sessid: '{%sessid%}' },
        }],
        eventClick: function(calEvent, jsEvent, view) {
            if (!calEvent.isBackground&&!calEvent.payment) {
              if (calEvent.type == 'reservation') {
                $.ajax({
                  type: 'GET',
                  url: '{url}?action=getReservation',
                  data: 'id='+calEvent.id,
                  dataType: 'json',
                  success: function(data) {
                      $('#editReservation_id').val(calEvent.id);
                      $('#editReservation_payed').val(data.payed);
                      $('#editReservation_line_number').show();
                      $('#editReservation_number').html(data.number);
                      $('#editReservation_visualFrom').html(data.from);
                      $('#editReservation_visualTo').html(data.to);
                      $('#editReservation_userId').val(data.userId);
                      $('#editReservation_userName').val(data.userName);
                      $('#editReservation_line_price').show();
                      $('#editReservation_price').html(data.price);
                      $('#editReservation_currency').html(data.currency);
                      
                      reservationDialog.dialog('open');
                  },
                  error: function(data) { alert('{__label.calendar_ajaxError}'); }
                });
              } else if (calEvent.type == 'event') {
                $.ajax({
                    type: 'GET',
                    url: '{url}?action=getEvent',
                    data: 'id='+calEvent.id,
                    dataType: 'json',
                    success: function(data) {
                        $('#editEvent_id').val(calEvent.id);
                        $('#editEvent_name').html(data.name);
                        $('#editEvent_description').html(data.description);
                        $('#editEvent_organiserName').html(data.organiserName);
                        $('#editEvent_visualStart').html(data.start);
                        $('#editEvent_visualEnd').html(data.end);
                        $('#editEvent_visualMaxAttendees').html(data.maxAttendees+' ({__label.calendar_editEvent_free} '+data.free+')');
                        $('#editEvent_maxAttendees').val(data.maxAttendees);
                        $('#editEvent_free').val(data.free);
                        $('#editEvent_price').html(data.price);
                        $('#editEvent_currency').html(data.currency);
                        
                        eventDialog.dialog('open');
                    },
                    error: function(data) { alert('{__label.calendar_ajaxError}'); }
                });
              }
            }
        },
        eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
            if (reservationCheckOverlap(event.start,event.end,event.id)) {
                revertFunc();
            } else {
                var params = { id: event.id, start: event.start, end: event.end };
                if (!reservationSave(params)) {
                    revertFunc();
                }
            }
        },
        eventResize: function(event,dayDelta,minuteDelta,revertFunc,jsEvent,ui,view) {
            if (reservationCheckOverlap(event.start,event.end,event.id)) {
                revertFunc();
            } else {
                var params = { id: event.id, start: event.start, end: event.end };
                if (!reservationSave(params)) {
                    revertFunc();
                }
            }
        },
        select: function(start,end,allDay) {
            if (reservationCheckOverlap(start,end)) {
                calendar.fullCalendar('unselect');
            } else {
                $('#editReservation_id').val('');
                $('#editReservation_payed').val('');
                $('#editReservation_line_number').hide();
                $('#editReservation_from').val(start);
                $('#editReservation_visualFrom').html(formatDateTime(start,'human'));
                $('#editReservation_to').val(end);
                $('#editReservation_visualTo').html(formatDateTime(end,'human'));
                $('#editReservation_userId').val('');
                $('#editReservation_userName').val('');
                $('#editReservation_line_price').hide();
                    
                reservationDialog.dialog('open');
                calendar.fullCalendar('unselect');
            }
	},
    });
    
    function reservationCheckOverlap(start,end,id) {
        // kontroluje prekryti s jinymi rezervacemi v kalendari (id je kvuli tomu, aby se nekontrolovalo prekryti se sebou samym)
        var array = calendar.fullCalendar('clientEvents');
        for(i in array){
            if (array[i].isBackground) continue;
            if (id&&array[i].id==id) continue;
            if (end > array[i].start && start < array[i].end) {
                alert('{__label.calendar_overlapError}');    
                return true;
            }
        }
        return false;
    }
    
    function reservationSave(params) {
        ret = false;
        
        // ulozi data o rezervaci ajaxem
        dataString = 'resource={resourceId}&{%session%}';
        if (params.id) {
            if (dataString) dataString += '&';
            dataString += 'id='+params.id;
        }
        if (params.start) {
            if (dataString) dataString += '&';
            dataString += 'start='+formatDateTime(params.start,'mysql');
        }
        if (params.end) {
            if (dataString) dataString += '&';
            dataString += 'end='+formatDateTime(params.end,'mysql');
        }
        if (params.user) {
            if (dataString) dataString += '&';
            dataString += 'user='+params.user;
        }
        if (params.customer) {
            if (dataString) dataString += '&';
            dataString += 'customer='+params.customer;
        }
        if (params.pay) {
            if (dataString) dataString += '&';
            dataString += 'pay='+params.pay;
        }
        $.ajax({
            type: 'GET',
            url: '{url}?action=saveReservation',
            data: dataString,
            dataType: 'json',
            async: false,
            success: function(data) {
                if (data.error) {
                    ret = false;
                    alert(data.message);
                } else {
                    ret = data.number;   
                }
            },
            error: function(data) { alert('{__label.calendar_ajaxError}'); }
        });
        
        return ret;
    }
    
    function reservationCancel(params) {
        ret = false;
        
        // ulozi data o rezervaci ajaxem
        dataString = '{%session%}';
        if (params.id) {
            if (dataString) dataString += '&';
            dataString += 'id='+params.id;
        }
        $.ajax({
            type: 'GET',
            url: '{url}?action=cancelReservation',
            data: dataString,
            dataType: 'json',
            async: false,
            success: function(data) {
                if (data.error) {
                    ret = false;
                    alert(data.message);
                } else {
                    ret = true;   
                }
            },
            error: function(data) { alert('{__label.calendar_ajaxError}'); }
        });
        
        return ret;
    }
    
    function userSave(params) {
        ret = false;
        
        // ulozi data o zakaznikovi ajaxem
        dataString = '{%session%}&providerId={providerId}';
        if (params.firstname) {
            if (dataString) dataString += '&';
            dataString += 'firstname='+params.firstname;
        }
        if (params.lastname) {
            if (dataString) dataString += '&';
            dataString += 'lastname='+params.lastname;
        }
        if (params.street) {
            if (dataString) dataString += '&';
            dataString += 'street='+params.street;
        }
        if (params.city) {
            if (dataString) dataString += '&';
            dataString += 'city='+params.city;
        }
        if (params.postalCode) {
            if (dataString) dataString += '&';
            dataString += 'postalCode='+params.postalCode;
        }
        if (params.state) {
            if (dataString) dataString += '&';
            dataString += 'state='+params.state;
        }
        if (params.ic) {
            if (dataString) dataString += '&';
            dataString += 'ic='+params.ic;
        }
        if (params.dic) {
            if (dataString) dataString += '&';
            dataString += 'dic='+params.dic;
        }
        if (params.email) {
            if (dataString) dataString += '&';
            dataString += 'email='+params.email;
        }
        if (params.phone) {
            if (dataString) dataString += '&';
            dataString += 'phone='+params.phone;
        }
        $.ajax({
            type: 'GET',
            url: '{url}?action=saveUser',
            data: dataString,
            dataType: 'json',
            async: false,
            success: function(data) {
                if (data.error) {
                    ret = false;
                    alert(data.message);
                } else {
                    ret = { id: data.id, name: data.name };
                }
            },
            error: function(data) { alert('{__label.calendar_ajaxError}'); }
        });
        
        return ret;
    }
    
    var reservationDialog = $('#editReservation_form').dialog({
        autoOpen: false,
        height: 270,
        width: 450,
        modal: true,
        open: function(event,ui) {
            $(this).dialog('removebutton', '{__button.calendar_editReservation_savePay}');
            $(this).dialog('removebutton', '{__button.calendar_editReservation_cancel}');
            
            // tlacitko "Zaplatit rezervaci"
            if ($('#editReservation_payed').val()=='') {
                $(this).dialog('addbutton', '{__button.calendar_editReservation_savePay}', function() {
                    if (confirm('{__label.calendar_editReservation_confirmSavePay}')) {
                      var params = { id: $('#editReservation_id').val(), user: $('#editReservation_userId').val(), pay: 'Y' };
                      if (!$('#editReservation_id').val()) {
                          params.start = $('#editReservation_from').val();
                          params.end = $('#editReservation_to').val();
                      }
                      if (number=reservationSave(params)) {
                          $(this).dialog('close');
                          
                          calendar.fullCalendar('refetchEvents');
                      }
                    } 
                });
            }
            // tlacitko "Zrusit rezervaci"
            if ($('#editReservation_id').val()) {
                $(this).dialog('addbutton', '{__button.calendar_editReservation_cancel}', function() {
                    if (confirm('{__label.calendar_editReservation_confirmCancel}')) {
                        var params = { id: $('#editReservation_id').val() };
                        if (reservationCancel(params)) {
                            $(this).dialog('close');
                            
                            calendar.fullCalendar('refetchEvents');
                        }
                    }
                });
            }
        },
        buttons: {
            '{__button.calendar_editReservation_save}': function() {
                var params = { id: $('#editReservation_id').val(), user: $('#editReservation_userId').val() };
                if (!$('#editReservation_id').val()) {
                    params.start = $('#editReservation_from').val();
                    params.end = $('#editReservation_to').val();
                }
                if (number=reservationSave(params)) {
                    $(this).dialog('close');
                    
                    calendar.fullCalendar('refetchEvents');
                }
            }
        }
    });
    
    $('#editReservation_userName').click(function () { $(this).select(); })
    $('#editReservation_userName').combogrid({
        url: '{url}?action=getUser&sessid={%sessid%}',
        debug: true,
        //replaceNull: true,
        colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                   {'columnName':'name','width':'30','label':'{__label.calendar_editUser_name}','align':'left'},
                   {'columnName':'address','width':'40','label':'{__label.calendar_editUser_address}','align':'left'},
                   {'columnName':'email','width':'30','label':'{__label.calendar_editUser_email}','align':'left'}],
        select: function(event,ui) {
          $('#editReservation_userName').val(ui.item.name);
          $('#editReservation_userId').val(ui.item.id);
          return false;
        }
    });
    
    var userDialog = $('#editUser_form').dialog({
        autoOpen: false,
        height: 450,
        width: 500,
        modal: true,
        /*open: function(event,ui) {
            // tlacitko "Zrusit rezervaci"
            if ($('#editReservation_id').val()) {
                $(this).dialog('addbutton', '{__button.calendar_editReservation_cancel}', function() {
                    if (confirm('{__label.calendar_editReservation_confirmCancel}')) {
                        var params = { id: $('#editReservation_id').val() };
                        if (reservationCancel(params)) {
                            $(this).dialog('close');
                            
                            calendar.fullCalendar('refetchEvents');
                        }
                    }
                });
            } else {
                $(this).dialog('removebutton', '{__button.calendar_editReservation_cancel}');
            }
        },*/
        buttons: {
            '{__button.calendar_editUser_save}': function() {
                var params = {  firstname: $('#editUser_firstname').val(), lastname: $('#editUser_lastname').val(), 
                                street: $('#editUser_street').val(), city: $('#editUser_city').val(),
                                postalCode: $('#editUser_postalCode').val(), state: $('#editUser_state').val(),
                                ic: $('#editUser_ic').val(), dic: $('#editUser_dic').val(),
                                email: $('#editUser_email').val(), phone: $('#editUser_phone').val()
                              };
                if (data=userSave(params)) {
                    $(this).dialog('close');
                    
                    $('#editReservation_userName').val(data.name);
                    $('#editReservation_userId').val(data.id);
                }
            }
        }
    });
    
    $('#editReservation_userNew').click(function() {
        $('#editUser_id').val('');
        $('#editUser_name').val('');
        $('#editUser_street').val('');
        $('#editUser_city').val('');
        $('#editUser_postalCode').val('');
        $('#editUser_state').val('');
        $('#editUser_ic').val('');
        $('#editUser_dic').val('');
        $('#editUser_email').val('');
        $('#editUser_phone').val('');
        $('#editUser_firstname').val('');
        $('#editUser_lastname').val('');

        userDialog.dialog('open');
    });
    
    var eventDialog = $('#editEvent_form').dialog({
        autoOpen: false,
        autoResize: true,
        //width: 450,
        modal: true,
        //open: function(event,ui) {
        //    eventAttendeesRefresh($('#editEvent_id').val());
        //},
        buttons: { },
    });
});