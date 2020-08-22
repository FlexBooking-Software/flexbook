$(document).ready(function() {
  var tabCookieName = 'ui-resourcepool-tab';
  var tabCookieValue = $.cookie(tabCookieName);
  var tab = $('#tab').tabs({
    active : (tabCookieValue || 0),
    activate : function( event, ui ) {
      var newIndex = ui.newTab.parent().children().index(ui.newTab);
      // my setup requires the custom path, yours may not
      $.cookie(tabCookieName, newIndex);
    }
  });

  $('#fi_tag').tokenInput('{ajaxUrlPath}/ajax.php?action=getTag&provider={provider}',{
    minChars: 0,
    showAllResults: true,
    queryParam: 'term', theme: 'facebook',
    tokenValue: 'name',
    preventDuplicates: true,
    hintText: '{__label.searchTag_hint}',
    searchingText: '{__label.searchTag_searching}',
    noResultsText: '{__label.searchTag_noResult}',
    onResult: function (item) {
      if ($.isEmptyObject(item)) {
        return [ { id: $('tester').text(),name: $('tester').text() } ];
      } else {
        return item;
      }
    },
  });
  {tagTokenInit}
  
  {additionalEditJS}
});