$(function() {
    var id = $('#editSeason_id'),
        index = $('#editSeason_index'),
        name = $('#editSeason_name'),
        start = $('#editSeason_start'),
        end = $('#editSeason_end'),
        basePrice = $('#editSeason_basePrice'),
        monPrice = $('#editSeason_monPrice'),
        tuePrice = $('#editSeason_tuePrice'),
        wedPrice = $('#editSeason_wedPrice'),
        thuPrice = $('#editSeason_thuPrice'),
        friPrice = $('#editSeason_friPrice'),
        satPrice = $('#editSeason_satPrice'),
        sunPrice = $('#editSeason_sunPrice'),
        allFields = $([]).add(id).add(name).add(start).add(end).add(basePrice).add(monPrice).add(tuePrice).add(wedPrice).add(thuPrice).add(friPrice).add(satPrice).add(sunPrice);
        
        tips = $('.validateTips');
        
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
    
    var dayPrice = $('#editDayPrice_price');
    
    start.datetimepicker({format:'d.m.',dayOfWeekStart:'1',timepicker:false});
    end.datetimepicker({format:'d.m.',dayOfWeekStart:'1',timepicker:false});
    
    $('#fi_newSeason_form').dialog({
        autoOpen: false,
        height: 340,
        width: 870,
        modal: true,
        buttons: {
            '{__button.editPriceList_seasonSave}': function() {
                var valid = true;
                
                allFields.removeClass('ui-state-error');
                valid = valid && checkLength(name, '{__label.editPriceList_seasonName}', 1, 255);
                valid = valid && checkLength(start, '{__label.editPriceList_seasonFrom}', 1, 255);
                valid = valid && checkLength(end, '{__label.editPriceList_seasonTo}', 1, 255);
                valid = valid && checkLength(basePrice, '{__label.editPriceList_seasonBasePrice}', 1, 5);
                
                if (valid) {
                    var html =
                            '<td>' + name.val() + '</td>' + '<td>' + start.val() + ' - ' + end.val() + '</td>' +
                            '<td>[<a href="#" id="fi_seasonEdit">{__button.grid_edit}</a>][<a href="#" id="fi_seasonRemove">{__button.grid_remove}]</a></td>';
                    if (index.val()) {
                      i = index.val();
                    } else {
                      i = Math.floor(Math.random()*10000);
                    }
                    input = '<input type="hidden" name="newSeason['+ i +']" value="seasonId~'+id.val()+';name~'+name.val()+';start~'+start.val()+';end~'+end.val()+';basePrice~'+basePrice.val()+
                            ';monPrice~'+monPrice.val().replace(/\"/g,'&quot;')+';tuePrice~'+tuePrice.val().replace(/\"/g,'&quot;')+';wedPrice~'+wedPrice.val().replace(/\"/g,'&quot;')+
                            ';thuPrice~'+thuPrice.val().replace(/\"/g,'&quot;')+';friPrice~'+friPrice.val().replace(/\"/g,'&quot;')+';satPrice~'+satPrice.val().replace(/\"/g,'&quot;')+';sunPrice~'+sunPrice.val().replace(/\"/g,'&quot;')
                            +'"/>';
                    html += input;

                    if (index.val()) {
                      $('#fi_seasonTable tbody').find('tr#'+index.val()).html(html);
                    } else {
                      $('#fi_seasonTable tbody').append('<tr id="'+i+'" db_id="">'+html+'</tr>');
                    }
                    
                    $(this).dialog('close');
                }
            },
            '{__button.editPriceList_dayPrice}': function() {
                $('#fi_dayPrice_form').dialog('open');
            },
            '{__button.editPriceList_seasonCancel}': function() {
                $(this).dialog('close');
            }
        },
        open: function() {
          if (!monPrice.val()) { resetPriceDay('mon'); }
          if (!tuePrice.val()) { resetPriceDay('tue'); }
          if (!wedPrice.val()) { resetPriceDay('wed'); }
          if (!thuPrice.val()) { resetPriceDay('thu'); }
          if (!friPrice.val()) { resetPriceDay('fri'); }
          if (!satPrice.val()) { resetPriceDay('sat'); }
          if (!sunPrice.val()) { resetPriceDay('sun'); }
          
          renderPriceDay('mon');
          renderPriceDay('tue');
          renderPriceDay('wed');
          renderPriceDay('thu');
          renderPriceDay('fri');
          renderPriceDay('sat');
          renderPriceDay('sun');
        },
        close: function() {
          index.val('');
          allFields.val('').removeClass('ui-state-error');
          $('td.default').html('');
        }
    });
    
    $('#fi_addSeason').click(function() {
      $('#fi_newSeason_form').dialog('open');
    });
    
    $('#editPriceList').on('click','#fi_seasonRemove', function() {
      $(this).closest('tr').remove();
      return false;
    });
    
    $('#editPriceList').on('click','#fi_seasonEdit', function() {
      var tr = $(this).closest('tr');
      index.val(tr.attr('id'));
      id.val(tr.attr('db_id'));
      var input = tr.find('input');
      var values = input.val().split(';');
      for (i=0;i<values.length;i++) {
        value = values[i].split('~');
        if (value[0]=='name') { name.val(value[1]); }
        if (value[0]=='start') { start.val(value[1]); }
        if (value[0]=='end') { end.val(value[1]); }
        if (value[0]=='basePrice') { basePrice.val(value[1]); $('td.default').html(value[1]); }
        if (value[0]=='monPrice') { monPrice.val(value[1].replace(/&quot;/g,'"')); }
        if (value[0]=='tuePrice') { tuePrice.val(value[1].replace(/&quot;/g,'"')); }
        if (value[0]=='wedPrice') { wedPrice.val(value[1].replace(/&quot;/g,'"')); }
        if (value[0]=='thuPrice') { thuPrice.val(value[1].replace(/&quot;/g,'"')); }
        if (value[0]=='friPrice') { friPrice.val(value[1].replace(/&quot;/g,'"')); }
        if (value[0]=='satPrice') { satPrice.val(value[1].replace(/&quot;/g,'"')); }
        if (value[0]=='sunPrice') { sunPrice.val(value[1].replace(/&quot;/g,'"')); }
      }
      
      $('#fi_newSeason_form').dialog('open');
      
      return false;
    });
    
    $('#editSeason_basePrice').change(function() {
      $('td.default').html($('#editSeason_basePrice').val());
    });
    
    $('#fi_dayPrice_form').dialog({
        autoOpen: false,
        height: 300,
        width: 500,
        modal: true,
        buttons: {
            '{__button.editPriceList_daySave}': function() {
                var valid = true;
                
                if (!dayPrice.val()) { alert('{__error.editPriceList_dayPriceMissingPrice}'); valid = false; }
                
                if (valid) {
                  if ($('#editDayPrice_mon').is(':checked')) { addPriceToDay('mon',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                  if ($('#editDayPrice_tue').is(':checked')) { addPriceToDay('tue',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                  if ($('#editDayPrice_wed').is(':checked')) { addPriceToDay('wed',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                  if ($('#editDayPrice_thu').is(':checked')) { addPriceToDay('thu',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                  if ($('#editDayPrice_fri').is(':checked')) { addPriceToDay('fri',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                  if ($('#editDayPrice_sat').is(':checked')) { addPriceToDay('sat',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                  if ($('#editDayPrice_sun').is(':checked')) { addPriceToDay('sun',dayPrice.val(),$('#slider').slider('values',0),$('#slider').slider('values',1)); }
                      
                  $(this).dialog('close');
                }
            },
            '{__button.editPriceList_dayCancel}': function() {
                $(this).dialog('close');
            }
        }
    });
    
    $('#slider').slider({
      range: true,
      min: 0,
      max: 24,
      values: [8, 16],
      slide: function( event, ui ) {
        $('#editDayPrice_hour').val(ui.values[ 0 ] + ':00 - ' + ui.values[ 1 ] + ':00');
      }
    });
    
    $('#editDayPrice_hour').val($('#slider').slider('values', 0) + ':00 - ' + $('#slider').slider('values', 1) + ':00');
    
    function resetPriceDay(day) {
      $('#editSeason_'+day+'Price').val('[{"from":0,"to":24,"default":1}]');
    }
    
    function addPriceToDay(day,price,from,to) {
      var pricesInput = $('#editSeason_'+day+'Price');
      var prices = $.parseJSON(pricesInput.val());
    
      // udelam "misto" na novou cenu
      var position = 0;
      for (var i=0;i<prices.length;i++) {
        if (prices[i].to>from) {
          // najdu cenu, za kterou se ma nova vlozit
          position = i;
          
          if (prices[i].to>to) {
            // kdyz je existujici cena delsi nez vkladana, rozdelim existujici (vlozim cenu "za" novou)
            prices.splice(i+1,0,{'from':to,'to':prices[i].to,'price':prices[i].price,'default':prices[i].default});
          } else {
            // jinak musim upravit vsechny nasledujici ceny
            for (var j=i+1;j<prices.length;j++) {
              if ((prices[j].from<to)&&(prices[j].to<to)) {
                // smazu uplne prekryvajici ceny
                prices.splice(j,1);
                j--;
              } else if (prices[j].to>to) {
                // zkratim zacatek castecne prekyvajici a dalsi resit nemusim
                prices[j].from = to;
                
                break;
              }           
            }
          }
          
          // nakonec zkratim existujici cenu
          prices[i].to = from;
          if (prices[i].to==prices[i].from) {
            // kdyz zkracena cena nema smysl
            prices.splice(i,1);
            position--;
          }
          
          break;
        }
      }
      // vlozim novou cenu
      prices.splice(position+1,0,{'from':from,'to':to,'price':price,'default':0});
      //alert(JSON.stringify(prices));
      
      pricesInput.val(JSON.stringify(prices));
      
      renderPriceDay(day);
    }
    
    function renderPriceDay(day) {
      var output = '';
      
      var tr = $('#editSeason_'+day);
      var pricesInput = $('#editSeason_'+day+'Price');
      var prices = $.parseJSON(pricesInput.val());
      
      // nactu label radku
      output += '<td class="label">'+tr.find('td:first').html()+'</td>';
      
      // vlozim jednotlive ceny
      if (prices.length) {
        for (var i=0;i<prices.length;i++) {
          var cssClass = (prices[i].default)?' default':'';
          output += '<td class="priceSlot'+cssClass+'" colspan="'+(prices[i].to-prices[i].from)+'">'+(prices[i].default?basePrice.val():prices[i].price)+'</td>';
        }
      } else {
        output += '<td class="priceSlot default" colspan="24">'+basePrice.val()+'</td>';
      }
      
      // vlozim tlacitko na odstraneni cen
      output += '<td><a href="#" id="fi_dayPriceRemove" day="'+day+'">X</a></td>';
      
      tr.html(output);
    }
    
    $('#fi_newSeason_form').on('click','#fi_dayPriceRemove', function() {

      var day = $(this).attr('day');
      
      resetPriceDay(day);
      renderPriceDay(day);
    });
});