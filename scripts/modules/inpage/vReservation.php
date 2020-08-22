<?php

class ModuleInPageReservation extends InPageModule {

  protected function _userInsert() {
    $this->setTemplateString('
          <b>{__label.inpage_reservation}</b>
          <form action="{%basefile%}" method="post">
            <div id="resourceCalendar">
              <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
              {reservationList}
              <br/>
              <div class="formButton">
                <input class="fb_eHidden" type="submit" name="action_eBack" value="{__button.back}" />
              </div>
            </div>
          </form>');
    
    $this->insert(new GuiInPageReservationList, 'reservationList');
  }
}

?>
