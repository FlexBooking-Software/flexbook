$(document).ready(function() {
  $('#{prefix}flb_resource_calendar').on('click','#{prefix}flb_calendar_back', function() {
    $('#{prefix}editEvent_form').remove();
    $('#{prefix}editReservationResource_form').remove();
    $('#{prefix}editReservationEvent_form').remove();
    //eventDialog.dialog('destroy');
    //reservationResourceDialog.dialog('destroy');
    //reservationEventDialog.dialog('destroy');
    
    flbLoadHtml('guiResourceList', $(this).closest('.flb_output').parent(), {params});            
  });
  
  $.extend($.ui.dialog.prototype, {
    'addbutton': function (buttonName, buttonId, buttonClass, func) {
      var buttons = this.element.dialog('option', 'buttons');
      buttons[buttonName] = {
                                id: buttonId,
                                text: buttonName,
                                click: func
                                };
      if (buttonClass) buttons[buttonName]['class'] = buttonClass;
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

  var useUserSubaccount = {useUserSubaccount};
  var {prefix}calAd = {calAd};
  var {prefix}orgMR = {orgMR};
  var {prefix}orgSR = {orgSR};
  var {prefix}orgHA = {orgHA};
  var allowedPaymentCash = {allowedPaymentCash};
  if ({prefix}calAd) {
    $('#{prefix}editReservationResource_userName').click(function () { $(this).select(); });
    $('#{prefix}editReservationResource_userName').combogrid({
      url: $('#flb_core_url').val()+'action=getUser&sessid={%sessid%}',
      debug: true,
      autoFocus: true,
      //replaceNull: true,
      colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                 {'columnName':'firstname','width':'20','label':'{__label.calendar_editUser_firstname}','align':'left'},
                 {'columnName':'lastname','width':'20','label':'{__label.calendar_editUser_lastname}','align':'left'},
                 {'columnName':'address','width':'40','label':'{__label.calendar_editUser_address}','align':'left'},
                 {'columnName':'email','width':'20','label':'{__label.calendar_editUser_email}','align':'left'}],
      select: function(event,ui) {
        $('#{prefix}editReservationResource_userName').val(ui.item.name);
        $('#{prefix}editReservationResource_userId').val(ui.item.id);
        $('#{prefix}editReservationResource_userNameSelected').val(ui.item.name);
        
        var userData = getUserDetail({ user: $('#{prefix}editReservationResource_userId').val(), resource: $('#{prefix}editReservationResource_resourceId').val(), price: $('#{prefix}editReservationResource_price').html() });
        if (!userData) $('#{prefix}editReservationResource_userName').val('');
        reservationResourceButtonRefresh(userData);
        
        return false;
      }
    });
    $('#{prefix}editReservationResource_userName').change(function () {
      if ($(this).val()=='') {
        $('#{prefix}editReservationResource_userName').val('');
        $('#{prefix}editReservationResource_userId').val('');
        $('#{prefix}editReservationResource_userNameSelected').val('');
        
        var userData = getUserDetail({ user: $('#{prefix}editReservationResource_userId').val(), resource: $('#{prefix}editReservationResource_resourceId').val(), price: $('#{prefix}editReservationResource_price').html() });
        reservationResourceButtonRefresh(userData);
      }
    });
  }
  
  // zdroje pro kalendar
  if (!window.calSource||!(window.calSource instanceof Array)) window.calSource = [];
  window.calSource['{prefix}cal_{id}'] = {
    url: $('#flb_core_url').val()+'action=getResourceCalendar',
    type: 'GET',
    data: { {commodity_id} type: '{commodity_type}', calAd: {prefix}calAd, sessid: $('#flb_core_sessionid').val(){shownData} }
  };
  
  $('#{prefix}cal_{id}').fullCalendar({
    schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
    locale: '{language}',
    longPressDelay: 200,
    customButtons: {
      selectDate: {
        text: '{__label.calendar_selectDate}',
        click: function() {
          var placeHolder = $('#{prefix}cal_{id} .fc-left h2');
          placeHolder.html('<input type="text" id="{prefix}_cal_dateSelector"/>');
          $('#{prefix}_cal_dateSelector').datetimepicker({
            lang: '{language}',
            timepicker: false,
            format:'d.m.Y',
            dayOfWeekStart:'1',
            onSelectDate: function(ct) {
              $('#{prefix}cal_{id}').fullCalendar('gotoDate',ct.dateFormat('Y-m-d'));
            }
          });
          $('#{prefix}_cal_dateSelector').focus();
        }
      }
    },
    header: {
        left: 'prev,next{today}{selectDate} title',
        center: false,
        right: false
    },
    //titleFormat: { week: 'D. MMMM YYYY', day: 'dd D. MMMM YYYY' },
    //columnFormat: { week: 'dd D.M.' },
    timezone: 'local',
    timeFormat: 'H:mm',
    allDaySlot: false,
    contentHeight: 'auto',
    slotDuration: '00:{timeSlot}:00',
    //slotLabelInterval: 60,
    slotLabelFormat: 'H:mm',
    minTime: '{minTime}:00',
    maxTime: '{maxTime}:00',
    defaultView: '{viewType}',
    weekNumbers: false,
    firstDay: 1,
    {defaultDate}
    startParam: 'startDate',
    endParam: 'endDate',
    selectable: {selectable},
    selectHelper: true,
    editable: true,
    slotEventOverlap: false,
    eventOverlap: false, 
    eventStartEditable: {prefix}calAd,
    eventDurationEditable: {prefix}calAd,
    eventSources: [ window.calSource['{prefix}cal_{id}'] ],
    {designParams}
    {calResource}
    select: function(start, end, jsEvent, view, resource) {
      if (!$('#flb_core_userid').val()) {
        flbLoginRequired('{language}');
        $('#{prefix}cal_{id}').fullCalendar('unselect');
        return false;
      }
      // nelze rezervovat v minulosti
      if (start.isBefore(moment())) {
        alert('{__label.calendar_selectionPast}');
        $('#{prefix}cal_{id}').fullCalendar('unselect');
        return false;
      }

      // zarovnani na timealignment a minimalni delku rezervace
      var alignmentStart = '{alignmentStart}';
      var alignmentEnd = '{alignmentEnd}';
      var alignmentStep = {alignmentGrid}*60;
      var timeEndFrom = '{timeEndFrom}';
      var timeEndTo = '{timeEndTo}';
      var unitRounding = '{unitRounding}';
      var minimalLength = {unit}*{minimumUnitQuantity}*60;
      var maximalLength = {unit}*{maximumUnitQuantity}*60;
      var startStamp = parseInt(start.locale('en').format('X'),10);
      var endStamp = parseInt(end.locale('en').format('X'),10);
      var alignmentMessage = '';

      //alert(start);
      //alert(end);

      // nejdriv je potreba upravit "to" pro kalendar "na dny"
      if (unitRounding=='day') {
        // kdyz jsou rezervacni jednotky dny, je potreba zmensit "to" z "den 00:00:00" na "den-1 22:00:00", aby se nerezervoval den navic
        end = moment.unix(endStamp-7200);
      } else if (unitRounding=='night') {
        // kdyz jsou rezervacni jednotky noci, je potreba zmensit "to" z "den 00:00:00" na "den-1 22:00:00", aby se nerezervovala noc navic
        var newEnd = moment.unix(endStamp-7200);
        // ale pouze kdyz je vybrano vice noci
        if (newEnd.locale('en').format('YYYY-MM-DD')>start.locale('en').format('YYYY-MM-DD')) {
          end = newEnd;
        }
      }
      
      if (alignmentStart) {
        var alignmentStartString = start.locale('en').format('ddd MMM DD YYYY')+' '+alignmentStart;
        var alignmentEndString = start.locale('en').format('ddd MMM DD YYYY')+' '+alignmentEnd;
        
        var alignmentStartStamp = parseInt(moment(alignmentStartString,'ddd MMM DD YYYY hh:mm:ss').format('X'),10); 
        if (alignmentStep) {
          while (alignmentStartStamp+alignmentStep<=startStamp) {
            alignmentStartStamp += alignmentStep;
          }
          
          if (startStamp!=alignmentStartStamp) {
            // posunu start podle aligmentu
            start = moment.unix(alignmentStartStamp);
            // pokud se nerezervuji dny/noci, posunu adekvatne i end
            if ((unitRounding!='day')&&(unitRounding!='night')) end = moment.unix(endStamp-(startStamp-start.unix()));
            
            if (view.name!='month') alignmentMessage = '{__label.calendar_startChangedDueAlignment}';
          }
        } else if (startStamp<alignmentStartStamp) {
          // posunu start podle aligmentu
          start = moment.unix(alignmentStartStamp);
          // pokud se nerezervuji dny/noci, posunu adekvatne i end
          if ((unitRounding!='day')&&(unitRounding!='night')) end = moment.unix(endStamp-(startStamp-start.unix()));
          
          if (view.name!='month') alignmentMessage = '{__label.calendar_startChangedDueAlignment}';
        }
      }
      if (minimalLength>endStamp-startStamp) {
        end = moment(start);
        end.add(minimalLength,'s');
        
        if (alignmentMessage) alignmentMessage += '\n';
        alignmentMessage += '{__label.calendar_lengthChangedDueAlignment}';
      } else if (maximalLength&&(maximalLength<endStamp-startStamp)) {
        end = moment(start);
        end.add(maximalLength,'s');
        
        if (alignmentMessage) alignmentMessage += '\n';
        alignmentMessage += '{__label.calendar_lengthChangedDueAlignment}';
      }
      if (timeEndTo) {
        var newEndString = end.locale('en').format('ddd MMM DD YYYY')+' '+timeEndTo;
        end = moment(newEndString,'ddd MMM DD YYYY hh:mm');
      }

      // kontrola na prekryti pro rezervace zdroje (dela se az tady kvuli automatickym alignmentum
      if ($('#{prefix}flb_commodity_type').val()=='resource') {
        var resourceId = null;
        if (resource) resourceId = resource.id;
        if (reservationCheckOverlap('{prefix}cal_{id}',start,end,null,resourceId)) {
          alert('{__label.calendar_notAvailable}');
          $('#{prefix}cal_{id}').fullCalendar('unselect');

          return false;
        }

        $("#{prefix}editReservationResource_places").empty().append($('<option></option>').attr('value', '1').text('1'));

        $('#{prefix}editReservationResource_line_resources').hide();
      } else if ($('#{prefix}flb_commodity_type').val()=='pool') {
        var allowedResource = reservationPoolGetResource($('#{prefix}flb_resourcepool_id').val(),moment(start).format('YYYY-MM-DD HH:mm:ss'),moment(end).format('YYYY-MM-DD HH:mm:ss'));
        if (!allowedResource) {
          alert('{__label.calendar_poolNotAvailable}');
          $('#{prefix}cal_{id}').fullCalendar('unselect');
          return false;
        }
        //alert(resource);
        //$('#{prefix}cal_{id}').fullCalendar('unselect');return false;

        $('#{prefix}editReservationResource_allowedResourceIds').val(allowedResource);
        $('#{prefix}flb_resource_id').val(allowedResource[0]);
        $("#{prefix}editReservationResource_places").empty();
        for (var i=1;i<=allowedResource.length;i++) {
          $("#{prefix}editReservationResource_places").append($('<option></option>').attr('value', i).text(i));
        }

        $('#{prefix}editReservationResource_line_resources').show();
      } else {
        alert('Invalid use of calendar!')
        $('#{prefix}cal_{id}').fullCalendar('unselect');
        return false;
      }

      // kdyz byl nejaky alignment, zobrazim info
      if (alignmentMessage) alert(alignmentMessage);

      // u view "month" kdyz je alignment interval je potreba umoznit vybrat cas from
      if ((view.name=='month')&&(alignmentStart<alignmentEnd)) {
        $('#{prefix}editReservationResource_inputTimeFrom').show();
        $('#{prefix}editReservationResource_inputTimeFrom').datetimepicker({
          format:'H:i', datepicker:false, step: alignmentStep?alignmentStep/60:1,
          minTime: alignmentStart.substring(0,5), maxTime: alignmentEnd.substring(0,4)+'1',
          onSelectTime: function(current_time) {
            var newStart = start.format('YYYY-MM-DD')+' '+moment(current_time).format('HH:mm:ss');
            $('#{prefix}editReservationResource_from').val(moment(newStart,'YYYY-MM-DD HH:mm:ss'));

            var newStartStamp = parseInt(moment(newStart).format('X'),10);
            $('#{prefix}editReservationResource_visualDateTo').html(newEndStamp.format('D.M.YYYY'));

            //var newEndStamp = parseInt(end.format('X'),10);
            //newEndStamp = moment.unix(newEndStamp+(newStartStamp-start.unix()));
            
            //$('#{prefix}editReservationResource_to').val(newEndStamp);
            //$('#{prefix}editReservationResource_visualTimeTo').html(newEndStamp.format('HH:mm'));
          }
        });
        
        $('#{prefix}editReservationResource_visualTimeFrom').hide();
      } else {
        $('#{prefix}editReservationResource_inputTimeFrom').hide();
        $('#{prefix}editReservationResource_visualTimeFrom').show();
      }

      // u view "month" kdyz je time end interval je potreba umoznit vybrat cas to
      if (view.name=='month') {
        if (timeEndFrom&&timeEndTo) {
          $('#{prefix}editReservationResource_inputTimeTo').show();
          $('#{prefix}editReservationResource_visualTimeTo').hide();
          $('#{prefix}editReservationResource_inputTimeTo').datetimepicker({
            format:'H:i', datepicker:false, step: {unit},
            minTime: timeEndFrom.substring(0,5), maxTime: timeEndTo.substring(0,4)+'1',
            onSelectTime: function(current_time) {
              var newEnd = end.format('YYYY-MM-DD')+' '+moment(current_time).format('HH:mm:ss');
              $('#{prefix}editReservationResource_to').val(moment(newEnd,'YYYY-MM-DD HH:mm:ss'));
            }
          });
        } else {
          $('#{prefix}editReservationResource_inputTimeTo').hide();
          $('#{prefix}editReservationResource_visualTimeTo').show();
        }
      } else {
        $('#{prefix}editReservationResource_inputTimeTo').show();
        $('#{prefix}editReservationResource_visualTimeTo').hide();
        $('#{prefix}editReservationResource_inputTimeTo').datetimepicker({
          format:'H:i', datepicker:false, step: {unit},
          minTime: start.format('HH:mm'), maxTime: '{maxTime}',
          onSelectTime: function(current_time) {
            var newEnd = end.format('YYYY-MM-DD')+' '+moment(current_time).format('HH:mm:ss');
            $('#{prefix}editReservationResource_to').val(moment(newEnd,'YYYY-MM-DD HH:mm:ss'));
            
            reservationResourceGetPrice(moment($('#{prefix}editReservationResource_from').val()).format('YYYY-MM-DD HH:mm:ss'), newEnd);

            var userData = getUserDetail({ user: $('#{prefix}editReservationResource_userId').val(), resource: $('#{prefix}editReservationResource_resourceId').val(), price: $('#{prefix}editReservationResource_priceHidden').val() });
            reservationResourceButtonRefresh(userData);
          }
        });
      }
      
      if (($('#{prefix}flb_commodity_type').val()=='resource')&&resource) $('#{prefix}editReservationResource_resourceId').val(resource.id);
      else $('#{prefix}editReservationResource_resourceId').val($('#{prefix}flb_resource_id').val());
      
      $('#{prefix}editReservationResource_id').val('');
      $('#{prefix}editReservationResource_payed').val('');
      $('#{prefix}editReservationResource_line_number').show();
      $('#{prefix}editReservationResource_number').html('{__label.calendar_editReservation_newTitle}');
      $('#{prefix}editReservationResource_form .reservation_detail').hide();

      $('#{prefix}editReservationResource_allowedPaymentHidden').val($('#{prefix}flb_commodity_allowedPayment').val());
      
      $('#{prefix}editReservationResource_from').val(start);
      $('#{prefix}editReservationResource_visualDateFrom').html(formatDateTime(start,'humanDate'));
      $('#{prefix}editReservationResource_visualTimeFrom').html(formatDateTime(start,'humanTime'));
      $('#{prefix}editReservationResource_inputTimeFrom').val(formatDateTime(start,'humanTime'));
      
      $('#{prefix}editReservationResource_to').val(end);
      $('#{prefix}editReservationResource_visualDateTo').html(formatDateTime(end,'humanDate'));
      $('#{prefix}editReservationResource_visualTimeTo').html(formatDateTime(end,'humanTime'));
      $('#{prefix}editReservationResource_inputTimeTo').val(formatDateTime(end,'humanTime'));
      
      if ({prefix}calAd) {
        $('#{prefix}editReservationResource_visualUser').hide();
        $('#{prefix}editReservationResource_userName').val('');
        $('#{prefix}editReservationResource_userName').show();
        $('#{prefix}editReservationResource_userId').val('');
        $('#{prefix}editReservationResource_userNameSelected').val('');
        $('#{prefix}editReservationResource_line_note').show();
        $('#{prefix}editReservationResource_note').val('');
        $('#{prefix}editReservationResource_note').attr('readonly',false);
        $('#{prefix}editReservationResource_mandatoryReservation').prop('checked', false);
        $('#{prefix}editReservationResource_line_mandatoryReservation').show();
      } else {
        $('#{prefix}editReservationResource_visualUser').show();
        $('#{prefix}editReservationResource_userName').hide();
        $('#{prefix}editReservationResource_visualUser').html($('#flb_core_username').val());
        $('#{prefix}editReservationResource_userId').val($('#flb_core_userid').val());
        $('#{prefix}editReservationResource_line_note').hide();
        $('#{prefix}editReservationResource_line_mandatoryReservation').hide();
      }

      reservationResourceGetPrice(moment(start).format('YYYY-MM-DD HH:mm:ss'), moment(end).format('YYYY-MM-DD HH:mm:ss'));
      
      getReservationAttribute('#{prefix}editReservationResource_attribute', null, $('#{prefix}editReservationResource_resourceId').val());
      
      $('#{prefix}editReservationResource_line_payment').hide();
      $('#{prefix}editReservationResource_line_failed').hide();

      reservationResourceDialog.dialog('open');
      $('#{prefix}cal_{id}').fullCalendar('unselect');
    },
    eventDrop: function(event,delta,revertFunc,jsEvent,ui,view) {
      if (reservationCheckOverlap('{prefix}cal_{id}',event.start,event.end,event._id,event.resourceId)) {
        alert('{__label.calendar_notAvailable}'); 
        revertFunc();
      } else {
        var params = { id: event.reservation_id, resource: event.resourceId, start: event.start, end: event.end };
        if (!reservationResourceSave(params)) {
          revertFunc();
        } else $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
      }
    },
    eventResize: function(event,delta,revertFunc,jsEvent,ui,view) {
      if (reservationCheckOverlap('{prefix}cal_{id}',event.start,event.end,event._id,event.resourceId)) {
        alert('{__label.calendar_notAvailable}'); 
        revertFunc();
      } else {
        var params = { id: event.reservation_id, resource: event.resourceId, start: event.start, end: event.end };
        if (!reservationResourceSave(params)) {
          revertFunc();
        } else $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
      }
    },
    eventClick: function(calEvent, jsEvent, view, resource) {
      if (!calEvent.isBackground) {
        if ((calEvent.type == 'reservation')&&
            ({prefix}calAd||!calEvent.failed)) {
          $('#{prefix}cal_{id}').css({'cursor' : 'wait'});

          var ajaxUrl = $('#flb_core_url').val()+'action=getReservation';
          var params = {params};
          if (params) ajaxUrl += '&'+$.param(params);
          $.ajax({
            type: 'GET',
            url: ajaxUrl,
            data: 'id='+calEvent.reservation_id+'&sessid='+$('#flb_core_sessionid').val(),
            dataType: 'json',
            success: function(data) {
              if (data.error) alert(data.message);
              else {
                $('#{prefix}editReservationResource_userName').hide();
                $('#{prefix}editReservationResource_visualUser').show();
                $('#{prefix}editReservationResource_inputTimeFrom').hide();
                $('#{prefix}editReservationResource_visualTimeFrom').show();
                $('#{prefix}editReservationResource_inputTimeTo').hide();
                $('#{prefix}editReservationResource_visualTimeTo').show();
                $('#{prefix}editReservationResource_line_resources').hide();
                $('#{prefix}editReservationResource_line_mandatoryReservation').hide();
        
                $('#{prefix}editReservationResource_id').val(calEvent.reservation_id);
                if (resource) $('#{prefix}editReservationResource_resourceId').val(resource.id);
                else $('#{prefix}editReservationResource_resourceId').val($('#{prefix}flb_resource_id').val());
                $('#{prefix}editReservationResource_payed').val(data.payed);
                $('#{prefix}editReservationResource_line_number').show();
                $('#{prefix}editReservationResource_number').html(data.number);
                $('#{prefix}editReservationResource_visualDateFrom').html(formatDateTime(data.fromRaw.replace(/-/g,'/'),'humanDate'));
                $('#{prefix}editReservationResource_visualTimeFrom').html(formatDateTime(data.fromRaw.replace(/-/g,'/'),'humanTime'));
                $('#{prefix}editReservationResource_visualDateTo').html(formatDateTime(data.toRaw.replace(/-/g,'/'),'humanDate'));
                $('#{prefix}editReservationResource_visualTimeTo').html(formatDateTime(data.toRaw.replace(/-/g,'/'),'humanTime'));
                $('#{prefix}editReservationResource_userId').val(data.userId);
                $('#{prefix}editReservationResource_visualUser').html(data.userName);
                $('#{prefix}editReservationResource_line_price').show();
                $('#{prefix}editReservationResource_price').html(data.price);
                $('#{prefix}editReservationResource_currency').html(data.currency);
                $('#{prefix}editReservationResource_priceHidden').val(data.priceRaw);
                $('#{prefix}editReservationResource_allowedPaymentHidden').val(data.allowedPayment);
                $('#{prefix}editReservationResource_line_payment').show();
                $('#{prefix}editReservationResource_line_failed').hide();
                if (data.payed) $('#{prefix}editReservationResource_visualPayed').html(formatDateTime(data.payed.replace(/-/g,'/'),'human')+' ('+data.payedBy+')');
                else $('#{prefix}editReservationResource_visualPayed').html('---');
                if ({prefix}calAd) {
                  $('#{prefix}editReservationResource_note').val(data.note);
                  $('#{prefix}editReservationResource_note').attr('readonly',true);
                  $('#{prefix}editReservationResource_line_note').show();
                  $('#{prefix}editReservationResource_form .reservation_detail').show();
                  if (data.failed) {
                    $('#{prefix}editReservationResource_line_failed').show();
                    $('#{prefix}editReservationResource_visualFailed').html(formatDateTime(data.failed.replace(/-/g, '/'), 'human'));
                  }
                } else {
                  $('#{prefix}editReservationResource_line_note').hide();
                  $('#{prefix}editReservationResource_form .reservation_detail').hide();
                }

                getReservationAttribute('#{prefix}editReservationResource_attribute',null,$('#{prefix}editReservationResource_resourceId').val(),calEvent.reservation_id,1);
                
                reservationResourceDialog.dialog('open');
              }
              $('#{prefix}cal_{id}').css({'cursor' : 'default'});
            },
            error: function(data) {
              $('#{prefix}cal_{id}').css({'cursor' : 'default'});

              alert('{__label.calendar_ajaxError}');
            }
          });
        } else if (calEvent.type == 'event') {
          $('#{prefix}cal_{id}').css({'cursor' : 'wait'});

          $.ajax({
            type: 'GET',
            url: $('#flb_core_url').val()+'action=getEvent',
            data: 'id='+calEvent.event_id+'&sessid='+$('#flb_core_sessionid').val(),
            dataType: 'json',
            success: function(data) {
              if (data.error) alert(data.message);
              else {
                $('#{prefix}editEvent_id').val(calEvent.event_id);
                $('#{prefix}editEvent_reserved').val(data.reserved);
                $('#{prefix}editEvent_reservationMaxAttendees').val(data.reservationMaxAttendees);
                $('#{prefix}editEvent_coAttendees').val(data.coAttendees);
                $('#{prefix}editEvent_name').html(data.name);
                $('#{prefix}editEvent_description').html(data.description);
                $('#{prefix}editEvent_organiser').val(data.organiserId);
                $('#{prefix}editEvent_organiserName').html(data.organiserName);
                $('#{prefix}editEvent_visualStart').html(data.start);
                $('#{prefix}editEvent_visualEnd').html(data.end);
                $('#{prefix}editEvent_visualMaxAttendees').html(data.maxAttendees+' ({__label.calendar_editEvent_free} '+data.free+')');
                $('#{prefix}editEvent_maxAttendees').val(data.maxAttendees);
                $('#{prefix}editEvent_repeatReservation').val(data.repeatReservation);
                $('#{prefix}editEvent_priceSingle').val(data.price);
                $('#{prefix}editEvent_pricePack').val(data.repeatPrice);
                $('#{prefix}editEvent_quickReservation').val(data.quickReservation);
                $('#{prefix}editEvent_allowedPayment').val(data.allowedPayment);
                $('#{prefix}editEvent_paymentNeeded').val(data.paymentNeeded?1:0);
                $('#{prefix}editEvent_free').val(data.free);
                $('#{prefix}editEvent_freeSubstitute').val(data.freeSubstitute);
                $('#{prefix}editEvent_price').html(data.priceHtml);
                $('#{prefix}editEvent_currency').html(data.currency);
                
                eventAttendeeRefresh(data,false);

                if (parseInt(data.maxAttendees)>0) {
                  $('#{prefix}editEvent_line_place').show();
                } else {
                  $('#{prefix}editEvent_line_place').hide();
                }
                
                if (($('#{prefix}editEvent_free').val()<=0)&&($('#{prefix}editEvent_freeSubstitute').val()>0)) {
                  $('#{prefix}editEvent_occupiedWarning').show();
                } else {
                  $('#{prefix}editEvent_occupiedWarning').hide();
                }
                
                if ({prefix}calAd) {
                  $('#{prefix}editEvent_form .event_detail').show();
                } else {
                  $('#{prefix}editEvent_form .event_detail').hide();
                }
                
                eventDialog.dialog('open');
              }

              $('#{prefix}cal_{id}').css({'cursor' : 'default'});
            },
            error: function(data) {
              $('#{prefix}cal_{id}').css({'cursor' : 'default'});

              alert('{__label.calendar_ajaxError}');
            }
          });
        }
      }
    },
    viewRender: function(view,element) {
      {parsePrevButton}
      {parseNextButton}
    },
    eventRender: function (event, element) {
      if (event.poolCapacity) {
        //element.addClass('poolCapacity');
        element.css("opacity", "0.75");
        element.append('<div class="title">'+event.title+'</div>');
      }
    }
  });

  var reservationResourceDialog = $('#{prefix}editReservationResource_form').dialog({
    autoOpen: false,
    height: 'auto',
    width: 550,
    modal: true,
    buttons: { },
    open: function(event,ui) {
        $(this).dialog( "option", "position", { my: "center", at: "center", of: window });
        
        if ({prefix}calAd) $('#{prefix}editReservationResource_userName').focus();
        
        var userData = getUserDetail({ user: $('#{prefix}editReservationResource_userId').val(), resource: $('#{prefix}editReservationResource_resourceId').val(), price: $('#{prefix}editReservationResource_priceHidden').val() });
        reservationResourceButtonRefresh(userData);
    },
    close: function(event,ui) {
      $('#{prefix}editReservationResource_inputTimeFrom').datetimepicker('destroy');
      $('#{prefix}editReservationResource_inputTimeTo').datetimepicker('destroy');
    }
  });

  $('#{prefix}editReservationResource_places').change(function() {
    var start = $('#{prefix}editReservationResource_from').val();
    var end = $('#{prefix}editReservationResource_to').val();
    //Thu Feb 14 2019 01:00:00 GMT+0100
    reservationResourceGetPrice(moment(start, 'ddd MMM DD YYYY HH:mm:ss ZZ').format('YYYY-MM-DD HH:mm:ss'), moment(end, 'ddd MMM DD YYYY HH:mm:ss ZZ').format('YYYY-MM-DD HH:mm:ss'));

    var userData = getUserDetail({ user: $('#{prefix}editReservationResource_userId').val(), resource: $('#{prefix}editReservationResource_resourceId').val(), price: $('#{prefix}editReservationResource_priceHidden').val() });
    reservationResourceButtonRefresh(userData);
  });
  
  function reservationResourceButtonRefresh(userData) {
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_save}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_savePayCash}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_savePayCredit}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_saveChargePayCredit}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_savePayTicket}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_payCash}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_payCredit}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_chargePayCredit}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_payTicket}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_cancel}');
    $('#{prefix}editReservationResource_form').dialog('removebutton', '{__button.calendar_editReservation_cancelRefund}');
    {removeResourcePaymentGatewayButton}

    $('#{prefix}editReservationResource_skipReservationCondition').prop('checked', false);
    $('#{prefix}editReservationResource_line_skipReservationCondition').hide();

    var reservationPrice = $('#{prefix}editReservationResource_priceHidden').val();
    var allowedPaymentCredit = 1&$('#{prefix}editReservationResource_allowedPaymentHidden').val();
    var allowedPaymentTicket = 10&$('#{prefix}editReservationResource_allowedPaymentHidden').val();
    var allowedPaymentOnline = 100&$('#{prefix}editReservationResource_allowedPaymentHidden').val();

    if ($('#{prefix}editReservationResource_id').val()) {
      $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_cancel}', '{prefix}editReservationResourceButtonCancel', '', function() {
        var confirmMsg;
        if ($('#{prefix}editReservationResource_payed').val()) confirmMsg = '{__label.ajax_reservation_confirmCancelRefund}';
        else confirmMsg = '{__label.ajax_reservation_confirmCancel}';
        if (confirm(confirmMsg)) {
          var params = { id: $('#{prefix}editReservationResource_id').val() };
          if ($('#{prefix}editReservationResource_payed').val()) params.refund = 'Y';
          if (reservationCancel(params)) {
            $('#{prefix}editReservationResource_form').dialog('close');

            $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
          }
        }
      });
      if (!$('#{prefix}editReservationResource_payed').val()&&(parseFloat(reservationPrice)>0)) {
        if (allowedPaymentCash&&{prefix}calAd) {
          $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_payCash}', '{prefix}editReservationResourceButtonPayCash', '', function () {
            message = '{__label.calendar_editReservation_confirmSavePayCash}';
            message = message.replace('{amount}',flbFormatNumber($('#{prefix}editReservationResource_priceHidden').val(),'human'));
            if (confirm(message)) {
              var params = {
                id: $('#{prefix}editReservationResource_id').val(),
                pay: 'Y',
                payArrangeCredit: 'Y',
                payArrangeCreditAmount: parseFloat(flbFormatNumber($('#{prefix}editReservationResource_priceHidden').val())),
                payType: 'credit'
              };
              if (reservationPay(params)) {
                $('#{prefix}editReservationResource_form').dialog('close');

                $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
              }
            }
          });
        }
        if (allowedPaymentCredit) {
          if (userData.credit&&(parseFloat(userData.credit)>parseFloat(reservationPrice))) {
            $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_payCredit}', '{prefix}editReservationResourceButtonPayCredit', '', function() {
              if (confirm('{__label.calendar_editReservation_confirmSavePay}')) {
                var params = { id: $('#{prefix}editReservationResource_id').val() };
                if (reservationPay(params)) {
                    $('#{prefix}editReservationResource_form').dialog('close');

                    $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
                }
              }
            });
          } else if (userData.credit&&(userData.credit>0)&&{prefix}calAd) {
            $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_chargePayCredit}', '{prefix}editReservationResourceButtonChargePayCredit', '', function() {
              if (userData.id) {
                message = '{__label.calendar_editReservation_confirmSaveChargePay}';
                message = message.replace('{credit}',flbFormatNumber(userData.credit,'human'));
                var diff = parseFloat(flbFormatNumber($('#{prefix}editReservationResource_priceHidden').val())) - parseFloat(userData.credit);
                message = message.replace('{creditMissing}',flbFormatNumber(diff.toString(),'human'));
              } else message = '{__label.calendar_editReservation_confirmSavePay}';
              if (confirm(message)) {
                var params = { id: $('#{prefix}editReservationResource_id').val(),
                               pay: 'Y', payArrangeCredit: 'Y', payType: 'credit' };
                if (reservationPay(params)) {
                    $('#{prefix}editReservationResource_form').dialog('close');

                    $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
                }
              }
            });
          }
        }
        if (allowedPaymentTicket&&userData.ticket&&userData.ticket.length>0) {
          $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_payTicket}', '{prefix}editReservationResourceButtonPayTicket', '', function() {
            if (!$('#{prefix}editReservationResource_ticket').val()) {
              alert('{__label.calendar.editReservation_noTicket}');
              return;
            }
            if (confirm('{__label.calendar_editReservation_confirmSavePayTicket}')) {
              var params = { id: $('#{prefix}editReservationResource_id').val(), 
                             pay: 'Y', payType: 'ticket', payTicket: $('#{prefix}editReservationResource_ticket').val() };
              if (id=reservationPay(params)) {
                $('#{prefix}editReservationResource_form').dialog('close');
                
                $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
              }
            }
          });
          
          var ticketCombo = $('#{prefix}editReservationResource_ticket').html('');
          ticketCombo.append('<option value=\"\">{__label.select_choose}</option>');
          $.each(userData.ticket, function(index,element) {
            ticketCombo.append('<option value=\"'+element.id+'\">'+element.name+' / '+element.value+' '+element.currency+'</option>');
          });
          $('#{prefix}editReservationResource_line_ticket').show();
        } else $('#{prefix}editReservationResource_line_ticket').hide();

        if (allowedPaymentOnline) {
          {addResourcePaymentGatewayPayButton}
        }
      } else $('#{prefix}editReservationResource_line_ticket').hide(); 
    } else {
      if (parseInt($('#{prefix}editReservationResource_paymentNeeded').val())==0) {
        $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_save}', '{prefix}editReservationResourceButtonSave', 'flb_primaryButton', function() {
          var params = { id: $('#{prefix}editReservationResource_id').val(), user: $('#{prefix}editReservationResource_userId').val() };
          if (!$('#{prefix}editReservationResource_id').val()) {
            params.resource = getResourceForReservation('{prefix}');
            params.start = $('#{prefix}editReservationResource_from').val();
            params.end = $('#{prefix}editReservationResource_to').val();
            params.attribute = reservationAttributePrepare('#{prefix}editReservationResource_form');
            if ({prefix}calAd) params.note = $('#{prefix}editReservationResource_note').val();
          }
          if ({prefix}calAd&&($('#{prefix}editReservationResource_userNameSelected').val()!=$('#{prefix}editReservationResource_userName').val())) {
            alert('{__label.calendar.editReservation_unknownUser}');
          } else if (reservationResourceSave(params)) {
            $('#{prefix}editReservationResource_form').dialog('close');

            $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
          }
        });
      }
      if (parseFloat(reservationPrice)>0) {
        if (allowedPaymentCash&&{prefix}calAd) {
          $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_savePayCash}', '{prefix}editReservationResourceButtonSavePayCash', 'flb_primaryButton', function () {
            message = '{__label.calendar_editReservation_confirmSavePayCash}';
            message = message.replace('{amount}',flbFormatNumber($('#{prefix}editReservationResource_priceHidden').val(),'human'));
            if (confirm(message)) {
              var params = {
                user: $('#{prefix}editReservationResource_userId').val(),
                resource: getResourceForReservation('{prefix}'),
                start: $('#{prefix}editReservationResource_from').val(),
                end: $('#{prefix}editReservationResource_to').val(),
                pay: 'Y',
                payArrangeCredit: 'Y',
                payArrangeCreditAmount: parseFloat(flbFormatNumber($('#{prefix}editReservationResource_priceHidden').val())),
                payType: 'credit'
              };
              params.attribute = reservationAttributePrepare('#{prefix}editReservationResource_form');
              if (reservationResourceSave(params)) {
                $('#{prefix}editReservationResource_form').dialog('close');

                $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
              }
            }
          });
        }
        if (allowedPaymentCredit) {
          if (userData.credit&&(parseFloat(userData.credit)>parseFloat(reservationPrice))) {
            $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_savePayCredit}', '{prefix}editReservationResourceButtonSavePayCredit', 'flb_primaryButton', function() {
              if ({prefix}calAd&&($('#{prefix}editReservationResource_userNameSelected').val()!=$('#{prefix}editReservationResource_userName').val())) {
                alert('{__label.calendar.editReservation_unknownUser}');
              } else if (confirm('{__label.calendar_editReservation_confirmSavePay}')) {
                var params = { user: $('#{prefix}editReservationResource_userId').val(), resource: getResourceForReservation('{prefix}'),
                               start: $('#{prefix}editReservationResource_from').val(), end: $('#{prefix}editReservationResource_to').val(),
                               pay: 'Y', payType: 'credit' };
                params.attribute = reservationAttributePrepare('#{prefix}editReservationResource_form');
                if (reservationResourceSave(params)) {
                  $('#{prefix}editReservationResource_form').dialog('close');

                  $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
                }
              }
            });
          } else if (userData.credit&&(userData.credit>0)&&{prefix}calAd) {
            $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_saveChargePayCredit}', '{prefix}editReservationResourceButtonSaveChargePayCredit', 'flb_primaryButton', function() {
              if ({prefix}calAd&&($('#{prefix}editReservationResource_userNameSelected').val()!=$('#{prefix}editReservationResource_userName').val())) {
                alert('{__label.calendar.editReservation_unknownUser}');
              } else {
                if (userData.id) {
                  message = '{__label.calendar_editReservation_confirmSaveChargePay}';
                  message = message.replace('{credit}',flbFormatNumber(userData.credit,'human'));
                  var diff = parseFloat(flbFormatNumber($('#{prefix}editReservationResource_priceHidden').val())) - parseFloat(userData.credit);
                  message = message.replace('{creditMissing}',flbFormatNumber(diff.toString(),'human'));
                } else message = '{__label.calendar_editReservation_confirmSavePay}';
                if (confirm(message)) {
                  var params = { user: $('#{prefix}editReservationResource_userId').val(), resource: getResourceForReservation('{prefix}'),
                                 start: $('#{prefix}editReservationResource_from').val(), end: $('#{prefix}editReservationResource_to').val(),
                                 pay: 'Y', payArrangeCredit: 'Y', payType: 'credit' };
                  params.attribute = reservationAttributePrepare('#{prefix}editReservationResource_form');
                  if (reservationResourceSave(params)) {
                    $('#{prefix}editReservationResource_form').dialog('close');

                    $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
                  }
                }
              }
            });
          }
        }
        if (allowedPaymentTicket&&userData.ticket&&userData.ticket.length>0) {
          $('#{prefix}editReservationResource_form').dialog('addbutton', '{__button.calendar_editReservation_savePayTicket}', '{prefix}editReservationResourceButtonSavePayTicket', 'flb_primaryButton', function() {
            if (!$('#{prefix}editReservationResource_ticket').val()) {
              alert('{__label.calendar.editReservation_noTicket}');
              return;
            }
            if ({prefix}calAd&&($('#{prefix}editReservationResource_userNameSelected').val()!=$('#{prefix}editReservationResource_userName').val())) {
              alert('{__label.calendar.editReservation_unknownUser}');
            } else if (confirm('{__label.calendar_editReservation_confirmSavePayTicket}')) {
              var params = { user: $('#{prefix}editReservationResource_userId').val(), resource: getResourceForReservation('{prefix}'),
                             start: $('#{prefix}editReservationResource_from').val(), end: $('#{prefix}editReservationResource_to').val(),
                             pay: 'Y', payType: 'ticket', payTicket: $('#{prefix}editReservationResource_ticket').val() };
              params.attribute = reservationAttributePrepare('#{prefix}editReservationResource_form');
              if (reservationResourceSave(params)) {
                $('#{prefix}editReservationResource_form').dialog('close');

                $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
              }
            }
          });

          var ticketCombo = $('#{prefix}editReservationResource_ticket').html('');
          ticketCombo.append('<option value=\"\">{__label.select_choose}</option>');
          $.each(userData.ticket, function(index,element) {
            ticketCombo.append('<option value=\"'+element.id+'\">'+element.name+' / '+element.value+' '+element.currency+'</option>');
          });
          $('#{prefix}editReservationResource_line_ticket').show();
        } else $('#{prefix}editReservationResource_line_ticket').hide();

        if (allowedPaymentOnline) {
          {addResourcePaymentGatewaySavePayButton}
        }
      } else {
        $('#{prefix}editReservationResource_line_ticket').hide();
      }
    }
  }

  function prepareEventReservationUserSelect() {
    var userData;

    if ({prefix}calAd||({prefix}orgSR&&($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val()))) {
      var urlSuffix = '';
      if (!{prefix}calAd) urlSuffix = '{organiserCanReserveOnBehalf}';

      userData = getUserDetail({ user: null });
      $('#{prefix}editReservationEvent_attendees').html('');
      eventInputAttendeesRefresh(null);

      $('#{prefix}editReservationEvent_userName').click(function () { $(this).select(); })
      $('#{prefix}editReservationEvent_userName').combogrid({
        url: $('#flb_core_url').val()+'action=getUser&sessid={%sessid%}'+urlSuffix,
        debug: true,
        //replaceNull: true,
        colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
          {'columnName':'firstname','width':'30','label':'{__label.calendar_editUser_firstname}','align':'left'},
          {'columnName':'lastname','width':'30','label':'{__label.calendar_editUser_lastname}','align':'left'},
          {'columnName':'{organiserCanReserveOnBehalfCustomColumnValue}','width':'40','label':'{organiserCanReserveOnBehalfCustomColumnTitle}','align':'left'}],
        select: function(event,ui) {
          $('#{prefix}editReservationEvent_userName').val(ui.item.name);
          $('#{prefix}editReservationEvent_userId').val(ui.item.id);
          $('#{prefix}editReservationEvent_userNameSelected').val(ui.item.name);

          $('#{prefix}editReservationEvent_userName').blur();

          $('#{prefix}editReservationEvent_attendee_1 [meaning=firstname]').each(function() { $(this).val(ui.item.firstname); });
          $('#{prefix}editReservationEvent_attendee_1 [meaning=lastname]').each(function() { $(this).val(ui.item.lastname); });
          $('#{prefix}editReservationEvent_attendee_1 [meaning=email]').each(function() { $(this).val(ui.item.email); });

          var userData = getUserDetail({ user: $('#{prefix}editReservationEvent_userId').val(), event: $('#{prefix}editReservationEvent_event').val(), price: $('#{prefix}editReservationEvent_visualPrice').html() });
          if (!userData) $('#{prefix}editReservationEvent_userName').val('');

          eventInputAttendeesRefresh(userData);
          reservationEventButtonRefresh(userData);

          return false;
        }
      });
      $('#{prefix}editReservationEvent_userName').change(function () {
        if ($(this).val()=='') {
          $('#{prefix}editReservationEvent_userName').val('');
          $('#{prefix}editReservationEvent_userId').val('');
          $('#{prefix}editReservationEvent_userNameSelected').val('');

          var userData = getUserDetail({ user: $('#{prefix}editReservationEvent_userId').val(), event: $('#{prefix}editReservationEvent_event').val(), price: $('#{prefix}editReservationEvent_visualPrice').html() });

          reservationEventButtonRefresh(userData);
        }
      });

      $('#{prefix}editReservationEvent_visualUser').hide();
      $('#{prefix}editReservationEvent_userName').val('');
      $('#{prefix}editReservationEvent_userName').show();
      $('#{prefix}editReservationEvent_userId').val('');
    } else {
      $('#{prefix}editReservationEvent_visualUser').html($('#flb_core_username').val());
      $('#{prefix}editReservationEvent_visualUser').show();
      $('#{prefix}editReservationEvent_userName').hide();
      $('#{prefix}editReservationEvent_visualUser').html($('#flb_core_username').val());
      $('#{prefix}editReservationEvent_userId').val($('#flb_core_userid').val());

      userData = getUserDetail({ user: $('#{prefix}editReservationEvent_userId').val(), event: $('#{prefix}editReservationEvent_event').val(), price: $('#{prefix}editReservationEvent_visualPrice').html() });
      $('#{prefix}editReservationEvent_attendees').html('');
      eventInputAttendeesRefresh(userData);
    }

    $('#{prefix}editReservationEvent_userNameSelected').val('');

    if (!{prefix}calAd&&($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val())) {
      $('#{prefix}editReservationEvent_anonymousUser').show();
    } else {
      $('#{prefix}editReservationEvent_anonymousUser').hide();
    }

    return userData;
  }
  
  var eventDialog = $('#{prefix}editEvent_form').dialog({
    autoOpen: false,
    width: 'auto',
    modal: true,
    open: function(event,ui) { eventButtonRefresh(); },
    buttons: { }
  });

  $('#{prefix}editReservationEvent_form').on('focus', '#{prefix}editReservationEvent_userName', function() {
    $('#{prefix}editReservationEvent_userName').trigger($.Event("keydown", {keyCode: 16}));
  });
  
  function eventButtonRefresh() {
    var feSubstituteButtonLabel;
    if ({prefix}calAd) feSubstituteButtonLabel = '{__button.calendar_editEvent_newSubstitute}';
    else feSubstituteButtonLabel = '{__button.calendar_editEvent_newSubstitute_fe}';

    eventDialog.dialog('removebutton', '{__button.calendar_editEvent_newReservation}');
    eventDialog.dialog('removebutton', '{__button.calendar_editEvent_newSubstitute}');
    eventDialog.dialog('removebutton', '{__button.calendar_editEvent_newSubstitute_fe}');

    // tlacitko 'Vytvorit rezervaci'
    if ($('#{prefix}editEvent_free').val()>0) {
      eventDialog.dialog('addbutton', '{__button.calendar_editEvent_newReservation}', '{prefix}editEventReservation', 'flb_primaryButton', function() {
        if ($('#flb_core_userid').val()) {
          $('#{prefix}editReservationEvent_id').val('');
          $('#{prefix}editReservationEvent_substitute').val('N');
          $('#{prefix}editReservationEvent_visualEvent').html($('#{prefix}editEvent_name').html());
          $('#{prefix}editReservationEvent_event').val($('#{prefix}editEvent_id').val());

          $('#{prefix}editReservationEvent_places').html('');
          for (var i=1;(i<=$('#{prefix}editEvent_reservationMaxAttendees').val())&&(i<=$('#{prefix}editEvent_free').val());i++) {
            $('#{prefix}editReservationEvent_places').append('<option value=\"'+i+'\">'+i+'</option>');
          }
          $('#{prefix}editReservationEvent_places').val('1');

          var userData = prepareEventReservationUserSelect();
          
          $('#{prefix}editReservationEvent_pack').val('N');
          if ($('#{prefix}editEvent_repeatReservation').val()=='BOTH') $('#{prefix}editReservationEvent_line_pack').show();
          else $('#{prefix}editReservationEvent_line_pack').hide();
          if  ($('#{prefix}editEvent_repeatReservation').val()=='PACK') $('#{prefix}editReservationEvent_pack').val('Y');
          
          getReservationAttribute('#{prefix}editReservationEvent_attribute', $('#{prefix}editEvent_id').val(), null);

          if ({prefix}calAd) {
            $('#{prefix}editReservationEvent_line_note').show();
            $('#{prefix}editReservationEvent_note').val('');
            $('#{prefix}editReservationEvent_note').attr('readonly',false);
            $('#{prefix}editReservationEvent_mandatoryReservation').prop('checked', false);
            $('#{prefix}editReservationEvent_line_mandatoryReservation').show();
          } else {
            $('#{prefix}editReservationEvent_line_note').hide();
            if ($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val()) {
              if ({prefix}orgMR) {
                $('#{prefix}editReservationEvent_mandatoryReservation').prop('checked', true);
                $('#{prefix}editReservationEvent_line_mandatoryReservation').hide();
              } else {
                $('#{prefix}editReservationEvent_mandatoryReservation').prop('checked', false);
                $('#{prefix}editReservationEvent_line_mandatoryReservation').show();
              }
            } else {
              $('#{prefix}editReservationEvent_line_mandatoryReservation').hide();
              $('#{prefix}editReservationEvent_mandatoryReservation').prop('checked', false);
            }
          }

          // jestli se maji schovat ucastnici
          if ({prefix}orgHA&&($('#{prefix}editEvent_reservationMaxAttendees').val()==1)&&($('#{prefix}editEvent_coAttendees').val()==1)) {
            $('#{prefix}editReservationEvent_line_attendee').hide();
          } else {
            $('#{prefix}editReservationEvent_line_attendee').show();
          }
          
          calculateEventPrice();

          reservationEventButtonRefresh(userData);

          // quick reservation bude fungovat jenom ve frontendu pro normalni uzivatele
          if (!{prefix}calAd&&($('#flb_core_userid').val()!=$('#{prefix}editEvent_organiser').val())&&($('#{prefix}editEvent_quickReservation').val()=='Y')) {
            var params = { id: $('#{prefix}editReservationEvent_id').val(), user: $('#{prefix}editReservationEvent_userId').val(),
              places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val(), pack: $('#{prefix}editReservationEvent_pack').val() };
            reservationEventSave(params);
          } else {
            $('#{prefix}editReservationEvent_number').html('{__label.calendar_editReservation_newTitle}');

            reservationEventDialog.dialog('open');
            if ($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val()) {
              $('#{prefix}editReservationEvent_userName').focus();
            }
          }
        } else {
          eventDialog.dialog('close');

          flbLoginRequired('{language}');
        }
      });
    }
    // tlacitko pro vytvoreni nahradnika vidi uzivatel backoffice, organizator akce vzdycky, bezny uzivatel pouze kdyz jsou klasicka mista obsazena
    if (({prefix}calAd||($('#{prefix}editEvent_organiser').val() == $('#flb_core_userid').val())||($('#{prefix}editEvent_free').val()==0))&&($('#{prefix}editEvent_freeSubstitute').val()>0)) {
      eventDialog.dialog('addbutton', feSubstituteButtonLabel, '{prefix}editEventSubstitute', 'flb_primaryButton', function() {
        if ($('#flb_core_userid').val()) {
          $('#{prefix}editReservationEvent_id').val('');
          $('#{prefix}editReservationEvent_substitute').val('Y');
          $('#{prefix}editReservationEvent_visualEvent').html($('#{prefix}editEvent_name').html());
          $('#{prefix}editReservationEvent_event').val($('#{prefix}editEvent_id').val());

          $('#{prefix}editReservationEvent_places').html('');
          for (var i=1;i<=$('#{prefix}editEvent_freeSubstitute').val();i++) {
            $('#{prefix}editReservationEvent_places').append('<option value=\"'+i+'\">'+i+'</option>');
          }
          $('#{prefix}editReservationEvent_places').val('1');

          var userData = prepareEventReservationUserSelect();
          $('#{prefix}editReservationEvent_anonymousUser').hide();

          $('#{prefix}editReservationEvent_pack').val('N');
          $('#{prefix}editReservationEvent_line_pack').hide();
          $('#{prefix}editReservationEvent_line_ticket').hide();
          
          getReservationAttribute('#{prefix}editReservationEvent_attribute', $('#{prefix}editEvent_id').val(), null);

          var userData = getUserDetail({ user: $('#{prefix}editReservationEvent_userId').val(), event: $('#{prefix}editReservationEvent_event').val(), price: $('#{prefix}editReservationEvent_visualPrice').html() });
          $('#{prefix}editReservationEvent_attendees').html('');
          eventInputAttendeesRefresh(userData);

          $('#{prefix}editReservationEvent_mandatoryReservation').prop('checked', false);
          $('#{prefix}editReservationEvent_line_mandatoryReservation').hide();
          
          calculateEventPrice();

          reservationEventButtonRefresh(userData);

          // quick reservation bude fungovat jenom ve frontendu pro normalni uzivatele
          if (!{prefix}calAd&&($('#flb_core_userid').val()!=$('#{prefix}editEvent_organiser').val())&&($('#{prefix}editEvent_quickReservation').val()=='Y')) {
            var params = { user: $('#{prefix}editReservationEvent_userId').val(),
              places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val() };
            substituteEventSave(params);
          } else {
            $('#{prefix}editReservationEvent_number').html('{__label.calendar_editReservation_substituteTitle}');

            reservationEventDialog.dialog('open');
            if ($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val()) {
              $('#{prefix}editReservationEvent_userName').focus();
            }
          }
        } else {
          eventDialog.dialog('close');

          flbLoginRequired('{language}');
        }
      });
    }
  }
  
  $('#{prefix}editEvent_form').on('click','.btn_substituteSwap', function() {
    if (confirm('{__label.calendar_editEvent_substituteSwapConfirm}')) {
      var idExt = $(this).attr('id');
      idExt = idExt.replace('{prefix}substituteSwap_','');
      $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=swapSubstitute',
        data: 'event='+$('#{prefix}editEvent_id').val()+'&substitute='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
        dataType: 'json',
        success: function(data) {
          if (data.error) alert(data.message);
          if (data.popup) {
            alert(data.popup);
            
            eventAttendeeRefresh(false,true);
          }
        },
        error: function(data) {
          alert('{__label.calendar_ajaxError}'); return;
        }
      });
    }
  });
  
  $('#{prefix}editEvent_form').on('click','.btn_reservationSwap', function() {
    if (confirm('{__label.calendar_editEvent_attendeeSwapConfirm}')) {
      var idExt = $(this).attr('id');
      idExt = idExt.replace('{prefix}substituteSwap_','');
      $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=swapSubstitute',
        data: 'event='+$('#{prefix}editEvent_id').val()+'&reservation='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
        dataType: 'json',
        success: function(data) {
          if (data.error) alert(data.message);
          if (data.popup) {
            alert(data.popup);
            
            eventAttendeeRefresh(false,true);
          }
        },
        error: function(data) {
          alert('{__label.calendar_ajaxError}'); return;
        }
      });
    }
  });

  $('#{prefix}editEvent_form').on('click','.btn_attendeeFail', function() {
    if (confirm('{__label.calendar_editEvent_attendeeFailConfirm}')) {
      var idExt = $(this).attr('id');
      idExt = idExt.replace('{prefix}attendeeFail_','');
      $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=failReservationEventPackItem',
        data: 'event='+$('#{prefix}editEvent_id').val()+'&reservation='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
        dataType: 'json',
        success: function(data) {
          if (data.error) alert(data.message);
          if (data.popup) alert(data.popup)

          eventAttendeeRefresh(false,true);
        },
        error: function(data) {
          alert('{__label.calendar_ajaxError}'); return;
        }
      });
    }
  });

  $('#{prefix}editEvent_form').on('click','.btn_attendeeCancel', function() {
    if (confirm('{__label.calendar_editEvent_attendeeCancelConfirm}')) {
      var idExt = $(this).attr('id');
      idExt = idExt.replace('{prefix}attendeeCancel_','');
      $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=cancelReservationEventPackItem',
        data: 'event='+$('#{prefix}editEvent_id').val()+'&reservation='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
        dataType: 'json',
        success: function(data) {
          if (data.error) alert(data.message);
          if (data.popup) alert(data.popup)

          eventAttendeeRefresh(false,true);
        },
        error: function(data) {
          alert('{__label.calendar_ajaxError}'); return;
        }
      });
    }
  });

  $('#{prefix}editEvent_form').on('click','.btn_substituteCancel', function() {
    if (confirm('{__label.calendar_editEvent_substituteCancelConfirm}')) {
      var idExt = $(this).attr('id');
      idExt = idExt.replace('{prefix}substituteCancel_','');
      $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=cancelSubstitute',
        data: 'id='+idExt+'&sessid='+$('#flb_core_sessionid').val(),
        dataType: 'json',
        success: function(data) {
          if (data.error) alert(data.message);
          if (data.popup) alert(data.popup)

          eventAttendeeRefresh(false,true);
        },
        error: function(data) {
          alert('{__label.calendar_ajaxError}'); return;
        }
      });
    }
  });
  
  $('#{prefix}editEvent_form').on('click','.btn_eventDetail', function() {
    if ({prefix}calAd) {
      var url = $('#flb_core_url_path').val()+'index.php?action=eEventEdit&id='+$('#{prefix}editEvent_id').val()+'&sessid='+$('#flb_core_sessionid').val();
      window.location.replace(url);
    }
  });
  
  $('#{prefix}editReservationResource_form').on('click','.btn_reservationDetail', function() {
    if ({prefix}calAd) {
      var url = $('#flb_core_url_path').val()+'index.php?action=eReservationEdit&id='+$('#{prefix}editReservationResource_id').val()+'&sessid='+$('#flb_core_sessionid').val();
      window.location.replace(url);
    }
  });

  function eventAttendeeRefresh(attData,refreshButton) {
    if (!attData) {
      $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=getEvent',
        data: 'id='+$('#{prefix}editEvent_id').val()+'&sessid='+$('#flb_core_sessionid').val(),
        dataType: 'json',
        async: false,
        success: function(data) {
          $('#{prefix}editEvent_visualMaxAttendees').html(data.maxAttendees+' ({__label.calendar_editEvent_free} '+data.free+')');
          $('#{prefix}editEvent_free').val(data.free);
          $('#{prefix}editEvent_freeSubstitute').val(data.freeSubstitute);

          if ((parseInt(data.free)==0)&&(parseInt(data.freeSubstitute)>0)) $('#{prefix}editEvent_occupiedWarning').show();
          else $('#{prefix}editEvent_occupiedWarning').hide();

          attData = data;
        },
        error: function(data) {
          alert('{__label.calendar_ajaxError}'); return;
        }
      });
    }

    if (attData.attendee.length) {
      $('#{prefix}editEvent_line_attendee').show();

      $('#{prefix}editEvent_attendee').html('');
      // sluceni ucastniku podle rezervace kvuli prohazovani rezervace/nahradnik
      var attendee = {};
      $.each(attData.attendee, function (index, el) {
        if (attendee[el.id]) attendee[el.id]['name'] += ',' + el.name;
        else {
          attendee[el.id] = {};
          attendee[el.id]['user'] = el.user;
          attendee[el.id]['name'] = el.name;
          attendee[el.id]['places'] = el.places;
          attendee[el.id]['reservation'] = el.reservation;
          attendee[el.id]['failed'] = el.failed;
          attendee[el.id]['payed'] = el.payed;
          attendee[el.id]['eventPack'] = el.eventPack;
        }
      });
      $.each(attendee, function (id, el) {
        var failedClass = '';
        // jestli je rezervace propadla vidi pouze organizator
        if (($('#{prefix}editEvent_organiser').val() == $('#flb_core_userid').val())&&el.failed) failedClass = ' attendeeFailed';
        var html = '<div class="attendeeLine" id="' + id + '"><div class="attendee' + failedClass + '">' + el.name + '</div><div class="attendeeSwap">';
        // prohrazovat lze z backoffice nebo organizatorem
        if ({prefix}calAd||($('#{prefix}editEvent_organiser').val()==$('#flb_core_userid').val())) {
          if (!el.failed) {
            // aby sla prohodit rezervaci na nahradnika nesmi byt na cely cyklus opakovani
            if (el.places <= $('#{prefix}editEvent_freeSubstitute').val()
                && el.user && !el.payed
                && (el.eventPack!='Y')) html += '<input id="{prefix}substituteSwap_' + el.reservation + '" class="btn_reservationSwap btn_small" title="{__label.calendar_editEvent_attendeeSwapTitle}" type="button" value="v"/>';
            html += '<input id="{prefix}attendeeFail_' + el.reservation + '" class="btn_attendeeFail btn_small" title="{__label.calendar_editEvent_attendeeFailTitle}" type="button" value="-"/>';
            if (!el.payed) html += '<input id="{prefix}attendeeCancel_' + el.reservation + '" class="btn_attendeeCancel btn_small" title="{__label.calendar_editEvent_attendeeCancelTitle}" type="button" value="x"/>';
          }
        }
        html += '</div></div>';

        $('#{prefix}editEvent_attendee').append(html);
      });
    } else {
      $('#{prefix}editEvent_line_attendee').hide();
    }
    
    if (attData.substitute.length) {
      $('#{prefix}editEvent_line_substitute').show();
      
      $('#{prefix}editEvent_substitute').html('');
      // sluceni nahradniku podle rezervace kvuli prohazovani rezervace/nahradnik
      var substitute = {};
      $.each(attData.substitute, function(index,el) {
        if (substitute[el.id]) substitute[el.id]['name'] += ','+el.name;
        else {
          substitute[el.id] = {};
          substitute[el.id]['name'] = el.name;
          substitute[el.id]['places'] = el.places;
        }
      });
      $.each(substitute, function(id,el) {
        var html = '<div class="attendeeLine" id="'+id+'"><div class="attendee">'+el.name+'</div><div class="attendeeSwap">';
        // prohrazovat lze z backoffice nebo organizatorem
        if ({prefix}calAd||($('#{prefix}editEvent_organiser').val()==$('#flb_core_userid').val())) {
          if (el.places<=$('#{prefix}editEvent_free').val()) html += '<input id="{prefix}substituteSwap_'+id+'" class="btn_substituteSwap btn_small" title="{__label.calendar_editEvent_substituteSwapTitle}" type="button" value="^"/>';
          html += '<input id="{prefix}substituteCancel_' + id + '" class="btn_substituteCancel btn_small" title="{__label.calendar_editEvent_substituteCancelTitle}" type="button" value="x"/>';
        }
        html += '</div></div>';
        
        $('#{prefix}editEvent_substitute').append(html);
      });
    } else $('#{prefix}editEvent_line_substitute').hide();
    
    if (refreshButton) eventButtonRefresh();
  }
  
  var reservationEventDialog = $('#{prefix}editReservationEvent_form').dialog({
    autoOpen: false,
    width: 'auto',
    height: 'auto',
    modal: true,
    buttons: { }
  });
  
  function reservationEventButtonRefresh(userData) {
    $('#{prefix}editReservationEvent_form').dialog('removebutton', '{__button.calendar_editReservation_saveSubstitute}');
    $('#{prefix}editReservationEvent_form').dialog('removebutton', '{__button.calendar_editReservation_save}');
    $('#{prefix}editReservationEvent_form').dialog('removebutton', '{__button.calendar_editReservation_savePayCash}');
    $('#{prefix}editReservationEvent_form').dialog('removebutton', '{__button.calendar_editReservation_savePayCredit}');
    $('#{prefix}editReservationEvent_form').dialog('removebutton', '{__button.calendar_editReservation_saveChargePayCredit}');
    $('#{prefix}editReservationEvent_form').dialog('removebutton', '{__button.calendar_editReservation_savePayTicket}');
    {removeEventPaymentGatewayButton}

    $('#{prefix}editReservationEvent_skipReservationCondition').prop('checked', false);
    $('#{prefix}editReservationEvent_line_skipReservationCondition').hide();

    var reservationPrice = $('#{prefix}editReservationEvent_visualPrice').html();

    var allowedPaymentCredit = 1&$('#{prefix}editEvent_allowedPayment').val();
    var allowedPaymentTicket = 10&$('#{prefix}editEvent_allowedPayment').val();
    var allowedPaymentOnline = 100&$('#{prefix}editEvent_allowedPayment').val();

    if ($('#{prefix}editReservationEvent_substitute').val()=='Y') {
      $('#{prefix}editReservationEvent_form').dialog('addbutton', '{__button.calendar_editReservation_saveSubstitute}', '{prefix}editSubstituteEventButtonSave', 'flb_primaryButton', function() {
        if ({prefix}calAd&&($('#{prefix}editReservationEvent_userNameSelected').val()!=$('#{prefix}editReservationEvent_userName').val())) {
          alert('{__label.calendar.editReservation_unknownUser}');
        } else {
          var params = { user: $('#{prefix}editReservationEvent_userId').val(),
            places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val() };
          substituteEventSave(params);
        }
      });
    } else {
      if (parseInt($('#{prefix}editEvent_paymentNeeded').val())==0) {
        $('#{prefix}editReservationEvent_form').dialog('addbutton', '{__button.calendar_editReservation_save}', '{prefix}editReservationEventButtonSave', 'flb_primaryButton', function() {
          if ({prefix}calAd&&($('#{prefix}editReservationEvent_userNameSelected').val()!=$('#{prefix}editReservationEvent_userName').val())) {
            alert('{__label.calendar.editReservation_unknownUser}');
          } else {
            var params = { id: $('#{prefix}editReservationEvent_id').val(), user: $('#{prefix}editReservationEvent_userId').val(),
              places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val(), pack: $('#{prefix}editReservationEvent_pack').val() };
            reservationEventSave(params);
          }
        });
      }
      if (parseInt($('#{prefix}editEvent_price').html())>0) {
        if (allowedPaymentCash&&{prefix}calAd) {
          $('#{prefix}editReservationEvent_form').dialog('addbutton', '{__button.calendar_editReservation_savePayCash}', '{prefix}editReservationEventButtonSavePayCash', 'flb_primaryButton', function () {
            message = '{__label.calendar_editReservation_confirmSavePayCash}';
            message = message.replace('{amount}', $('#{prefix}editReservationEvent_visualPrice').html());
            if (confirm(message)) {
              var params = {
                id: $('#{prefix}editReservationEvent_id').val(),
                user: $('#{prefix}editReservationEvent_userId').val(),
                places: $('#{prefix}editReservationEvent_places').val(),
                event: $('#{prefix}editReservationEvent_event').val(),
                pack: $('#{prefix}editReservationEvent_pack').val(),
                pay: 'Y',
                payArrangeCredit: 'Y',
                payArrangeCreditAmount: $('#{prefix}editReservationEvent_pack').val()=='Y'?parseFloat(flbFormatNumber($('#{prefix}editEvent_pricePack').val())):parseFloat(flbFormatNumber($('#{prefix}editEvent_priceSingle').val()))
              };
              reservationEventSave(params);
            }
          });
        }
        if (allowedPaymentCredit) {
          if (userData.credit&&(parseFloat(userData.credit)>parseFloat(reservationPrice))) {
            $('#{prefix}editReservationEvent_form').dialog('addbutton', '{__button.calendar_editReservation_savePayCredit}', '{prefix}editReservationEventButtonSavePayCredit', 'flb_primaryButton', function() {
              if ({prefix}calAd&&($('#{prefix}editReservationEvent_userNameSelected').val()!=$('#{prefix}editReservationEvent_userName').val())) {
                alert('{__label.calendar.editReservation_unknownUser}');
              } else if (confirm('{__label.calendar_editReservation_confirmSavePay}')) {
                var params = { id: $('#{prefix}editReservationEvent_id').val(), user: $('#{prefix}editReservationEvent_userId').val(),
                               places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val(), pack: $('#{prefix}editReservationEvent_pack').val(),
                               pay: 'Y' };
                reservationEventSave(params);
              }
            });
          } else if (userData.credit&&(userData.credit>0)&&{prefix}calAd) {
            $('#{prefix}editReservationEvent_form').dialog('addbutton', '{__button.calendar_editReservation_saveChargePayCredit}', '{prefix}editReservationEventButtonSaveChargePayCredit', 'flb_primaryButton', function() {
              if ({prefix}calAd&&($('#{prefix}editReservationEvent_userNameSelected').val()!=$('#{prefix}editReservationEvent_userName').val())) {
                alert('{__label.calendar.editReservation_unknownUser}');
              } else {
                if (userData.id) {
                  message = '{__label.calendar_editReservation_confirmSaveChargePay}';
                  message = message.replace('{credit}',flbFormatNumber(userData.credit,'human'));
                  var diff = parseFloat(flbFormatNumber($('#{prefix}editReservationEvent_visualPrice').html())) - parseFloat(userData.credit);
                  message = message.replace('{creditMissing}',flbFormatNumber(diff.toString(),'human'));
                } else message = '{__label.calendar_editReservation_confirmSavePay}';
                if (confirm(message)) {
                  var params = { id: $('#{prefix}editReservationEvent_id').val(), user: $('#{prefix}editReservationEvent_userId').val(),
                                 places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val(), pack: $('#{prefix}editReservationEvent_pack').val(),
                                 pay: 'Y', payArrangeCredit: 'Y' };
                  reservationEventSave(params);
                }
              }
            });
          }
        }
        if (allowedPaymentTicket&&userData.ticket&&userData.ticket.length>0) {
          $('#{prefix}editReservationEvent_form').dialog('addbutton', '{__button.calendar_editReservation_savePayTicket}', '{prefix}editReservationEventButtonSavePayTicket', 'flb_primaryButton', function() {
            if (!$('#{prefix}editReservationEvent_ticket').val()) {
              alert('{__label.calendar.editReservation_noTicket}');
              return;
            }
            if ({prefix}calAd&&($('#{prefix}editReservationEvent_userNameSelected').val()!=$('#{prefix}editReservationEvent_userName').val())) {
              alert('{__label.calendar.editReservation_unknownUser}');
            } else if (confirm('{__label.calendar_editReservation_confirmSavePay}')) {
              var params = { id: $('#{prefix}editReservationEvent_id').val(), user: $('#{prefix}editReservationEvent_userId').val(),
                             places: $('#{prefix}editReservationEvent_places').val(), event: $('#{prefix}editReservationEvent_event').val(), pack: $('#{prefix}editReservationEvent_pack').val(),
                             pay: 'Y', payType: 'ticket', payTicket: $('#{prefix}editReservationEvent_ticket').val() };
              reservationEventSave(params);
            }
          });
          
          var ticketCombo = $('#{prefix}editReservationEvent_ticket').html('');
          ticketCombo.append('<option value=\"\">{__label.select_choose}</option>');
          $.each(userData.ticket, function(index,element) {
            ticketCombo.append('<option value=\"'+element.id+'\">'+element.name+' / '+element.value+' '+element.currency+'</option>');
          });
          $('#{prefix}editReservationEvent_line_ticket').show();
        } else $('#{prefix}editReservationEvent_line_ticket').hide();

        if (allowedPaymentOnline) {
          {addEventPaymentGatewaySavePayButton}
        }
      } else $('#{prefix}editReservationEvent_line_ticket').hide();
    }
  }
  
  $('#{prefix}editReservationEvent_pack').change(function() {
    calculateEventPrice();

    var userData = getUserDetail({ user: $('#{prefix}editReservationEvent_userId').val(), event: $('#{prefix}editReservationEvent_event').val(), price: $('#{prefix}editReservationEvent_visualPrice').html() });
    reservationEventButtonRefresh(userData);
  });
  
  $('#{prefix}editReservationEvent_anonymousUser').click(function () {
    $('#{prefix}editReservationEvent_userId').val(0);
    $('#{prefix}editReservationEvent_userName').val('');
    $('#{prefix}editReservationEvent_visualUser').html('');
    
    $('#{prefix}editReservationEvent_attendees input').val('');
  });
  
  $('#{prefix}editReservationEvent_places').change(function() {
    calculateEventPrice();
    
    eventInputAttendeesRefresh(null);

    var userData = getUserDetail({ user: $('#{prefix}editReservationEvent_userId').val(), event: $('#{prefix}editReservationEvent_event').val(), price: $('#{prefix}editReservationEvent_visualPrice').html() });
    reservationEventButtonRefresh(userData);
  });
  
  function calculateEventPrice() {
    var price = parseInt($('#{prefix}editEvent_priceSingle').val());
    if ($('#{prefix}editReservationEvent_pack').val()=='Y') price = parseInt($('#{prefix}editEvent_pricePack').val());
    
    $('#{prefix}editReservationEvent_visualPrice').html(price * parseInt($('#{prefix}editReservationEvent_places').val()));
  }
  
  function eventInputAttendeesRefresh(userData) {
    var subaccountSelectHtml = '';
    if (useUserSubaccount) {
      if (!userData) subaccountSelectHtml = $('#{prefix}editReservationEvent_attendee_1 select').html();
      if (!subaccountSelectHtml) {
        $.ajax({
          type: 'GET',
          async: false,
          dataType: 'json',
          data: {
            user : $('#{prefix}editReservationEvent_userId').val(),
            sessid: $('#flb_core_sessionid').val(),
          },
          url: $('#flb_core_url').val()+'action=getUserSubaccount',
          success: function(data) {
            var selectHtml = '';
            $.each(data, function(index,element) {
              subaccountSelectHtml += '<option value=\"'+element.id+'\">'+element.name+'</option>';
            });
          },
          error: function(error) { alert('{__label.ajaxError}'); }
        });
      }
    }

    var person = parseInt($('#{prefix}editReservationEvent_places').val())*parseInt($('#{prefix}editEvent_coAttendees').val());
    if (person>0) {
      var oldValues = new Array(); var oIndex = 0;
      $('#{prefix}editReservationEvent_attendees [meaning=attendee]').each(function() {
        // musim ulozit puvodni hodnoty
        oldValues[oIndex] = {
          user: $(this).find('[meaning=user]').val(),
          firstname: $(this).find('[meaning=firstname]').val(),
          lastname: $(this).find('[meaning=lastname]').val(),
          email: $(this).find('[meaning=email]').val()
        }
        $(this).remove();

        oIndex++;
      });
      var count = 0;
      while (count<person) {
        var index = count+1;
        if (useUserSubaccount) {
          $('#{prefix}editReservationEvent_attendees').append('<tr meaning=\"attendee\" id=\"{prefix}editReservationEvent_attendee_'+index.toString()+'\">'+
            '<td><select meaning=\"user\">'+subaccountSelectHtml+'</select></td>'+
            '</tr>');
          // nastavim puvodni hodnotu
          if ((typeof oldValues[count] === 'object')&&oldValues[count].user&&
            ($('#{prefix}editReservationEvent_attendee_'+index.toString()+' select option[value='+oldValues[count].user+']').length>0)) {
            $('#{prefix}editReservationEvent_attendee_'+index.toString()+' select').val(oldValues[count].user);
          }
        } else {
          var fVal='';var lVal='';var eVal='';
          if (oIndex>count) {
            fVal = oldValues[count].firstname;
            lVal = oldValues[count].lastname;
            eVal = oldValues[count].email;
          } else if (!count&&userData) {
            fVal = userData.firstname;
            lVal = userData.lastname;
            eVal = userData.email;
          }

          $('#{prefix}editReservationEvent_attendees').append('<tr meaning=\"attendee\" id=\"{prefix}editReservationEvent_attendee_'+index.toString()+'\">'+
            '<td><input meaning=\"firstname\" type=\"text\" value=\"'+fVal+'\"/></td>'+
            '<td><input meaning=\"lastname\" type=\"text\" value=\"'+lVal+'\"/></td>'+
            '<td><input meaning=\"email\" class=\"email\" type=\"text\" value=\"'+eVal+'\"/></td>'+
            '</tr>');
        }

        count++;
      }
    }
  }
  
  function reservationEventAttendeePrepare() {
    var ret = {};

    if (useUserSubaccount) {
      var index = 0;
      $('#{prefix}editReservationEvent_attendees tr [meaning=user]').each(function() { ret[index] = { user: $(this).val() }; index++; });
    } else {
      var index = 0;
      $('#{prefix}editReservationEvent_attendees tr [meaning=firstname]').each(function() { ret[index] = { firstname: $(this).val() }; index++; });
      index = 0;
      $('#{prefix}editReservationEvent_attendees tr [meaning=lastname]').each(function() { ret[index].lastname = $(this).val(); index++; });
      index = 0;
      $('#{prefix}editReservationEvent_attendees tr [meaning=email]').each(function() { ret[index].email = $(this).val(); index++; });
    }
                                                                             
    return ret;
  }
  
  function reservationResourceGetPrice(from, to) {
    var resource = '';
    if ($('#{prefix}flb_commodity_type').val()=='pool') {
      var allowedResource = $('#{prefix}editReservationResource_allowedResourceIds').val().split(',');
      for (var i=0;i<$("#{prefix}editReservationResource_places").val();i++) {
        if (resource) resource += ',';
        resource += allowedResource[i];
      }
    } else {
      resource = $('#{prefix}editReservationResource_resourceId').val();
    }

    $.ajax({
      async: false,
      type: 'GET',
      dataType: 'json',
      data: {
        resourceId : resource,
        from: from,
        to: to
      },
      url: $('#flb_core_url').val()+'action=getResourcePrice',
      success: function(data) {
        //alert('{prefix}');
        $('#{prefix}editReservationResource_paymentNeeded').val(data.paymentNeeded?1:0);
        $('#{prefix}editReservationResource_priceHidden').val(data.priceRaw);
        $('#{prefix}editReservationResource_price').html(data.price);
        $('#{prefix}editReservationResource_currency').html(data.currency);
      },
      error: function(error) {
        alert('{__label.calendar_ajaxError}');
      }
    });
  }

  function substituteEventSave(params) {
    params.attendee = reservationEventAttendeePrepare();
    params.attribute = reservationAttributePrepare('#{prefix}editReservationEvent_form');

    if (id=substituteEventSave_apiCall(params)) {
      $('#{prefix}editReservationEvent_form').dialog('close');

      if ($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val()) eventAttendeeRefresh(false,true);
      else $('#{prefix}editEvent_form').dialog('close');

      $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
    }

    return id;
  }

  function reservationEventSave(params) {
    params.attendee = reservationEventAttendeePrepare();
    params.attribute = reservationAttributePrepare('#{prefix}editReservationEvent_form');
    if ({prefix}calAd) params.note = $('#{prefix}editReservationEvent_note').val();
    if (id=reservationEventSave_apiCall(params)) {
      $('#{prefix}editReservationEvent_form').dialog('close');

      if ($('#flb_core_userid').val()==$('#{prefix}editEvent_organiser').val()) eventAttendeeRefresh(false,true);
      else $('#{prefix}editEvent_form').dialog('close');

      $('#{prefix}cal_{id}').fullCalendar('refetchEvents');
    }

    return id;
  }
});

function formatDateTime(date,output) {
  var d = new Date(date || Date.now()),
    month = (d.getMonth() + 1).toString(),
    day = d.getDate().toString(),
    year = d.getFullYear().toString(),
    hour = d.getHours().toString(),
    minute = d.getMinutes().toString();

  if (output=='mysql') {
    if (day.length < 2) day = '0' + day;
    if (month.length < 2) month = '0' + month;
    if (hour.length < 2) hour = '0' + hour;
    if (!minute) minute = '00';
    else if (minute.length < 2) minute = '0' + minute;
    
    outDate = [year, month, day].join('-');
    outTime = [hour, minute, '00'].join(':');
  } else if (output=='human') {
    if (!minute) minute = '00';
    else if (minute.length < 2) minute = '0' + minute;
    
    outDate = [day,month,year].join('.');
    outTime = [hour, minute].join(':');
  } else if (output=='humanDate') {
    outDate = [day,month,year].join('.');
    outTime = '';
  } else if (output=='humanTime') {
    if (!minute) minute = '00';
    else if (minute.length < 2) minute = '0' + minute;
    
    outDate = '';
    outTime = [hour, minute].join(':');
  }
  
  ret = outDate;
  if (ret&&outTime) ret += ' ';
  ret += outTime;
    
  return ret;
}

function reservationCheckOverlap(calendarId,start,end,eventId,resourceId) {
  // kontroluje prekryti s jinymi rezervacemi v kalendari (id je kvuli tomu, aby se nekontrolovalo prekryti se sebou samym)
  var startStamp = start.format('YYYYMMDDHHmmss');
  var endStamp = end.format('YYYYMMDDHHmmss');
  var events = $('#'+calendarId).fullCalendar('clientEvents');
  for (i in events) {
    // kdyz udalost nema casy, tak ji nekontroluju
    if (!events[i].start||!events[i].end) continue;
    // netestuju prunik se sebou samou
    if (eventId&&(events[i]._id==eventId)) continue;
    
    var eventStartStamp = events[i].start.format('YYYYMMDDHHmmss');
    var eventEndStamp = events[i].end.format('YYYYMMDDHHmmss');
    
    if (endStamp > eventStartStamp && startStamp < eventEndStamp) {
      //alert(JSON.stringify(resourceId));
      if (!resourceId||(events[i].resourceId==resourceId)) {
        return true;
      }
    }
  }
  return false;
}

function reservationPoolGetResource(resourcePool,from,to) {
  //console.log('===== reservationPoolGetResource =====');
  var ret = null;

  $.ajax({
    type: 'GET',
    url: $('#flb_core_url').val()+'action=getResourcePoolResource',
    dataType: 'json',
    async: false,
    data: {
        resourcePoolId : resourcePool,
        from: from,
        to: to
      },
    success: function(data) {
      if (data) {
        ret = data.id;
      }
    },
    error: function(error) {
      alert('{__label.ajaxError}');
    }
  });

  //console.log('Returning resources: '+ret);
  //console.log('=====================================');
  
  return ret;
}

function reservationAttributePrepare(placeHolder) {
  var ret = {};
  $(placeHolder+' [meaning=reservation_attribute]').each(function () {
    var idExt = $(this).attr('id');
    idExt = idExt.replace('{prefix}attr_','');
    
    ret[idExt] = $(this).val();
  });
  
  return ret;
}

function getReservationAttribute(placeHolder,event,resource,reservation,readonly) {
  if (event) data = { eventId: event, reservationId: reservation, readonly: readonly };
  else data = { resourceId: resource, reservationId: reservation, readonly: readonly };

  $.ajax({
    type: 'GET',
    url: $('#flb_core_url').val()+'action=guiReservationAttribute&sessid='+$('#flb_core_sessionid').val()+'&target=calendar&prefix={prefix}',
    dataType: 'json',
    data: data,
    success: function(data) {
      $(placeHolder).html(data.output);
    },
    error: function(error) {
      alert('{__label.ajaxError}');
    }
  });
}

function getResourceForReservation(prefix) {
  //console.log('===== getResourceForReservation =====');
  //console.log('Commodity type: '+$('#'+prefix+'flb_commodity_type').val());
  //console.log('Allowed resources: '+$('#'+prefix+'editReservationResource_allowedResourceIds').val());
  //console.log('Places: '+$('#'+prefix+'editReservationResource_places').val());

  var resource = '';

  if ($('#'+prefix+'flb_commodity_type').val()=='pool') {
    var allowedResource = $('#'+prefix+'editReservationResource_allowedResourceIds').val().split(',');
    for (var i=0;i<$('#'+prefix+'editReservationResource_places').val();i++) {
      if (resource) resource += ',';
      resource += allowedResource[i];
    }
  } else {
    resource = $('#'+prefix+'editReservationResource_resourceId').val();
  }

  //console.log('Returning resources: '+resource);
  //console.log('=====================================');

  return resource;
}

function reservationResourceSave(params) {
  // ulozi data o rezervaci ajaxem
  ret = false;
  
  data = { resource: params.resource };
  if (params.id) data['id'] = params.id;
  if (params.start) data['start'] = formatDateTime(params.start,'mysql');
  if (params.end) data['end'] = formatDateTime(params.end,'mysql');
  if (params.user) data['user'] = params.user;
  if (params.customer) data['customer'] = params.customer;
  if (params.pay) data['pay'] = params.pay;
  if (params.payType) data['payType'] = params.payType;
  if (params.payArrangeCredit) data['payArrangeCredit'] = params.payArrangeCredit;
  if (params.payArrangeCreditAmount) data['payArrangeCreditAmount'] = params.payArrangeCreditAmount;
  if (params.payTicket) data['payTicket'] = params.payTicket;
  if (params.paymentOnline) data['paymentOnline'] = params.paymentOnline;
  if (params.attribute) data['attribute'] = params.attribute;
  if (params.note) data['note'] = params.note;
  if ($('#{prefix}flb_commodity_type').val()=='pool') data['pool'] = 'Y';
  if (ch = $('#{prefix}editReservationResource_skipReservationCondition')) {
    if (ch.is(':checked')) data['skipCondition'] = 'all';
  }
  if (ch = $('#{prefix}editReservationResource_mandatoryReservation')) {
    if (ch.is(':checked')) data['mandatory'] = 'Y';
  }

  $.ajax({
    type: 'GET',
    url: $('#flb_core_url').val()+'action=saveReservation&sessid='+$('#flb_core_sessionid').val(),
    data: data,
    dataType: 'json',
    async: false,
    success: function(data) {
      if (data.error) {
        ret = false;
        alert(data.message);
        $('#{prefix}editReservationResource_line_skipReservationCondition').show();
      } else {
        if (data.popup) alert(data.popup);
        
        ret = data.id;   
      }
    },
    error: function(data) {
      alert('{__label.calendar_ajaxError}');
    }
  });
  
  return ret;
}

function substituteEventSave_apiCall(params) {
  ret = false;
  
  data = { event: params.event };
  if (params.places) data['places'] = params.places;
  if (params.attendee) data['attendee'] = JSON.stringify(params.attendee);
  if (params.user) data['user'] = params.user;
  if (params.attribute) data['attribute'] = params.attribute;

  $.ajax({
    type: 'GET',
    url: $('#flb_core_url').val()+'action=saveSubstitute&sessid='+$('#flb_core_sessionid').val(),
    data: data,
    dataType: 'json',
    async: false,
    success: function(data) {
      if (data.error) {
        ret = false;
        alert(data.message);
      } else {
        if (data.popup) alert(data.popup);
        
        ret = true;   
      }
      
      $('#{prefix}editEvent_reserved').val(1);
    },
    error: function(data) {
      alert('{__label.calendar_ajaxError}');
    }
  });
  
  return ret;
}

function reservationEventSave_apiCall(params) {
  // ulozi data o rezervaci ajaxem
  ret = false;
  
  data = { event: params.event };
  if (params.id) data['id'] = params.id;
  if (params.places) data['places'] = params.places;
  if (params.pack) data['pack'] = params.pack;
  if (params.attendee) data['attendee'] = JSON.stringify(params.attendee);
  if (params.user) data['user'] = params.user;
  if (params.customer) data['customer'] = params.customer;
  if (params.pay) data['pay'] = params.pay;
  if (params.payType) data['payType'] = params.payType;
  if (params.payArrangeCredit) data['payArrangeCredit'] = params.payArrangeCredit;
  if (params.payArrangeCreditAmount) data['payArrangeCreditAmount'] = params.payArrangeCreditAmount;
  if (params.payTicket) data['payTicket'] = params.payTicket;
  if (params.paymentOnline) data['paymentOnline'] = params.paymentOnline;
  if (params.attribute) data['attribute'] = params.attribute;
  if (params.note) data['note'] = params.note;
  if (ch = $('#{prefix}editReservationEvent_skipReservationCondition')) {
    if (ch.is(':checked')) data['skipCondition'] = 'all';
  }
  if (ch = $('#{prefix}editReservationEvent_mandatoryReservation')) {
    if (ch.is(':checked')) data['mandatory'] = 'Y';
  }

  $.ajax({
    type: 'GET',
    url: $('#flb_core_url').val()+'action=saveReservation&sessid='+$('#flb_core_sessionid').val(),
    data: data,
    dataType: 'json',
    async: false,
    success: function(data) {
      if (data.error) {
        ret = false;
        alert(data.message);
        $('#{prefix}editReservationEvent_line_skipReservationCondition').show();
      } else {
        if (data.popup) alert(data.popup);
        
        ret = data.id;   
      }
      
      $('#{prefix}editEvent_reserved').val(1);
    },
    error: function(data) {
      alert('{__label.calendar_ajaxError}');
    }
  });
  
  return ret;
}

function reservationCancel(params) {
  ret = false;
  
  // ulozi data o rezervaci ajaxem
  dataString = 'sessid='+$('#flb_core_sessionid').val()
  if (params.id) {
      if (dataString) dataString += '&';
      dataString += 'id='+params.id;
  }
  if (params.refund) {
    if (dataString) dataString += '&';
    dataString += 'refund='+params.refund;
  }

  $.ajax({
      type: 'GET',
      url: $('#flb_core_url').val()+'action=cancelReservation',
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
      error: function(data) {
        alert('{__label.calendar_ajaxError}');
      }
  });
  
  return ret;
}

function reservationPay(params) {
  ret = false;
  
  // ulozi data o rezervaci ajaxem
  dataString = 'sessid='+$('#flb_core_sessionid').val()
  if (params.id) {
      if (dataString) dataString += '&';
      dataString += 'id='+params.id;
  }
  if (params.payType) {
    if (dataString) dataString += '&';
    dataString += 'type='+params.payType;
  }
  if (params.payArrangeCredit) {
    if (dataString) dataString += '&';
    dataString += 'arrangeCredit='+params.payArrangeCredit;
  }
  if (params.payArrangeCreditAmount) {
    if (dataString) dataString += '&';
    dataString += 'arrangeCreditAmount='+params.payArrangeCreditAmount;
  }
  if (params.payTicket) {
    if (dataString) dataString += '&';
    dataString += 'ticket='+params.payTicket;
  }

  $.ajax({
      type: 'GET',
      url: $('#flb_core_url').val()+'action=payReservation',
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
      error: function(data) {
        alert('{__label.calendar_ajaxError}');
      }
  });
  
  return ret;
}

function flbFormatNumber(number,output) {
  if (output=='human') {
    ret = number.split('.');
    integral = ret[0];
    decimal = ret[1];
    
    if (integral.length>3) {
      integral = integral.substring(0,integral.length-3) + ' ' + integral.substring(integral.length-3);
    }
    ret = integral;
    if (decimal) ret = ret + ',' + decimal;
  } else {
    ret = number.replace(' ','');
    ret = ret.replace(',','.');
  }
  return ret;
}
