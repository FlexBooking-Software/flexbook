function flbValidateCookieSession() {
  ret = false;

  $.ajax({
    type: 'GET',
    async: false,
    dataType: 'json',
    data: { provider: $('#flb_core_provider').val(), sessid: $.cookie('flb_core_sessionid') },
    url: $('#flb_core_url').val()+'action=validateSession',
    success: function(data) {
      ret = data;
    },
    error: function(jqXHR, jqTextStatus, jqException) {
      flbAjaxParseError(jqXHR, jqTextStatus, jqException);
    }
  });

  return ret;
}

function flbInitMisc() {
  
}

function flbInitLogin() {
  if ($.cookie&&$.cookie('flb_core_sessionid')) {
    if (!flbValidateCookieSession()) {
      $.removeCookie('flb_core_sessionid', { path: '/' });
      $.removeCookie('flb_core_userid', { path: '/' });
      $.removeCookie('flb_core_username', { path: '/' });
      $.removeCookie('flb_core_useremail', { path: '/' });
    }

    if ($.cookie('flb_core_sessionid')) {
      $('#flb_core_sessionid').val($.cookie('flb_core_sessionid'));
      $('#flb_core_userid').val($.cookie('flb_core_userid'));
      $('#flb_core_username').val($.cookie('flb_core_username'));
      $('#flb_core_useremail').val($.cookie('flb_core_useremail'));
    }
  }
}

function flbInitElements(elements) {
  if (elements instanceof Array) {
    var index = 0;
    for (index=0;index<elements.length;++index) {
      window[elements[index].type](elements[index].placeHolder, elements[index].params);
    }
  }
}

function flbLoadHtml(gui,domElement,params) {
  domElement.css({'cursor' : 'wait'});

  if (!params) { params = {}; }
  
  var urlParams = $('#flb_core_url').val()+'action='+gui;
  
  $.ajax({
      type: 'GET',
      dataType: 'json',
      data: $.extend(params, { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val() }),
      url: urlParams,
      success: function(data) {
        if (data.error) alert(data.message);
        else {
          domElement.html(data.output);
          if (data.popup) alert(data.popup);
        }

        domElement.css({'cursor' : 'default'});
      },
      error: function(jqXHR, jqTextStatus, jqException) {
        domElement.css({'cursor' : 'default'});

        flbAjaxParseError(jqXHR, jqTextStatus, jqException);
      }
  });
}

function flbRefresh(selector,exception) {
  $(selector).each(function() {
    if (!exception||(this.id!=exception)) {
      var type = $(this).find('#flb_guiType');
      if (type.length) {
        var domElement = $(this).parent();
        var gui = $(this).find('#flb_guiType')[0].value;
        var params = JSON.parse($(this).find('#flb_guiParams')[0].value);

        if (params.hideOnRefresh) domElement.hide();
        else flbLoadHtml(gui,domElement,params);
      }
    }
  });
}

function flbLoginRequired(language) {
  $('#flb_core_not_logged').html('');

  params = {};
  params['parentNode'] = 'flb_core';
  params['showNotLoggedTitle'] = 1;
  params['hideOnRefresh'] = 1;
  params['buttons'] = ['close','login','sendPassword','registration'];
  params['externalAccount'] = $('#flb_core_not_logged_use_externalaccount').val();
  params['externalAccountFirst'] = $('#flb_core_not_logged_use_externalaccount').val();
  if (language) params['language'] = language;

  flbLoadHtml('guiProfile',$('#flb_core_not_logged'),params);
  $('#flb_core_not_logged').show();
}

function flbLoginUser(userid, username, useremail, sessionid) {
  $('#flb_core_userid').val(userid);
  $('#flb_core_username').val(username);
  $('#flb_core_useremail').val(useremail);
  $('#flb_core_sessionid').val(sessionid);
  
  if ($.cookie) {
    $.cookie('flb_core_userid', userid, { expires: 3, path: '/' });
    $.cookie('flb_core_username', username, { expires: 3, path: '/' });
    $.cookie('flb_core_useremail', useremail, { expires: 3, path: '/' });
    $.cookie('flb_core_sessionid', sessionid, { expires: 3, path: '/' });
  }                      

  flbRefresh('.flb_output');
  
  $('.flb_cal_core').each(function() {
    $('#'+this.id).fullCalendar('removeEventSource', window.calSource[this.id]);
    window.calSource[this.id].data.sessid = sessionid;
    $('#'+this.id).fullCalendar('addEventSource', window.calSource[this.id]);
  });
}

function flbProfile(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;
  
  flbLoadHtml('guiProfile',$('#'+selector),params);
};

function flbEventList(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;
  
  flbLoadHtml('guiEventList',$('#'+selector),params);
};

function flbEventWeekList(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;

  flbLoadHtml('guiEventWeekList',$('#'+selector),params);
};

function flbEventDetail(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;
  
  flbLoadHtml('guiEventDetail',$('#'+selector),params);
};

function flbResourceList(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;
  params['backButton'] = 1;
  if (!params['clickAction']) params['clickAction'] = 'Calendar';
  
  flbLoadHtml('guiResourceList',$('#'+selector),params);
};

function flbResourceDetail(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;
  
  flbLoadHtml('guiResourceDetail',$('#'+selector),params);
};

function flbResourceCalendar(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;
  
  flbLoadHtml('guiResourceCalendar',$('#'+selector),params);
}

function flbResourceAvailability(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;

  flbLoadHtml('guiResourceAvailability',$('#'+selector),params);
}

function flbResourcePoolAvailability(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;

  flbLoadHtml('guiResourcePoolAvailability',$('#'+selector),params);
}

function flbEventCalendar(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;

  flbLoadHtml('guiEventCalendar',$('#'+selector),params);
}

function flbReservationList(selector,params) {
  if (!params) { params = {}; }
  params['parentNode'] = selector;

  flbLoadHtml('guiReservationList',$('#'+selector),params);
}

function flbPaymentGatewayMessageListener(e) {
  if (e.data.status==0) {
    if (e.data.action) eval(e.data.action);
  }
  if (e.data.message) alert(e.data.message);
}

function flbPaymentGateway(url,gateway,target,targetId,targetParams,placeHolder,jsBackAction,failText) {
  var w = 900;
  var h = 650;
  
  var left = (screen.width/2)-(w/2);
  var top = (screen.height/2)-(h/2);
  var simpleUrl = url;
  if (target) url = url+'&target='+target;
  if (targetId) url = url+'&targetid='+targetId;
  if (targetParams) url = url+'&targetparams='+encodeURIComponent(targetParams);
  if (gateway) url = url+'&gateway='+gateway;
  if (placeHolder) url = url+'&placeholder='+encodeURIComponent(placeHolder);
  if (jsBackAction) url = url+'&jsbackaction='+encodeURIComponent(jsBackAction);
  
  var newWindow = window.open(url,'_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
  if (newWindow) {
  //if (false) {
    console.log('new window created');
    //newWindow.location.href = 'about:blank';
    //newWindow.location.href = url;
    if (window.addEventListener) {
      window.addEventListener('message', flbPaymentGatewayMessageListener, false);
    } else {
      window.attachEvent('message', flbPaymentGatewayMessageListener);
    }
  } else {
    simpleUrl = simpleUrl.substring(0,simpleUrl.search('&'));
    if (!failText) failText = 'Online payment initialization failed, possibly pop-ups are blocked. Use link bellow to process online payment:';
    var newDiv = '<script>'+jsBackAction+'</script><div id="flb_core_alert" class="flb_output flb_profile_extra">'+
              '  <div class="title">'+failText+'</div>'+
              '  <div class="paymentUrl"><a target="_blank" href="'+url+'">' + simpleUrl + '</a></div>'+
              '  <input type="button" value="OK" onclick="$(this).parent().remove();"/>'+
              '</div>';
    $(newDiv).appendTo('#flb_core_envelope');

    console.log('new window creation failed...');
    console.log('url: '+url);
    console.log('placeHolder: '+placeHolder);
    console.log('backAction: '+jsBackAction);
  }
}

function flbExternalAccountMessageListener(e) {
  if (e.data.status==-1) alert(e.data.message);
  else if (e.data.status==0) {
    if (e.data.type=='login') flbLoginUser(e.data.userId,e.data.userName,e.data.userEmail,e.data.sessId);
    else if (e.data.type=='assign') {
      $('#'+e.data.placeHolder).val(e.data.accountId);
      displayAccountIcon();
    }
  }
}

function flbExternalAccount(url,type,placeHolder) {
  var w = 900;
  var h = 650;
  
  var left = (screen.width/2)-(w/2);
  var top = (screen.height/2)-(h/2);
  url = url + '&type='+type;
  url = url + '&sessid='+$('#flb_core_sessionid').val();
  url = url + '&provider='+$('#flb_core_provider').val();
  url = url + '&jsreturn=1';
  url = url + '&jsplaceholder='+placeHolder;
  //alert(url);return;
  
  // fake pro IE10, aby fungovalo postMessage pro cross-domain
  // normalne by stacil ten zakomentovanej radek
  //window.open(url,'_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
  var newWindow = window.open('/','_blank','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
  newWindow.location.href = 'about:blank';
  newWindow.location.href = url;
  if (window.addEventListener) {
    window.addEventListener('message', flbExternalAccountMessageListener, false);
  } else {
    window.attachEvent('message', flbExternalAccountMessageListener);
  }
}

function flbAjaxParseError(jqXHR, jqTextStatus, jqException) {
  if (jqXHR.getAllResponseHeaders()) {
  //if (true) {
    if (jqXHR.status === 0) {
      alert('Not connected. Verify Network.');
    } else if (jqXHR.status == 404) {
      alert('Requested page not found. [404]');
    } else if (jqXHR.status == 500) {
      alert('Internal Server Error. [500].');
    } else if (jqTextStatus === 'parsererror') {
      alert('Internal Server Error.');
    } else if (jqTextStatus === 'timeout') {
      alert('Time out error.');
    } else if (jqTextStatus === 'abort') {
      alert('Ajax request aborted.');
    } else {
      alert('Uncaught Error: ' + jqXHR.responseText);
    }
  }
}

function getUserDetail(params) {
  ret = { firstname:'',lastname:'',email:'',phone:'',credit:0,ticket:[] };
  
  if (params.user) {
    var data = { provider: $('#flb_core_provider').val(), sessid: $('#flb_core_sessionid').val(), user: params.user }
    if (params.resource) data.resource = params.resource;
    if (params.event) data.event = params.event;
    if (params.price) data.price = params.price;
    
    $.ajax({
        type: 'GET',
        url: $('#flb_core_url').val()+'action=getUserDetail',
        data: data,
        dataType: 'json',
        async: false,
        success: function(data) {
            if (data.error) {
                ret = false;
                alert(data.message);
            } else {
                ret = data;   
            }
        },
        error: function(jqXHR, jqTextStatus, jqException) { flbAjaxParseError(jqXHR, jqTextStatus, jqException); },
    });
  }
  
  return ret;
}

function fitToWindow(elementId) {
  console.log('Popup window arrangement for: '+elementId);
  var element = $('#'+elementId);
  if (element.length) {
    var elementTop = element.offset().top;
    var elementBottom = elementTop + element.outerHeight();

    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();

    if (elementBottom > viewportBottom) {
      console.log('Popup window arrangement fired..');

      var diff = elementBottom - viewportBottom + 10;
      element.height(element.height() - diff);
    }
  }
}