<?php

class AjaxGetResourceCalendar extends AjaxAction {
  private $_id;
  
  private $_start;
  private $_end;
  
  private function _getResourceCalendar() {
    $resource = $this->_id;
    if (!is_array($resource)) $resource = array($resource);
    
    $hideReservation = false;
    $hideEvent = true;
    $hideEventTitle = false;
    $hideOccupied = false;
    $eventTitleFormat = null;

    if (isset($this->_params['customerView'])&&$this->_params['customerView']) {
      $hideReservation = true;
      $hideEvent = false;
    }
    
    if (isset($this->_params['showEvent'])&&$this->_params['showEvent']) $hideEvent = false;
    if (isset($this->_params['showEventTitle'])) $hideEventTitle = !$this->_params['showEventTitle'];
    if (isset($this->_params['showReservation'])) $hideReservation = !$this->_params['showReservation'];
    if (isset($this->_params['showOccupied'])) $hideOccupied = !$this->_params['showOccupied'];
    
    if (isset($this->_params['eventTitle'])) $eventTitleFormat = $this->_params['eventTitle'];

    if (!isset($this->_params['calAd'])) $this->_params['calAd'] = false;
    
    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s IN (%s)',$this->_app->db->escapeString(implode(',',$resource)))));
    $s->addStatement(new SqlStatementQuad(
                      new SqlStatementQuad($this->_start, $s->columns['resource_from'], $s->columns['resource_to'], $this->_end,'%s<=%s AND %s<=%s'),
                      new SqlStatementQuad($this->_start, $s->columns['resource_from'], $s->columns['resource_from'], $this->_end,'%s<=%s AND %s<%s'),
                      new SqlStatementQuad($this->_start, $s->columns['resource_to'], $s->columns['resource_to'], $this->_end,'%s<%s AND %s<=%s'),
                      new SqlStatementQuad($s->columns['resource_from'], $this->_start, $this->_end, $s->columns['resource_to'],'%s<%s AND %s<%s'),
                      '((%s) OR (%s) OR (%s) OR (%s))'
                    ));
    $s->setColumnsMask(array('reservation_id','resource','provider','number','user','user_name','resource_from','resource_to','resource_unit','payed','failed','pool','note'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $row['resourceId'] = $row['resource'];
      $row['allDay'] = false;
      $row['type'] = 'reservation';
      $row['className'] = 'event_reservation';
      if ($row['payed']) {
        $row['className'] .= ' event_reservation_payed';
        $row['durationEditable'] = false;
      }
      if ($row['failed']) {
        $row['className'] .= ' event_reservation_failed';
      }
      if (isset($this->_params['calAd'])&&$this->_params['calAd']&&($row['pool']=='Y')) $row['className'] .= ' event_reservation_pool';
      //$row['id'] = $row['reservation_id'];
      $row['title'] = ifsetor($this->_params['reservationTitle'],$row['user_name']);
      if (!$row['title']&&$this->_params['calAd']) $row['title'] = $row['note'];
      $row['start'] = $row['resource_from'];
      $row['end'] = $row['resource_to'];
      
      if ($hideReservation|| 																																	// bud chci rezervace schovat vsechny
          ($this->_app->session->getExpired()||!$this->_app->auth->getUserId())||							// nebo uzivatel neni prihlasen   // nebo rezervace neni prihlaseneho a neni v backofficu nebo nema prava
					(($this->_app->auth->getUserId()!=$row['user'])&&(!$this->_params['calAd']||!$this->_app->auth->haveRight('reservation_admin', $row['provider'])))
					) {
        // kdyz jsou rezervacni jednotky den, tak se zobrazuje pouze month kalendar a tam nefungujou klasicke background akce
        if ($row['resource_unit']==1440) $row['allDay'] = true;
        
        unset($row['reservation_id']);
        $row['title'] = 'occupied';
        $row['isBackground'] = true;
        $row['rendering'] = 'background'; // fullcalendar 2+ umi background eventy automaticky
        $row['className'] = '';
        
        if ($hideOccupied) continue;
      }
      
      unset($row['resource']);
      unset($row['user']);
      
      $this->_result[] = $this->_request->convertOutput($row);
    }
    #adump($this->_result);
    #die;
    
    // pridam jako udalosti obdobi, kdy je zdroj "nepouzitelny" podle availabilityprofile
    if (!$hideOccupied) {
			$minTime = '24';
			$maxTime = '0';
			$availability = array();
      foreach ($resource as $index=>$res) {
        $b = new BResource($res);
        $bAvailability = $b->getAvailabilityProfileData();

        foreach ($bAvailability as $weekDay=>$dayAvailability) {
          if ($dayAvailability['from']<$minTime) $minTime = substr($dayAvailability['from'],0,2);
          if ($maxTime<$dayAvailability['to']) $maxTime = substr($dayAvailability['to'],0,2);
        }

        $availability[$index] = $bAvailability;
      }

			foreach ($resource as $index=>$res) {
				$bAvailability = $availability[$index];
        $time = $this->_start;
        while ($time <= $this->_end) {
          $dayOfWeek = $this->_app->regionalSettings->getDayOfWeek($time);
          $date = substr($time,0,10);
          #adump($time);
          #adump($date);
          #adump($dayOfWeek);
          
          if (!isset($bAvailability[$dayOfWeek])) {
            $this->_result[] = array('start'=>$date.' '.$minTime.':00:00','end'=>$date.' '.$maxTime.':00:00','resourceId'=>$res,'isBackground'=>true,'rendering'=>'background','allDay'=>false);
          } else {
            if ($bAvailability[$dayOfWeek]['from']>$minTime.':00:00') {
              $this->_result[] = array('start'=>$date.' '.$minTime.':00:00','end'=>$date.' '.$bAvailability[$dayOfWeek]['from'],'resourceId'=>$res,'isBackground'=>true,'rendering'=>'background','allDay'=>false);
            }
            if ($bAvailability[$dayOfWeek]['to']<$maxTime.':00:00') {
              $this->_result[] = array('start'=>$date.' '.$bAvailability[$dayOfWeek]['to'],'end'=>$date.' '.$maxTime.':00:00','resourceId'=>$res,'isBackground'=>true,'rendering'=>'background','allDay'=>false);
            }
          }
          
          $time = $this->_app->regionalSettings->increaseDateTime($time, 1);
        }

        // pridam jako udalosti vyjimky dostupnosti
				$b = new BResource($res);
        $bAvailabilityException = $b->getAvailabilityExceptionProfileData(substr($this->_start,0,10), substr($this->_end,0,10));
        foreach ($bAvailabilityException as $exception) {
          $row['start'] = $exception['from'];
          $row['end'] = $exception['to'];
          $row['resourceId'] = $res;
          $row['isBackground'] = true;
          $row['rendering'] = 'background'; // fullcalendar 2+ umi background eventy automaticky
          $row['allDay'] = false;
          $this->_result[] = $this->_request->convertOutput($row);
        }
      }
    }
    #adump($this->_result);
    #die;
    
    // pridam jako udalosti eventy na zdroj
    $s = new SEvent;
    if (!isset($this->_params['calAd'])||!$this->_params['calAd']) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
		if (isset($this->_params['organiser'])) {
			if (!strcmp($this->_params['organiser'],'loggedInUser')) $s->addStatement(new SqlStatementBi($s->columns['organiser'], $this->_app->auth->getUserId(), '%s=%s'));
			else $s->addStatement(new SqlStatementBi($s->columns['organiser_email'], $this->_params['organiser'], '%s=%s'));
		}
    $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$resource)))));
    $s->addStatement(new SqlStatementQuad($this->_start, $s->columns['start'],
                                          $s->columns['end'], $this->_end,
                                          '%s<=%s AND %s<=%s'));
    $s->setColumnsMask(array('event_id','resource','name','start','end','free','active','attendees_id'));
    #error_log($s->toString());
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $row['allDay'] = false;
      $row['resourceId'] = $row['resource'];
      unset($row['resource']);
      $row['type'] = 'event';
      $row['className'] = 'event_providerEvent';
      if ($this->_app->auth->getUserId() && in_array($this->_app->auth->getUserId(), explode(',', $row['attendees_id']))) $row['className'] .= ' event_providerEvent_reserved';
      elseif (!$row['free']) $row['className'] .= ' event_providerEvent_occupied';
      if ($row['active'] == 'N') $row['className'] .= ' event_providerEvent_inactive';

      //$row['id'] = $row['event_id'];
      if (!$hideEventTitle) {
        if (!$eventTitleFormat) $row['title'] = $row['name'];
        else {
          $rs = $this->_app->regionalSettings;
          list($startDate, $startTime) = explode(' ', $rs->convertDateTimeToHuman($row['start']));
          list($endDate, $endTime) = explode(' ', $rs->convertDateTimeToHuman($row['end']));

          $row['title'] = str_replace(array('@@name', '@@free', '@@start_time', '@@start_date', '@@start', '@@end_time', '@@end_date', '@@end'),
            array($row['name'], $row['free'],
              $startTime, $startDate, $rs->convertDateTimeToHuman($row['start']),
              $endTime, $endDate, $rs->convertDateTimeToHuman($row['end'])),
            $eventTitleFormat);
        }
      }
      #$row['start'] = $row['start'];
      #$row['end'] = $row['end'];
      $row['editable'] = false;
      $row['startEditable'] = false;
      $row['durationEditable'] = false;
      if ($hideEvent) {
        $row['isBackground'] = true;
        $row['rendering'] = 'background'; // fullcalendar 2+ umi background eventy automaticky

        if ($hideOccupied) continue;
      } else {
        $row['isBackground'] = false;
      }

      $this->_result[] = $this->_request->convertOutput($row);
    }
  }
  
  private function _decreasePoolCapacity(& $capacity, $from, $to) {
    #error_log(sprintf('Occupying interval: %s - %s', $from, $to));
    
    $decreaseIndex = array();
    
    $length = count($capacity);
    for ($index=0;$index<$length;$index++) {
      if ($from>=$to) break;
      
      $cap = $capacity[$index];
      // hledam v existujich intervalech ty, kam obsazenost zasahuje
      if (($from<=$cap['start'])&&($cap['end']<=$to)) {
        // kdyz je obsazenost na obou koncich vetsi, budu v celem intervalu snizovat kapacitu
        $decreaseIndex[] = $index;
        
        if (($from==$cap['start'])&&($to==$cap['end'])) break;
      } elseif (($cap['start']<=$from)&&($from<$cap['end'])) {
        // kdyz je zacatek obsazenosti v intervalu
        // kdyz v intervalu bude neco zbyvat na zacatku, vytvorim novy interval
        $di = $index;
        if ($cap['start']<$from) {
          $capacity[$index]['end'] = $from;
          $cap['start'] = $from;
          
          $capacity[] = $cap;
          $di = $length;
          $length++;
        }
        // kdyz v intervalu bude neco zbyvat na konci, vytvorim novy interval
        if ($to<$cap['end']) {
          $capacity[$di]['end'] = $to;
          $cap['start'] = $to;
          $capacity[] = $cap;
          $length++;
        }
        
        $decreaseIndex[] = $di;
        
        $from = $cap['end'];
      } elseif (($cap['start']<$to)&&($to<=$cap['end'])) {
        // kdyz je konec obsazenosti v intervalu
        // kdyz v intervalu neco zbyva na konci, vytvorim novy interval
        if ($to<$cap['end']) {
          $capacity[$index]['end'] = $to;
          $cap['start'] = $to;
          $capacity[] = $cap;
          $length++;
        }
        
        $decreaseIndex[] = $index;
        
        $to = $cap['start'];
      }
    }
      
    // kdyz mam interval, kde snizovat kapacitu
    foreach ($decreaseIndex as $i) {
      if ($capacity[$i]['capacity']) $capacity[$i]['capacity']--;
    }
  }
  
  private function _getResourcePoolCalendar() {
    $resourcePool = $this->_id;
    
    $s = new SResourcePoolItem;
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $resourcePool, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['resource_active'], "%s='Y'"));
    $s->setColumnsMask(array('resource','unitprofile_unit'));
    $res = $this->_app->db->doQuery($s->toString());
    $resourceCount = $this->_app->db->getRowsNumber($res);
    $resource = array();
    $unit = null;
    while ($row = $this->_app->db->fetchAssoc($res)) {
    	$resource[] = $row['resource'];
    	$unit = $row['unitprofile_unit'];
		}

    // nejdriv nastavim volne kapacity na kompletni dny v tydnu
    $time = $this->_start;
    while ($time <= $this->_end) {
      #$dayOfWeek = $this->_app->regionalSettings->getDayOfWeek($time);
      $date = substr($time,0,10);
      
      $this->_result[] = array('start'=>$date.' 00:00:00','end'=>$date.' 23:59:59','className'=>'poolCapacity',
                               'resourceId'=>$resourcePool,'isBackground'=>true,'rendering'=>'background','allDay'=>$unit==1440,
                               'poolCapacity'=>true,'capacity'=>$resourceCount);
      
      $time = $this->_app->regionalSettings->increaseDateTime($time, 1);
    }
    
    // snizim pocty dle nedostupnosti jednotlivych zdroju
    foreach ($resource as $res) {
      $b = new BResource($res);
      $bAvailability = $b->getAvailabilityProfileData();
      
      $minTime = '24';
      $maxTime = '0';
      foreach ($bAvailability as $weekDay=>$dayAvailability) {
        if ($dayAvailability['from']<$minTime) $minTime = substr($dayAvailability['from'],0,2);
        if ($maxTime<$dayAvailability['to']) $maxTime = substr($dayAvailability['to'],0,2);
      }
      
      $time = $this->_start;
      while ($time <= $this->_end) {
        $dayOfWeek = $this->_app->regionalSettings->getDayOfWeek($time);
        $date = substr($time,0,10);
        
        if (!isset($bAvailability[$dayOfWeek])) {
          $this->_decreasePoolCapacity($this->_result, $date.' '.$minTime.':00:00', $date.' '.$maxTime.':00:00');
        } else {
          if ($bAvailability[$dayOfWeek]['from']>$minTime.':00:00') $this->_decreasePoolCapacity($this->_result, $date.' '.$minTime.':00:00', $date.' '.$bAvailability[$dayOfWeek]['from']);
          if ($bAvailability[$dayOfWeek]['to']<$maxTime.':00:00') $this->_decreasePoolCapacity($this->_result, $date.' '.$bAvailability[$dayOfWeek]['to'], $date.' 24:00:00');
        }
        
        $time = $this->_app->regionalSettings->increaseDateTime($time, 1);
      }
      
      // pridam jako udalosti vyjimky dostupnosti
      $bAvailabilityException = $b->getAvailabilityExceptionProfileData(substr($this->_start,0,10), substr($this->_end,0,10));
      foreach ($bAvailabilityException as $exception) $this->_decreasePoolCapacity($this->_result, $exception['from'], $exception['to']);
      #break;
    }
    foreach ($this->_result as $index=>$res) {
      if ($res['capacity']==0) {
        $this->_result[$index]['className'] .= ' poolCapacity_unavailable';
        $this->_result[$index]['unavailable'] = 1;
      }
    }
    
    // snizim pocty dle rezervaci
    $s = new SReservation;
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
    #$s->addStatement(new SqlStatementBi($s->columns['resource'], $resource[0], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$resource)))));
    $s->addStatement(new SqlStatementQuad($s->columns['resource_from'], $this->_end, $s->columns['resource_to'], $this->_start, '%s<%s AND %s>%s'));
    $s->setColumnsMask(array('reservation_id','start','end'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_decreasePoolCapacity($this->_result, $row['start'], $row['end']);
    }
    
    // snizim pocty dle eventu na zdroji
    $s = new SEvent;
    if (!isset($this->_params['calAd'])||!$this->_params['calAd']) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['resource'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$resource)))));
    $s->addStatement(new SqlStatementQuad($this->_start, $s->columns['start'], $s->columns['end'], $this->_end, '%s<=%s AND %s<=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['start']));
    $s->addOrder(new SqlStatementAsc($s->columns['end']));
    $s->setColumnsMask(array('event_id','name','start','end','resource'));
    $res = $this->_app->db->doQuery($s->toString());
    #error_log($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_decreasePoolCapacity($this->_result, $row['start'], $row['end']);
    }
    
    // zrusim intervaly, kde uz neni dostupne nic a pripravim popisek
    $parsedResult = array();;
    foreach ($this->_result as $index=>$res) {
      if ($res['capacity']==0) {
        if (!isset($res['unavailable'])) {
          $res['className'] .= ' poolCapacity_occupied';
          $res['title'] = '0';
        } else $res['title'] = '';
      } else {
        $res['className'] .= ' poolCapacity_available';
        $res['title'] = sprintf('%d', $res['capacity']);
      }
      $parsedResult[] = $res;
    }
    $this->_result = $parsedResult;
  }
  
  private function _getEventCalendar() {
    if (!isset($this->_params['eventTitle'])) $this->_params['eventTitle'] = '@@name - @@free';

    // pridam jako udalosti eventy
    $s = new SEvent;
    if (!isset($this->_params['calAd'])||!$this->_params['calAd']) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if (isset($this->_params['provider'])) $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    if (isset($this->_params['center'])) $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
    if (isset($this->_params['tag'])&&$this->_params['tag']) {
      $tag = $this->_params['tag'];
      foreach ($tag as $key=>$value) {
        $tag[$key] = sprintf("'%s'", $this->_app->db->escapeString($value));
      }
      $s->addStatement(new SqlStatementMono($s->columns['tag_name'], sprintf("%%s IN (%s)", implode(',',$tag))));
    }

    $s->addStatement(new SqlStatementQuad($this->_start, $s->columns['start'],
                                          $s->columns['end'], $this->_end,
                                          '%s<=%s AND %s<=%s'));
    $s->setColumnsMask(array('event_id'/*,'resource'*/,'name','start','end','free','active','attendees_id'));
    #error_log($s->toString());
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $row['allDay'] = false;
      #$row['resourceId'] = $row['resource'];
      #unset($row['resource']);
      $row['type'] = 'event';
      $row['className'] = 'event_providerEvent';
      if ($this->_app->auth->getUserId()&&in_array($this->_app->auth->getUserId(),explode(',',$row['attendees_id']))) $row['className'] .= ' event_providerEvent_reserved';
      elseif (!$row['free']) $row['className'] .= ' event_providerEvent_occupied';
      if ($row['active']=='N') $row['className'] .= ' event_providerEvent_inactive';
      
      //$row['id'] = $row['event_id'];

      $rs = $this->_app->regionalSettings;
      list($startDate,$startTime) = explode(' ', $rs->convertDateTimeToHuman($row['start']));
      list($endDate,$endTime) = explode(' ', $rs->convertDateTimeToHuman($row['end']));

      $row['title'] = str_replace(array('@@name','@@free','@@start_time','@@start_date','@@start','@@end_time','@@end_date','@@end'),
                                  array($row['name'],$row['free'],
                                        $startTime,$startDate,$rs->convertDateTimeToHuman($row['start']),
                                        $endTime,$endDate,$rs->convertDateTimeToHuman($row['end'])),
                                  $this->_params['eventTitle']);

      $row['description'] = $row['title'];
      $row['editable'] = false;
      $row['startEditable'] = false;
      $row['durationEditable'] = false;
      $row['isBackground'] = false;
      
      $this->_result[] = $this->_request->convertOutput($row);
    }
  }

  protected function _userRun() {
    #sleep(2);
    if (isset($this->_params['startDate'])) $this->_start = substr($this->_params['startDate'],0,10).' 00:00:00';
    else $this->_start = @date('Y-m-d H:i:s', $this->_params['start']);
    if (isset($this->_params['endDate'])) $this->_end = substr($this->_params['endDate'],0,10). ' 23:59:59';
    else $this->_end = @date('Y-m-d H:i:s', $this->_params['end']);
    
    $this->_id = ifsetor($this->_params['id']);
    $type = ifsetor($this->_params['type'],'resource');
    
    if (isset($this->_params['calAd'])) $this->_params['calAd'] = evaluateLogicalValue($this->_params['calAd']);
    
    if (!strcmp($type,'resource')) $this->_getResourceCalendar();
    elseif (!strcmp($type,'pool')) $this->_getResourcePoolCalendar();
    elseif (!strcmp($type,'event')) $this->_getEventCalendar();
  }
}

?>