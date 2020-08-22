<?php

class AjaxGuiEventWeekList extends AjaxGuiEventList {

  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_event_week_list', $this->_params['prefix']);
    $this->_class = 'flb_list flb_event_list';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['eventTemplate'])) $this->_params['eventTemplate'] = '@@EVENT_NAME (@@EVENT_START) - @@EVENT_OVERALL_FREE - @@EVENT_PRICE';
    if (!isset($this->_params['eventCycleTemplate'])) $this->_params['eventCycleTemplate'] = '@@EVENT_NAME (@@EVENT_START_DATE - @@EVENT_END_DATE @@EVENT_START_TIME - @@EVENT_END_TIME) - @@EVENT_OVERALL_FREE - @@EVENT_PRICE';
    if (!isset($this->_params['eventResourcePrefix'])) $this->_params['eventResourcePrefix'] = '';
    if (!isset($this->_params['eventResourcePostfix'])) $this->_params['eventResourcePostfix'] = '';

    if (isset($this->_params['tag'])&&!is_array($this->_params['tag'])) $this->_params['tag'] = array($this->_params['tag']);
    if (!isset($this->_params['tagOperator'])) $this->_params['tagOperator'] = 'OR';
    $this->_params['tagOperator'] = strtoupper($this->_params['tagOperator']);

    if (!isset($this->_params['showInactive'])) $this->_params['showInactive'] = 1;
    if (!isset($this->_params['dateStart'])) $this->_params['dateStart'] = date('Y-m-d');
    if (!isset($this->_params['allowPast'])) $this->_params['allowPast'] = 1;
    if (!isset($this->_params['noEventTemplate'])) $this->_params['noEventTemplate'] = $this->_app->textStorage->getText('label.ajax_event_noDayEvent');
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = "{toolbar}
                      {eventList}
                      <script>
                        $(document).ready(function() {
                          {buttonLeft}
                          {buttonRight}
                          
                          $('#{prefix}flb_event_week_list').on('click','.flb_event_list_item_desc', function() {
                            flbLoadHtml('guiEventDetail', $('#{prefix}flb_event_week_list').parent(), $.extend({params}, { backGui: 'guiEventWeekList', eventId: $(this).closest('.flb_event_list_item').attr('id') }));  
                          });
                          $('#{prefix}flb_event_week_list').on('click','.flb_event_list_item_button_detail', function() {
                            flbLoadHtml('guiEventDetail', $('#{prefix}flb_event_week_list').parent(), $.extend({params}, { backGui: 'guiEventWeekList', eventId: $(this).closest('.flb_event_list_item').attr('id') }));  
                          });
                          $('#{prefix}flb_event_week_list').on('click','.flb_event_list_item_button_reserve', function() {
                            flbLoadHtml('guiEventDetail', $('#{prefix}flb_event_week_list').parent(), $.extend({params}, { backGui: 'guiEventWeekList', eventId: $(this).closest('.flb_event_list_item').attr('id'), reserve: 1 }));  
                          });
                          $('#{prefix}flb_event_week_list').on('click','.flb_event_list_item_button_reserve_notlogged', function() {
                            flbLoginRequired('{language}');
                         });
                        });
                      </script>";
  }

  private function _evaluateControls() {
    // posunuti dateStart na zacatek tydne
    $dayOfWeek = $this->_app->regionalSettings->getDayNumOfWeek($this->_params['dateStart']);
    $weekStartOffset = $dayOfWeek-1;
    if ($weekStartOffset) $this->_params['dateStart'] = $this->_app->regionalSettings->decreaseDate($this->_params['dateStart'], $weekStartOffset);

    $newLeftStart = $this->_app->regionalSettings->decreaseDate($this->_params['dateStart'], 7);
    $newLeftEnd = $this->_app->regionalSettings->decreaseDate($this->_params['dateStart'], 1);
    if ((!isset($this->_params['dateMin'])||($this->_params['dateMin']<=$newLeftEnd))&&
        (!isset($this->_params['allowPast'])||evaluateLogicalValue($this->_params['allowPast'])||(date('Y-m-d')<=$newLeftEnd))) {
      $this->_guiParams['buttonLeft'] = sprintf("$('#%sflb_event_week_list').on('click','.flb_event_week_list_left_button', function() {
          flbLoadHtml('guiEventWeekList', $('#%sflb_event_week_list').parent(), $.extend(%s, { dateStart: '%s' }));
        });", $this->_params['prefix'], $this->_params['prefix'], encodeAjaxParams($this->_params), $newLeftStart);
    } else {
      $this->_guiParams['buttonLeft'] = sprintf("$('#%sflb_event_week_list .flb_event_week_list_left_button').addClass('flb_disabledButton');", $this->_params['prefix']);
    }

    $newRightStart = $this->_app->regionalSettings->increaseDate($this->_params['dateStart'], 7);
    if (!isset($this->_params['dateMax'])||($newRightStart<=$this->_params['dateMax'])) {
      $this->_guiParams['buttonRight'] = sprintf("$('#%sflb_event_week_list').on('click','.flb_event_week_list_right_button', function() {
          flbLoadHtml('guiEventWeekList', $('#%sflb_event_week_list').parent(), $.extend(%s, { dateStart: '%s' }));
        });", $this->_params['prefix'], $this->_params['prefix'], encodeAjaxParams($this->_params), $newRightStart);
    } else {
      $this->_guiParams['buttonRight'] = sprintf("$('#%sflb_event_week_list .flb_event_week_list_right_button').addClass('flb_disabledButton');", $this->_params['prefix']);
    }
  }

  protected function _prepareTerms($row, & $start, & $startDate, & $startTime, & $end, & $endDate, & $endTime) {
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
  }

  protected function _getData() {
    global $AJAX;

    $this->_evaluateControls();

    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    if (isset($this->_params['showInactive'])&&!$this->_params['showInactive']) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
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
    if (isset($this->_params['dateStart'])) $s->addStatement(new SqlStatementTri($s->columns['start'], $this->_params['dateStart'],
      $this->_app->regionalSettings->increaseDate($this->_params['dateStart'],6).' 23:59:59', '%s BETWEEN %s AND %s'));
    if (isset($this->_params['organiser'])) $s->addStatement(new SqlStatementBi($s->columns['organiser_email'], $this->_params['organiser'], '%s=%s'));
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
    $res = $this->_app->db->doQuery($s->toString());
    $count = 0;
    $data = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_prepareTerms($row, $start, $startDate, $startTime, $end, $endDate, $endTime);

      $freeOverallText = sprintf('%s %s', $row['free'], $this->_app->textStorage->getText('label.ajax_event_freePlaces'));
      $freeOverall = $row['free'];
      if (!$row['free']&&$row['free_substitute']) {
        $freeOverallText = sprintf('%s %s', $row['free_substitute'], $this->_app->textStorage->getText('label.ajax_event_freePlacesSubstitute'));
        $freeOverall = $row['free_substitute'];
      }

      // kdyz se akce rezervuje pouze jako celek, zobrazuju prvni akci z cyklu
      if ($row['repeat_parent']&&!strcmp($row['repeat_reservation'],'PACK')) {
        $cycle_only = true;
        $priceText = sprintf('%s %s', $this->_app->regionalSettings->convertNumberToHuman($row['repeat_price'],2), $this->_app->textStorage->getText('label.currency_CZK'));
        $price = $row['repeat_price'];

        // datumy musim brat od prvni/posledni akce z cyklu
        $s1 = new SEvent;
        $s1->addStatement(new SqlStatementBi($s1->columns['repeat_parent'], $row['repeat_parent'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['active'], "%s='Y'"));
        $s1->addOrder(new SqlStatementAsc($s1->columns['start']));
        $s1->setColumnsMask(array('start'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $row1 = $this->_app->db->fetchAssoc($res1);
        $s1 = new SEvent;
        $s1->addStatement(new SqlStatementBi($s1->columns['repeat_parent'], $row['repeat_parent'], '%s=%s'));
        $s1->addStatement(new SqlStatementMono($s1->columns['active'], "%s='Y'"));
        $s1->addOrder(new SqlStatementDesc($s1->columns['start']));
        $s1->setColumnsMask(array('end'));
        $res1 = $this->_app->db->doQuery($s1->toString());
        $row2 = $this->_app->db->fetchAssoc($res1);

        $this->_prepareTerms(array('start'=>$row1['start'],'end'=>$row2['end']), $start, $startDate, $startTime, $end, $endDate, $endTime);
      } else {
        $cycle_only = false;
        $priceText = sprintf('%s %s', $this->_app->regionalSettings->convertNumberToHuman($row['price'],2), $this->_app->textStorage->getText('label.currency_CZK'));
        $price = $row['price'];
      }
      if ($price == 0) $priceText = sprintf('<span class="flb_price_free_of_charge">%s</span>', $this->_app->textStorage->getText('label.ajax_price_free_of_charge'));

      $parts = explode(' ', $row['start']);
      $data[$parts[0]][] = array(
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
        'event_photo'             => $row['url_photo']?explode(',',$row['url_photo'])[0]:null,
        'event_organiser_id'      => $row['organiser'],
        'event_organiser'         => $row['organiser_fullname'],
        'event_price'             => $priceText,
        'event_free'              => sprintf('%s %s', $row['free'], $this->_app->textStorage->getText('label.ajax_event_freePlaces')),
        'event_substitute_free'   => sprintf('%s %s', $row['free_substitute'], $this->_app->textStorage->getText('label.ajax_event_freePlacesSubstitute')),
        'event_overall_free_num'  => $freeOverall,
        'event_overall_free'      => $freeOverallText,
      );

      $count++;
      if (isset($this->_params['count'])&&($count>=$this->_params['count'])) break;
    }

    $html = $this->_renderWeekList($data);
    
    $this->_guiParams['eventList'] = $html;

    $this->_guiParams['toolbar'] = $this->_renderToolbar();

    $this->_guiParams['prevDateStart'] = $this->_app->regionalSettings->decreaseDate($this->_params['dateStart'],7);
    $this->_guiParams['nextDateStart'] = $this->_app->regionalSettings->increaseDate($this->_params['dateStart'],7);
  }

  private function _renderWeekList($data) {
    $ret = '';
    for ($i=0;$i<7;$i++) {
      $date = $this->_app->regionalSettings->increaseDate($this->_params['dateStart'],$i);
      $ret .= sprintf('<div class="flb_event_list_item flb_list_item_title">%s</div>',
        $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($date), isset($this->_params['format']['day'])?$this->_params['format']['day']:'l j.F'));

      if (isset($data[$date])) {
        foreach ($data[$date] as $event) {
          $dataString = $this->_parseEventLine($event);
          $ret .= sprintf('<div class="flb_event_list_item flb_list_item" id="%s%s"><div class="flb_event_list_item_cycle">%s</div>
                                  <div class="flb_event_list_item_desc">%s</div>
                                  <div class="flb_button flb_event_list_item_button flb_event_list_item_button_detail"><span>%s</span></div>',
            $this->_params['prefix'], $event['id'], $event['cycle_icon'], $dataString, strtoupper($this->_app->textStorage->getText('button.ajax_event_detail')));
          if ($event['event_overall_free_num']) {
            if ($this->_app->auth->getUserId()&&!$this->_app->session->getExpired()) {
              $ret .= sprintf('<div class="flb_button flb_primaryButton flb_event_list_item_button flb_event_list_item_button_reserve"><span>%s</span></div>', strtoupper($this->_app->textStorage->getText('button.ajax_event_reserve')));
            } else {
              $ret .= sprintf('<div class="flb_button flb_event_list_item_button flb_event_list_item_button_reserve_notlogged"><span>%s</span></div>', strtoupper($this->_app->textStorage->getText('button.ajax_event_reserve')));
            }
          } else {
            $ret .= sprintf('<div class="flb_button flb_primaryButton flb_event_list_item_button" style="visibility: hidden;"><span>%s</span></div>', strtoupper($this->_app->textStorage->getText('button.ajax_event_reserve')));
          }
          $ret .= '</div>';
        }
      } else {
        $ret .= sprintf('<div class="flb_event_list_item flb_list_item">%s</div>', $this->_params['noEventTemplate']);
      }
    }

    return $ret;
  }

  private function _renderToolbar() {
    $from = $this->_params['dateStart'];
    $to = $this->_app->regionalSettings->increaseDate($this->_params['dateStart'],6);
    $formatFrom = $formatTo = isset($this->_params['format']['period'])?$this->_params['format']['period']:'j.F.';

    /*// pokud je stejny mesic/rok, vynecham ho v prvnim datumu
    $fromStruct = getdate(date(strtotime($from)));
    $toStruct = getdate(date(strtotime($to)));
    if ($fromStruct['year']==$toStruct['year']) $formatFrom = str_replace(array(), array(), $formatFrom);
    if ($fromStruct['mon']==$toStruct['mon']) $formatFrom = str_replace(array(), array(), $formatFrom);*/

    $ret = sprintf('<div class="flb_event_week_list_header">
                      <div class="flb_event_week_list_controls">
                        <div class="flb_button flb_event_week_list_left_button"><span>&lt&lt</span></div>
                        <div class="flb_event_week_list_period"><span>%s - %s</span></div>
                        <div class="flb_button flb_event_week_list_right_button"><span>&gt&gt</span></div>
                      </div>
                   </div>',
                  $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($from), $formatFrom),
                  $this->_app->regionalSettings->convertTimeStampToLocale(strtotime($to), $formatTo));

    return $ret;
  }
}

?>
