$(function() {
    var id = $('#editTerm_id'),
        index = $('#editTerm_index'),
        name = $('#editTerm_name'),
        date = $('#editTerm_date'),
        dateFrom = $('#editTerm_dateFrom'),
        dateTo = $('#editTerm_dateTo'),
        timeFrom = $('#editTerm_timeFrom'),
        timeTo = $('#editTerm_timeTo'),
        repeatUntil = $('#editTerm_repeatUntil');
        allFields = $([]).add(id).add(name).add(date).add(dateFrom).add(dateTo).add(timeFrom).add(timeTo).add(repeatUntil);
    
    date.datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false});
    dateFrom.datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false});
    dateTo.datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false});
    timeFrom.datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1'});
    timeTo.datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1'});
    repeatUntil.datetimepicker({format:'d.m.Y',dayOfWeekStart:'1',timepicker:false,allowBlank: true,});
    
    $('#fi_newTerm_form').dialog({
        autoOpen: false,
        height: 380,
        width: 550,
        modal: true,
        buttons: {
            '{__button.editAvailExProfile_termSave}': function() {
                var valid = true;
                var message = '';
                var type, htmlValue;
                if ($('#editTerm_typeDate').is(':checked')) {
                  if (!date.val()) { message += '{__error.editAvailExProfile_termMissingDate}'; valid = false; }
                  type = 'Date';
                  htmlValue = date.val();  
                } else if ($('#editTerm_typeDateRange').is(':checked')) {
                  if (!dateFrom.val()) { if (message) message += "\n"; message += '{__error.editAvailExProfile_termMissingDateFrom}'; valid = false; }
                  if (!dateTo.val()) { if (message) message += "\n"; message += '{__error.editAvailExProfile_termMissingDateTo}'; valid = false; }
                  if (formatDateTime(dateTo.val(),'mysql')<=formatDateTime(dateFrom.val(),'mysql')) { if (message) message += "\n"; message += '{__error.editAvailExProfile_termDateFromDateTo}'; valid = false; }
                  type = 'DateRange';
                  htmlValue = dateFrom.val()+' - '+dateTo.val();  
                } else if ($('#editTerm_typeTimeRange').is(':checked')) {
                  if (!timeFrom.val()) { if (message) message += "\n"; message += '{__error.editAvailExProfile_termMissingTimeFrom}'; valid = false; }
                  if (!timeTo.val()) { if (message) message += "\n"; message += '{__error.editAvailExProfile_termMissingTimeTo}'; valid = false; }
                  if (formatDateTime(timeTo.val(),'mysql')<=formatDateTime(timeFrom.val(),'mysql')) { if (message) message += "\n"; message += '{__error.editAvailExProfile_termTimeFromTimeTo}'; valid = false; }
                  type = 'TimeRange';
                  htmlValue = timeFrom.val()+' - '+timeTo.val();  
                }
                if ($('#editTerm_repeated').is(':checked')) {
                  if (!$('#editTerm_repeatCycle').val()) { if (message) message += "\n"; message += '{__error.editAvailExProfile_repeatedCycleMissing}'; valid = false; }
                }
                if (message) alert(message);

                if (valid) {
                    var html =
                            '<td>' + name.val() + '</td>' +
                            '<td>' + htmlValue + '</td>' +
                            '<td>[<a href="#" id="fi_termEdit">{__button.grid_edit}</a>][<a href="#" id="fi_termRemove">{__button.grid_remove}]</a></td>';
                    
                    if (index.val()) {
                      i = index.val();
                    } else {
                      i = Math.floor(Math.random()*10000);
                    }
                    
                    html += '<input type="hidden" name="newTerm['+i+']" value="termId~'+id.val()+';name~'+name.val()+';type~'+type+';date~'+date.val()+
                            ';dateFrom~'+dateFrom.val()+';dateTo~'+dateTo.val()+';timeFrom~'+timeFrom.val()+';timeTo~'+timeTo.val()+
                            ';repeated~'+($('#editTerm_repeated').is(':checked')?'Y':'N')+';repeatCycle~'+$('#editTerm_repeatCycle').val()+';repeatUntil~'+repeatUntil.val()+
                            ';repeatWeekday_mon~'+($('#editTerm_repeatWeekday_mon').is(':checked')?'1':'0')+
                            ';repeatWeekday_tue~'+($('#editTerm_repeatWeekday_tue').is(':checked')?'1':'0')+
                            ';repeatWeekday_wed~'+($('#editTerm_repeatWeekday_wed').is(':checked')?'1':'0')+
                            ';repeatWeekday_thu~'+($('#editTerm_repeatWeekday_thu').is(':checked')?'1':'0')+
                            ';repeatWeekday_fri~'+($('#editTerm_repeatWeekday_fri').is(':checked')?'1':'0')+
                            ';repeatWeekday_sat~'+($('#editTerm_repeatWeekday_sat').is(':checked')?'1':'0')+
                            ';repeatWeekday_sun~'+($('#editTerm_repeatWeekday_sun').is(':checked')?'1':'0')+
                            '"/>';
                    
                    if (index.val()) {
                      $('#fi_termTable tbody').find('tr#'+index.val()).html(html);
                    } else {
                      $('#fi_termTable tbody').append('<tr id="'+i+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editAvailExProfile_termCancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
          $('div.item').hide();
          index.val('');
          allFields.val('').removeClass('ui-state-error');
        }
    });
    
    $('#editTerm_typeDate').click(function() { $('div.item').hide(); $('#date').show(); });
    $('#editTerm_typeDateRange').click(function() { $('div.item').hide(); $('#dateRange').show(); });
    $('#editTerm_typeTimeRange').click(function() { $('div.item').hide(); $('#timeRange').show(); });
    $('#editTerm_repeated').change(function() { repeatClick(); });
    $('#editTerm_repeatCycle').change(function() { repeatCycleClick(); });
                                 
    function repeatClick() {
      if ($('#editTerm_repeated').is(':checked')) {
        $('#repeatDiv').css({display:'block'});
      } else {
        $('#repeatDiv').css({display:'none'});
      }
    };
    
    function repeatCycleClick() {
      if ($('#editTerm_repeatCycle').val().substring(0,4)=='WEEK') {
        $('#repeatWeekdayDiv').show();
      } else {
        $('#repeatWeekdayDiv').hide();
      }
    };
    
    $('#fi_addTerm').click(function() {
      $('#editTerm_typeDate').click();
      $('#editTerm_repeated').prop('checked', false);
      $('#editTerm_repeatCycle').val('');
      $('#editTerm_repeatWeekday_mon').prop('checked', false);
      $('#editTerm_repeatWeekday_tue').prop('checked', false);
      $('#editTerm_repeatWeekday_wed').prop('checked', false);
      $('#editTerm_repeatWeekday_thu').prop('checked', false);
      $('#editTerm_repeatWeekday_fri').prop('checked', false);
      $('#editTerm_repeatWeekday_sat').prop('checked', false);
      $('#editTerm_repeatWeekday_sun').prop('checked', false);
      repeatClick();
      repeatCycleClick();
      $('#fi_newTerm_form').dialog('open');
    });
    
    $('#editAvailExProfile').on('click','#fi_termRemove', function() {
      $(this).closest('tr').remove();
      return false;
    });
    
    $('#editAvailExProfile').on('click','#fi_termEdit', function() {
      var tr = $(this).closest('tr');
      index.val(tr.attr('id'));
      id.val(tr.attr('db_id'));
      var input = tr.find('input');
      var values = input.val().split(';');
      for (i=0;i<values.length;i++) {
        value = values[i].split('~');
        if (value[0]=='name') { name.val(value[1]); }
        if (value[0]=='type') { type = value[1]; }
        if (value[0]=='date') { date.val(value[1]); }
        if (value[0]=='dateFrom') { dateFrom.val(value[1]); }
        if (value[0]=='dateTo') { dateTo.val(value[1]); }
        if (value[0]=='timeFrom') { timeFrom.val(value[1]); }
        if (value[0]=='timeTo') { timeTo.val(value[1]); }
        if (value[0]=='repeated') {
          if (value[1]=='Y') { $('#editTerm_repeated').prop('checked', true); }
          else { $('#editTerm_repeated').prop('checked', false); }
        }
        var days = ['mon','tue','wed','thu','fri','sat','sun'];
        $.each(days, function(index,day) {
          if (value[0]=='repeatWeekday_'+day) {
            if (value[1]>0) { $('#editTerm_repeatWeekday_'+day).prop('checked', true); }
            else { $('#editTerm_repeatWeekday_'+day).prop('checked', false); }
          }
        })
        if (value[0]=='repeatCycle') { $('#editTerm_repeatCycle').val(value[1]); }
        if (value[0]=='repeatUntil') { repeatUntil.val(value[1]); }
      }
      
      $('#editTerm_type'+type).click();
      $('#fi_newTerm_form').dialog('open');
      repeatClick();
      repeatCycleClick();
      
      return false;
    });
});