<?php

class ModuleResource extends ProjectModule {

  protected function _userInsert() {
    $this->_app->auth->setSection('resource');
  
    $this->setTemplateString('
      <div class="resource">
        <div id="tab">
          <ul>
            <li><a href="#tab-1">{__label.listResource_title}</a></li>
            <li><a href="#tab-2">{__label.listResource_titlePool}</a></li>
          </ul>
          <div id="tab-1">
            <div class="listResource">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newResource}
                </div>
              </form>
              {listResource}
            </div>
          </div>
          <div id="tab-2">
            <div class="listResourceGroup">
              <form action="{%basefile%}" method="post">
                <div>
                  <input type="hidden" name="sessid" value="{%sessid%}" />
                  {newResourceGroup}
                </div>
              </form>
              {listResourceGroup}
            </div>
          </div>
        </div>
      </div>');
    
    $this->_app->document->addJavascript("
      $(function() {
        var tabCookieName = 'ui-resourcelist-tab';
        var tab = $('#tab').tabs({
          active : ($.cookie(tabCookieName) || 0),
          activate : function( event, ui ) {
            var newIndex = ui.newTab.parent().children().index(ui.newTab);
            // my setup requires the custom path, yours may not
            $.cookie(tabCookieName, newIndex);
          }
        });
      })");
    
    $this->insert(new GuiListResource, 'listResource');
    
    if ($this->_app->auth->haveRight('commodity_admin',$this->_app->auth->getActualProvider())) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listResource_new'),
              'classInput' => 'inputSubmit',
              'action' => 'eResourceEdit',
              'showDiv' => false)), 'newResource');
    } else {
      $this->insertTemplateVar('newResource', '');
    }
    
    $this->insert(new GuiListResourcePool, 'listResourceGroup');
    
    if ($this->_app->auth->haveRight('commodity_admin',$this->_app->auth->getActualProvider())) {
      $this->insert(new GuiFormButton(array(
              'label' => $this->_app->textStorage->getText('button.listResource_newPool'),
              'classInput' => 'inputSubmit',
              'action' => 'eResourcePoolEdit',
              'showDiv' => false)), 'newResourceGroup');
    } else {
      $this->insertTemplateVar('newResourceGroup', '');
    }
  }
}

?>
