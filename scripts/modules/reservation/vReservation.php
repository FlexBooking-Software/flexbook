<?php

class ModuleReservation extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSection('reservation');
  
    $this->setTemplateString('
      <div class="reservations">
        <div id="tab">
          <ul>
            <li><a href="#tab-1">{__label.listReservation_title}</a></li>
            <li><a href="#tab-2">{__label.listReservation_titleSubstitute}</a></li>
          </ul>
          <div id="tab-1">
            <div class="listReservation">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newReservation}
                </div>
              </form>
              {listReservation}
            </div>
          </div>
          <div id="tab-2">
            <div class="listSubstitute">
              {listSubstitute}
            </div>
          </div>
        </div>
      </div>');

    $this->_app->document->addJavascript("
      $(function() {
        var tabCookieName = 'ui-reservationlist-tab';
        var tab = $('#tab').tabs({
          active : ($.cookie(tabCookieName) || 0),
          activate : function( event, ui ) {
            var newIndex = ui.newTab.parent().children().index(ui.newTab);
            // my setup requires the custom path, yours may not
            $.cookie(tabCookieName, newIndex);
          }
        });
      })");
    
    $this->insert(new GuiListReservation, 'listReservation');
    $this->insert(new GuiListEventSubstitute('listSubstitute'), 'listSubstitute');
    
    if ($this->_app->auth->haveRight('reservation_admin','ANY')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listReservation_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eReservationEdit',
              'showDiv' => false)), 'newReservation');
    } else {
      $this->insertTemplateVar('newReservation', '');
    }
  }
}

?>
