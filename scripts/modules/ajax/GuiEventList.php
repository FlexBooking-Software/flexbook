<?php

class AjaxGuiEventList extends AjaxGuiAction2 {
  const DAYOFWEEK = array('sun'=>1,'mon'=>2,'tue'=>3,'wed'=>4,'thu'=>5,'fri'=>6,'sat'=>7);

  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_event_list', $this->_params['prefix']);
    $this->_class = 'flb_list flb_event_list';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['eventTemplate'])) $this->_params['eventTemplate'] = '@@EVENT_NAME (@@EVENT_START - @@EVENT_END) - @@EVENT_OVERALL_FREE - @@EVENT_PRICE';
    if (!isset($this->_params['eventCycleTemplate'])) $this->_params['eventCycleTemplate'] = '@@EVENT_NAME (@@EVENT_START_DATE - @@EVENT_END_DATE @@EVENT_START_TIME - @@EVENT_END_TIME) - @@EVENT_OVERALL_FREE - @@EVENT_PRICE';
    if (!isset($this->_params['eventResourcePrefix'])) $this->_params['eventResourcePrefix'] = '';
    if (!isset($this->_params['eventResourcePostfix'])) $this->_params['eventResourcePostfix'] = '';

    if (isset($this->_params['tag'])&&!is_array($this->_params['tag'])) $this->_params['tag'] = array($this->_params['tag']);
    if (!isset($this->_params['tagOperator'])) $this->_params['tagOperator'] = 'OR';
    $this->_params['tagOperator'] = strtoupper($this->_params['tagOperator']);
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = "{eventList}{nextPage}
                      <script>
                        $(document).ready(function() {
                          $('#{prefix}flb_event_list').on('click','.flb_event_list_item_button_detail', function() {
                            if ($(this).closest('.flb_event_list_item').attr('data-organiser')==$('#flb_core_userid').val()) flbLoadHtml('guiEventOrganiserDetail', $('#{prefix}flb_event_list').parent(), $.extend({params}, { eventId: $(this).closest('.flb_event_list_item').attr('id') }));  
                            else flbLoadHtml('guiEventDetail', $('#{prefix}flb_event_list').parent(), $.extend({params}, { eventId: $(this).closest('.flb_event_list_item').attr('id') }));
                          });
                          $('#{prefix}flb_event_list').on('click','.flb_event_list_item_button_reserve', function() {
                            if ($(this).closest('.flb_event_list_item').attr('data-organiser')==$('#flb_core_userid').val()) flbLoadHtml('guiEventOrganiserDetail', $('#{prefix}flb_event_list').parent(), $.extend({params}, { eventId: $(this).closest('.flb_event_list_item').attr('id'), reserve: 1 }));
                            else flbLoadHtml('guiEventDetail', $('#{prefix}flb_event_list').parent(), $.extend({params}, { eventId: $(this).closest('.flb_event_list_item').attr('id'), reserve: 1 }));  
                          });
                          $('#{prefix}flb_event_list').on('click','.flb_event_list_item_button_reserve_notlogged', function() {
                            flbLoginRequired('{language}');
                         });
                         $('#{prefix}flb_event_list').on('click','.flb_event_list_next_page_button', function() {
                            var params = JSON.parse($('#{prefix}flb_event_list #flb_guiParams').val());
                            if (typeof(params.pages) !== 'undefined') params.pages++;
                            else params.pages = 2;
                            $('#{prefix}flb_event_list #flb_guiParams').val(JSON.stringify(params));
                            flbRefresh('#{prefix}flb_event_list');
                         });
                        });
                      </script>";
  }
  
  protected function _parseEventLine($lineData) {
    foreach ($lineData as $key=>$value) $data['@@'.strtoupper($key)] = $value;

    if ($data['@@EVENT_PHOTO']) $data['@@EVENT_PHOTO'] = sprintf('<img class="flb_event_photo" src="%s"/>', $data['@@EVENT_PHOTO']);
    if ($data['@@EVENT_PHOTOTHUMB']) $data['@@EVENT_PHOTOTHUMB'] = sprintf('<div class="photo">%s</div>', $this->_getPhotoThumb($lineData['id'], $data['@@EVENT_PHOTOTHUMB']));
    
    if (strpos($this->_params['eventTemplate'],'EVENT_ATTRIBUTE')!==false) {
      // nejdriv vsechny atributy poskytovatele "vynuluju"
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute_id','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@EVENT_ATTRIBUTE('.$row['short_name'].')'] = ''; }
      $s = new SEventAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['event'], $lineData['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute','short_name','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@EVENT_ATTRIBUTE('.$row['short_name'].')'] = $row['value']; }  
    }

    if (isset($lineData['cycle_only'])&&$lineData['cycle_only']) $ret = str_replace(array_keys($data), $data, $this->_params['eventCycleTemplate']);
    else $ret = str_replace(array_keys($data), $data, $this->_params['eventTemplate']);

    return $ret;
  }

  protected function _getData() {
    global $AJAX;

    $this->_guiParams['nextPage'] = '';

    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if (isset($this->_params['center'])&&$this->_params['center']) {
      if (!is_array($this->_params['center'])) $this->_params['center'] = array($this->_params['center']);
      $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf("%%s IN (%s)", $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
    }
    if (isset($this->_params['region'])&&$this->_params['region']) {
      if (!is_array($this->_params['region'])) $this->_params['region'] = array($this->_params['region']);
      $regionString = '';
      foreach ($this->_params['region'] as $region) {
        if ($regionString) $regionString .= ',';
        $regionString .= sprintf("'%s'", $this->_app->db->escapeString($region));
      }
      $s->addStatement(new SqlStatementMono($s->columns['region'], sprintf("%%s IN (%s)", $regionString)));
    }
    if (isset($this->_params['resource'])&&$this->_params['resource']) $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_params['resource'], '%s=%s'));
    if (isset($this->_params['dateMin'])) $s->addStatement(new SqlStatementBi($s->columns['start'], $this->_params['dateMin'], '%s>=%s'));
    if (isset($this->_params['dateMax'])) $s->addStatement(new SqlStatementBi($s->columns['end'], $this->_params['dateMax'] . ' 23:59:59', '%s<=%s'));
    if (isset($this->_params['weekday'])) {
      $mysqlWeekDay = '';
      foreach (explode(',',$this->_params['weekday']) as $day) {
        if ($mysqlWeekDay) $mysqlWeekDay .= ',';
        $mysqlWeekDay .= self::DAYOFWEEK[$day];
      }
      $s->addStatement(new SqlStatementMono($s->columns['start'], sprintf('DAYOFWEEK(%%s) IN (%s)', $mysqlWeekDay)));
    }
    if (isset($this->_params['organiser'])) {
      if (!strcmp($this->_params['organiser'],'loggedInUser')) $s->addStatement(new SqlStatementBi($s->columns['organiser'], $this->_app->auth->getUserId(), '%s=%s'));
      else $s->addStatement(new SqlStatementBi($s->columns['organiser_email'], $this->_params['organiser'], '%s=%s'));
    }
    if (isset($this->_params['dateNowPlusXDays'])) $s->addStatement(new SqlStatementBi($s->columns['start'], $s->columns['start'], sprintf('(%%s>=NOW() AND %%s<=DATE_ADD(NOW(),INTERVAL %d DAY))', $this->_params['dateNowPlusXDays']*1)));
    if (isset($this->_params['tag'])&&$this->_params['tag']) {
      if ($this->_params['tagOperator']=='OR') {
        $tag = $this->_params['tag'];
        foreach ($tag as $key => $value) {
          $tag[$key] = sprintf("'%s'", $this->_app->db->escapeString($value));
        }
        $s->addStatement(new SqlStatementMono($s->columns['tag_name'], sprintf("%%s IN (%s)", implode(',', $tag))));
      } elseif ($this->_params['tagOperator']=='AND') {
        foreach ($this->_params['tag'] as $tagName) {
          $s1 = new STag;
          $s1->addStatement(new SqlStatementBi($s1->columns['name'], $tagName, '%s=%s'));
          $s1->setColumnsMask(array('tag_id'));
          $res1 = $this->_app->db->doQuery($s1->toString());
          $row1 = $this->_app->db->fetchAssoc($res1);
          $s->addStatement(new SqlStatementMono($s->columns['all_tag_select'], sprintf("%s IN (%%s)", ifsetor($row1['tag_id'],-1))));
        }
      }
    }
    
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('event_id','start','end','name','description','center','center_name','street','city','region','postal_code','state','url_photo',
      'organiser','organiser_fullname','price','max_attendees','free','free_substitute','all_resource_name','repeat_parent','repeat_reservation','repeat_price'));
    #error_log($s->toString());
    #adump($s->toString());die;
    $res = $this->_app->db->doQuery($s->toString());
    if (!$totalCount = $this->_app->db->getRowsNumber($res)) {
      $html = sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData'));
    } else {
      $count = 0; $data = array(); $eventParentRendered = array(); $pages = ifsetor($this->_params['pages'],1);
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $startDate = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['start']), isset($this->_params['format']['date'])?$this->_params['format']['date']:'d.m.Y');
        $startTime = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['start']), isset($this->_params['format']['time'])?$this->_params['format']['time']:'H:i');
        $endDate = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['date'])?$this->_params['format']['date']:'d.m.Y');
        $endTime = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['time'])?$this->_params['format']['time']:'H:i');

        $start = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['start']), isset($this->_params['format']['datetime'])?$this->_params['format']['datetime']:'d.m.Y H:i');
        if (substr($row['start'],0,10)==substr($row['end'],0,10)) {
          $end = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['time'])?$this->_params['format']['time']:'H:i');
        } else {
          $end = $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($row['end']), isset($this->_params['format']['datetime'])?$this->_params['format']['datetime']:'d.m.Y H:i');
        }

        $freeOverallText = sprintf('%s %s', $row['free'], $this->_app->textStorage->getText('label.ajax_event_freePlaces'));
        $freeOverall = $row['free'];
        if (!$row['free']&&$row['free_substitute']) {
          $freeOverallText = sprintf('%s %s', $row['free_substitute'], $this->_app->textStorage->getText('label.ajax_event_freePlacesSubstitute'));
          $freeOverall = $row['free_substitute'];
        }
        
        // kdyz se akce rezervuje pouze jako celek, zobrazuju prvni akci z cyklu
        if ($row['repeat_parent']&&!strcmp($row['repeat_reservation'],'PACK')) {
          if (in_array($row['repeat_parent'],array_keys($eventParentRendered))) {
            $data[$eventParentRendered[$row['repeat_parent']]]['event_end'] = $end;
            $data[$eventParentRendered[$row['repeat_parent']]]['event_end_date'] = $endDate;
            $data[$eventParentRendered[$row['repeat_parent']]]['event_end_time'] = $endTime;

            continue;
          }



          $cycle_only = true;
          $priceText = sprintf('%s %s', $this->_app->regionalSettings->convertNumberToHuman($row['repeat_price'],2), $this->_app->textStorage->getText('label.currency_CZK'));
          $price = $row['repeat_price'];
        } else {
          $cycle_only = false;
          $priceText = sprintf('%s %s', $this->_app->regionalSettings->convertNumberToHuman($row['price'],2), $this->_app->textStorage->getText('label.currency_CZK'));
          $price = $row['price'];
        }
        if ($price == 0) $priceText = sprintf('<span class="flb_price_free_of_charge">%s</span>', $this->_app->textStorage->getText('label.ajax_price_free_of_charge'));

        if ((!isset($this->_params['count'])||($count<$this->_params['count']))&&
            (!isset($this->_params['onPageCount'])||($count<($pages*$this->_params['onPageCount'])))) {

          if ($row['repeat_parent']&&!strcmp($row['repeat_reservation'],'PACK')) $eventParentRendered[$row['repeat_parent']] = $row['event_id'];

          $data[$row['event_id']] = array(
            'id'                      => $row['event_id'],
            'cycle_only'              => $cycle_only,
            'cycle_icon'              => $cycle_only?sprintf('<img title="%s" src="%s/img/icon_repeat.png"/>', $this->_app->textStorage->getText('label.ajax_event_repeatInfo'), dirname($AJAX['url'])):'',
            'event_cycle_count'       => 1,
            'event_name'              => $row['name'],
            'event_description'       => $row['description'],
            'event_resource'          => $row['all_resource_name']?sprintf('%s%s%s', $this->_params['eventResourcePrefix'], $row['all_resource_name'], $this->_params['eventResourcePostfix']):'',
            'event_places'            => $row['max_attendees'],
            'event_start_date'        => $startDate,
            'event_start_time'        => $startTime,
            'event_end_date'          => $endDate,
            'event_end_time'          => $endTime,
            'event_start'             => $start,
            'event_end'               => $end,
            'event_center_street'     => $row['street'],
            'event_center_city'       => $row['city'],
            'event_center_region'     => $row['region'],
            'event_center_zip'        => $row['postal_code'],
            'event_center_country'    => $row['state'],
            'event_center'            => $row['center_name'],
            'event_photothumb'        => $row['url_photo'],
            'event_photo'             => $row['url_photo']?explode(',',$row['url_photo'])[0]:null,
            'event_organiser_id'      => $row['organiser'],
            'event_organiser'         => $row['organiser_fullname'],
            'event_price'             => $priceText,
            'event_free'              => sprintf('%s %s', $row['free'], $this->_app->textStorage->getText('label.ajax_event_freePlaces')),
            'event_substitute_free'   => sprintf('%s %s', $row['free_substitute'], $this->_app->textStorage->getText('label.ajax_event_freePlacesSubstitute')),
            'event_overall_free'      => $freeOverallText,
          );
          $count++;

          if (isset($this->_params['onPageCount'])) {
            if ($count==($pages*$this->_params['onPageCount'])) {
              $this->_guiParams['nextPage'] = sprintf('<div class="flb_button flb_event_list_next_page_button"><span>%s</span></div>', $this->_app->textStorage->getText('button.ajax_event_nextPage'));
            }
          }
        }
      }

      $html = '';
      foreach ($data as $id=>$event) {
        $dataString = $this->_parseEventLine($event);
        $html .= sprintf('<div class="flb_event_list_item flb_list_item" id="%s%s" data-organiser="%s"><div class="flb_event_list_item_cycle">%s</div><div class="flb_event_list_item_desc">%s</div>
                            <div class="flb_button flb_event_list_item_button flb_event_list_item_button_detail"><span>%s</span></div>',
          $this->_params['prefix'], $event['id'], $event['event_organiser_id'], $event['cycle_icon'], $dataString, strtoupper($this->_app->textStorage->getText('button.ajax_event_detail')));
        if ($freeOverall) {
          if ($this->_app->auth->getUserId()&&!$this->_app->session->getExpired()) {
            $html .= sprintf('<div class="flb_button flb_primaryButton flb_event_list_item_button flb_event_list_item_button_reserve"><span>%s</span></div>', strtoupper($this->_app->textStorage->getText('button.ajax_event_reserve')));
          } else {
            $html .= sprintf('<div class="flb_button flb_event_list_item_button flb_event_list_item_button_reserve_notlogged"><span>%s</span></div>', strtoupper($this->_app->textStorage->getText('button.ajax_event_reserve')));
          }
        } else {
          $html .= sprintf('<div class="flb_button flb_primaryButton flb_event_list_item_button" style="visibility: hidden;"><span>%s</span></div>', strtoupper($this->_app->textStorage->getText('button.ajax_event_reserve')));
        }
        $html .= '</div>';
      }
    }
    
    $this->_guiParams['eventList'] = $html;
  }
}

?>
