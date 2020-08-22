<?php

class GuiEditProviderPortal extends GuiElement {

  private function _insertActiveSelect($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_active',
            'name' => 'active',
            'label' => $this->_app->textStorage->getText('label.editProviderPortal_active'),
            'dataSource' => $ds,
            'value' => $data['active'],
            'userTextStorage' => false)), 'fi_active');
  }
  
  private function _insertProviderSelect($data) {
    if ($this->_app->auth->isAdministrator()) {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'name' => 'providerId',
              'classLabel' => 'bold',
              'label' => $this->_app->textStorage->getText('label.editProviderPortal_provider'),
              'dataSource' => $ds,
              'value' => $data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
              'userTextStorage' => false)), 'fi_provider');
    } else {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" id="fi_provider" name="providerId" value="%s" />', $this->_app->auth->getActualProvider()), false);
    }
  }

  private function _insertHomePageSelect($data) {
    $select = new SProviderPortalPage;
    $select->addStatement(new SqlStatementBi($select->columns['providerportal'], $data['id'], '%s=%s'));
    $select->setColumnsMask(array('providerportalpage_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_homePage',
            'name' => 'homePage',
            'showDiv' => false,
            'dataSource' => $ds,
            'value' => $data['homePage'],
            'firstOption' => Application::get()->textStorage->getText('label.select_choose'),
            'userTextStorage' => false)), 'fi_homePage');
  }
  
  private function _insertMenu($data) {
    $this->insertTemplateVar('menu', '<input type="hidden" name="menu[]" value=""/>', false);
    
    $select = new SProviderPortalPage;
    $select->addStatement(new SqlStatementBi($select->columns['providerportal'], $data['id'], '%s=%s'));
    $select->addOrder(new SqlStatementAsc($select->columns['name']));
    $select->setColumnsMask(array('providerportalpage_id','name'));
    $dataSource = new SqlDataSource(new DataSourceSettings, $select);
    
    $guiSelect = new GuiFormSelect(array(
                    'name'        => 'menuPage[]',
                    'showDiv'     => false,
                    'dataSource'  => $dataSource,
                    'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
                    ));
    $itemTemplateHtml = sprintf('<div class="menuItem">
                                  <div class="removeMenuItem">X</div>
                                  <input type="hidden" name="menuId[]" value="" />
                                  %s: <input type="text" name="menuName[]" value=""/>
                                  %s: %s
                                </div>',
                                $this->_app->textStorage->getText('label.editProviderPortal_menuName'),
                                $this->_app->textStorage->getText('label.editProviderPortal_menuPage'), $guiSelect->render());
    $this->insertTemplateVar('menuItemTemplate', $itemTemplateHtml, false);
    
    foreach ($data['menu'] as $id=>$menu) {
      $guiSelect = new GuiFormSelect(array(
                    'name'        => sprintf('menuPage[%d]', $id),
                    'showDiv'     => false,
                    'dataSource'  => $dataSource,
                    'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
                    'value'       => $menu['page'],
                    ));
      
      $itemHtml = sprintf('<div class="menuItem">
                            <div class="removeMenuItem">X</div>
                            <input type="hidden" name="menuId[%d]" value="%d" />
                            %s: <input type="text" name="menuName[%d]" value="%s"/>
                            %s: %s
                           </div>',
                           $id, $menu['id'],
                           $this->_app->textStorage->getText('label.editProviderPortal_menuName'), $id, $menu['name'],
                           $this->_app->textStorage->getText('label.editProviderPortal_menuPage'), $guiSelect->render());
      $this->insertTemplateVar('menu', $itemHtml, false);
    }
    
    $s = new SPageTemplate;
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $ds = new SqlDataSource(new DataSourceSettings, $s);
    $this->insert(new GuiFormSelect(array(
        'name'        => 'pageTemplateSelect',
        'id'          => 'fi_pageTemplateSelect',
        'dataSource'  => $ds,
        'firstOption' => $this->_app->textStorage->getText('label.select_choose'),
        'value'       => null,
        'showDiv'     => false,
        )), 'fi_pageTemplateSelect');
  }

  private function _insertPageList($data) {
    $this->insert(new GuiListProviderPortalPage, 'pageList');
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ProviderPortalEdit.html');

    $validator = Validator::get('providerPortal', 'ProviderPortalValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editProviderPortal_titleExisting').' '.$data['name']);
    
    $this->_insertActiveSelect($data);
    $this->_insertProviderSelect($data);
    $this->_insertHomePageSelect($data);
    $this->_insertMenu($data);
    $this->_insertPageList($data);
    
    $this->_app->document->addJavascript(sprintf("
                $(document).ready(function() {            
                  var tabCookieName = 'ui-providerportal-tab';
                  var tab = $('#tab').tabs({
                          active : ($.cookie(tabCookieName) || 0),
                          activate : function( event, ui ) {
                            var newIndex = ui.newTab.parent().children().index(ui.newTab);
                            // my setup requires the custom path, yours may not
                            $.cookie(tabCookieName, newIndex);
                          }
                  });
                  
                  $('.portalMenu').on('click', '.removeMenuItem', function() {
                    $(this).closest('.menuItem').remove();
                  });
                  
                  $('.portalMenu').sortable({
                    items: '.menuItem'
                  });
                  
                  $('#fi_newMenuItem').click(function() {
                    $('.portalMenu').append($('#menuItemTemplate').html());
                  });
                   
                  $('#fi_helpDiv').dialog({
                    autoOpen: false, 
                    width: 600,
                  });
                  
                  $('.fi_help').click(function() {
                    $('#fi_helpDiv').dialog('open');
                  });
                })"));
  }
}

?>
