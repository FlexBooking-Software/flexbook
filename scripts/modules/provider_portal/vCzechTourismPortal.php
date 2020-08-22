<?php

class ModuleCzechTourismPortal extends DocumentModule {
  private $_orderExists = false;
  private $_orderPayed = false;
  private $_template = array(
      'welcome'       => '<span class="static">Dear Partners,<br/>
<br/>
Czech Tourism Authority - CzechTourism, together with the Embassy of the Czech Republic in Israel, the spa town of Karlovy Vary and the Travel Service airline company are delighted to cordially invite you to B2B workshop “Czech Republic – Land of Stories” which will be held on December 8th at the Leonardo Art hotel.
<br/><br/>
We believe that the workshop will be a great opportunity to make new contacts, which can be used in the future as a basis for solid business relations.
<br/>
Best regards,<br/>
<br/>
Monika Palatková<br/>
managing director<br/>
Czech Tourism Authority - CzechTourism </span>',
      'registration'  => '<span class="static">Dear Partners,<br/>
<br/>
Welcome to the reservation system for the B2B workshop “Czech Republic – Land of Stories”.<br/>
<br/>
If you wish to make an appointment with any Czech company, please sign up.{registrationButton}<br/>
<br/>
After filling in the form you will receive an email stating that your registration was successful. You can then log in to the system and reserve a maximum of 8 meetings with the Czech participants. Each meeting lasts 10 minutes.<br/>
<br/>
The workshop is free of charge.',
      'program'       => '<span class="static"><span class="title">B2B workshop „Czech Republic – Land of Stories“</span><br/>
<br/>
<span class="subtitle">8. December 2015, 10:00 – 14:30<br/>Hotel Leonardo Art,</span><br/>
<b>9 Eliezer Peri Street, Tel Aviv</b><br/>
<br/>
<b>Program:</b><br/>
<table>
<tr><td style="width: 150px;">10:00 - 10:30</td><td>Registration</td></tr>
<tr><td>10:30 - 10:40</td><td>Welcome speech</td></tr> 
<tr><td>10:40 - 11:30</td><td>Presentations: the Czech Republic – Land of Stories; spa town Karlovy Vary; Czech participants</td></tr> 
<tr><td>11:30 - 12:00</td><td>Coffee break</td></tr>
<tr><td>12:00 - 13:30</td><td>Workshop</td></tr>
<tr><td>13:30 - 14:30</td><td>Lunch</td></tr>
</table>',
       'contact'     => '<span class="static">If you have any question or request, please contact:<br/>
<br/>
<b>Silvie Santarová</b><br/>
<a href="mailto:s.santarova@travel-marketing.cz">s.santarova@travel-marketing.cz</a>');
  
  private function _insertLoginForm($data) {
    if (!$this->_app->auth->getUserId()) {
      $template = '
          <form class="login" action="inpage.php">
            {%sessionInput%}
            <label for="fi_login_username">User name (email):</label>
            <input class="mediumText" type="text" name="username" value="" />
            <label for="fi_password">Password:</label>
            <input class="mediumText" type="password" name="password" value="" />
            <input type="hidden" name="progressAction" value="vCzechTourismPortal?section=userRegistration" />
            <input type="hidden" name="finishAction" value="vCzechTourismPortal?section=welcome" />
            <input class="button" type="submit" name="action_eInPageLogin" value="Log in" />
            <input class="button" id="fb_registrateButton" type="submit" name="action_eInPageRegistration" value="Sign up" />
          </form>
          ';
    } else {
      $template = sprintf('<form class="login">
          <span class="name">%s</span>&nbsp;&nbsp;&nbsp;[<a href="inpage.php?action=eInPageLogout%s">Sign out</a>] [<a href="inpage.php?action=vCzechTourismPortal&section=userReservation%s">List of reservations</a>]
          </form>', $this->_app->auth->getFullname(), $this->_app->session->getTagForUrl(), $this->_app->session->getTagForUrl());
    }
    
    $this->insert(new GuiElement(array('template'=>$template)), 'loginForm');
  }
  
  private function _insertOrder() {
    $gui = new GuiElement(array('template'=>'
          <form class="normal event" action="{%basefile%}" method="post">
            <div>
              <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
              {form}
              <input class="button" type="submit" name="action_eCzechTourismPortalOrderSave" value="{__button.czechTourism_order}" {onclick}/>
            </div>
          </form>'));
    $gui->insertTemplateVar('form', czechTourismGetConfirmDialog2(), false);
    if (!$this->_app->auth->getUserId()) $gui->insertTemplateVar('onclick', sprintf(' onclick="alert(\'%s\');return false;"', $this->_app->textStorage->getText('label.inpage_loginRequired')) ,false);
    else $gui->insertTemplateVar('onclick', '');
    
    $this->insert($gui);
  }
  
  protected function _insertTimeTable() {
    $validator = Validator::get('inpage', 'InPageValidator');
    $data = $validator->getValues();
    
    $provider = $data['providerId'];
    $center = 58;
    $start = '2015-12-08 00:00:00';
    $end = '2015-12-08 23:59:59';
    
    $template = '';
    
    $info = '';
    if (!$this->_app->auth->getUserId()) $info = '<tr><td colspan="8"><div class="slotInfo">If you wish to make a reservation, please sign up.</div></td></tr>';
    
    // nejdriv nactu rezervace akci prihlaseneho uzivatele, aby sli odlisit
    $s = new SReservation;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['cancelled'], "%s IS NULL"));
    $s->addStatement(new SqlStatementBi($s->columns['center'], $center, '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['user'], $this->_app->auth->getUserId(), '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['event'], "%s IS NOT NULL"));
    $s->setColumnsMask(array('reservation_id', 'event'));
    $res = $this->_app->db->doQuery($s->toString());
    $reservedEvent = array();
    while ($row = $this->_app->db->fetchAssoc($res)) $reservedEvent[] = $row['event'];
    
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addStatement(new SqlStatementBi($s->columns['center'], $center, '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name','center','center_name','description'));
    $res = $this->_app->db->doQuery($s->toString());
    $template .= sprintf('<table>%s                    
<tr><td class="legend_free">&nbsp;</td><td>Available</td><td class="legend_mine">&nbsp;</td><td>Reserved</td><td class="legend_occupied">&nbsp;</td><td>Booked</td></tr>
</table><table class="eventList resource"><tr class="header">
                       <th colspan="2"><b>AVAILABLE WORKSHOPS</b></th>
                       </tr>', $info);
    
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $slotTable = '<table class="slot"><tr>';
      
      $s = new SEvent;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $row['resource_id'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $provider, '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
      $s->addStatement(new SqlStatementBi($s->columns['center'], $center, '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['start'], $start, '%s>=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['end'], $end, '%s<=%s'));
      $s->setColumnsMask(array('event_id','start','name','free'));
      $res1 = $this->_app->db->doQuery($s->toString());
      $lastTime = null;
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        list($date,$time) = explode(' ', $row1['start']);
        if ($lastTime&&($this->_app->regionalSettings->decreaseTime($time,0,10)>$lastTime)) {
          $slotTable .= '<td class="break">&nbsp;</td>';
        }
  
        if ($row1['free']) {
          if ($this->_app->auth->getUserId()) $href = sprintf('inpage.php?action=vCzechTourismPortal&section=event&event_id=%s%s', $row1['event_id'], $this->_app->session->getTagForUrl());
          else $href = '#';

          $slotTable .= sprintf('<td class="free"><a title="%s" href="%s">%s</a></td>',
                                $this->_app->textStorage->getText('button.inpage_event_reserve'),
                                $href, 
                                $this->_app->regionalSettings->convertTimeToHuman($time,'h:m'));
        } else {
          $class = 'occupied';
          $title = '';
          if (in_array($row1['event_id'], $reservedEvent)) {
            $class .= ' mine';
            $title = sprintf(' title="%s"', $this->_app->textStorage->getText('label.czechTourismPortal_alreadyReserved'));
          }
          $slotTable .= sprintf('<td class="%s"%s>%s</td>', $class, $title, $this->_app->regionalSettings->convertTimeToHuman($time, 'h:m'));
        }
        $lastTime = $time;
      }
      $slotTable .= '</tr></table>';
      
      $template .= sprintf('<tr><td title="%s" class="name">%s</td><td class="slot">%s</td></tr>',
                           $row['description'], $row['name'], $slotTable);
    }
    $template .= '</table>';
    $g = new GuiElement(array('template'=>$template));
    
    $this->insert($g);
  }
  
  private function _insertEvent() {
    $g = new GuiElement(array('template'=>'<form class="normal event" action="{%basefile%}" method="post">
  <div>
    <input type="hidden" name="{%sessname%}" value="{%sessid%}" />
    <input type="hidden" name="id" value="{event_id}" />
    <div class="title"><label class="bold">{__label.inpage_event_name}:</label>
    <label>{name} - {resource_name}</label></div>
    <label class="bold">{__label.inpage_event_center}:</label>
    <label>{center_name}</label><br/>
    <label class="bold">{__label.inpage_event_start}:</label>
    <label>{start}</label><br/>
    <label class="bold">{__label.inpage_event_end}:</label>
    <label>{end}</label><br/>
    <label class="bold">{__label.inpage_event_description}:</label>
    <label>{description} - {resource_description}</label><br/>
    <div class="title"><label class="bold">{__label.inpage_event_price}:</label>
    <label>{price} {__label.currency_CZK}</label></div>
    <br/>
    <input class="button" type="submit" name="action_eBack" value="{__button.back}" />
    {reserveButton}
  </div>
</form>'));
    
    $s = new SEvent;
    $s->addStatement(new SqlStatementBi($s->columns['event_id'], $this->_app->request->getParams('event_id'), '%s=%s'));
    $s->setColumnsMask(array('event_id','start','end','name','resource_name','center_name','price','description','resource_description','free','free_substitute'));
    $res = $this->_app->db->doQuery($s->toString());
    $row = $this->_app->db->fetchAssoc($res);
    $row['start'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['start']);
    $row['end'] = $this->_app->regionalSettings->convertDateTimeToHuman($row['end']);
    $row['description'] = str_replace("\n",'<br/>',$row['description']);
    $row['resource_description'] = str_replace("\n",'<br/>',$row['resource_description']);
    foreach($row as $key=>$value) {
      $g->insertTemplateVar($key, $value,false); 
    }
    
    if (!$this->_app->auth->getUserId()) {
      $onclick = sprintf(' onclick="alert(\'%s\');return false;"', $this->_app->textStorage->getText('label.inpage_loginRequired'));
    } else {
      $onclick = '';
    }
    
    if ($row['free']) {
      $g->insertTemplateVar('reserveButton', sprintf('<input class="button" type="submit" name="action_eCzechTourismPortalEventReserve" value="%s" %s/>',
                                                        $this->_app->textStorage->getText('button.inpage_event_reserve'), $onclick), false);
    } else $g->insertTemplateVar('reserveButton', '');
    
    $this->insert($g);
  }
  
  private function _insertContent($data) {
    $section = $data['portalData']['section'];
    switch ($section) {
      case 'userRegistration': $this->insert(new GuiInPageRegistration); break;
      case 'userOrder': $this->_insertOrder(); break;
      case 'userReservation': $this->insert(new GuiInPageReservationList); break;
      case 'registration':
        $t = $this->_template['registration']; 
        if (!$this->_app->auth->getUserId()) $t = str_replace('{registrationButton}', '&nbsp;<input type="submit" class="button" value="Sign up" onclick="document.getElementById(\'fb_registrateButton\').click();return false;"/>', $t);
        else $t = str_replace('{registrationButton}', '', $t);
        if ($this->_app->auth->getUserId()&&!$this->_orderExists) $t = str_replace('{orderButton}', '&nbsp;<input type="submit" class="button" value="Objednávka" onclick="document.getElementById(\'fi_orderUrl\').click();return false;"/> ', $t);
        else $t = str_replace('{orderButton}', '', $t);
        if ($this->_app->auth->getUserId()&&$this->_orderExists) $t = str_replace('{reservationButton}', '&nbsp;<input type="submit" class="button" value="Rezervace" onclick="document.getElementById(\'fi_reservationUrl\').click();return false;"/> ', $t);
        else $t = str_replace('{reservationButton}', '', $t);
        
        $this->insertTemplateVar('children', $t, false);
        break;
      case 'reservation': $this->_insertTimeTable(); break;
      case 'event': $this->_insertEvent(); break;
      default: $this->insertTemplateVar('children', $this->_template[$section], false);
    }
  }
  
  private function _getInternalData($providerId) {
    if ($id=$this->_app->auth->getUserId()) {
      $bUser = new BUser($id);
      $attr = $bUser->getAttribute($providerId, true);
      
      if (isset($attr[9])&&$attr[9]) $this->_orderExists = true;
      if (isset($attr[8])&&($attr[8]['value']=='Y')) $this->_orderPayed = true;
    }
  }
  
  protected function _projectInsert() {
    global $HTTPS;
    if ($HTTPS) { Application::get()->setProtocol('https'); }

    $this->addJavascriptFile('script.js');
    
    // jQuery
    $this->_app->document->addJavascriptFile('jq/jquery.js');
    $this->_app->document->addJavascriptFile('jq/jquery-ui.js');
    $this->_app->document->addCssFile('jq/jquery-ui.css');   
    $this->_app->document->addJavascriptFile('jq/jquery.datetimepicker.js');
    $this->_app->document->addCssFile('jq/jquery.datetimepicker.css');
    
    $this->insert(new GuiMessages, 'message');
    $this->insert(new GuiDialog, 'dialog');
    
    $validator = Validator::get('inpage', 'InPageValidator');
    $data = $validator->getValues();
    
    $this->_getInternalData($data['providerId']);
    
    if ($section = $this->_app->request->getParams('section')) {
      // kdyz se uzivatel odloguje, tak musi vypadnout z nekterych sekci
      if (in_array($section,array('userOrder','userReservation','userSchedule'))&&!$this->_app->auth->getUserId()) $section = '';
      
      $data['portalData']['section'] = $section;
      $validator->setValues(array('portalData'=>$data['portalData']));
    }
    
    $this->setTemplateString(sprintf('
          <div class="page">
            {message}{dialog}
            <a href="{%%basefile%%}?id=czechtourism{%%sessionUrl%%}"><img src="img/ct_logo.png"/></a>
            {loginForm}
            <hr/>
            <div>
              <div class="menu">
                <div><a href="{%%basefile%%}?action=vCzechTourismPortal&section=welcome{%%sessionUrl%%}">{__label.czechTourismPortal_menu1}</a></div>
                <div><a href="{%%basefile%%}?action=vCzechTourismPortal&section=registration{%%sessionUrl%%}">{__label.czechTourismPortal_menu2}</a></div>
                <div><a href="{%%basefile%%}?action=vCzechTourismPortal&section=program{%%sessionUrl%%}">{__label.czechTourismPortal_menu3}</a></div>
                <div><a id="fi_reservationUrl" href="{%%basefile%%}?action=vCzechTourismPortal&section=reservation{%%sessionUrl%%}">{__label.czechTourismPortal_menu4}</a></div>
                <div class="last"><a href="{%%basefile%%}?action=vCzechTourismPortal&section=contact{%%sessionUrl%%}">{__label.czechTourismPortal_menu5}</a></div>
              </div>
              <div class="titleBar">{__label.czechTourismPortal_titleBar}</div>
              <div class="content">{children}</div>
              <div class="cleaner">&nbsp;</div>
            </div>
          </div>'
          ));
    
    $this->setTitle('CzechTourism');
    $this->addCssFile('czechtourismportal_style.css');
    
    if (!$providerName = $validator->getVarValue('providerName')) $providerName = 'NO PROVIDER!';
    $this->insertTemplateVar('title', $providerName);
        
    $this->_insertLoginForm($data);
    
    if (!isset($data['portalData']['section'])||!$data['portalData']['section']) {
      $data['portalData']['section'] = 'registration';
    }
    $this->_insertContent($data);
    
  }
}

?>
