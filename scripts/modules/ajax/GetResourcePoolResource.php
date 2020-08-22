<?php

class AjaxGetResourcePoolResource extends AjaxAction {

  protected function _userRun() {
    #$this->_result = array('id'=>3);
    #return;
    
    // nejdriv zistim vsechny volne zdroje
    $s = new SResourcePoolItem;
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $this->_params['resourcePoolId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementMono($s->columns['resource_active'], "%s='Y'"));
    $s->setColumnsMask(array('resource'));
    $res = $this->_app->db->doQuery($s->toString());
    
    $resource = array();
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $bResource = new BResource($row['resource']);
      if ($avail = $bResource->getAvailability($this->_params['from'], $this->_params['to'])) $resource[] = array('id'=>$row['resource'],'reservation'=>array());
    }
    
    if (!count($resource)) return;

    $this->_result = array('id'=>array());
    $resourceOrder = array();
    if (count($resource)>1) {
      $reservationNum = array();
      // kdyz je vice volnych zdroju, vezmu ten, kde bude rezervace nnavazovat na jiz existujici nebo nebo ten, kde je nejvic rezervaci
      foreach ($resource as $index=>$r) {
        $reservationNum[$index] = 0;

        $s = new SReservation;
        $s->addStatement(new SqlStatementBi($s->columns['resource'], $r['id'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['cancelled'], '%s IS NULL'));
        $s->addStatement(new SqlStatementMono($s->columns['start'], sprintf("DATE(%%s)='%s'", substr($this->_params['from'],0,10))));
        $s->setColumnsMask(array('reservation_id','number','start','end'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          // kdyz existuje rezervace na zdroji, ktera bude primo navazovat na novou, tak to dal neresim a zdroj je vhodny pro rezervaci
          if (($row['end']==$this->_params['from'])||($row['start']==$this->_params['to'])) {
            $resourceOrder[] = $index;
            unset($reservationNum[$index]);
            break;
          }
          $resource[$index]['reservation'][] = array('id'=>$row['reservation_id'],'number'=>$row['number'],'start'=>$row['start'],'end'=>$row['end']);
          if (isset($reservationNum[$index])) $reservationNum[$index]++;
          else $reservationNum[$index] = 1;
        }
      }

      // zdroje, ktere nemaji rezervaci, ktera by navazovala, pridam do seznamu podle poctu rezervaci (nejvic rezervaci - nevhodnejsi zdroj)
      arsort($reservationNum);
      foreach (array_keys($reservationNum) as $index) $resourceOrder[] = $index;
    } else $resourceOrder[] = 0;

    // vratim vsechny volne zdroje v poradi, jak jsou vhodne pro rezervaci v danem terminu (toto je kvuli moznosti rezervaci vice zdroju z pool-u najednou)
    foreach ($resourceOrder as $index) {
      $this->_result['id'][] = $resource[$index]['id'];
    }
  }
}

?>
