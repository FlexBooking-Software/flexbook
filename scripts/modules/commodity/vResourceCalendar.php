<?php

class ModuleResourceCalendar extends ProjectModule {

  protected function _userInsert() {
    $this->setTemplateString('<div id="resourceCalendar">{children}<form action="index.php">{backButton}{%sessionInput%}</form></div>');
    
    global $AJAX;
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_provider" type="hidden" value="%s" />', $this->_app->request->getParams('provider')), false);
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_userid" type="hidden" value="%s" />', $this->_app->auth->getUserId()), false);
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_username" type="hidden" value="%s" />', $this->_app->auth->getFullname()), false);
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_useremail" type="hidden" value="%s" />', $this->_app->auth->getEmail()), false);
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_sessionid" type="hidden" value="%s" />', $this->_app->session->getId()), false);
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_url" type="hidden" value="%s?" />', $AJAX['adminUrl']), false);
    $this->insertTemplateVar('children', sprintf('<input id="flb_core_url_path" type="hidden" value="%s/" />', dirname($AJAX['adminUrl'])), false);
    
    $this->insert(new GuiCalendar(array('params'=>array(
                  'calendarType'  => 'resource',
                  'prefix'        => 'backoffice_',
                  'provider'      => $this->_app->request->getParams('provider'),
                  'resourceId'    => $this->_app->request->getParams('id'),
                  'renderText'    => array('name'),
                  'render'        => array('reservation','occupied','event'),
                  ))), 'children');
    
    $this->insert(new GuiFormButton(array(
        'id'        => 'fi_back',
        'label'     => $this->_app->textStorage->getText('button.back'),
        'action'    => 'eBack',
        )), 'backButton');

    $this->_app->document->addCssFile('flb.css');
  }
}

?>
