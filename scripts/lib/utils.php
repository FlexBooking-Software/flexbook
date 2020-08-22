<?php

function getEncodingError($string) {
  $ret = false;
  
  // kontrola na povolene znaky v kodovani ISO-8859-2
  for ($i=0;$i<strlen($string);$i++) {
    $ordChar = ord($string[$i]);
    if ((128<=$ordChar)&&($ordChar<=159)) {
      $ret = array('position'=>$i,'ordValue'=>$ordChar);
      break;
    }
  }
  
  return $ret;
}

function getNextWeekTerm(& $from, & $to, $weekday, $skipWeek=null) {
  $rs = Application::get()->regionalSettings;

  if (is_array($weekday)) {
    if (count($weekday)) {
      $weekNumber = date('W', strtotime($from));
      do {
        // budu pridavat den dokud novy den v tydnu noveho datumu neni v povolenych dnech v tydnu
        $from = $rs->increaseDateTime($from,1);
        $to = $rs->increaseDateTime($to,1);
        $dayOfWeek = strtolower(date('D', strtotime($from)));
      } while (!isset($weekday[$dayOfWeek])||!$weekday[$dayOfWeek]);
      // kdyz nalezeny datum pretekl do dalsiho tydne, musim preskocit pres jeden tyden
      if ($skipWeek) {
        $newWeekNumber = date('W', strtotime($from));
        if ($newWeekNumber>$weekNumber) {
          $from = $rs->increaseDateTime($from,7*$skipWeek);
          $to = $rs->increaseDateTime($to,7*$skipWeek);
        }
      }
    } else {
      $from = '9999-99-99 12:00:00';
    }
  } else {
    // kdyz nejsou definovany povolene dny v tydnu, opakuje se jednou za tyden
    $from = $rs->increaseDateTime($from, 7+(7*$skipWeek));
    $to = $rs->increaseDateTime($to, 7+(7*$skipWeek));
  }
}

function getNextMonthTerm(& $from, & $to, $weekday, $weekdayOrder, $skipMonth=null) {
  $rs = Application::get()->regionalSettings;

  if ($weekdayOrder) {
    if (is_array($weekday)&&count($weekday)) {
      switch ($weekdayOrder) {
        case 1: $orderString = 'first';break;
        case 2: $orderString = 'second';break;
        case 3: $orderString = 'third';break;
        case 4: $orderString = 'fourth';break;
      }
      $dayHash = array('mon'=>'monday','tue'=>'tuesday','wed'=>'wednesday','thu'=>'thursday','fri'=>'friday','sat'=>'saturday','sun'=>'sunday');
      // projdu vsechny povolene dny v tydnu a pokud x-ty dany den v tydnu v danem mesici je vetsi aktualni datum, beru ho
      $candidate = null;
      foreach (array_keys($weekday) as $day) {
        $newDate = date('Y-m-d', strtotime(sprintf('%s %s of %s', $orderString, $dayHash[$day], substr($from,0,7))));
        if (($newDate>$from)&&(!$candidate||($newDate<$candidate))) $candidate = $newDate;
      }
      if (!$candidate) {
        // kdyz v danem mesici uz vsechny pozadovane dny byly, vezmu nejmensi z dalsiho mesice
        foreach (array_keys($weekday) as $day) {
          $newDate = date('Y-m-d', strtotime(sprintf('%s %s of %s', $orderString, $dayHash[$day], substr($rs->increaseDateTime($from,0,1+$skipMonth),0,7))));
          if (!$candidate||($newDate<$candidate)) $candidate = $newDate;
        }
      }
      // @todo poresit, kdyz akce neni v jednom dni (datumove)
      $from = sprintf('%s %s', $candidate, substr($from,11));
      $to = sprintf('%s %s', $candidate, substr($to,11));
    } else {
      $from = '9999-99-99 12:00:00';
    }
  } else {
    // kdyz nejsou definovany povolene dny v tydnu, opakuje se jednou za mesic
    $from = $rs->increaseDateTime($from, 0, 1+(1*$skipMonth));
    $to = $rs->increaseDateTime($to, 0, 1+(1*$skipMonth));
  }
}

function getNextIndividualTerm(& $from, & $to, $individual) {
  $rs = Application::get()->regionalSettings;

  $candidate = null;

  $individual = explode(',', $individual);
  if (is_array($individual)&&count($individual)) {
    // individualni terminy jsou serazeny vzestupne, vemu prvni vetsi nez aktualni $from
    foreach ($individual as $date) {
      if ($rs->checkDate($date)&&($date>$from)) {
        $candidate = $date;
        break;
      }
    }

    if ($candidate) {
      // @todo poresit, kdyz akce neni v jednom dni (datumove)
      $from = sprintf('%s %s', $candidate, substr($from, 11));
      $to = sprintf('%s %s', $candidate, substr($to, 11));
    }
  }

  if (!$candidate) {
    $from = '9999-99-99 12:00:00';
  }
}

function getNextRepeatTerm($cycle, & $from, & $to, $weekday=null, $weekdayOrder=null, $individual=null) {
  $rs = Application::get()->regionalSettings;
  switch ($cycle) {
    case 'DAY_1': $from = $rs->increaseDateTime($from,1); $to = $rs->increaseDateTime($to,1); break;
    case 'DAY_2': $from = $rs->increaseDateTime($from,2); $to = $rs->increaseDateTime($to,2); break;
    case 'WEEK_1': getNextWeekTerm($from,$to,$weekday); break;
    case 'WEEK_2': getNextWeekTerm($from,$to,$weekday,1); break;
    case 'WEEK_3': getNextWeekTerm($from,$to,$weekday,2); break;
    case 'MONTH_1': getNextMonthTerm($from,$to,$weekday,$weekdayOrder); break;
    case 'MONTH_2': getNextMonthTerm($from,$to,$weekday,$weekdayOrder,1); break;
    case 'YEAR_1': $from = $rs->increaseDateTime($from,0,0,1); $to = $rs->increaseDateTime($to,0,0,1); break;
    case 'INDIVIDUAL': getNextIndividualTerm($from,$to,$individual); break;
  }
  //error_log(sprintf('new repeat term: %s - %s', $from, $to));
}

function czechTourismGetConfirmDialog($forMail=false) {
  $template = '<div class="confirmDialog">
      <div class="title">POTVRZENÍ ZÁVAZNÉ OBJEDNÁVKY</div>
      <div class="bold">DODAVATEL:</div>
      <div class="fromAddress">
        Česká centrála cestovního ruchu - CzechTourism<br/>
        Vinohradská 46, P.O.Box 32, 120 41 Praha 2<br/>
        tel.: +420 221 580 111<br/>
        fax: +420 224 247 516<br/>
        IČO: 49277600<br/>
        DIČ: CZ49277600<br/>
        Bankovní spojení: 87637-011 / 0100, KB Praha Václavské nám. <br/>
      </div>
      <div class="subject">Název akce: Travel Trade Day Networkingový večer</div>
      <div class="subject">Termín a místo konání: Parkhotel Praha 22.7.2015 od 18 hodin</div>
      <div class="bold">OBJEDNATEL:</div>
      <div class="to">
        <table>
          <tr><td class="bold">Firma:</td><td colspan="3"><input class="text" type="text" name="company" value="{company}"/></td></tr>
          <tr><td class="bold">Křestní jméno:</td><td><input type="text" class="text" name="firstname" value="{firstname}"/></td><td class="bold">Příjmení:</td><td><input type="text" class="text" name="lastname" value="{lastname}"/></td></tr>
          <tr><td class="bold">Email:</td><td colspan="3"><input type="text" class="text" name="email" value="{email}"/></td></tr>
          <tr><td class="bold">Ulice:</td><td><input type="text" class="text" name="street" value="{street}"/></td><td class="bold">IČO:</td><td><input type="text" class="text" name="ic" value="{ic}"/></td></tr>
          <tr><td class="bold">Město:</td><td><input type="text" class="text" name="city" value="{city}"/></td><td>DIČ:</td><td><input type="text" class="text" name="dic" value="{dic}"/></td></tr>
          <tr><td class="bold">PSČ:</td><td><input type="text" class="text" name="postalCode" value="{postalCode}"/></td><td class="bold">Telefon:</td><td><input type="text" class="text" name="phone" value="{phone}"/></td></tr>
          <tr><td class="bold">Stát:</td><td colspan="3"><input type="text" class="text" name="state" value="{state}"/></td></tr>
        </table>
      </div>
      <div class="info">Tučné položky musí být vyplněny.</div>
      <div class="subject">Účastnický poplatek: 250 Kč</div>
      <div class="comment">Fakturu dle uvedených údajů objednatele vystaví ČCCR - CzechToursim.</div>
      <div class="comment">Objednatel je povinen uhradit fakturu do 15 dní po jejím obdržení.</div>
      <div class="comment">STORNO PODMÍNKY: V případě, že objednatel zruší svou účast na akci, je dodavatel oprávněn požadovat stornovací poplatek ve výši 100% celkové částky.</div>
      <div class="subject">Cena celkem bez DPH: 250 Kč</div>';
  if (!$forMail) {
    $template .= '<input type="hidden" name="confirm" value="" />
      <input type="checkbox" name="confirm" value="1"/>
      <span class="comment">Souhlasím s výše uvedenými podmínkami a zavazuji se k jejich dodržení.</span>';
  } else {
    $template .= '<style media="screen" type="text/css">
      .confirmDialog { margin-bottom: 20px; }
      .confirmDialog .text { width: 220px; }
      .confirmDialog .title { font-weight: bold; }
      .confirmDialog .subject { font-weight: bold; font-size: 1.1em; margin: 15px 0; }
      .confirmDialog .comment { font-weight: bold; }
      .confirmDialog table td.bold { font-weight: bold; }
    </style>';
  }
  $template .= '</div>';
    
  $validator = Validator::get('login','InPageLoginValidator');
  $data = $validator->getValues();
  
  $gui = new GuiElement(array('template'=>$template));
  $gui->insertTemplateVar('company', $data['company']);
  $gui->insertTemplateVar('firstname', $data['firstname']);
  $gui->insertTemplateVar('lastname', $data['lastname']);
  $gui->insertTemplateVar('email', $data['email']);
  $gui->insertTemplateVar('street', $data['street']);
  $gui->insertTemplateVar('city', $data['city']);
  $gui->insertTemplateVar('postalCode', $data['postalCode']);
  $gui->insertTemplateVar('state', $data['state']);
  $gui->insertTemplateVar('ic', $data['ic']);
  $gui->insertTemplateVar('dic', $data['dic']);
  $gui->insertTemplateVar('phone', $data['phone']);
  
  return $gui->render();
}

function czechTourismGetConfirmDialog2($forMail=false) {
  $template = '<div class="confirmDialog">
      <div class="title">ZÁVAZNÁ OBJEDNÁVKA ÚČASTI</div>
      <div class="subject">Název akce: Czech Republic - Land of Stories, INCOMING WORKSHOP</div>
      <div class="subject">Termín: 20.10. 2015</div>
      <div class="subject">Místo konání: <br/>PVA EXPO Praha<br/>Beranových 667, 199 00 Praha 9 – Letňany<br>Kongresový sál, 1. patro</div>
      <div class="info">Pořadatel: PVA EURO EXPO, a.s., Mimoňská 645, 190 00  Praha 9, IČ: 27888550 DIČ: 27888550</div>
      <div class="bold">Společnost:</div>
      <div class="to">
        <table>
          <tr><td class="bold">Firma:</td><td colspan="3"><input class="text" type="text" name="company" value="{company}" tabindex="1"/></td></tr>
          <tr><td class="bold">Ulice:</td><td><input type="text" class="text" name="street" value="{street}" tabindex="2"/></td><td class="bold">IČ:</td><td><input type="text" class="text" name="ic" value="{ic}" tabindex="6"/></td></tr>
          <tr><td class="bold">Město:</td><td><input type="text" class="text" name="city" value="{city}" tabindex="3"/></td><td>DIČ:</td><td><input type="text" class="text" name="dic" value="{dic}" tabindex="7"/></td></tr>
          <tr><td class="bold">PSČ:</td><td colspan="3"><input type="text" class="text" name="postalCode" value="{postalCode}" tabindex="4"/></td></tr>
          <tr><td class="bold">Stát:</td><td colspan="3"><input type="text" class="text" name="state" value="{state}" tabindex="5"/></td></tr>
        </table>
      </div>
      <br/>
      <div class="bold">Zástupce společnosti:</div>
      <div class="to">
        <table>
          <tr><td class="bold">Jméno zástupce 1:</td><td><input type="text" class="text" name="firstname1" value="{firstname1}" tabindex="8"/></td><td>Jméno zástupce 2:</td><td><input type="text" class="text" name="firstname2" value="{firstname2}" tabindex="13"/></td></tr>
          <tr><td class="bold">Příjmení zástupce 1:</td><td><input type="text" class="text" name="lastname1" value="{lastname1}" tabindex="9"/></td><td>Příjmení zástupce 2:</td><td><input type="text" class="text" name="lastname2" value="{lastname2}" tabindex="14"/></td></tr>
          <tr><td class="bold">Funkce:</td><td><input type="text" class="text" name="role1" value="{role1}" tabindex="10"/></td><td>Funkce:</td><td><input type="text" class="text" name="role2" value="{role2}" tabindex="15"/></td></tr>
          <tr><td class="bold">Email:</td><td><input type="text" class="text" name="email1" value="{email1}" tabindex="11"/></td><td>Email:</td><td><input type="text" class="text" name="email2" value="{email2}" tabindex="16"/></td></tr>
          <tr><td class="bold">Telefon:</td><td><input type="text" class="text" name="phone1" value="{phone1}" tabindex="12"/></td><td>Telefon:</td><td><input type="text" class="text" name="phone2" value="{phone2}" tabindex="17"/></td></tr>
          <tr><td colspan="2">&nbsp;</td><td>Catering pro 2.účastníka - 300,- Kč</td><td><input type="hidden" name="catering" value=""/><input type="checkbox" class="checkbox" name="catering" value="1" tabindex="18" {cateringChecked}/></td></tr>
        </table>
      </div>
      <div class="info">Tučné položky musí být vyplněny.</div>
      <div class="bold">Účastnický poplatek:</div>
      <table>
        <tr><td><input type="radio" class="radio" name="fee" value="2800-partner" {2800-partner_checked}/></td><td class="bold">ZVÝHODNĚNÝ POPLATEK - partner agentury CzechTourism: 2.800,- Kč + DPH</td></tr>
        <tr><td><input type="radio" class="radio" name="fee" value="2800-ack_cr" {2800-ack_cr_checked}/></td><td class="bold">ZVÝHODNĚNÝ POPLATEK - člen ACK ČR: 2.800,- Kč + DPH</td></tr>
        <tr><td><input type="radio" class="radio" name="fee" value="2800-accka" {2800-accka_checked}/></td><td class="bold">ZVÝHODNĚNÝ POPLATEK - člen AČCKA: 2.800,- Kč + DPH</td></tr>
        <tr><td><input type="radio" class="radio" name="fee" value="4000" {4000_checked}/></td><td class="bold">PLNÝ POPLATEK: 4.000,- Kč + DPH</td></tr>
      </table>
      <br>
      <div class="comment">Vyplněním a odesláním této závazné objednávky se zavazuji zaplatit účastnický poplatek za akci "Czech Republic - Land of Stories, INCOMING WORKSHOP".</div>
      <div class="comment">Odeslání této závazné objednávky prohlašuji, že všechny uvedené údaje jsou správné.</div>
      <div class="comment">Nebude-li účastnický poplatek uhrazen resp. připsán na bankovní účet organizátora nejpozději 19. 10.2015 vylučují se zástupci společnosti z účasti na akci.</div>
      <div class="comment">Odesláním této závazné objednávky akceptuji všechny výše uvedené podmínky a zavazuji se k jejich dodržení.</div>
      ';
  if (!$forMail) {
    #$template .= '<input type="hidden" name="confirm" value="" />
    #  <input type="checkbox" name="confirm" value="1"/>
    #  <span class="comment">Souhlasím s výše uvedenými podmínkami a zavazuji se k jejich dodržení.</span>';
  } else {
    $template .= '<style media="screen" type="text/css">
      .confirmDialog { margin-bottom: 20px; }
      .confirmDialog .text { width: 220px; }
      .confirmDialog .title { font-weight: bold; }
      .confirmDialog .subject { font-weight: bold; font-size: 1.1em; margin: 15px 0; }
      .confirmDialog .info { font-size: 0.8em; font-weight: bold; font-style: italic; margin-bottom: 20px; }
      .confirmDialog .comment { font-weight: bold; margin-bottom: 10px; }
      .confirmDialog table td.bold { font-weight: bold; }
      .totalPrice { font-weight: bold; font-size: 1.2em; }
    </style>';
  }
  $template .= '</div>';
    
  $validator = Validator::get('cz','CzechTourismValidator');
  $data = $validator->getValues();
  
  $gui = new GuiElement(array('template'=>$template));
  $gui->insertTemplateVar('company', $data['company']);
  $gui->insertTemplateVar('street', $data['street']);
  $gui->insertTemplateVar('city', $data['city']);
  $gui->insertTemplateVar('postalCode', $data['postalCode']);
  $gui->insertTemplateVar('state', $data['state']);
  $gui->insertTemplateVar('ic', $data['ic']);
  $gui->insertTemplateVar('dic', $data['dic']);
  
  $gui->insertTemplateVar('firstname1', $data['firstname1']);
  $gui->insertTemplateVar('lastname1', $data['lastname1']);
  $gui->insertTemplateVar('role1', $data['role1']);
  $gui->insertTemplateVar('email1', $data['email1']);
  $gui->insertTemplateVar('phone1', $data['phone1']);
  
  $gui->insertTemplateVar('firstname2', $data['firstname2']);
  $gui->insertTemplateVar('lastname2', $data['lastname2']);
  $gui->insertTemplateVar('role2', $data['role2']);
  $gui->insertTemplateVar('email2', $data['email2']);
  $gui->insertTemplateVar('phone2', $data['phone2']);
  
  if ($data['catering']) $gui->insertTemplateVar('cateringChecked', 'checked="yes"', false);
  else $gui->insertTemplateVar('cateringChecked', '');
  
  switch ($data['fee']) {
    case '2800-partner': $gui->insertTemplateVar('2800-partner_checked', 'checked="yes"', false); break;
    case '2800-ack_cr': $gui->insertTemplateVar('2800-ack_cr_checked', 'checked="yes"', false); break;
    case '2800-accka': $gui->insertTemplateVar('2800-accka_checked', 'checked="yes"', false); break;
    case '4000': $gui->insertTemplateVar('4000_checked', 'checked="yes"', false); break;
  }
  
  return $gui->render();
}

function convertPeriodToHuman($period) {
  $textStorage = Application::get()->textStorage;
  
  $unit = $textStorage->getText('label.minute_l'); $count = $period;
  
  if ($period%1440===0) { $unit = $textStorage->getText('label.day_l'); $count = $period/1440; }
  elseif ($period%60===0) { $unit = $textStorage->getText('label.hour_l'); $count = $period/60; }
  
  return sprintf('%d %s', $count, $unit);
}

function htmlize($string) {
  $ret = $string;
    
  $ret = removeDiakritics($ret);
  $ret = strtolower($ret);
  $ret = str_replace(array(' '),'',$ret);
  
  return $ret;
}

function evaluateLogicalValue($value) {
  $ret = in_array(strtoupper(trim($value)),array('1','Y','YES','A','ANO','TRUE'));
    
  return $ret;
}

function encodeAjaxParams($params) {
  $ret = null;

  if (isset($params['parentNode'])&&$params['parentNode']) $ret['parentNode'] = $params['parentNode'];
  if (isset($params['hideOnRefresh'])) $ret['hideOnRefresh'] = $params['hideOnRefresh'];
  if (isset($params['prefix'])&&$params['prefix']) $ret['prefix'] = $params['prefix'];
  if (isset($params['tag'])&&$params['tag']) $ret['tag'] = $params['tag'];
  if (isset($params['center'])&&$params['center']) $ret['center'] = $params['center'];
  if (isset($params['region'])&&$params['region']) $ret['region'] = $params['region'];
  if (isset($params['format'])&&$params['format']) $ret['format'] = $params['format'];
  if (isset($params['backButton'])&&$params['backButton']) $ret['backButton'] = $params['backButton'];
  if (isset($params['clickAction'])&&$params['clickAction']) $ret['clickAction'] = $params['clickAction'];
  if (isset($params['buttons'])&&$params['buttons']) $ret['buttons'] = $params['buttons'];
  if (isset($params['dateStart'])&&$params['dateStart']) $ret['dateStart'] = $params['dateStart'];
  if (isset($params['dateMin'])&&$params['dateMin']) $ret['dateMin'] = $params['dateMin'];
  if (isset($params['dateMax'])&&$params['dateMax']) $ret['dateMax'] = $params['dateMax'];
  if (isset($params['timeMin'])&&$params['timeMin']) $ret['timeMin'] = $params['timeMin'];
  if (isset($params['timeMax'])&&$params['timeMax']) $ret['timeMax'] = $params['timeMax'];
  if (isset($params['dateNowPlusXDays'])&&$params['dateNowPlusXDays']) $ret['dateNowPlusXDays'] = $params['dateNowPlusXDays'];
  if (isset($params['count'])&&$params['count']) $ret['count'] = $params['count'];
  if (isset($params['onPageCount'])&&$params['onPageCount']) $ret['onPageCount'] = $params['onPageCount'];
  if (isset($params['pages'])&&$params['pages']) $ret['pages'] = $params['pages'];
  if (isset($params['weekday'])&&$params['weekday']) $ret['weekday'] = $params['weekday'];
  if (isset($params['organiser'])&&$params['organiser']) $ret['organiser'] = $params['organiser'];
  if (isset($params['language'])&&$params['language']) $ret['language'] = $params['language'];
  if (isset($params['render'])&&$params['render']) $ret['render'] = $params['render'];
  if (isset($params['renderText'])&&$params['renderText']) $ret['renderText'] = $params['renderText'];
  if (isset($params['showAttribute'])&&$params['showAttribute']) $ret['showAttribute'] = $params['showAttribute'];
  if (isset($params['checkAttributeMandatory'])&&$params['checkAttributeMandatory']) $ret['checkAttributeMandatory'] = $params['checkAttributeMandatory'];
  if (isset($params['registrationAttributeMandatoryOnly'])) $ret['registrationAttributeMandatoryOnly'] = $params['registrationAttributeMandatoryOnly'];
  if (isset($params['hideAdvertising'])&&$params['hideAdvertising']) $ret['hideAdvertising'] = $params['hideAdvertising'];
  if (isset($params['disablePast'])&&$params['disablePast']) $ret['disablePast'] = $params['disablePast'];
  if (isset($params['extraDiv'])) $ret['extraDiv'] = $params['extraDiv'];
  if (isset($params['externalAccount'])) $ret['externalAccount'] = $params['externalAccount'];
  if (isset($params['externalAccountFirst'])) $ret['externalAccountFirst'] = $params['externalAccountFirst'];
  if (isset($params['loggedTemplate'])) $ret['loggedTemplate'] = $params['loggedTemplate'];
  if (isset($params['eventTemplate'])) $ret['eventTemplate'] = $params['eventTemplate'];
  if (isset($params['eventCycleTemplate'])) $ret['eventCycleTemplate'] = $params['eventCycleTemplate'];
  if (isset($params['resourceTemplate'])) $ret['resourceTemplate'] = $params['resourceTemplate'];
  if (isset($params['attendeeTemplate'])) $ret['attendeeTemplate'] = $params['attendeeTemplate'];
  if (isset($params['cycleItemTemplate'])) $ret['cycleItemTemplate'] = $params['cycleItemTemplate'];
  if (isset($params['showAttendeePayment'])) $ret['showAttendeePayment'] = $params['showAttendeePayment'];
  if (isset($params['eventResourcePrefix'])) $ret['eventResourcePrefix'] = $params['eventResourcePrefix'];
  if (isset($params['eventResourcePostfix'])) $ret['eventResourcePostfix'] = $params['eventResourcePostfix'];
  if (isset($params['resourceListTemplate'])) $ret['resourceListTemplate'] = $params['resourceListTemplate'];
  if (isset($params['filter'])) $ret['filter'] = $params['filter'];
  if (isset($params['withTime'])) $ret['withTime'] = $params['withTime'];
  if (isset($params['tagOperator'])) $ret['tagOperator'] = $params['tagOperator'];
  if (isset($params['showInactive'])) $ret['showInactive'] = $params['showInactive'];
  if (isset($params['allowPast'])) $ret['allowPast'] = $params['allowPast'];
  if (isset($params['showPast'])) $ret['showPast'] = $params['showPast'];
  if (isset($params['noEventTemplate'])) $ret['noEventTemplate'] = $params['noEventTemplate'];
  if (isset($params['organiserShowReservationAttendee'])) $ret['organiserShowReservationAttendee'] = $params['organiserShowReservationAttendee'];
  if (isset($params['organiserCanReserveOnBehalf'])) $ret['organiserCanReserveOnBehalf'] = $params['organiserCanReserveOnBehalf'];
  if (isset($params['organiserCanReserveOnBehalfFunctionButtons'])) $ret['organiserCanReserveOnBehalfFunctionButtons'] = $params['organiserCanReserveOnBehalfFunctionButtons'];
  if (isset($params['organiserCanReserveOnBehalfCustomColumn'])) $ret['organiserCanReserveOnBehalfCustomColumn'] = $params['organiserCanReserveOnBehalfCustomColumn'];

  return json_encode($ret);
}

// pro formatovani popisu zdroje/akce
function formatCommodityDescription($description) {
  while (($pos=strpos($description, '@@LINK('))!==false) {
    // najdu konec parametru
    $end = $pos + strlen('@@LINK(');
    $linkParams = '';
    while ($end < strlen($description) && $description[$end] != ')') {
      $linkParams .= $description[$end];
      $end++;
    }
    $linkParams = explode(',',str_replace('\'','',$linkParams));
    $url = $linkParams[0];
    if (strcmp(substr($url,0,4),'http')) $url = 'http://'.$url;
    $replace = sprintf('<a href="%s" target="_blank">%s</a>', $url, ifsetor($linkParams[1],$linkParams[0]));

    $description = str_replace(substr($description,$pos,$end-$pos+1),$replace,$description);
  }

  $ret = str_replace("\n", '<br/>', $description);

  return $ret;
}

// pro formatovani nazvu atributu (muze tam byt odkaz)
function formatAttributeName($name, $url) {
  $ret = $name;

  if ($url) {
    preg_match('/^([^\|]*)\|([^\|]+)\|([^\|]*)$/', $name, $matches);
    if (count($matches)) {
      $ret = sprintf('%s<a href="%s" target="_blank">%s</a>%s', $matches[1], $url, $matches[2], $matches[3]);
    } else {
      $ret = sprintf('<a href="%s" target="_blank">%s</a>', $url, $ret);
    }
  }

  return $ret;
}

?>
