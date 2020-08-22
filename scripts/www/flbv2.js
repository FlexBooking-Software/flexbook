var waitForFlexbook = function(callback) {
  if ($('#flb_core_envelope #flb_initialized').length) {
    callback();
  } else {
    setTimeout(function() {
      waitForFlexbook(callback);
    }, 100);
  }
};

function flbInit(urlPath,provider,elements,options) {
  var initElements = elements instanceof Array;
  var externalAccount = 1;

  if (options !== undefined) {
    if (options.externalAccount !== undefined) externalAccount = options.externalAccount;
  }

  var div = $('#flb_core_envelope');
  if (!div.length) {
    div = $('<div id="flb_core_envelope" />').appendTo('body');
    $.ajax({
      dataType: 'json',
      data: { externalAccount: externalAccount },
      url: urlPath+'?action=guiCore&provider='+provider,
      success: function(result) {
        div.html(result.output);

        $.ajax({ url: urlPath+'?action=getVersion', success: function(result) { version = result; }, async: false });
        if (version) {
          $.ajax({ url: $('#flb_core_url_path').val()+'jq/moment.min.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
            $.ajax({ url: $('#flb_core_url_path').val()+'jq/jquery.form.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
              $.ajax({ url: $('#flb_core_url_path').val()+'jq/jquery-ui.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                $.ajax({ url: $('#flb_core_url_path').val()+'jq/jquery.datetimepicker.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                  $.ajax({ url: $('#flb_core_url_path').val()+'jq/jquery.ui.combogrid.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                    $.ajax({ url: $('#flb_core_url_path').val()+'jq/fullcalendar-3.10.0.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                      $.ajax({ url: $('#flb_core_url_path').val()+'jq/scheduler-1.9.4.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                        $.ajax({ url: $('#flb_core_url_path').val()+'jq/daterangepicker.min.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                          $.ajax({ url: $('#flb_core_url_path').val()+'jq/locale-all.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                            $.ajax({ url: $('#flb_core_url_path').val()+'jq/intlTelInput.min.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                              $.ajax({ url: $('#flb_core_url_path').val()+'jq/intlTelInputUtils.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                                $.ajax({ url: $('#flb_core_url_path').val()+'jq/jquery.cookie.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                                  $.ajax({ url: $('#flb_core_url_path').val()+'jq/jquery.uploadfile.js?version='+version, dataType: 'script', async: false, cache: true, success: function(result) {
                                    $.ajax({ url: 'https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.3/fotorama.js', dataType: 'script', async: false, cache: true, success: function(result) {
                                      $.ajax({
                                        url: $('#flb_core_url_path').val() + 'flbv2fn.js?version=' + version,
                                        dataType: 'script',
                                        async: false,
                                        cache: true,
                                        success: function (result) {
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + '/jq/jquery-ui.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + '/jq/jquery.datetimepicker.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + 'jq/jquery.ui.combogrid.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + '/jq/fullcalendar-3.10.0.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + '/jq/scheduler-1.9.4.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + '/jq/daterangepicker.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + '/jq/intlTelInput.css?version=' + version + '" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="https://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.3/fotorama.css" type="text/css" rel="stylesheet" />');
                                          $('head').append('<link href="' + $('#flb_core_url_path').val() + 'flb.css?version=' + version + '" type="text/css" rel="stylesheet" />');

                                          flbInitLogin();
                                          flbInitMisc();

                                          $('<input type="hidden" id="flb_initialized" value="1" />').appendTo('#flb_core_envelope');

                                          if (initElements) flbInitElements(elements);
                                        }
                                      });
                                    }});
                                  }});
                                }});
                              }});
                            }});
                          }});
                        }});
                      }});
                    }});
                  }});
                }});
              }});
            }});
          }});
        }
      },
      async: initElements,
    });
  } else {
    waitForFlexbook(function() {
      if (initElements) flbInitElements(elements);
    });
  }
}