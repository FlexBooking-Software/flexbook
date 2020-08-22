<?php

class GuiHome extends GuiElement {
  
  private function _insertCommodity($data) {
    if (count($data['id'])) {
      if (!strcmp($data['commodity'],'resource')) {
        global $AJAX;
        
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_provider" type="hidden" value="%s" />', $this->_app->auth->getActualProvider()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_userid" type="hidden" value="%s" />', $this->_app->auth->getUserId()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_username" type="hidden" value="%s" />', $this->_app->auth->getFullname()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_useremail" type="hidden" value="%s" />', $this->_app->auth->getEmail()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_sessionid" type="hidden" value="%s" />', $this->_app->session->getId()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_url" type="hidden" value="%s?" />', $AJAX['adminUrl']), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_url_path" type="hidden" value="%s/" />', dirname($AJAX['adminUrl'])), false);
        
        $this->insert(new GuiCalendar(array('params'=>array(
                      'prefix'        => 'backoffice_',
                      'calendarType'  => 'resource',
                      'provider'      => $this->_app->auth->getActualProvider(),
                      'resourceId'    => $data['id'],
                      'renderText'    => array('name'),
                      'render'        => array('reservation','occupied','event'),
                      //'format'        => array('reservationTitle'=>'OBSAZENO'),
                      ))), 'commodity');
      } elseif (!strcmp($data['commodity'],'resourcePool')) {
        global $AJAX;
        
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_provider" type="hidden" value="%s" />', $this->_app->auth->getActualProvider()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_userid" type="hidden" value="%s" />', $this->_app->auth->getUserId()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_username" type="hidden" value="%s" />', $this->_app->auth->getFullname()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_useremail" type="hidden" value="%s" />', $this->_app->auth->getEmail()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_sessionid" type="hidden" value="%s" />', $this->_app->session->getId()), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_url" type="hidden" value="%s?" />', $AJAX['adminUrl']), false);
        $this->insertTemplateVar('commodity', sprintf('<input id="flb_core_url_path" type="hidden" value="%s/" />', dirname($AJAX['adminUrl'])), false);
        
        $this->insert(new GuiCalendar(array('params'=>array(
                      'prefix'        => 'backoffice_',
                      'calendarType'  => 'resource',
                      'provider'      => $this->_app->auth->getActualProvider(),
                      'resourcePoolId'=> $data['id'][0],
                      'renderText'    => array('name'),
                      ))), 'commodity'); 
      } else {
        $this->insert(new GuiEventSummary(array('eventId'=>ifsetor($data['id'][0]))), 'commodity');
      }
    } else $this->insertTemplateVar('commodity', '');
  }
  
  private function _showHidePoolTab($data) {
    $s = new SResourcePool;
    if ($this->_app->auth->getActualCenter()) $s->addStatement(new SqlStatementBi($s->columns['center'], $this->_app->auth->getActualCenter(), '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['s_resource_all'], "%s<>''"));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('resourcepool_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res)>0) {
      $this->insertTemplateVar('poolTab', sprintf('<span class="title" id="fi_titleResourcePool">%s</span>', $this->_app->textStorage->getText('label.home_resourcePool')), false);
    } else $this->insertTemplateVar('poolTab', '');
  }

  protected function _userRender() {
    $validator = Validator::get('home', 'HomeValidator');
    $data = $validator->getValues();
    #adump($data);die;
    
    global $AJAX;
    $this->setTemplateString('
        <script>
          $(document).ready(function() {
            if ($.cookie(\'ui-home-tab\')==\'event\') {
              $(\'#fi_resourceList\').hide();
              $(\'#fi_resourcePoolList\').hide();
              $(\'#fi_eventList\').show();
              
              $(\'#fi_titleEvent\').addClass(\'selected\');
            } else if ($.cookie(\'ui-home-tab\')==\'resourcePool\') {
              $(\'#fi_resourceList\').hide();
              $(\'#fi_resourcePoolList\').show();
              $(\'#fi_eventList\').hide();
              
              $(\'#fi_titleResourcePool\').addClass(\'selected\');
            } else {
              $(\'#fi_resourceList\').show();
              $(\'#fi_resourcePoolList\').hide();
              $(\'#fi_eventList\').hide();
              
              $(\'#fi_titleResource\').addClass(\'selected\');
            }
            $(\'#fi_titleResourcePool\').click(function() {
              $(\'#fi_resourceList\').hide();
              $(\'#fi_resourcePoolList\').show();
              $(\'#fi_eventList\').hide();
              
              $.cookie(\'ui-home-tab\', \'resourcePool\');
              
              $(\'#fi_titleResource\').removeClass(\'selected\');
              $(\'#fi_titleResourcePool\').addClass(\'selected\');
              $(\'#fi_titleEvent\').removeClass(\'selected\');
            });
            $(\'#fi_titleResource\').click(function() {
              $(\'#fi_resourceList\').show();
              $(\'#fi_resourcePoolList\').hide();
              $(\'#fi_eventList\').hide();
              
              $.cookie(\'ui-home-tab\', \'resource\');
              
              $(\'#fi_titleResource\').addClass(\'selected\');
              $(\'#fi_titleResourcePool\').removeClass(\'selected\');
              $(\'#fi_titleEvent\').removeClass(\'selected\');
            });
            $(\'#fi_titleEvent\').click(function() {
              $(\'#fi_resourceList\').hide();
              $(\'#fi_resourcePoolList\').hide();
              $(\'#fi_eventList\').show();
              
              $.cookie(\'ui-home-tab\', \'event\');
              
              $(\'#fi_titleResource\').removeClass(\'selected\');
              $(\'#fi_titleResourcePool\').removeClass(\'selected\');
              $(\'#fi_titleEvent\').addClass(\'selected\');
            });
           
            $(\'#fi_resourceTag\').tokenInput(\'aajax.php?action=getTag&provider={providerId}\',{
              minChars: 0,
              showAllResults: true,
              queryParam: \'term\', theme: \'facebook\',
              preventDuplicates: true,
              hintText: \'{__label.searchTag_hint}\',
              searchingText: \'{__label.searchTag_searching}\',
              noResultsText: \'{__label.searchTag_noResult}\',
              onAdd: function() { filterCommodity(\'resource\'); },
              onDelete: function() { filterCommodity(\'resource\'); }
            });
            $(\'#fi_resourcePoolTag\').tokenInput(\'aajax.php?action=getTag&provider={providerId}\',{
              minChars: 0,
              showAllResults: true,
              queryParam: \'term\', theme: \'facebook\',
              preventDuplicates: true,
              hintText: \'{__label.searchTag_hint}\',
              searchingText: \'{__label.searchTag_searching}\',
              noResultsText: \'{__label.searchTag_noResult}\',
              onAdd: function() { filterCommodity(\'resourcePool\'); },
              onDelete: function() { filterCommodity(\'resourcePool\'); }
            });
            $(\'#fi_eventTag\').tokenInput(\'aajax.php?action=getTag&provider={providerId}\',{
              minChars: 0,
              showAllResults: true,
              queryParam: \'term\', theme: \'facebook\',
              preventDuplicates: true,
              hintText: \'{__label.searchTag_hint}\',
              searchingText: \'{__label.searchTag_searching}\',
              noResultsText: \'{__label.searchTag_noResult}\',
              onAdd: function() { filterCommodity(\'event\'); },
              onDelete: function() { filterCommodity(\'event\'); }
            });
            {addedTokens}
            
            $(\'body\').on(\'click\', \'a.commodityChange\', function(e) {
              var type = $(this).attr(\'meaning\');
              var tags = \'\';
              var tokens = $(\'#fi_\'+type+\'Tag\').tokenInput(\'get\');
              for (token in tokens) {
                if (tags) tags += \',\';
                tags += tokens[token].id;
              }
              $(this).attr(\'href\', $(this).attr(\'href\')+\'&\'+type+\'Tag=\'+tags);
              
              if (type==\'event\') {
                var from = $(\'#fi_eventFrom\').val();
                if (from) $(this).attr(\'href\', $(this).attr(\'href\')+\'&eventFrom=\'+from);
                var to = $(\'#fi_eventTo\').val();
                if (to) $(this).attr(\'href\', $(this).attr(\'href\')+\'&eventTo=\'+to);
              }
            });
              
            $(\'#fi_eventFrom\').datetimepicker({
              format: \'d.m.Y\',
              dayOfWeekStart: \'1\',
              lang: \'cz\',
              timepicker: false,
              allowBlank: true,
              scrollInput: false,
              onChangeDateTime: function() { filterCommodity(\'event\'); }
            });
            $(\'#fi_eventTo\').datetimepicker({
              format: \'d.m.Y\',
              dayOfWeekStart: \'1\',
              lang: \'cz\',
              timepicker: false,
              allowBlank: true,
              scrollInput: false,
              onChangeDateTime: function() { filterCommodity(\'event\'); }
            });
              
            function alignPanels() {
							maxHeight = $(\'#fi_right\').height();
							$(\'#fi_resource\').height(maxHeight-36);
							$(\'#fi_resourcePool\').height(maxHeight-26);
							$(\'#fi_event\').height(maxHeight-41);
						}
            
            function filterCommodity(type) {
              var tags = \'\';
              var tokens = $(\'#fi_\'+type+\'Tag\').tokenInput(\'get\');
              for (token in tokens) {
                if (tags) tags += \',\';
                tags += tokens[token].id;
              }
              var center = {centerId};
              var from = $(\'#fi_eventFrom\').val();
              var to = $(\'#fi_eventTo\').val();
              
              var data = { provider: {providerId} };
              if (center) data.center = center;
              if (tags) data.tag = tags;
              if (type == \'event\') {
                if (from) data.from = from;
                if (to) data.to = to;
                if (from||to) data.inactive = \'Y\';  // kdyz se zadava u akci od-do, zobrazuju i inactive @todo: to prijde predelat az se nebudou akce zneaktivnovat
              }
              
              var destination = $(\'#fi_\'+type);
              $.ajax({
                type: \'GET\',
                dataType: \'json\',
                data: data,
                //async: false,
                url: \'aajax.php?action=get\'+capitalizeFirstLetter(type),
                success: function(data) {
                  var content = \'\';
                  $.each(data, function(index,element) {
                    var hint = \'\'; var extraClass = \'\'; var multi = \'\';
                    var selected = [{id}];
                    if (type == \'event\') hint = \' title="\'+formatDateTime(element.start,\'human\')+\' - \'+formatDateTime(element.end,\'human\')+\'" \';
                    if ($.inArray(parseInt(element.id),selected)!=-1) {
                      extraClass = \' bold\';
                      if (selected.length>1) multi = \'<a class="commodityChange add_remove" meaning="\'+type+\'" href="{%basefile%}?action=eMain&commodity=\'+type+\'&remove=1&id=\'+element.id+\'{%sessionUrl%}">&ndash;</a>\';
                    } else {
                      multi = \'<a class="commodityChange add_remove" meaning="\'+type+\'" href="{%basefile%}?action=eMain&commodity=\'+type+\'{selectedId}&id[]=\'+element.id+\'{%sessionUrl%}">+</a>\';
                    }
                    content += \'<div class="line\'+extraClass+\'"\'+hint+\'>\';
                    content += \'<a class="commodityChange" meaning="\'+type+\'" href="{%basefile%}?action=eMain&commodity=\'+type+\'&id[]=\'+element.id+\'{%sessionUrl%}">\'+element.name+\'</a>\';
                    if (type == \'resource\') content += multi;
                    content += \'</div>\';
                  });
                  destination.html(content);
                  
                  alignPanels();
                },
                error: function(error) { alert(\'{__label.ajaxError}\'); }
              });
            }
            
            filterCommodity(\'resource\');
            filterCommodity(\'resourcePool\');
            filterCommodity(\'event\');
          });
        </script>
                             
        <div id="home">
          <div class="left" id="fi_left">
            <span class="title" id="fi_titleResource">{__label.home_resource}</span>{poolTab}<span class="title" id="fi_titleEvent">{__label.home_event}</span>
            <div class="resource" id="fi_resourceList">
              <input type="text" class="filter" id="fi_resourceTag" />
              <div class="all"><a class="commodityChange" meaning="resource" href="{%basefile%}?action=eMain&commodity=resource&id[]=all{%sessionUrl%}">{__label.all}</a></div>
              <div class="panel" id="fi_resource">
              </div>
            </div>
            <div class="event" id="fi_eventList">
              <input type="text" class="filter" id="fi_eventTag"/>
              <input type="text" class="filterDate" id="fi_eventFrom" value="{eventFrom}"/>
              <div class="filterDateSeparator">-</div>
              <input type="text" class="filterDate" id="fi_eventTo" value="{eventTo}"/>
              <div class="panel" id="fi_event">
              </div>
            </div>
            <div class="resourcePool" id="fi_resourcePoolList">
              <input type="text" class="filter" id="fi_resourcePoolTag"/>
              <div class="panel" id="fi_resourcePool">
              </div>
            </div>
          </div>
          <div class="right" id="fi_right">
            {commodity}
          </div>
        </div>');
    
    $addedTokens = '';
    foreach (explode(',',$data['resourceTag']) as $tagId) {
      if (!$tagId) continue;
      
      $o = new OTag($tagId);
      $oData = $o->getData();
      $tagName = $oData['name'];
      
      $addedTokens .= "$('#fi_resourceTag').tokenInput('add', {id: $tagId, name: '$tagName'});";
    }
    foreach (explode(',',$data['resourcePoolTag']) as $tagId) {
      if (!$tagId) continue;
      
      $o = new OTag($tagId);
      $oData = $o->getData();
      $tagName = $oData['name'];
      
      $addedTokens .= "$('#fi_resourcePoolTag').tokenInput('add', {id: $tagId, name: '$tagName'});";
    }
    foreach (explode(',',$data['eventTag']) as $tagId) {
      if (!$tagId) continue;
      
      $o = new OTag($tagId);
      $oData = $o->getData();
      $tagName = $oData['name'];
      
      $addedTokens .= "$('#fi_eventTag').tokenInput('add', {id: $tagId, name: '$tagName'});";
    }
    $this->insertTemplateVar('addedTokens', $addedTokens, false);

    //$this->insertTemplateVar('urlPath', $AJAX['relativeUrlPath']);
    $this->insertTemplateVar('url', $AJAX['adminUrl']);
    $this->insertTemplateVar('providerId', $this->_app->auth->getActualProvider());
    $this->insertTemplateVar('centerId', $this->_app->auth->getActualCenter()?$this->_app->auth->getActualCenter():sprintf('[%s]', $this->_app->auth->getAllowedCenter('list')));
    $this->insertTemplateVar('id', implode(',',$data['id']));
    $this->insertTemplateVar('resourceTag', $data['resourceTag']);
    $this->insertTemplateVar('resourcePoolTag', $data['resourcePoolTag']);
    $this->insertTemplateVar('eventTag', $data['eventTag']);
    $this->insertTemplateVar('eventFrom', $data['eventFrom']);
    $this->insertTemplateVar('eventTo', $data['eventTo']);
    
    //$this->_app->document->addOnLoad("filterCommodity('resource');filterCommodity('resourcePool');filterCommodity('event');alignPanels();");
    //$this->_app->document->addOnLoad("alignPanels();");
    
    $selectedId = '';
    if (!strcmp($data['commodity'],'resource')) {
      foreach ($data['id'] as $id) {
        $selectedId .= sprintf('&id[]=%s', $id);
      }  
    }
    $this->insertTemplateVar('selectedId', $selectedId, false);
    
    #$this->_insertResource($data);
    #$this->_insertEvent($data);
    
    $this->_showHidePoolTab($data);
    $this->_insertCommodity($data);

    $this->_app->document->addCssFile('flb.css');
  }
}

?>
