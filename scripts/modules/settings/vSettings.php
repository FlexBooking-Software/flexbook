<?php

class ModuleSettings extends ProjectModule {

  private function _insertGeneralForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="contentTitle">{__label.settingsGeneral_providerTitle}</div>
          {children}
        </div>');
    
    $this->insert(new GuiListProvider('listProviderForSettings'));
  }
  
  private function _insertAvailabilityForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div id="tab">
            <ul>
              <li><a href="#tab-1">{__label.settings_listAvailProfile_title}</a></li>
              <li><a href="#tab-2">{__label.settings_listAvailExProfile_title}</a></li>
              <li><a href="#tab-3">{__label.settings_listUnitProfile_title}</a></li>
            </ul>
            <div id="tab-1">
              <div class="listAvailProfile">
                <form action="{%basefile%}" method="post">
                  <div>
                    <input type="hidden" name="sessid" value="{%sessid%}" />
                    {newAvailProfile}
                  </div>
                </form>
                {listAvailProfile}
              </div>
            </div>
            <div id="tab-2">
              <div class="listAvailProfile">
                <form action="{%basefile%}" method="post">
                  <div>
                    <input type="hidden" name="sessid" value="{%sessid%}" />
                    {newAvailExProfile}
                  </div>
                </form>
                {listAvailExProfile}
              </div>
            </div>
            <div id="tab-3">
              <div class="listUnitProfile">
                <form action="{%basefile%}" method="post">
                  <div>
                    <input type="hidden" name="sessid" value="{%sessid%}" />
                    {newUnitProfile}
                  </div>
                </form>
                {listUnitProfile}
              </div>
            </div>
          </div>
        </div>');
    
    $this->_app->document->addJavascript('
          $(function() {
          var tabCookieName = \'ui-settings-tab\';
          var tab = $(\'#tab\').tabs({
                  active : ($.cookie(tabCookieName) || 0),
                  activate : function( event, ui ) {
                    var newIndex = ui.newTab.parent().children().index(ui.newTab);
                    // my setup requires the custom path, yours may not
                    $.cookie(tabCookieName, newIndex);
                  }
              });
          })');
    
    $this->insert(new GuiListAvailProfile, 'listAvailProfile');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listAvailProfile_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eAvailProfileEdit',
            'showDiv' => false)), 'newAvailProfile');
            
    $this->insert(new GuiListAvailExProfile, 'listAvailExProfile');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listAvailExProfile_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eAvailExProfileEdit',
            'showDiv' => false)), 'newAvailExProfile');
            
    $this->insert(new GuiListUnitProfile, 'listUnitProfile');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listUnitProfile_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eUnitProfileEdit',
            'showDiv' => false)), 'newUnitProfile');
  }
  
  private function _insertPriceListForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div id="tab">
            <ul>
              <li><a href="#tab-1">{__label.listPriceList_title}</a></li>
            </ul>
            <div id="tab-1">
              <div class="priceList">
                <div class="listPriceList">
                  <form action="{%basefile%}" method="post">
                    <div>
                      <input type="hidden" name="sessid" value="{%sessid%}" />
                      {newPriceList}
                    </div>
                  </form>
                  {priceList}
                </div>
              </div>
            </div>
          </div>
        </div>');
    
    $this->_app->document->addJavascript('
          $(function() {
          var tabCookieName = \'ui-price-tab\';
          var tab = $(\'#tab\').tabs({
                  active : ($.cookie(tabCookieName) || 0),
                  activate : function( event, ui ) {
                    var newIndex = ui.newTab.parent().children().index(ui.newTab);
                    // my setup requires the custom path, yours may not
                    $.cookie(tabCookieName, newIndex);
                  }
              });
          })');
    
    $this->insert(new GuiListPriceList, 'priceList');  
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listPriceList_new'),
            'classInput' => 'inputSubmit',
            'action' => 'ePriceListEdit',
            'showDiv' => false)), 'newPriceList');
  }
  
  private function _insertTicketListForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="ticketList">
            <div class="contentTitle">{__label.listTicket_title}</div>
            <div class="listTicket">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {new}
                </div>
              </form>
              {list}
            </div>
          </div>
        </div>');
    
    $this->insert(new GuiListTicket, 'list');  
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listTicket_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eTicketEdit',
            'showDiv' => false)), 'new');
  }

  private function _insertVoucherListForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="voucherList">
            <div class="contentTitle">{__label.listVoucher_title}</div>
            <div class="listVoucher">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {new}
                </div>
              </form>
              {list}
            </div>
          </div>
        </div>');

    $this->insert(new GuiListVoucher, 'list');
    $this->insert(new GuiFormButton(array(
      'label' => $this->_app->textStorage->getText('button.listVoucher_new'),
      'classInput' => 'inputSubmit',
      'action' => 'eVoucherEdit',
      'showDiv' => false)), 'new');
  }
  
  private function _insertTagForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="tags">
            <div class="contentTitle">{__label.listTag_title}</div>
            <div class="listTag">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newTag}
                </div>
              </form>
              {listTag}
            </div>
          </div>
        </div>');
    
    $this->insert(new GuiListTag, 'listTag');
    if ($this->_app->auth->haveRight('customer_admin')) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listTag_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eTagEdit',
              'showDiv' => false)), 'newTag');
    } else {
      $this->insertTemplateVar('newTag', '');
    }
  }
  
  private function _insertReservationConditionForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="tags">
            <div class="contentTitle">{__label.listReservationCondition_title}</div>
            <div class="listReservationCondition">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newReservationCondition}
                </div>
              </form>
              {listReservationCondition}
            </div>
          </div>
        </div>');
    
    $this->insert(new GuiListReservationCondition, 'listReservationCondition');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listReservationCondition_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eReservationConditionEdit',
            'showDiv' => false)), 'newReservationCondition');
  }
  
  private function _insertNotificationTemplateForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="tags">
            <div class="contentTitle">{__label.listNotificationTemplate_title}</div>
            <div class="listNotificationTemplate">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newNotificationTemplate}
                </div>
              </form>
              {listNotificationTemplate}
            </div>
          </div>
        </div>');
    
    $this->insert(new GuiListNotificationTemplate, 'listNotificationTemplate');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listNotificationTemplate_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eNotificationTemplateEdit',
            'showDiv' => false)), 'newNotificationTemplate');
  }

  private function _insertDocumentTemplateForm() {
    $this->setTemplateString('
        <div class="settingsContent">
          <div class="tags">
            <div class="contentTitle">{__label.listDocumentTemplate_title}</div>
            <div class="listDocumentTemplate">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newDocumentTemplate}
                </div>
              </form>
              {listDocumentTemplate}
            </div>
          </div>
        </div>');

    $this->insert(new GuiListDocumentTemplate, 'listDocumentTemplate');
    $this->insert(new GuiFormButton(array(
      'label' => $this->_app->textStorage->getText('button.listDocumentTemplate_new'),
      'classInput' => 'inputSubmit',
      'action' => 'eDocumentTemplateEdit',
      'showDiv' => false)), 'newDocumentTemplate');
  }

  private function _insertProviderPortalForm() {
    if ($this->_app->auth->isAdministrator()) {
      $this->setTemplateString('
          <div class="providerPortalContent">
            <div id="tab">
              <ul>
                <li><a href="#tab-1">{__label.settings_listPortalTemplate_title}</a></li>
                <li><a href="#tab-2">{__label.settings_listPageTemplate_title}</a></li>
                <li><a href="#tab-3">{__label.settings_listProviderPortal_title}</a></li>
              </ul>
              <div id="tab-1">
                <div class="listPortalTemplate">
                  <form action="{%basefile%}" method="post">
                    <div>
                      <input type="hidden" name="sessid" value="{%sessid%}" />
                      {newPortalTemplate}
                    </div>
                  </form>
                  {listPortalTemplate}
                </div>
              </div>
              <div id="tab-2">
                <div class="listPageTemplate">
                  <form action="{%basefile%}" method="post">
                    <div>
                      <input type="hidden" name="sessid" value="{%sessid%}" />
                      {newPageTemplate}
                    </div>
                  </form>
                  {listPageTemplate}
                </div>
              </div>
              <div id="tab-3">
                <div class="listProviderPortal">
                  <form action="{%basefile%}" method="post">
                    <div>
                      <input type="hidden" name="sessid" value="{%sessid%}" />
                      {newProviderPortal}
                    </div>
                  </form>
                  {listProviderPortal}
                </div>
              </div>
            </div>
          </div>');
      
      $this->insert(new GuiListPortalTemplate, 'listPortalTemplate');
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listPortalTemplate_new'),
              'classInput' => 'inputSubmit',
              'action' => 'ePortalTemplateEdit',
              'showDiv' => false)), 'newPortalTemplate');
      
      $this->insert(new GuiListPageTemplate, 'listPageTemplate');
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listPageTemplate_new'),
              'classInput' => 'inputSubmit',
              'action' => 'ePageTemplateEdit',
              'showDiv' => false)), 'newPageTemplate');
      
      $this->_app->document->addJavascript('
          $(function() {
          var tabCookieName = \'ui-portaltemplatesettings-tab\';
          var tab = $(\'#tab\').tabs({
                  active : ($.cookie(tabCookieName) || 0),
                  activate : function( event, ui ) {
                    var newIndex = ui.newTab.parent().children().index(ui.newTab);
                    // my setup requires the custom path, yours may not
                    $.cookie(tabCookieName, newIndex);
                  }
              });
          })');
    } else {
      $this->setTemplateString('
              <div class="contentTitle">{__label.listProviderPortal_title}</div>
              <div class="listProviderPortal">
                <form action="{%basefile%}" method="post">
                  <div>
                    <input type="hidden" name="sessid" value="{%sessid%}" />
                    {newProviderPortal}
                  </div>
                </form>
                {listProviderPortal}
              </div>');
    }
    
    $this->insert(new GuiListProviderPortal, 'listProviderPortal');
    $this->insert(new GuiFormButton(array(
            'label' => $this->_app->textStorage->getText('button.listProviderPortal_new'),
            'classInput' => 'inputSubmit',
            'action' => 'eProviderPortalPrepare',
            'showDiv' => false)), 'newProviderPortal');
  }

  protected function _userInsert() {
    if (!$this->_app->auth->haveRight('settings_admin', $this->_app->auth->getActualProvider())) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_app->auth->setSection('settings');
    
    if (!$subSection = $this->_app->request->getParams('section')) $subSection = 'general';
    $this->_app->auth->setSubSection($subSection);
    
    switch ($subSection) {
      case 'general': $this->_insertGeneralForm(); break;
      case 'availability': $this->_insertAvailabilityForm(); break;
      case 'priceList': $this->_insertPriceListForm(); break;
      case 'ticketList': $this->_insertTicketListForm(); break;
      case 'voucherList': $this->_insertVoucherListForm(); break;
      case 'tag': $this->_insertTagForm(); break;
      case 'reservationCondition': $this->_insertReservationConditionForm(); break;
      case 'notificationTemplate': $this->_insertNotificationTemplateForm(); break;
      case 'documentTemplate': $this->_insertDocumentTemplateForm(); break;
      case 'providerPortal': $this->_insertProviderPortalForm(); break;
      default: $this->setTemplateString('<b>Settings</b>');
    }
  }
}

?>
