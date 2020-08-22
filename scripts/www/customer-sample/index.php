<?php
  require(dirname(__FILE__).'/../../config.php');
  
  mysql_connect($DB['server'],$DB['user'],$DB['password']);
  mysql_select_db($DB['database']);
  mysql_query('SET NAMES latin2');
  $res = mysql_query(sprintf('select provider_id from provider where short_name="%s"', $_GET['id']));
  $row = mysql_fetch_assoc($res);
  $id = $row['provider_id'];
  if ($id == 1) {
    $multicalendar = "{ type: 'flbResourceCalendar',
                        placeHolder: 'f_5',
                        params: { 
                                   XresourceId: [162,163,164], XresourceAssetId: ['Z-1','Z-2'],
                                   resourcePoolAssetId: 'PRVNI',
                                   resourceLabel: 'Nemovitosti', ratio: 1.3, 
                                   timeSlot: '30', XtimeMin: '13:00', XtimeMax: '18:0', showToday: false,
                                   render: ['reservation','occupied','event'], XdisableResourceReservation: true,
                                   XdateStart: '2016-02-22', dateMin: '2016-03-01', XdateMax: '2016-06-30'
                                }
                      },";
  } else {
    $multicalendar = '';
  }
  
  echo sprintf('
      <html>
      <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8" />
      <link rel="stylesheet" type="text/css" href="customer.css" />
      <script src="%s/jq/jquery.js"></script>
      <script src="%s/jq/moment.min.js"></script>
      <script type="text/javascript" src="%s/jq/jquery.form.js"></script>
      <script type="text/javascript" src="%s/jq/jquery-ui.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/jq/jquery-ui.css" />
      <script type="text/javascript" src="%s/jq/jquery.datetimepicker.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/jq/jquery.datetimepicker.css" />
      <script type="text/javascript" src="%s/jq/fullcalendar-2.6.0.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/jq/fullcalendar-2.6.0.css" />
      <script type="text/javascript" src="%s/jq/lang-all.js"></script>
      <script type="text/javascript" src="%s/jq/scheduler-1.2.0.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/jq/scheduler-1.2.0.css" />
      <script type="text/javascript" src="%s/jq/daterangepicker.min.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/jq/daterangepicker.css" />
      <script type="text/javascript" src="%s/jq/jquery.cookie.js"></script>
      <script type="text/javascript" src="%s/jq/jquery.uploadfile.js"></script>
      <script type="text/javascript" src="%s/jq/intlTelInput.min.js"></script>
      <script type="text/javascript" src="%s/jq/intlTelInputUtils.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/jq/intlTelInput.css" />
      <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.3/fotorama.js"></script>
      <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.3/fotorama.css" />
      <script src="%s/flb.js"></script>
      <link rel="stylesheet" type="text/css" href="%s/flb.css" />
      </head>
      <body>
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
        <div id="flexbook_5">
          Vice-zdrojovy kalendar
          <div id="f_5"></div>
        </div>
        <script>        
          flbInit(\'%s\', %d, [
              { type: \'flbEventList\',
                placeHolder: \'f_1\',
                params: { Xcount:5, XdateMin: \'2016-03-01\', XdateMax: \'2017-03-31\', 
                          language: \'cz\',
                          format: { datetime: \'l j.F H:i\' },
                          XrenderText: [\'name\',\'center\',\'description\'],
                },
              },
              { type: \'flbEventList\',
                placeHolder: \'f_2\',
                params: { renderText: [\'name\',\'center\',\'description\',\'resource\',\'organiser\',\'start\',\'end\',\'price\',\'attribute\',\'attendees\',\'reservation\'],
                          eventTemplate: \'@@EVENT_NAME @@EVENT_START @@EVENT_RESOURCE\',
                          attendeeTemplate: \'@@FIRSTNAME\',
                          showAttendeePayment: 1,
                          eventResourcePrefix: \'- (\', eventResourcePostfix: \')\', 
                          XdateNowPlusXDays: 14, 
                          language: \'en\',
                          Xresource: 519,
                          Xweekday: \'tue\',
                          format: { Xdatetime: \'d/m/y H:i\', Xdate: \'d/m/y\' }},
              },
              { type: \'flbResourceList\',
                placeHolder: \'f_3\',
                params: { Xcount: 1, clickAction: \'Calendar\',
                          render: [\'reservation\',\'event\',\'occupied\'],
                          XresourceTemplate: \'@@RESOURCE_NAME - @@RESOURCE_PRICE - @@RESOURCE_ATTRIBUTE(barva)\',
                          renderText: [\'name\',\'description\',\'price\',\'attribute\'],
                          XtimeMin: \'07:30\', XtimeMax: \'19:00\', XdateMin: \'2018-09-01\', XdateMax: \'2018-09-31\', XdateStart: \'2018-09-22\',
                          format: { datetime: \'d.m. H:i\', time: \'H:i\' } },
              },
              { type: \'flbProfile\',
                placeHolder: \'f_6\',
                params: { Xcenter: [2], format: { datetime: \'d.m. Y H:i\', date: \'d.m. Y\', time: \'H:i\' }, extraDiv: \'all\', language: \'cz\', externalAccount: 1,
                          buttons: [\'login\',\'sendPassword\',\'registration\',\'reservation\',\'profile\',\'password\',\'logout\',\'credit\'],
                          checkAttributeMandatory: \'0\', \'loggedTemplate\': \'<span>@@USER_NAME (@@USER_EMAIL)</span>\'
                },
              },
              { type: \'flbProfile\',
                placeHolder: \'f_4\',
                params: { format: { datetime: \'d.m. Y H:i\', date: \'d.m. Y\', time: \'H:i\' }, extraDiv: 1, language: \'en\', externalAccount: 1,
                          buttons: [\'login\',\'sendPassword\',\'registration\',\'reservation\',\'profile\',\'password\',\'logout\',\'credit\'],
                          checkAttributeMandatory: \'1\',
                },
              },
              { type: \'flbResourceAvailability\',
                placeHolder: \'f_7\',
                params: { language: \'cz\', tag: [\'byt\',\'zahrada\',\'chalupa\'], XresourceDetailTemplate: [\'name\',\'attribute\'], XshowAttribute: [\'Lůžka\'] },
              },
              %s
              ]);
          
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
        </script>
      </body>',
      dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']),
      dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']),
      dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']), dirname($AJAX['url']),
      dirname($AJAX['url']), dirname($AJAX['url']),
      dirname($AJAX['url']), $AJAX['url'], $id, $multicalendar);
?>
