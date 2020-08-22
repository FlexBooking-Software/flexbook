<?php

class ModuleResourceReservation extends ProjectModule {

  protected function _userInsert() {
    $this->setTemplateString('
      <div class="users">
        <div class="contentTitle">{__label.editResource_titleReservation}</div>
        <div class="listReservation">
          <div class="formItem">
            <label class="bold">{__label.editResource_provider}:</label>
            {fi_provider}
          </div>
          <div class="formItem">
            <label class="bold">{__label.editResource_name}:</label>
            {fi_name}
          </div>
          <div class="formItem">
            <label class="bold">{__label.editEvent_center}:</label>
            {fi_center}
          </div>
          {listReservation}
          <br/>
          <form action="{%basefile%}" method="post">
            <div>
              <input type="hidden" name="sessid" value="{%sessid%}" />
              <input type="submit" class="inputSubmit ui-button" name="action_eBack" value="{__button.back}"/>
            </div>
          </form>
        </div>
      </div>');
      
    $validator = Validator::get('resource', 'ResourceValidator');
    $data = $validator->getValues();
    $this->insertTemplateVar('fi_provider', $data['providerName']);
    $this->insertTemplateVar('fi_name', $data['name']);
    $this->insertTemplateVar('fi_center', $data['centerName']);
    
    $this->insert(new GuiListReservation('listResourceReservation'), 'listReservation');
  }
}

?>
