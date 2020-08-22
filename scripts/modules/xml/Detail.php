<?php

class XMLActionDetail extends XMLAction {
  private $_id;
  private $_type;
  private $_from;
  private $_duration;
  private $_to;
  private $_data = array();
  
  private function _readRequest() {
    $type = $this->_reqDoc->getElementsByTagName('commodity_type');
    if ($type->length) {
      $this->_type = $type->item(0)->nodeValue;
    }
    if (!$this->_type) throw new ExceptionUser(XML_FATAL_MISSING_COMMODITY_TYPE);
    
    $id = $this->_reqDoc->getElementsByTagName('commodity_id');
    if ($id->length) {
      $this->_id = $id->item(0)->nodeValue;
    }
    if (!$this->_id) throw new ExceptionUser(XML_FATAL_MISSING_COMMODITY_ID);
    
    $from = $this->_reqDoc->getElementsByTagName('from');
    if ($from->length) {
      $this->_from = $from->item(0)->nodeValue;
      if ($this->_from&&!$this->_app->regionalSettings->checkHumanDateTime($this->_from)) throw new ExceptionUser(XML_FATAL_INVALID_FROM);
      $this->_from = $this->_app->regionalSettings->convertHumanToDateTime($this->_from);
    }
    if (!$this->_from) {
      $this->_from = $this->_app->regionalSettings->increaseDateTime(date('Y-m-d H:i:s'), 0, 0, 0, 2);
    }
    
    $duration = $this->_reqDoc->getElementsByTagName('duration');
    if ($duration->length) {
      $this->_duration = $duration->item(0)->nodeValue;
      if ($this->_duration) {
        $this->_to = $this->_from;
        
        if ($this->_duration==='exact_date') {
          list($date,$time) = explode(' ', $this->_from);
          $this->_to = $date . ' 23:59:59';
        } elseif (strpos($this->_duration,'plus_day_')!==false) {
          $day = str_replace('plus_day_', '', $this->_duration);
          $this->_to = $this->_app->regionalSettings->increaseDateTime($this->_to, $day); 
        } elseif (strpos($this->_duration,'plus_month_')!==false) {
          $day = str_replace('plus_month_', '', $this->_duration);
          $this->_to = $this->_app->regionalSettings->increaseDateTime($this->_to, 0, $day); 
        }
      }
    }
  }
  
  private function _prepareData($row, $type) {
    $this->_data = array(
          'id'                  => $row[$type.'_id'],
          'type'                => $type,
          'name'                => $row['name'],
          'description'         => $row['description'],
          'street'              => $row['street'],
          'city'                => $row['city'],
          'postal_code'         => $row['postal_code'],
          'center_id'           => $row['center_id'],
          'center_name'         => $row['center_name'],
          'center_payment_info' => $row['center_payment_info'],
          'provider_name'       => $row['provider_name'],
          'www'                 => $row['provider_www'],
          'email'               => $row['provider_email'],
          'phone'               => $row['provider_phone_1'].','.$row['provider_phone_2'],
          'gps_latitude'        => $row['gps_latitude'],
          'gps_longitude'       => $row['gps_longitude'],
          'price'               => $this->_app->regionalSettings->convertNumberToHuman($row['price']),
          'url_description'     => $row['url_description'],
          'url_price'           => $row['url_price'],
          'url_opening'         => $row['url_opening'],
          'url_photo'           => ($row['url_photo'])?explode(',',$row['url_photo']):array(),
          );
    /*if ($row['gps_latitude']&&$row['gps_longitude']) {
      global $GOOGLE_MAP_URL;
      $mapUrl = str_replace(array('{dims}','{coords}'), array('200x200',sprintf('%s,%s',$row['gps_latitude'],$row['gps_longitude'])),
                            $GOOGLE_MAP_URL);
      //echo $mapUrl;
      $this->_data['url_map'] = urlencode($mapUrl);
    }*/
    
    if ($type == 'resource') {
      $this->_data['unitprofile_name'] = $row['unitprofile_name'];
      $this->_data['unit'] = $row['unit'];
      
      // uz zdroje jeste vracim availability v danem obdodi
      $this->_data['availability'] = array();
      $s = new SResourceAvailability;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $this->_data['id'], '%s=%s'));
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
      $res = $this->_app->db->doQuery($s->toString());
      while ($row1 = $this->_app->db->fetchAssoc($res)) {
        if (substr($row1['start'],0,10)==substr($row1['end'],0,10)) {
          $row1['end'] = $this->_app->regionalSettings->convertTimeToHuman(substr($row1['end'],11),'h:m');
        } else {
          $row1['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row1['end']);
        }
        $this->_data['availability'][] = array(
                          'id'    => $row1['resourceavailability_id'],
                          'start' => $this->_app->regionalSettings->convertDateTimeToHuman($row1['start']),
                          'end'   => $row1['end']);
      }  
    } elseif ($type == 'event') {
      $this->_data['from'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
      $this->_data['free_places'] = $row['free'];
      $this->_data['organiser_fullname'] = $row['organiser_fullname'];
      $this->_data['organiser_email'] = $row['organiser_email'];
      $this->_data['attendees'] = $row['attendees'];
    }
  }

  protected function _prepareResponse() {
    $this->_readRequest();
    
    if ($this->_type == 'resource') {
      $s = new SResource;
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('resource_id','name','description','price',
                               'center_id','center_name','center_payment_info',
                               'street','city','postal_code','gps_latitude','gps_longitude',
                               'provider_name','provider_www','provider_email','provider_phone_1','provider_phone_2',
                               'unit','unitprofile_name',
                               'url_description','url_price','url_opening','url_photo'));
    } elseif ($this->_type == 'event') {
      $s = new SEvent;
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_id, '%s=%s'));
      $s->setColumnsMask(array('event_id','name','description','price','start',
                               'center_id','center_name','center_payment_info',
                               'street','city','postal_code','gps_latitude','gps_longitude',
                               'provider_name','provider_www','provider_email','provider_phone_1','provider_phone_2',
                               'organiser_fullname','organiser_email',
                               'free','attendees',
                               'url_description','url_price','url_opening','url_photo'));
    } else throw new ExceptionUser(XML_FATAL_INVALID_COMMODITY_TYPE);
    
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_app->db->getRowsNumber($res)) throw new ExceptionUser(XML_FATAL_INVALID_COMMODITY);
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $this->_prepareData($row, $this->_type);
    }
    
    $response = $this->_getResponseDataTag();
    foreach ($this->_data as $key=>$val) {
      if ($key == 'availability') {
        foreach ($val as $availability) {
          $node = $this->_respDoc->createElement('availability');
          $response->appendChild($node);
          foreach ($availability as $aKey=>$aVal) {
            $node1 = $this->_respDoc->createElement($aKey, $this->_convertOutput($aVal));
            $node->appendChild($node1);
          }
        }
      } elseif ($key == 'url_photo') {
        if (count($val)) {
          foreach ($val as $photo) {
            $node = $this->_respDoc->createElement('photo', $photo);
            $response->appendChild($node);
          }
        }
      } else {
        $node = $this->_respDoc->createElement($key, $this->_convertOutput($val));
        $response->appendChild($node);
      }
    }
    $node = $this->_respDoc->createElement('currency', $this->_getTSText('label.currency_CZK'));
    $response->appendChild($node);
  }
}

?>