$(function() {
    var id = $('#editCondition_id'),
        index = $('#editCondition_index'),
        name = $('#editCondition_name'),
        from = $('#editCondition_from'),
        to = $('#editCondition_to'),
        center = $('#editCondition_center'),
        centerMessage = $('#editCondition_centerMessage'),
        firstTimeBeforeCount = $('#editCondition_firstTimeBeforeCount'),
        firstTimeBeforeUnit = $('#editCondition_firstTimeBeforeUnit'),
        firstTimeBeforeMessage = $('#editCondition_firstTimeBeforeMessage'),
        lastTimeBeforeCount = $('#editCondition_lastTimeBeforeCount'),
        lastTimeBeforeUnit = $('#editCondition_lastTimeBeforeUnit'),
        lastTimeBeforeMessage = $('#editCondition_lastTimeBeforeMessage'),
        afterStartEvent = $('#editCondition_afterStartEvent'),
        afterStartEventMessage = $('#editCondition_afterStartEventMessage'),
        advancePaymentCount = $('#editCondition_advancePaymentCount'),
        advancePaymentUnit = $('#editCondition_advancePaymentUnit'),
        advancePaymentMessage = $('#editCondition_advancePaymentMessage'),
        cancelBeforeCount = $('#editCondition_cancelBeforeCount'),
        cancelBeforeUnit = $('#editCondition_cancelBeforeUnit'),
        cancelBeforeMessage = $('#editCondition_cancelBeforeMessage'),
        cancelPayedBeforeCount = $('#editCondition_cancelPayedBeforeCount'),
        cancelPayedBeforeUnit = $('#editCondition_cancelPayedBeforeUnit'),
        cancelPayedBeforeMessage = $('#editCondition_cancelPayedBeforeMessage'),
        anonymousBeforeCount = $('#editCondition_anonymousBeforeCount'),
        anonymousBeforeUnit = $('#editCondition_anonymousBeforeUnit'),
        anonymousBeforeMessage = $('#editCondition_anonymousBeforeMessage'),
        quantity = $('#editCondition_quantity'),
        period = $('#editCondition_period'),
        type = $('#editCondition_type'),
        scope = $('#editCondition_scope'),
        quantityMessage = $('#editCondition_quantityMessage'),
        otherScope = $('#editCondition_otherScope'),
        resource = $('#editCondition_resource'),
        resourceExists = $('#editCondition_resourceExists'),
        resourcePayed = $('#editCondition_resourcePayed'),
        resourceAll = $('#editCondition_resourceAll'),
        resourceMessage = $('#editCondition_resourceMessage'),
        event = $('#editCondition_event'),
        eventExists = $('#editCondition_eventExists'),
        eventPayed = $('#editCondition_eventPayed'),
        eventAll = $('#editCondition_eventAll'),
        eventMessage = $('#editCondition_eventMessage'),
        totalQuantity = $('#editCondition_totalQuantity'),
        totalQuantityPeriod = $('#editCondition_totalQuantityPeriod'),
        totalQuantityType = $('#editCondition_totalQuantityType'),
        totalQuantityTag = $('#editCondition_totalQuantityTag'),
        totalQuantityMessage = $('#editCondition_totalQuantityMessage'),
        overlapQuantity = $('#editCondition_overlapQuantity'),
        overlapQuantityScope = $('#editCondition_overlapQuantityScope'),
        overlapQuantityTag = $('#editCondition_overlapQuantityTag'),
        overlapQuantityMessage = $('#editCondition_overlapQuantityMessage'),
        
        allFields = $([]).add(id).add(name).add(from).add(to).
                          add(center).
                          add(firstTimeBeforeCount).add(firstTimeBeforeUnit).
                          add(lastTimeBeforeCount).add(lastTimeBeforeUnit).
                          add(afterStartEvent).
                          add(advancePaymentCount).add(advancePaymentUnit).
                          add(cancelBeforeCount).add(cancelBeforeUnit).
                          add(cancelPayedBeforeCount).add(cancelPayedBeforeUnit).
                          add(anonymousBeforeCount).add(anonymousBeforeUnit).
                          add(quantity).add(period).add(type).
                          add(resource).add(resourceExists).add(resourcePayed).add(resourceAll).add(event).add(eventExists).add(eventPayed).add(eventAll).
                          add(totalQuantity).add(totalQuantityPeriod).add(totalQuantityType).add(totalQuantityTag).
                          add(overlapQuantity).add(overlapQuantityScope).
                          add(firstTimeBeforeMessage).add(lastTimeBeforeMessage).add(afterStartEventMessage).add(advancePaymentMessage).add(cancelBeforeMessage).
                          add(cancelPayedBeforeMessage).add(anonymousBeforeMessage).add(quantityMessage).add(resourceMessage).add(eventMessage).
                          add(totalQuantityMessage).add(overlapQuantityMessage);
    
    from.datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',allowBlank: true,scrollInput: false});
    to.datetimepicker({format:'d.m.Y H:i',dayOfWeekStart:'1',allowBlank: true,scrollInput: false});
    
    $('#editCondition_resource').tokenInput(
           function() { return '{url}?action=getResource&provider='+$('#fi_provider').val(); }, {
           minChars:3, queryParam:'name', theme:'facebook',
           preventDuplicates:true,
           hintText:'{__label.editReservationCondition_conditionRequired_resourceHint}',
           searchingText:'{__label.editReservationCondition_searching}',
           noResultsText:'{__label.editReservationCondition_noresult}'
    });
    $('#editCondition_event').tokenInput(
           function() { return '{url}?action=getEvent&provider='+$('#fi_provider').val(); }, {
           minChars:3, queryParam:'name', theme:'facebook', propertyToSearch: 'nameWithStart',
           preventDuplicates:true,
           hintText:'{__label.editReservationCondition_conditionRequired_eventHint}',
           searchingText:'{__label.editReservationCondition_searching}',
           noResultsText:'{__label.editReservationCondition_noresult}'
    });
    
    $('#editCondition_totalQuantityTag').tokenInput(
           function() { return '{url}?action=getTag&provider='+$('#fi_provider').val(); }, {
           minChars:3, queryParam:'term', theme:'facebook',
           preventDuplicates:true,
           hintText:'{__label.editReservationCondition_conditionTotalQuantity_hint}',
           searchingText:'{__label.editReservationCondition_searching}',
           noResultsText:'{__label.editReservationCondition_noresult}'
    });

    $('#editCondition_overlapQuantityTag').tokenInput(
      function() { return '{url}?action=getTag&provider='+$('#fi_provider').val(); }, {
        minChars:3, queryParam:'term', theme:'facebook',
        preventDuplicates:true,
        hintText:'{__label.editReservationCondition_conditionTotalQuantity_hint}',
        searchingText:'{__label.editReservationCondition_searching}',
        noResultsText:'{__label.editReservationCondition_noresult}'
    });
    
    $('#fi_newCondition_form').dialog({
        autoOpen: false,
        height: 740,
        width: 1100,
        modal: true,
        buttons: {
            '{__button.editReservationCondition_conditionSave}': function() {
                var valid = true;
                
                if (valid) {
                    var html =
                            '<td>' + name.val() + '</td>' +
                            '<td>[<a href="#" id="fi_conditionEdit">{__button.grid_edit}</a>][<a href="#" id="fi_conditionRemove">{__button.grid_remove}]</a></td>';
                    
                    if (index.val()) {
                      i = index.val();
                    } else {
                      i = Math.floor(Math.random()*10000);
                    }
                    
                    html += '<input type="hidden" name="newCondition['+i+']" value="conditionId~'+id.val()+
                            ';name~'+name.val()+';from~'+from.val()+';to~'+to.val()+
                            ';center~'+center.val()+';centerMessage~'+centerMessage.val()+
                            ';firstTimeBeforeCount~'+firstTimeBeforeCount.val()+';firstTimeBeforeUnit~'+firstTimeBeforeUnit.val()+
                            ';firstTimeBeforeMessage~'+firstTimeBeforeMessage.val()+
                            ';lastTimeBeforeCount~'+lastTimeBeforeCount.val()+';lastTimeBeforeUnit~'+lastTimeBeforeUnit.val()+
                            ';lastTimeBeforeMessage~'+lastTimeBeforeMessage.val()+
                            ';afterStartEvent~'+afterStartEvent.val()+';afterStartEventMessage~'+afterStartEventMessage.val()+
                            ';advancePaymentCount~'+advancePaymentCount.val()+';advancePaymentUnit~'+advancePaymentUnit.val()+
                            ';advancePaymentMessage~'+advancePaymentMessage.val()+
                            ';cancelBeforeCount~'+cancelBeforeCount.val()+';cancelBeforeUnit~'+cancelBeforeUnit.val()+
                            ';cancelBeforeMessage~'+cancelBeforeMessage.val()+
                            ';cancelPayedBeforeCount~'+cancelPayedBeforeCount.val()+';cancelPayedBeforeUnit~'+cancelPayedBeforeUnit.val()+
                            ';cancelPayedBeforeMessage~'+cancelPayedBeforeMessage.val()+
                            ';anonymousBeforeCount~'+anonymousBeforeCount.val()+';anonymousBeforeUnit~'+anonymousBeforeUnit.val()+
                            ';anonymousBeforeMessage~'+anonymousBeforeMessage.val()+
                            ';quantity~'+quantity.val()+';period~'+period.val()+';type~'+type.val()+';scope~'+scope.val()+
                            ';quantityMessage~'+quantityMessage.val()+
                            ';otherScope~'+otherScope.val()+
                            ';event~'+event.val()+';eventExists~'+eventExists.val()+';eventPayed~'+eventPayed.val()+';eventAll~'+eventAll.val()+
                            ';eventMessage~'+eventMessage.val()+
                            ';resource~'+resource.val()+';resourceExists~'+resourceExists.val()+';resourcePayed~'+resourcePayed.val()+';resourceAll~'+resourceAll.val()+
                            ';resourceMessage~'+resourceMessage.val()+
                            ';totalQuantity~'+totalQuantity.val()+';totalQuantityPeriod~'+totalQuantityPeriod.val()+';totalQuantityType~'+totalQuantityType.val()+';totalQuantityTag~'+totalQuantityTag.val()+
                            ';totalQuantityMessage~'+totalQuantityMessage.val()+
                            ';overlapQuantity~'+overlapQuantity.val()+';overlapQuantityScope~'+overlapQuantityScope.val()+';overlapQuantityTag~'+overlapQuantityTag.val()+
                            ';overlapQuantityMessage~'+overlapQuantityMessage.val()+
                            '"/>';
                    
                    if (index.val()) {
                      $('#fi_conditionTable tbody').find('tr#'+index.val()).html(html);
                    } else {
                      $('#fi_conditionTable tbody').append('<tr id="'+i+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                    //alert(html);
                }
            },
            '{__button.editReservationCondition_conditionCancel}': function() {
                $(this).dialog('close');
            }
        },
        close: function() {
          index.val('');
          allFields.val('').removeClass('ui-state-error');
          
          resource.tokenInput('clear');
          event.tokenInput('clear');
          totalQuantityTag.tokenInput('clear');
          overlapQuantityTag.tokenInput('clear');

          center.val('');
          firstTimeBeforeUnit.val('min');
          lastTimeBeforeUnit.val('min');
          afterStartEvent.val('Y');
          advancePaymentUnit.val('min');
          cancelBeforeUnit.val('min');
          cancelPayedBeforeUnit.val('min');
          anonymousBeforeUnit.val('min');
          eventPayed.val('N');
          resourcePayed.val('N');
          type.val('ALL');
          scope.val('USER');
          otherScope.val('USER');
          totalQuantityType.val('ALL');
        }
    });    
    
    $('#fi_addCondition').click(function() {
      if ($('#fi_provider').val()) {
        $('.messageIcon').attr('src','img/icon_message_empty.png');

        $('#fi_newCondition_form').dialog('open');
      } else {
        alert('{__error.editReservationCondition_missingProvider}');
      }
    });
    
    $('#editReservationCondition').on('click','#fi_conditionRemove', function() {
      $(this).closest('tr').remove();
      return false;
    });
    
    $('#editReservationCondition').on('click','#fi_conditionEdit', function() {
      var tr = $(this).closest('tr');
      index.val(tr.attr('id'));
      id.val(tr.attr('db_id'));
      var input = tr.find('input');
      var values = input.val().split(';');
      for (i=0;i<values.length;i++) {
        value = values[i].split('~');
        if (value[0]=='name') { name.val(value[1]); }
        if (value[0]=='from') { from.val(value[1]); }
        if (value[0]=='to') { to.val(value[1]); }
        if (value[0]=='center') { center.val(value[1]); }
        if (value[0]=='centerMessage') {
          centerMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_centerMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_centerMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='firstTimeBeforeCount') { firstTimeBeforeCount.val(value[1]); }
        if (value[0]=='firstTimeBeforeUnit') { firstTimeBeforeUnit.val(value[1]); }
        if (value[0]=='firstTimeBeforeMessage') {
          firstTimeBeforeMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_firstTimeBeforeMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_firstTimeBeforeMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='lastTimeBeforeCount') { lastTimeBeforeCount.val(value[1]); }
        if (value[0]=='lastTimeBeforeUnit') { lastTimeBeforeUnit.val(value[1]); }
        if (value[0]=='lastTimeBeforeMessage') {
          lastTimeBeforeMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_lastTimeBeforeMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_lastTimeBeforeMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='afterStartEvent') { afterStartEvent.val(value[1]); }
        if (value[0]=='afterStartEventMessage') {
          afterStartEventMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_afterStartEventMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_afterStartEventMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='advancePaymentCount') { advancePaymentCount.val(value[1]); }
        if (value[0]=='advancePaymentUnit') { advancePaymentUnit.val(value[1]); }
        if (value[0]=='advancePaymentMessage') {
          advancePaymentMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_advancePaymentMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_advancePaymentMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='cancelBeforeCount') { cancelBeforeCount.val(value[1]); }
        if (value[0]=='cancelBeforeUnit') { cancelBeforeUnit.val(value[1]); }
        if (value[0]=='cancelBeforeMessage') {
          cancelBeforeMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_cancelBeforeMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_cancelBeforeMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='cancelPayedBeforeCount') { cancelPayedBeforeCount.val(value[1]); }
        if (value[0]=='cancelPayedBeforeUnit') { cancelPayedBeforeUnit.val(value[1]); }
        if (value[0]=='cancelPayedBeforeMessage') {
          cancelPayedBeforeMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_cancelPayedBeforeMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_cancelPayedBeforeMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='anonymousBeforeCount') { anonymousBeforeCount.val(value[1]); }
        if (value[0]=='anonymousBeforeUnit') { anonymousBeforeUnit.val(value[1]); }
        if (value[0]=='anonymousBeforeMessage') {
          anonymousBeforeMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_anonymousBeforeMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_anonymousBeforeMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='quantity') { quantity.val(value[1]); }
        if (value[0]=='period') { period.val(value[1]); }
        if (value[0]=='type') { type.val(value[1]); }
        if (value[0]=='scope') { scope.val(value[1]); }
        if (value[0]=='quantityMessage') {
          quantityMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_quantityMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_quantityMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='otherScope') { otherScope.val(value[1]); }
        if (value[0]=='totalQuantity') { totalQuantity.val(value[1]); }
        if (value[0]=='totalQuantityPeriod') { totalQuantityPeriod.val(value[1]); }
        if (value[0]=='totalQuantityType') { totalQuantityType.val(value[1]); }
        if (value[0]=='totalQuantityMessage') {
          totalQuantityMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_totalQuantityMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_totalQuantityMessage]').attr('src','img/icon_message_empty.png');
        }
        if ((value[0]=='totalQuantityTag')&&value[1]) {
          tags = value[1].split(',');
          for (j=0;j<tags.length;j++) {
            $.ajax({ url: '{url}?action=getTag&id='+tags[j],
            }).done(function(data) {
              totalQuantityTag.tokenInput('add',{ id: data.id, name: data.name });
            });
          }
        }
        if (value[0]=='overlapQuantity') { overlapQuantity.val(value[1]); }
        if (value[0]=='overlapQuantityScope') { overlapQuantityScope.val(value[1]); }
        if ((value[0]=='overlapQuantityTag')&&value[1]) {
          tags = value[1].split(',');
          for (j=0;j<tags.length;j++) {
            $.ajax({ url: '{url}?action=getTag&id='+tags[j],
            }).done(function(data) {
              overlapQuantityTag.tokenInput('add',{ id: data.id, name: data.name });
            });
          }
        }
        if (value[0]=='overlapQuantityMessage') {
          overlapQuantityMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_overlapQuantityMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_overlapQuantityMessage]').attr('src','img/icon_message_empty.png');
        }
        if ((value[0]=='event')&&value[1]) {
          events = value[1].split(',');
          for (j=0;j<events.length;j++) {
            $.ajax({ url: '{url}?action=getEvent&id='+events[j],
            }).done(function(data) {
              event.tokenInput('add',{ id: data.id, nameWithStart: data.nameWithStart });
            });
          }
        }
        if ((value[0]=='resource')&&value[1]) {
          resources = value[1].split(',');
          for (j=0;j<resources.length;j++) {
            $.ajax({ url: '{url}?action=getResource&id='+resources[j],
            }).done(function(data) {
              resource.tokenInput('add',{ id: data.id, name: data.name });
            });
          }
        }
        if (value[0]=='eventExists') { eventExists.val(value[1]); }
        if (value[0]=='resourceExists') { resourceExists.val(value[1]); }
        if (value[0]=='eventPayed') { eventPayed.val(value[1]); }
        if (value[0]=='resourcePayed') { resourcePayed.val(value[1]); }
        if (value[0]=='eventAll') { eventAll.val(value[1]); }
        if (value[0]=='resourceAll') { resourceAll.val(value[1]); }
        if (value[0]=='eventMessage') {
          eventMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_eventMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_eventMessage]').attr('src','img/icon_message_empty.png');
        }
        if (value[0]=='resourceMessage') {
          resourceMessage.val(value[1]);
          if (value[1]) $('#fi_newCondition_form img[target=editCondition_resourceMessage]').attr('src','img/icon_message.png');
          else $('#fi_newCondition_form img[target=editCondition_resourceMessage]').attr('src','img/icon_message_empty.png');
        }
      }
      
      $('#fi_newCondition_form').dialog('open');
      
      return false;
    });
    
    $('#fi_newConditionMessage_form').dialog({
        autoOpen: false,
        height: 220,
        width: 500,
        modal: true,
        buttons: {
            '{__button.editReservationCondition_conditionSave}': function() {
              var target = $('#editConditionMessage_target').val();
              $('#'+target).val($('#editConditionMessage_text').val());

              if ($('#editConditionMessage_text').val()) $('#fi_newCondition_form img[target='+target+']').attr('src','img/icon_message.png');
              else $('#fi_newCondition_form img[target='+target+']').attr('src','img/icon_message_empty.png');
              
              $(this).dialog('close'); 
            },
            '{__button.editReservationCondition_conditionCancel}': function() {
              $(this).dialog('close');
            }
        },
    });
    
    $('.messageIcon').click(function() {
      var target = $(this).attr('target');
      $('#editConditionMessage_target').val(target);
      $('#editConditionMessage_text').val($('#'+target).val());
      
      $('#fi_newConditionMessage_form').dialog('open');
    });
});