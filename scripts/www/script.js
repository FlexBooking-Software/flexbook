function addClass (elementName, newClass) {
  elementName.className = newClass + (elementName.className ? ' ' : '') + elementName.className;
}

function removeClass(elementName, oldClass) {
  var classesOld = elementName.className.split(' ');
  var classesNew = new Array();
  var i;

  for (i in classesOld) {
    if (classesOld[i] != oldClass) {
      classesNew.push(classesOld[i]);
    }
  }
  elementName.className = classesNew.join(' ');
}

function mySubmit(button, nextActionVar, nextActionValue) {
  na = document.getElementById(nextActionVar);
  na.value = nextActionValue;
  b = document.getElementById(button);
  b.click();
  return false;
}

function myConfirmedSubmit(message, button, nextActionVar, nextActionValue) {
  if (confirm(message)) {
    mySubmit(button, nextActionVar, nextActionValue);
  }
  return false;
}

var g_iCavTimer;
var g_CarEle = null;
var g_iCavDivLeft;
var g_iCavDivTop;

function setMyTitleTimer(evt) {
  var e = (window.event) ? window.event : evt;
  var src = (e.srcElement) ? e.srcElement : e.target;

  var scrollTop;
  if (document.documentElement) scrollTop = document.documentElement.scrollTop;
  else scrollTop = document.body.scrollTop;
  var scrollLeft;
  if (document.documentElement) scrollLeft = document.documentElement.scrollLeft;
  else scrollLeft = document.body.scrollLeft;

  g_iCavDivLeft = e.clientX - 2 + scrollLeft;
  g_iCavDivTop = e.clientY + 10 + scrollTop;

  window.clearTimeout(g_iCavTimer);
  g_iCavTimer = window.setTimeout("showMyTitle()", 500);
  g_CarEle = src;
}

function cancelMyTitleTimer(evt) {
  var e = (window.event) ? window.event : evt;
  var src = (e.srcElement) ? e.srcElement : e.target;

  var div = document.getElementById('myTitleDiv');
  if (div)
    document.body.removeChild(div);

  window.clearTimeout(g_iCavTimer);
  g_CarEle = null;
}

function showMyTitle() {
  for (var i = g_CarEle.attributes.length - 1; i >= 0; i--) {
    if (g_CarEle.attributes[i].name.toUpperCase() == 'MYTITLE') {
      var div = document.getElementById('myTitleDiv');
      if (div)
        break;

      div = document.createElement('div');
      div.id = 'myTitleDiv';

      var sLeft = new String();
      sLeft = g_iCavDivLeft.toString();
      sLeft += 'px';
      div.style.left = sLeft;
      var sTop = new String();
      sTop = g_iCavDivTop.toString();
      sTop += 'px';
      div.style.top = sTop;

      div.innerHTML = g_CarEle.attributes[i].value.split("\n").join("<br>").split(" ").join("&nbsp;");
      document.body.appendChild(div);

      var iWidth = div.scrollWidth + 10;
      var sWidth = new String();
      sWidth = iWidth.toString();
      sWidth += 'px';
      div.style.width = sWidth;

      break;
    }
  }
}

function formatDateTime(date,output) {
  if (!date) date = Date.now();

  var d;

  if (output=='mysql') {
    var parts = date.match(/(\d+)/g);
    d = new Date(parts[2], parts[1]-1, parts[0], parts[3]||'00', parts[4]||'00');
  } else {
    d = new Date(date);
  }

  var month = (d.getMonth() + 1).toString(),
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

function capitalizeFirstLetter(string) {
  return string[0].toUpperCase() + string.slice(1);
}

function escapeHtml(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}