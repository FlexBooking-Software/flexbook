<?php

class ModuleInPageCzechTourismResource extends InPageModule {
  
  protected function _insertDescription() {
    $this->insertTemplateVar('description', '
<p>Workshop s řediteli zahraničních zastoupení je platformou pro projednání možností navázání vzájemné spolupráce a probíhá formou předem sjednaných 10minutových schůzek.</p>
<p>Workshopu se zúčastní 19 ředitelů zahraničních zastoupení z těchto zemí: Benelux, Čína (Peking, Šanghaj), Hong Kong & jihovýchodní Asie, Francie, Itálie, Jižní Korea, Japonsko, Mexiko & Latinská Amerika, Německo, Polsko, Rakousko, Rusko, Slovensko, Španělsko, Švédsko, USA & Kanada (New York, Los Angeles), Velká Británie.</p>
<p>V rámci workshopu bude také možné sjednat si schůzky se zástupcem agentury CzechTourism k možnostem spolupráce v rámci mezinárodních projektů: podpora přímé linky HAINAN Airlines Peking - Praha, spolupráce zemí střední a východní Evropy s Čínou CEEC 16+1; spolupráce zemí Visegradské čtyřky.</p>
<table>
<tr><td class="legend_free">&nbsp;</td><td>volný termín</td><td class="legend_occupied">&nbsp;</td><td>obsazený termín</td><td class="legend_na">&nbsp;</td><td>přestávka</td></tr>
</table>', false);
  }
  
  protected function _userInsert() {
    $this->setTemplateString('{resourceList}<form class="form" action="inpage.php" method="post">{%sessionInput%}<input class="button" type="submit" name="action_eBack" value="{__button.back}"/></form>');
    
    $validator = Validator::get('inpage', 'InPageValidator');
    $data = $validator->getValues();
    if ($data['czechTourism_resourceTag']=='praha') {
      $center = 36;
      $startTime = '2015-07-22 13:30:00';
      $endTime = '2015-07-22 17:00:00';
    } else {
      $center = 37;
      $startTime = '2015-07-24 10:40:00';
      $endTime = '2015-07-24 12:30:00';
    }
    
    $template = '';
    
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $data['providerId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementBi($s->columns['center'], $center, '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name','center','center_name','description'));
    $res = $this->_app->db->doQuery($s->toString());
    $template .= '<table class="eventList resource"><tr class="header">
                       <th colspan="2"><b>{title}</b><br/><span class="italic">{place}</span></th>
                       </tr>';
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $when = $startTime;
      $bResource = new BResource($row['resource_id']);
      $slotTable = '<table class="slot"><tr>';
      while ($when<$endTime) {
        list($date,$time) = explode(' ', $when);
        if ($bResource->getAvailability($this->_app->regionalSettings->increaseDateTime($when,0,0,0,0,1))) {
          $slotTable .= sprintf('<td class="free"><a title="%s" href="inpage.php?action=vInPageCzechTourismResourceDetail&id=%s&time=%s%s">%s</a></td>',
                                $this->_app->textStorage->getText('button.inpage_event_reserve'),
                                $row['resource_id'], $when, $this->_app->session->getTagForUrl(),
                                $this->_app->regionalSettings->convertTimeToHuman($time,'h:m'));
        } elseif ($bResource->getReservation($when)) {
          $slotTable .= sprintf('<td class="occupied">%s</td>', $this->_app->regionalSettings->convertTimeToHuman($time, 'h:m'));
        } else {
          $slotTable .= sprintf('<td class="na">%s</td>', $this->_app->regionalSettings->convertTimeToHuman($time, 'h:m'));
        }
        $when = $this->_app->regionalSettings->increaseDateTime($when,0,0,0,0,10);
      }
      $slotTable .= '</tr></table>';
      
      $template .= sprintf('<tr><td title="%s">%s</td><td>%s</td></tr>',
                           $row['description'], $row['name'], $slotTable);
    }
    $template .= '</table>';
    $g = new GuiElement(array('template'=>$template));
    if ($center==36) {
      $g->insertTemplateVar('place', $this->_app->textStorage->getText('label.czechTourism_resourceListPraha_place'));
      $g->insertTemplateVar('title', $this->_app->textStorage->getText('label.czechTourism_resourceListPraha_title'));
    } else {
      $g->insertTemplateVar('place', $this->_app->textStorage->getText('label.czechTourism_resourceListBrno_place'));
      $g->insertTemplateVar('title', $this->_app->textStorage->getText('label.czechTourism_resourceListBrno_title'));
    }
    
    $this->insert($g, 'resourceList');
  }
}

?>
