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
        eventStartEditable: false,
        eventDurationEditable: false,
        eventSources: [{
            url: '{url}?action=getResourceCalendar',
            type: 'GET',
            data: { id: '{resourceId}', customerView: 1 },
        }],
        select: function(start,end,allDay) {
            if (reservationCheckOverlap(start,end)) {
                calendar.fullCalendar('unselect');
            } else {
                $('#editReservationResource_id').val('');
                $('#editReservationResource_from').val(start);
                $('#editReservationResource_visualFrom').html(formatDateTime(start,'human'));
                $('#editReservationResource_to').val(end);
                $('#editReservationResource_visualTo').html(formatDateTime(end,'human'));
                $('#editReservationResource_visualUser').html('{userName}');
                $('#editReservationResource_userId').val('{userId}');
                    
                reservationResourceDialog.dialog('open');
                calendar.fullCalendar('unselect');
            }
	},
        {loginRequiredResource}
        eventClick: function(calEvent, jsEvent, view) {
            if (!calEvent.isBackground) {
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
    
    function reservationResourceSave(params) {
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
    
    function reservationEventSave(params) {
        ret = false;
        
        // ulozi data o rezervaci ajaxem
        dataString = 'event='+params.event+'&{%session%}';
        if (params.id) {
            if (dataString) dataString += '&';
            dataString += 'id='+params.id;
        }
        if (params.places) {
            if (dataString) dataString += '&';
            dataString += 'places='+params.places;
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
    
    function eventAttendeesRefresh(id) {
      $.ajax({
          type: 'GET',
          url: '{url}?action=getEvent',
          data: 'id='+id,
          dataType: 'json',
          success: function(data) {
              $('#editEvent_visualMaxAttendees').html(data.maxAttendees+' ({__label.calendar_editEvent_free} '+data.free+')');
              $('#editEvent_free').val(data.free);
              $('#editEvent_attendee').html('');
              $.each(data.attendee, function(index,attendee) {
                $('#editEvent_attendee').append('<div class="attendee" id="'+attendee.reservation+'">'+attendee.name+' ('+attendee.places+')</div>');
              });
              
              // tlacitko "Vytvorit rezervaci"
              if ($('#editEvent_free').val()>0) {
                  eventDialog.dialog('addbutton', '{__button.calendar_editEvent_newReservation}', function() {
                    {loginRequiredEvent}
                    
                    $('#editReservationEvent_id').val('');
                    $('#editReservationEvent_visualEvent').html($('#editEvent_name').html());
                    $('#editReservationEvent_event').val($('#editEvent_id').val());
                    $('#editReservationEvent_visualUser').html('{userName}');
                    $('#editReservationEvent_userId').val('{userId}');
                    $('#editReservationEvent_places').val('1');
                        
                    reservationEventDialog.dialog('open');
                  });
              } else {
                  eventDialog.dialog('removebutton', '{__button.calendar_editEvent_newReservation}');
              }
          },
          error: function(data) { alert('{__label.calendar_ajaxError}'); }
      });
    }
    
    var eventDialog = $('#editEvent_form').dialog({
        autoOpen: false,
        autoResize: true,
        //width: 450,
        modal: true,
        open: function(event,ui) {
            eventAttendeesRefresh($('#editEvent_id').val());
        },
        buttons: { },
    });
    
    var reservationResourceDialog = $('#editReservationResource_form').dialog({
        autoOpen: false,
        height: 200,
        width: 450,
        modal: true,
        buttons: {
            '{__button.calendar_editReservation_save}': function() {
                var params = { id: $('#editReservationResource_id').val(), user: $('#editReservationResource_userId').val() };
                if (!$('#editReservationResource_id').val()) {
                    params.start = $('#editReservationResource_from').val();
                    params.end = $('#editReservationResource_to').val();
                }
                if (number=reservationResourceSave(params)) {
                    $(this).dialog('close');
                    
                    if (!$('#editReservationResource_id').val()) {
                        //alert(number);
                        //alert($('#editReservation_from').val());
                        //alert($('#editReservation_to').val());
                        
                    }
                    
                    calendar.fullCalendar('refetchEvents');
                }
            },
            '{__button.calendar_editReservation_savePay}': function() {
                var params = { id: $('#editReservationResource_id').val(), user: $('#editReservationResource_userId').val(), pay: 'Y' };
                if (!$('#editReservationResource_id').val()) {
                    params.start = $('#editReservationResource_from').val();
                    params.end = $('#editReservationResource_to').val();
                }
                if (number=reservationResourceSave(params)) {
                    $(this).dialog('close');
                    
                    if (!$('#editReservationResource_id').val()) {
                        //alert(number);
                        //alert($('#editReservation_from').val());
                        //alert($('#editReservation_to').val());
                        
                    }
                    
                    calendar.fullCalendar('refetchEvents');
                }
            }
        }
    });
    
    var reservationEventDialog = $('#editReservationEvent_form').dialog({
        autoOpen: false,
        height: 260,
        width: 450,
        modal: true,
        buttons: {
            '{__button.calendar_editReservation_save}': function() {
                var params = { id: $('#editReservationEvent_id').val(), user: $('#editReservationEvent_userId').val(),
                               places: $('#editReservationEvent_places').val(), event: $('#editReservationEvent_event').val() };
                if (number=reservationEventSave(params)) {
                    $(this).dialog('close');
                    
                    eventAttendeesRefresh($('#editReservationEvent_event').val());
                }
            },
            '{__button.calendar_editReservation_savePay}': function() {
                var params = { id: $('#editReservationEvent_id').val(), user: $('#editReservationEvent_userId').val(),
                               places: $('#editReservationEvent_places').val(), event: $('#editReservationEvent_event').val(), pay: 'Y' };
                if (number=reservationEventSave(params)) {
                    $(this).dialog('close');
                    
                    eventAttendeesRefresh($('#editReservationEvent_event').val());
                }
            }
        }
    });
});