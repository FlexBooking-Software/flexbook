<?php

class XMLActionSearch extends XMLAction {
  private $_portal;
  private $_page;
  private $_perPage;
  private $_sort;
  
  private $_subject;
  private $_address;
  private $_latitude;
  private $_longitude;
  private $_gpsRange;
  private $_from;
  private $_to;
  
  private $_item = array();
  
  private function _readRequest() {
    $portal = $this->_reqDoc->getElementsByTagName('portal');
    if ($portal->length) {
      $this->_portal = $this->_convertInput($portal->item(0)->nodeValue);
    }
    
    $pageNode = $this->_reqDoc->getElementsByTagName('page');
    if ($pageNode->length) {
      $this->_page = $this->_convertInput($pageNode->item(0)->nodeValue);
    }
    if (!$this->_page) $this->_page = 1;
    
    $perPageNode = $this->_reqDoc->getElementsByTagName('per_page');
    if ($perPageNode->length) {
      $this->_perPage = $this->_convertInput($perPageNode->item(0)->nodeValue);
    }
    if (!$this->_perPage) $this->_perPage = 10;
    
    $sortNode = $this->_reqDoc->getElementsByTagName('sort');
    if ($sortNode->length) {
      $this->_sort = $this->_convertInput($sortNode->item(0)->nodeValue);
    }
    if (!$this->_sort) $this->_sort = 'name';
    
    $subject = $this->_reqDoc->getElementsByTagName('subject');
    if ($subject->length) {
      $this->_subject = $this->_convertInput($subject->item(0)->nodeValue);
    }
    
    $location = $this->_reqDoc->getElementsByTagName('location');
    if ($location->length) {
      $loc = $this->_convertInput($location->item(0)->nodeValue);
      if (strpos($loc,'gps[')!==false) {
        $loc = str_replace(array('gps[',']'),'',$loc);
        $coords = explode(',',$loc);
        if ((count($coords)!=2)||!$coords[0]||!$coords[1]) throw new ExceptionUser(XML_FATAL_INVALID_GPS);
        $this->_latitude = $coords[0];
        $this->_longitude = $coords[1];
        
        $locationRange = $this->_reqDoc->getElementsByTagName('location_range');
        if ($locationRange->length) {
          $this->_gpsRange = $locationRange->item(0)->nodeValue;
        }
        if (!$this->_gpsRange) throw new ExceptionUser(XML_FATAL_MISSING_GPS_RANGE);
      } else {
        $this->_address = $loc;
      }
    }
    
    $from = $this->_reqDoc->getElementsByTagName('from');
    if ($from->length) {
      $this->_from = $from->item(0)->nodeValue;
      if ($this->_from&&!$this->_app->regionalSettings->checkHumanDateTime($this->_from)) throw new ExceptionUser(XML_FATAL_INVALID_FROM);
      $this->_from = $this->_app->regionalSettings->convertHumanToDateTime($this->_from);
    }
    if (!$this->_from) {
      $this->_from = $this->_app->regionalSettings->increaseDateTime(date('Y-m-d H:i:s'), 0, 0, 0, 2);
    }
    
    $durationNode = $this->_reqDoc->getElementsByTagName('duration');
    if ($durationNode->length) {
      $duration = $durationNode->item(0)->nodeValue;
      if (!$duration) $duration = 'exact_date';
      
      if ($duration) {
        $this->_to = $this->_from;
        
        if ($duration==='exact_date') {
          list($date,$time) = explode(' ', $this->_from);
          $this->_to = $date . ' 23:59:59';
        } elseif (strpos($duration,'plus_day_')!==false) {
          $day = str_replace('plus_day_', '', $duration);
          $this->_to = $this->_app->regionalSettings->increaseDateTime($this->_to, $day); 
        } elseif (strpos($duration,'plus_month_')!==false) {
          $day = str_replace('plus_month_', '', $duration);
          $this->_to = $this->_app->regionalSettings->increaseDateTime($this->_to, 0, $day); 
        } 
      } 
    }
  }

  private function _addItem($row, $type) {
    $item = array('type'=>$type,'id'=>$row[$type.'_id'],'name'=>$row['name'],'description'=>$row['description'],
                  'price'=>$row['price']);
    if ($type == 'event') {
      $item['start'] = $row['start'];
      $item['free'] = $row['free'];
    } elseif ($type == 'resource') {
      $item['availability'] = $row['availability'];
      $item['unitprofile_name'] = $row['unitprofile_name'];
    }
    
    if (isset($this->_item[$row['center_id']])) {
      if ($row['price']<$this->_item[$row['center_id']]['price_from']) $this->_item[$row['center_id']]['price_from'] = $row['price'];
      if ($row['price']>$this->_item[$row['center_id']]['price_to']) $this->_item[$row['center_id']]['price_to'] = $row['price'];
      
      $this->_item[$row['center_id']]['detail'][] = $item;
    } else {
      $this->_item[$row['center_id']] = array(
              'center_id'       => $row['center_id'],
              'provider_name'   => $row['provider_name'],
              'center_name'     => $row['center_name'],
              'www'             => $row['provider_www'],
              'email'           => $row['provider_email'],
              'phone'           => $row['provider_phone_1'].','.$row['provider_phone_2'],
              'street'          => $row['street'],
              'city'            => $row['city'],
              'postal_code'     => $row['postal_code'],
              'gps_latitude'    => $row['gps_latitude'],
              'gps_longitude'   => $row['gps_longitude'],
              'gps_distance'    => ifsetor($row['gps_distance']),
              'price_from'      => $row['price'],
              'price_to'        => $row['price'],
              'detail'          => array($item),
              );
    }
  }
  
  private function _getResource() {
    $s = new SResource;
    
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if ($this->_portal) $s->addStatement(new SqlStatementBi($s->columns['portal'], $this->_portal, '%s=%s'));
    if ($this->_subject) $s->addStatement(new SqlStatementMono($s->columns['tag_id'], sprintf("%%s IN (%s)", $this->_app->db->escapeString($this->_subject))));
    if ($this->_address) $s->addStatement(new SqlStatementMono($s->columns['center_description'], sprintf("%%s LIKE '%%%%%s%%%%'", $this->_app->db->escapeString($this->_address))));
    if ($this->_latitude&&$this->_longitude&&$this->_gpsRange) {
      $s->addStatement(new SqlStatementPenta(
            $s->columns['gps_latitude'], $s->columns['gps_longitude'], $this->_latitude, $this->_longitude, $this->_gpsRange,
            'gps_distance(%s,%s,%s,%s)*1000<=%s'  
            ));
      $s->addColumn(new SqlColumn(false, new SqlStatementQuad($s->columns['gps_latitude'], $s->columns['gps_longitude'], $this->_latitude, $this->_longitude, 'gps_distance(%s,%s,%s,%s)*1000'), 'gps_distance'));
    } else {
      $s->addColumn(new SqlColumn(false, 'NULL', 'gps_distance'));
    }
    if ($this->_from) {
      if ($this->_to) {
        // kdyz je zadane od i do, hledam zdroj, ktery je volny nekdy v dane dobe
        $s->addStatement(new SqlStatementQuad(
                new SqlStatementQuad($this->_from, $s->columns['resourceavailability_start'], $s->columns['resourceavailability_start'], $this->_to, '%s<=%s AND %s<=%s'),
                new SqlStatementQuad($this->_from, $s->columns['resourceavailability_end'], $s->columns['resourceavailability_end'], $this->_to, '%s<=%s AND %s<=%s'),
                new SqlStatementQuad($this->_from, $s->columns['resourceavailability_start'], $s->columns['resourceavailability_end'], $this->_to, '%s<=%s AND %s<=%s'),
                new SqlStatementQuad($s->columns['resourceavailability_start'], $this->_from, $this->_to, $s->columns['resourceavailability_end'], '%s<=%s AND %s<=%s'),
                '((%s) OR (%s) OR (%s) OR (%s))'));
      } else {
        // kdyz je zadane jenom od, hledam zdroj ktery je volny prave v dany cas
        $s->addStatement(new SqlStatementQuad($s->columns['resourceavailability_start'], $this->_from, $this->_from, $s->columns['resourceavailability_end'], '%s<=%s AND %s<=%s'));
      }
    }
    
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setDistinct(true);
    $s->setColumnsMask(array('resource_id','name','description','price',
                             'provider_name','provider_www','provider_email','provider_phone_1','provider_phone_2',
                             'center_id','center_name','street','city','postal_code','gps_latitude','gps_longitude',
                             'unitprofile_name','gps_distance'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      // uz zdroje jeste vracim availability v danem obdodi
      $row['availability'] = array();
      $s = new SResourceAvailability;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $row['resource_id'], '%s=%s'));
      if ($this->_to) {
        // kdyz je zadane od i do, hledam availabilitu zdroje nekdy v dane dobe
        $s->addStatement(new SqlStatementQuad(
                new SqlStatementQuad($this->_from, $s->columns['start'], $s->columns['start'], $this->_to, '%s<=%s AND %s<=%s'),
                new SqlStatementQuad($this->_from, $s->columns['end'], $s->columns['end'], $this->_to, '%s<=%s AND %s<=%s'),
                new SqlStatementQuad($this->_from, $s->columns['start'], $s->columns['end'], $this->_to, '%s<=%s AND %s<=%s'),
                new SqlStatementQuad($s->columns['start'], $this->_from, $this->_to, $s->columns['end'], '%s<=%s AND %s<=%s'),
                '((%s) OR (%s) OR (%s) OR (%s))'));
      } else {
        // kdyz je zadane jenom od, hledam availabilitu zdroje prave v dane dobe
        $s->addStatement(new SqlStatementQuad($s->columns['start'], $this->_from, $this->_from, $s->columns['end'], '%s<=%s AND %s<=%s'));
      }
      $s->setColumnsMask(array('resourceavailability_id','start','end'));
      $s->addOrder(new SqlStatementAsc($s->columns['start']));
      $res1 = $this->_app->db->doQuery($s->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        if (substr($row1['start'],0,10)==substr($row1['end'],0,10)) {
          $row1['end'] = $this->_app->regionalSettings->convertTimeToHuman(substr($row1['end'],11),'h:m');
        } else {
          $row1['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row1['end']);
        }
        $row['availability'][] = array(
                          //'id'    => $row1['resourceavailability_id'],
                          'start' => $this->_app->regionalSettings->convertDateTimeToHuman($row1['start']),
                          'end'   => $row1['end']);
      }
      
      $this->_addItem($row, 'resource');
    }
  }
  
  private function _getEvent() {
    $s = new SEvent;
    
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if ($this->_portal) $s->addStatement(new SqlStatementBi($s->columns['portal'], $this->_portal, '%s=%s'));
    if ($this->_subject) $s->addStatement(new SqlStatementMono($s->columns['tag_id'], sprintf("%%s IN (%s)", $this->_app->db->escapeString($this->_subject))));
    if ($this->_address) $s->addStatement(new SqlStatementMono($s->columns['center_description'], sprintf("%%s LIKE '%%%%%s%%%%'", $this->_app->db->escapeString($this->_address))));
    if ($this->_latitude&&$this->_longitude&&$this->_gpsRange) {
      $s->addStatement(new SqlStatementPenta(
            $s->columns['gps_latitude'], $s->columns['gps_longitude'], $this->_latitude, $this->_longitude, $this->_gpsRange,
            'gps_distance(%s,%s,%s,%s)*1000<=%s'  
            ));
      $s->addColumn(new SqlColumn(false, new SqlStatementQuad($s->columns['gps_latitude'], $s->columns['gps_longitude'], $this->_latitude, $this->_longitude, 'gps_distance(%s,%s,%s,%s)*1000'), 'gps_distance'));
    } else {
      $s->addColumn(new SqlColumn(false, 'NULL', 'gps_distance'));
    }
    if ($this->_from) {
      if ($this->_to) {
        // kdyz je zadane od i do, hledam akci, ktera zacina v te dobe
        $s->addStatement(new SqlStatementQuad($this->_from, $s->columns['start'], $s->columns['start'], $this->_to, '%s<=%s AND %s<=%s'));
      } else {
        // kdyz je zadane jenom od, hledam akci, ktera probiha v te dobe
        $s->addStatement(new SqlStatementQuad($s->columns['start'], $this->_from, $this->_from, $s->columns['end'], '%s<=%s AND %s<=%s'));
      }
    }
      
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setDistinct(true);
    $s->setColumnsMask(array('event_id','name','description','price','start','free',
                             'provider_name','provider_www','provider_email','provider_phone_1','provider_phone_2',
                             'center_id','center_name','street','city','postal_code','gps_latitude','gps_longitude','gps_distance'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $row['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
      $this->_addItem($row, 'event');
    }
  }
  
  private function _arrangeItem() {
    foreach ($this->_item['resource'] as $item) {
      $this->_item[] = $item;
    }
    foreach ($this->_item['event'] as $item) {
      $this->_item[] = $item;
    }
    unset($this->_item['resource']);
    unset($this->_item['event']);
  }
  
  private function _sort() {
    switch ($this->_sort) {
      case 'name':     $mapping = 'center_name'; break;
      case 'price':    $mapping = 'price_from'; break;
      case 'range':    $mapping = 'gps_distance'; break;
      default: return;  
    }
    
    $mappingArray = array();
    foreach ($this->_item as $index=>$item) {
      $mappingArray[$index] = $item[$mapping];
    }
    
    array_multisort($mappingArray, SORT_ASC, $this->_item);
  }

  protected function _prepareResponse() {
    $this->_readRequest();
    
    $this->_getResource();
    $this->_getEvent();
    $this->_sort();
    
    $response = $this->_getResponseDataTag();
    
    $firstRecord = (($this->_page-1)*$this->_perPage)+1;
    $lastRecord = $firstRecord+$this->_perPage-1;
    if ($lastRecord>count($this->_item)) $lastRecord = count($this->_item);
    $totalPages = ceil(count($this->_item)/$this->_perPage);
    
    $navigationNode = $this->_respDoc->createElement('navigation');
    $response->appendChild($navigationNode);
    $node = $this->_respDoc->createElement('total_records', count($this->_item));
    $navigationNode->appendChild($node);
    $node = $this->_respDoc->createElement('page', $this->_page);
    $navigationNode->appendChild($node);
    $node = $this->_respDoc->createElement('total_pages', $totalPages);
    $navigationNode->appendChild($node);
    $node = $this->_respDoc->createElement('first_record', $firstRecord);
    $navigationNode->appendChild($node);
    $node = $this->_respDoc->createElement('last_record', $lastRecord);
    $navigationNode->appendChild($node);
    
    $index = 0;
    foreach ($this->_item as $i) {
      $index++;
      if ($index<$firstRecord) continue;
      if ($index>$lastRecord) break;
      
      $itemNode = $this->_respDoc->createElement('item');
      $response->appendChild($itemNode);
      foreach (array('provider_name','center_id','center_name','www','email','phone','street','city','postal_code',
                     'gps_latitude','gps_longitude','price_from','price_to','gps_distance') as $key) {
        $node = $this->_respDoc->createElement($key, $this->_convertOutput($i[$key]));
        $itemNode->appendChild($node);
      }
      $node = $this->_respDoc->createElement('currency', $this->_convertOutput($this->_app->textStorage->getText('label.currency_CZK')));
      $itemNode->appendChild($node);
      foreach ($i['detail'] as $d) {
        $detailNode = $this->_respDoc->createElement('detail');
        $itemNode->appendChild($detailNode);
        foreach ($d as $key=>$value) {
          if (!is_array($value)) {
            $node = $this->_respDoc->createElement($key, $value);
            $detailNode->appendChild($node);
          } elseif ($key === 'availability') {
            foreach ($value as $availability) {
              $node = $this->_respDoc->createElement('availability');
              $detailNode->appendChild($node);
              foreach ($availability as $aKey=>$aVal) {
                $node1 = $this->_respDoc->createElement($aKey, $this->_convertOutput($aVal));
                $node->appendChild($node1);
              }
            }
          }
        }
      }
    }
  }
}

?>