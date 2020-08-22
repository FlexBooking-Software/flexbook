<?php
  require(dirname(__FILE__).'/../../config.php');
  
  $c = mysqli_connect($DB['server'],$DB['user'],$DB['password']);
  mysqli_select_db($c, $DB['database']);
  mysqli_query($c, 'SET NAMES latin2');
  $res = mysqli_query($c, sprintf('select provider_id from provider where short_name="%s"', $_GET['id']));
  $row = mysqli_fetch_assoc($res);
  $id = $row['provider_id'];
  if ($id == 1) {
    $multiCalendarHtml = '<div id="flexbook_8">
          Vice-zdrojovy kalendar varianta #1
          <div id="f_8"></div>
        </div>
        <div id="flexbook_5">
          Vice-zdrojovy kalendar varianta #2
          <div id="f_5"></div>
        </div>';
    $multicalendarJs = "{ type: 'flbResourceCalendar',
                        placeHolder: 'f_5',
                        params: { 
                                   XresourceId: [162,163,164], XresourceAssetId: ['Z-1','Z-2'],
                                   resourcePoolAssetId: 'PREDNASKA',
                                   resourceLabel: 'Nemovitosti', ratio: 1.3, 
                                   timeSlot: '30', XtimeMin: '13:00', XtimeMax: '18:0', showToday: false,
                                   render: ['reservation','occupied','event'], XdisableResourceReservation: true,
                                   XdateStart: '2016-02-22', dateMin: '2016-03-01', XdateMax: '2016-06-30'
                                }
                      },
                      { type: 'flbResourceCalendar',
                        placeHolder: 'f_8',
                        params: { 
                                   resourceId: [974,975,976,977,978], XresourceAssetId: ['Z-1','Z-2'],
                                   resourceLabel: 'Nemovitosti', ratio: 1.3, 
                                   organiser: 'loggedInUser', view: 'day',
                                   timeSlot: '10', XtimeMin: '13:00', XtimeMax: '18:0', showToday: false,
                                   render: ['reservation','occupied','event'], XdisableResourceReservation: true,
                                   XdateStart: '2016-02-22', dateMin: '2016-03-01', XdateMax: '2016-06-30'
                                }
                      },";
  } else {
    $multiCalendarHtml = '';
    $multicalendarJs = '';
  }
  
  echo sprintf('
      <html>
      <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="customer.css" />
      <script src="%s/jq/jquery.js"></script>
      <script src="%s/flbv2.js"></script>
      </head>
        <input id="test_button_1" type="button" value="TEST_1"/>&nbsp;<input id="test_button_2" type="button" value="TEST_2"/>
        <div id="flexbook_1">
          Seznam udalosti
          <div id="f_1"></div>
        </div>
        <div id="flexbook_2">
          Jiny seznam udalosti
          <div id="f_2"></div>
        </div>
        <div id="flexbook_3">
          Seznam zdroju
          <div id="f_3"></div>
        </div>
        <div id="flexbook_6">
          Mini Profil
          <div id="f_6"></div>
        </div>
        <div id="flexbook_4">
          Profil
          <div id="f_4"></div>
        </div>
        <div id="flexbook_7">
          Vyhledavani zdroju
          <div id="f_7"></div>
        </div>
        <div id="flexbook_9">
          Vyhledavani skupin zdroju
          <div id="f_9"></div>
        </div>
        %s
        <script>
          flbInit(\'%s\', %d, [
              /*{
                 lang: \'cz\',
                 type: \'flbEventCalendar\',
                 placeHolder: \'f_1\',
                 params: {
                   organiserCanReserveOnBehalf: \'allUsers\', 
                   tag: [\'děti\'],
                   timeMin: \'07:00\', 
                   timeMax: \'18:00\',
                   view: \'month\',
                 }
               },*/
              /*{ type: \'flbReservationList\',
                placeHolder: \'f_1\',
                params: { language: \'cz\',
                          format: { datetime: \'l j.F H:i\' },
                          Xcenter: 1,
                          buttons: [\'reservationPrint\'],
                },
              },*/
              { type: \'flbEventList\',
                placeHolder: \'f_1\',
                params: { Xcount:5, XdateMin: \'2016-03-01\', XdateMax: \'2017-03-31\', 
                          language: \'cz\',
                          Xregion: [\'Karlovarský\',\'Košický\'],
                          Xorganiser: \'loggedInUser\',
                          organiserShowReservationAttendee: 1,
                          organiserCanReserveOnBehalf: \'allUsers\', 
                          //organiserCanReserveOnBehalf: { usersHavingReservationsOnEventWithTag: \'hlavni\' },  
                          //organiserCanReserveOnBehalf: { usersHavingPaidReservationsOnEventWithTag: 31428 },
                          //organiserCanReserveOnBehalfFunctionButtons: [\'attendeeToSubstitute\',\'substituteToAttendee\'],
                          //organiserCanReserveOnBehalfCustomColumn: { title: \'Custom column\', value: \'attribute_BIRTH\' },
                          format: { datetime: \'l j.F H:i\', date: \'l j.F\' },
                          renderText: [\'name\',\'photoThumb\',\'center\',\'description\',\'organiser\',\'start\',\'end\',\'cycleItem\',\'price\',\'reservation\'],
                          eventTemplate: \'@@EVENT_NAME @@EVENT_PHOTOTHUMB @@EVENT_CENTER @@EVENT_START @@EVENT_RESOURCE\',
                          cycleItemTemplate: \'@@EVENT_START - @@EVENT_END (@@EVENT_ORGANISER) @@EVENT_CENTER_REGION\',
                          onPageCount: 10,
                },
              },
              { type: \'flbEventList\',
                placeHolder: \'f_2\',
                params: { renderText: [\'name\',\'center\',\'description\',\'resource\',\'organiser\',\'start\',\'end\',\'price\',\'places\',\'attribute\',\'attendees\',\'reservation\'],
                          eventTemplate: \'@@EVENT_NAME @@EVENT_START @@EVENT_RESOURCE\',
                          XeventCycleTemplate: \'@@EVENT_NAME @@EVENT_START @@EVENT_CENTER_REGION\',
                          attendeeTemplate: \'@@FIRSTNAME\',
                          showAttendeePayment: 1,
                          eventResourcePrefix: \'- (\', eventResourcePostfix: \')\', 
                          XdateNowPlusXDays: 14, 
                          language: \'en\',
                          Xresource: 519,
                          Xweekday: \'tue\',
                          tag: [\'angličtina\'],
                          XtagOperator: \'aNd\',
                          format: { Xdatetime: \'d/m/y H:i\', Xdate: \'d/m/y\' }},
              },
              /*{ type: \'flbEventWeekList\',
                placeHolder: \'f_3\',
                params: { XdateMin: \'2019-08-01\', XdateMax: \'2019-09-10\', allowPast: 0, XdateStart: \'2019-08-26\', noEventTemplate: \'Tento den nic nepořádáme.\',  showInactive: 1,
                  format: { datetime: \'d/m/y H:i\', date: \'d/m/y\', period: \'d.m.Y\', day: \'l d/m\' },
                },
              },*/
              { type: \'flbResourceList\',
                placeHolder: \'f_3\',
                params: { Xcount: 1, clickAction: \'Calendar\',
                          Xorganiser: \'loggedInUser\',
                          Xregion: [\'Karlovarský\',\'Košický\'],
                          //organiserShowReservationAttendee: 0,
                          organiserCanReserveOnBehalf: \'allUsers\', 
                          //organiserCanReserveOnBehalf: { usersHavingReservationsOnEventWithTag: \'hlavni\' },
                          //organiserCanReserveOnBehalf: { usersHavingPaidReservationsOnEventWithTag: 31428 },
                          //organiserCanReserveOnBehalfCustomColumn: { title: \'Custom column\', value: \'attribute_BIRTH\' },
                          render: [\'reservation\',\'event\',\'occupied\'],
                          XresourceTemplate: \'@@RESOURCE_NAME - @@RESOURCE_PRICE - @@RESOURCE_ATTRIBUTE(barva)\',
                          renderText: [\'name\',\'description\',\'price\',\'attribute\'],
                          XtimeMin: \'07:30\', XtimeMax: \'19:00\', XdateMin: \'2018-09-01\', XdateMax: \'2018-09-31\', XdateStart: \'2018-09-22\',
                          format: { datetime: \'d.m. H:i\', time: \'H:i\' } },
              },
              { type: \'flbProfile\',
                placeHolder: \'f_6\',
                params: { Xcenter: [2], format: { datetime: \'d.m. Y H:i\', date: \'d.m. Y\', time: \'H:i\' }, extraDiv: \'all\', language: \'cz\', externalAccount: 1,
                          Xbuttons: [\'login\',\'sendPassword\',\'registration\',\'reservation\',\'profile\',\'password\',\'logout\',\'subaccount\'],
                          checkAttributeMandatory: \'1\', \'loggedTemplate\': \'<span>@@USER_NAME (@@USER_EMAIL)</span>\'
                },
              },
              { type: \'flbProfile\',
                placeHolder: \'f_4\',
                params: { format: { datetime: \'d.m. Y H:i\', date: \'d.m. Y\', time: \'H:i\' }, extraDiv: 1, language: \'en\', externalAccount: 1,
                          Xbuttons: [\'login\',\'sendPassword\',\'registration\',\'reservation\',\'profile\',\'password\',\'logout\',\'reservationBack\'],
                          registrationAttributeMandatoryOnly: \'0\', checkAttributeMandatory: \'1\',
                },
              },
              /*{ type: \'flbReservationList\',
                placeHolder: \'f_7\',
                params: { language: \'cz\', Xcenter: 1, Xtag: [\'byt\',\'zahrada\',\'chalupa\'], XresourceDetailTemplate: [\'name\',\'attribute\'], XshowAttribute: [\'Lůžka\'],
                },
              },*/
              /*{ type: \'flbResourceCalendar\', placeHolder: \'f_7\', params: { render: [\'reservation\',\'event\',\'occupied\'],
                format: { datetime: \'d.m. H:i\', time: \'H:i\' },
                tag: [\'prednaska\'],
                renderText: [\'legend\'],
                viewDirection : \'vertical\', }, },*/ 
              { type: \'flbResourcePoolAvailability\',
                placeHolder: \'f_9\',
                params: { language: \'cz\', withTime: true, Xcenter: 1, Xtag: [\'zimni-vybava\'], XresourceDetailTemplate: [\'name\',\'attribute\'],
                 filter:[
  {
    id: \'type\',
    label: \'Vybavení\',
    type: \'select\',
    firstItem: \'nerozhoduje\',
    items: [
        {
            label: \'Lyže\',
            tag: \'lyze\',
        },
        {
            label: \'Hůlky\',
            tag: \'lyzarske-hulky\',
        },
        {
            label: \'Helmy\',
            tag: \'lyzarske-helmy\',
        }
    ],
  },
  {
    id: \'for\',
    label: \'Urcene pro\',
    type: \'select\',
    firstItem: \'vsechny\',
    items: [
        {
            label: \'pro rekreaci\',
            tag: \'amater\',
        },
        {
            label: \'pro profiky\',
            tag: \'profi\',
        },
    ],
  },
  {
    id: \'length\',
    label: \'Délka lyží\',
    type: \'select\',
    dependOn: \'type\',
    dependValue: \'lyze\',
    firstItem: \'vsechny\',
    items: [
        {
            label: \'160cm\',
            tag: \'160\',
        },
        {
            label: \'170cm\',
            tag: \'170\',
        },
        {
            label: \'180cm\',
            tag: \'180\',
        },
    ],
  },
]},
              },
              %s
              ], { externalAccount: 1 });
          
          //$(document).ready(function() {
          //  (function ($) {
          //    $.each([\'show\', \'hide\'], function (i, ev) {
          //      var el = $.fn[ev];
          //      $.fn[ev] = function () {
          //        this.trigger(ev);
          //        return el.apply(this, arguments);
          //      };
          //    });
          //  })(jQuery);
          //  
          //  $(\'#f_4\').on(\'show\', \'#f_4_flb_profile\', function() { 
          //    console.log(\'Showing registration DIV....\');
          //    
          //    $(\'#f_4_flb_registration_advertising\').attr(\'checked\', true); 
          //  });
          //});
          
          function fillOriginalInput() {
            var newValue = $(\'#f_9_flb_resource_availability #f_9_myCalendar_value\').val();
            newValue += \' - \'+moment(newValue,"DD.MM.YYYY HH:mm").add($(\'#f_9_flb_resource_availability #f_9_myLength_value\').val(),\'hours\').format("DD.MM.YYYY HH:mm");
            $(\'#f_9_flb_resource_availability #f_9_flb_resource_availability_period\').val(newValue);
          }
          
          // pokud prijde udalost flbGuiReady a je pro pozadovany DOM element
          $(window).on(\'flbGuiReady\', function (e) {
            if (e.parent==\'f_9\') {
              // schovam puvodni daterangepicker
              $(\'#f_9_flb_resource_availability_period\').data(\'daterangepicker\').remove();
              $(\'#f_9_flb_resource_availability .filterRange\').hide();
              // vlozim HTML pro novy picker/select a hidden inputy, kde budou hodnoty ulozeny
              $(\'#f_9_flb_resource_availability .flb_resource_availability_filter\').prepend(\'<div>\' +
               \'<input id="f_9_myCalendar"/><select id="f_9_myLength"><option value="">---</option><option value="1">1h</option><option value="2">2h</option><option value="3">3h</option>\' + 
               \'<input type="hidden" id="f_9_myCalendar_value"/><input type="hidden" id="f_9_myLength_value"/></div>\');
              // vlozim novy picker
              $(\'#f_9_flb_resource_availability #f_9_myCalendar\').datetimepicker({
                inline: true, 
                onChangeDateTime: function(selected) {
                  // kdyz se vybere novy datum/cas, ulozim hodnotu do hidden inputu  
                  $(\'#f_9_flb_resource_availability #f_9_myCalendar_value\').val(moment(selected).format("DD.MM.YYYY HH:mm"));
                  // a aktualizuju schovany originalni input pro vyhledavani 
                  fillOriginalInput();
                } 
              });
              $(\'#f_9_flb_resource_availability #f_9_myLength\').change(function() {
                // kdyz se zmeni delka v selectu, ulozim hodnotu do hidden inputu
                $(\'#f_9_flb_resource_availability #f_9_myLength_value\').val($(this).val());
                // a aktualizuju schovany originalni input pro vyhledavani
                fillOriginalInput();
              });
              if (origValue=$(\'#f_9_flb_resource_availability #f_9_flb_resource_availability_period\').val()) {
                // jeste musim nastavit hodnoty novych inputu podle originalniho inputu
                var date = origValue.split(\' - \');
                $(\'#f_9_flb_resource_availability #f_9_myCalendar\').datetimepicker({value:date[0],format:\'d.m.Y H:i\'});
                $(\'#f_9_flb_resource_availability #f_9_myCalendar_value\').val(date[0]);
                var diff = moment(date[1],"DD.MM.YYYY HH:mm").diff(moment(date[0],"DD.MM.YYYY HH:mm"));
                diff = moment.duration(diff);
                diff = Math.floor(diff.asHours());
                $(\'#f_9_flb_resource_availability #f_9_myLength\').val(diff);
              }
            }
          });                  
        </script>
      </body>',
      dirname($AJAX['url']), dirname($AJAX['url']), $multiCalendarHtml,
      $AJAX['url'], $id, $multicalendarJs);
?>
