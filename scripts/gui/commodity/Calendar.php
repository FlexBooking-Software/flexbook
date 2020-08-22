<?php

class GuiCalendar extends GuiElement {
  private $_id = null;
  private $_asset = null;
  private $_resource = array();

  private $_empty = false;
  private $_multi = false;
  private $_pool = false;
  private $_event = false;

	protected $_useUserSubaccount = false;
  
  private $_params = array();
  
  private $_guiTemplate;
  private $_guiVars = array();
  
  protected $_template = '
    <input type="hidden" id="{prefix}flb_commodity_type" value="{commodity_type}" />
    <input type="hidden" id="{prefix}flb_commodity_allowedPayment" value="{commodity_allowedPayment}" />
    <input type="hidden" id="{prefix}flb_resource_id" value="{resource_id}" />
    <input type="hidden" id="{prefix}flb_resourcepool_id" value="{resourcepool_id}" />
    <div class="flb_calendar flb_cal_core{pool}" id="{prefix}cal_{id}"></div>
    {backButton}
    <div id="{prefix}editEvent_form" class="ajaxForm eventDetailForm">
      <fieldset>
        <input type="hidden" id="{prefix}editEvent_id" name="id" />
        <input type="hidden" id="{prefix}editEvent_reserved" />
        <input type="hidden" id="{prefix}editEvent_reservationMaxAttendees" />
        <input type="hidden" id="{prefix}editEvent_coAttendees" />
        <input type="hidden" id="{prefix}editEvent_organiser" />
        <input type="hidden" id="{prefix}editEvent_repeatReservation" />
        <input type="hidden" id="{prefix}editEvent_priceSingle"/>
        <input type="hidden" id="{prefix}editEvent_pricePack"/>
        <input type="hidden" id="{prefix}editEvent_quickReservation"/>
        <input type="hidden" id="{prefix}editEvent_allowedPayment"/>
        <input type="hidden" id="{prefix}editEvent_paymentNeeded"/>
        <div class="line title">
          <span id="{prefix}editEvent_name"></span>
          <div class="event_detail"><div class="ui-dialog-buttonset"><button type="button" class="btn_eventDetail">{__button.grid_edit}</button></div></div>
        </div>
        <div class="line description">
          <span id="{prefix}editEvent_description"></span>
        </div>
        <div class="line">
          <span class="bold">{__label.calendar_editEvent_organiser}: </span>
          <span id="{prefix}editEvent_organiserName"></span>
        </div>
        <div class="line">
          <span class="bold">{__label.calendar_editEvent_start}: </span>
          <span id="{prefix}editEvent_visualStart"></span>
        </div>
        <div class="line">
          <span class="bold">{__label.calendar_editEvent_end}: </span>
          <span id="{prefix}editEvent_visualEnd"></span>
        </div>
        <div class="line" id="{prefix}editEvent_line_price">
          <span class="bold">{__label.calendar_editEvent_price}: </span>
          <span id="{prefix}editEvent_price"></span>
          <!--<span id="{prefix}editEvent_currency"></span>-->
        </div>
        <div class="line" id="{prefix}editEvent_line_place">
          <span class="bold">{__label.calendar_editEvent_maxAttendees}: </span>
          <input type="hidden" name="maxAttendees" id="{prefix}editEvent_maxAttendees" />
          <input type="hidden" name="free" id="{prefix}editEvent_free" />
          <input type="hidden" name="freeSubstitute" id="{prefix}editEvent_freeSubstitute" />
          <span id="{prefix}editEvent_visualMaxAttendees"></span>
        </div>
        <div class="line" id="{prefix}editEvent_line_attendee">
          <span class="bold">{__label.calendar_editEvent_attendee}: </span>
          <span id="{prefix}editEvent_attendee"></span>
        </div>
        <div class="line" id="{prefix}editEvent_line_substitute">
          <span class="bold">{__label.calendar_editEvent_substitute}: </span>
          <span id="{prefix}editEvent_substitute"></span>
        </div>
      </fieldset>
      <div class="label flb_event_occupied" id="{prefix}editEvent_occupiedWarning"><span>{__label.ajax_event_substituteAvailable}</span></div>
    </div>
    <div id="{prefix}editReservationResource_form" class="ajaxForm reservationResourceForm">
      <fieldset>
        <input type="hidden" id="{prefix}editReservationResource_id" name="id" />
        <input type="hidden" id="{prefix}editReservationResource_resourceId" name="resourceId" />
        <input type="hidden" id="{prefix}editReservationResource_payed" name="payed" />
        <input type="hidden" id="{prefix}editReservationResource_priceHidden" name="priceHidden" />
        <input type="hidden" id="{prefix}editReservationResource_allowedPaymentHidden" name="allowedPaymentHidden" />
        <input type="hidden" id="{prefix}editReservationResource_paymentNeeded" name="paymentNeeded" />
        <div class="line title" id="{prefix}editReservationResource_line_number">
          <span id="{prefix}editReservationResource_number"></span>
          <div class="reservation_detail"><div class="ui-dialog-buttonset"><button type="button" class="btn_reservationDetail">{__button.grid_edit}</button></div></div>
        </div>
        <div class="line" id="{prefix}editReservationResource_line_from">
          <span class="bold">{__label.calendar_editReservation_from}: </span>
          <span id="{prefix}editReservationResource_visualDateFrom"></span>&nbsp;<span id="{prefix}editReservationResource_visualTimeFrom"></span>
          <input type="text" style="width: 0px; height: 0px; top: -100px; position: absolute;" />
          <input id="{prefix}editReservationResource_inputTimeFrom" class="flb_inputTime" type="text"/>
          <input type="hidden" name="from" id="{prefix}editReservationResource_from" />
        </div>
        <div class="line" id="{prefix}editReservationResource_line_to">
          <span class="bold">{__label.calendar_editReservation_to}: </span>
          <span id="{prefix}editReservationResource_visualDateTo"></span>&nbsp;<span id="{prefix}editReservationResource_visualTimeTo"></span>
          <input id="{prefix}editReservationResource_inputTimeTo" class="flb_inputTime" type="text"/>
          <input type="hidden" name="to" id="{prefix}editReservationResource_to" />
        </div>
        <div class="line" id="{prefix}editReservationResource_line_resources">
          <input type="hidden" id="{prefix}editReservationResource_allowedResourceIds" name="allowedResourceIds" />
          <span class="bold">{__label.calendar_editReservation_places}: </span>
          <select id="{prefix}editReservationResource_places"></select>
        </div>
        <div class="line" id="{prefix}editReservationResource_line_user">
          <span class="bold">{__label.calendar_editReservation_userName}: </span>
          <span id="{prefix}editReservationResource_visualUser"></span>
          <input type="text" name="userName" id="{prefix}editReservationResource_userName" class="text ui-widget-content ui-corner-all"/>
          <input type="hidden" name="userId" id="{prefix}editReservationResource_userId" />
          <input type="hidden" name="userNameSelected" id="{prefix}editReservationResource_userNameSelected"/>
        </div>
        {resourceMandatoryReservation}
        <div id="{prefix}editReservationResource_attribute"></div>
        <div class="line" id="{prefix}editReservationResource_line_note">
          <span class="bold">{__label.calendar_editReservation_note}: </span>
          <textarea id="{prefix}editReservationResource_note" class="note"></textarea>
        </div>
        <div class="line" id="{prefix}editReservationResource_line_price">
          <span class="bold">{__label.calendar_editReservation_price}: </span>
          <span id="{prefix}editReservationResource_price"></span>
          <span id="{prefix}editReservationResource_currency"></span>
        </div>
        <div class="line" id="{prefix}editReservationResource_line_payment">
          <span class="bold">{__label.calendar_editReservation_payment}: </span>
          <span id="{prefix}editReservationResource_visualPayed"></span>
        </div>
        <div class="line" id="{prefix}editReservationResource_line_failed">
          <span class="bold">{__label.calendar_editReservation_failed}: </span>
          <span id="{prefix}editReservationResource_visualFailed"></span>
        </div>
        <div class="line" id="{prefix}editReservationResource_line_ticket">
          <span class="bold">{__label.calendar_editReservation_ticket}: </span>
          <select id="{prefix}editReservationResource_ticket"></select>
        </div>
        {resourceSkipReservationCondition}
      </fieldset>
    </div>
    <div id="{prefix}editReservationEvent_form" class="ajaxForm reservationEventForm">
      <fieldset>
        <input type="hidden" id="{prefix}editReservationEvent_id" name="id" />
        <input type="hidden" name="event" id="{prefix}editReservationEvent_event" />
        <input type="hidden" id="{prefix}editReservationEvent_substitute" name="substitute" />
        <div class="line title">
          <span id="{prefix}editReservationEvent_number"></span>
        </div>
        <div class="line">
          <span class="bold">{__label.calendar_editReservation_event}: </span>
          <span id="{prefix}editReservationEvent_visualEvent"></span>
        </div>
        <div class="line" id="{prefix}editReservationEvent_line_user">
          <span class="bold">{__label.calendar_editReservation_userName}: </span>
          <span id="{prefix}editReservationEvent_visualUser"></span>
          <div class="ui-dialog-buttonset"><button type="button" id="{prefix}editReservationEvent_anonymousUser" class="anonymousReservation">{__button.calendar_editReservation_anonymous}</button></div>
          <input type="text" name="userName" id="{prefix}editReservationEvent_userName" class="text ui-widget-content ui-corner-all"/>
          <input type="hidden" name="userId" id="{prefix}editReservationEvent_userId" />
          <input type="hidden" name="userNameSelected" id="{prefix}editReservationEvent_userNameSelected"/>
        </div>
        {eventMandatoryReservation}
        <div class="line" id="{prefix}editReservationEvent_line_pack">
          <span class="bold">{__label.calendar_editReservation_eventPack}: </span>
          <select id="{prefix}editReservationEvent_pack">
            <option value="N">{__label.calendar_editReservation_eventPack_single}</option>
            <option value="Y">{__label.calendar_editReservation_eventPack_pack}</option>
          </select>
        </div>
        <div class="line" id="{prefix}editReservationEvent_line_places">
          <span class="bold">{__label.calendar_editReservation_places}: </span>
          <select name="places" id="{prefix}editReservationEvent_places"></select>
        </div>
        <div class="line" id="{prefix}editReservationEvent_line_attendee">
          <span class="bold">{__label.calendar_editReservation_attendee}: </span>
          <table class="attendee">
            <tr{subaccountHiddenTitle}>
              <th>{__label.calendar_editReservation_attendeeFirstname}</th>
              <th>{__label.calendar_editReservation_attendeeLastname}</th>
              <th>{__label.calendar_editReservation_attendeeEmail}</th>
            </tr>
            <tbody id="{prefix}editReservationEvent_attendees"></tbody>
          </table>
        </div>
        <div id="{prefix}editReservationEvent_attribute"></div>
        <div class="line" id="{prefix}editReservationEvent_line_note">
          <span class="bold">{__label.calendar_editReservation_note}: </span>
          <textarea id="{prefix}editReservationEvent_note" class="note"></textarea>
        </div>
        <div class="line" id="{prefix}editReservationEvent_line_price">
          <span class="bold">{__label.calendar_editReservation_price}: </span>
          <span id="{prefix}editReservationEvent_visualPrice"></span>
          <span>{__label.currency_CZK}</span>
        </div>
        <div class="line" id="{prefix}editReservationEvent_line_ticket">
          <span class="bold">{__label.calendar_editReservation_ticket}: </span>
          <select id="{prefix}editReservationEvent_ticket"></select>
        </div>
        {eventSkipReservationCondition}
      </fieldset>
    </div>
    ';
    
  protected function _userParamsInit(&$params) {
    parent::_userParamsInit($params);
    
    if (isset($params['params'])) $this->_params = $params['params'];
  }

    
  private function _createTemplate() {
    $ret = sprintf('<script>%s</script>', file_get_contents(dirname(__FILE__).'/Calendar.js'));
    
    if (!$this->_multi&&!$this->_event) {
      $single = '';
      
      foreach ($this->_params['renderText'] as $render) {
        switch ($render) {
          case 'name':
            $single .= '<div class="label flb_resource_name_label"><span>{__label.calendar_resourceName}:</span></div><div class="value flb_resource_name">{name}</div>';
            break;
          case 'description':
            $single .= '<div class="label flb_resource_description_label"><span>{__label.calendar_resourceDescription}:</span></div><div class="value flb_resource_description">{description}</div>';
            break;
          case 'price':
            $single .= '<div class="label flb_resource_label_price"><span>{__label.calendar_resourcePrice}:</span></div><div class="value flb_resource_price">{price} {__label.currency_CZK} / {priceUnit}</div>';
            break;
          case 'attribute':
            if (!$this->_pool) $single .= '<div class="flb_commodity_attributes">{attribute}</div>';
            break;
        }
      }
      
      $ret .= sprintf('<div class="flb_resource_detail">%s</div>', $single);
    }
    if (in_array('legend', $this->_params['renderText'])) $ret .= '<div class="flb_calendar_legend flb_calendar fc-unthemed"><div class="fc-today"><span>{__label.calendar_legend_today}</span></div><div><span>{__label.calendar_legend_free}</span></div><div class="fc-bgevent"><span>{__label.calendar_legend_occupied}</span></div><div class="fc-event event_reservation"><span>{__label.calendar_legend_reservation}</span></div><div class="fc-event event_providerEvent"><span>{__label.calendar_legend_event}</span></div></div>';
    
    $ret .= $this->_template;

    if ((BCustomer::getProviderSettings($this->_params['provider'],'allowSkipReservationCondition')=='Y')&&
			  ($this->_app->auth->isAdministrator()||$this->_app->auth->isProvider()||$this->_app->auth->haveRight('power_organiser', $this->_params['provider']))) {
			$ret = str_replace('{resourceSkipReservationCondition}', '<div class="line" id="{prefix}editReservationResource_line_skipReservationCondition">
          <span class="bold">{__label.calendar_editReservation_skipReservationCondition}: </span>
          <input type="checkbox" id="{prefix}editReservationResource_skipReservationCondition"/>
        </div>', $ret);
			$ret = str_replace('{eventSkipReservationCondition}', '<div class="line" id="{prefix}editReservationEvent_line_skipReservationCondition">
          <span class="bold">{__label.calendar_editReservation_skipReservationCondition}: </span>
          <input type="checkbox" id="{prefix}editReservationEvent_skipReservationCondition"/>
        </div>', $ret);
		} else {
			$ret = str_replace('{resourceSkipReservationCondition}', '', $ret);
			$ret = str_replace('{eventSkipReservationCondition}', '', $ret);
		}

		if (BCustomer::getProviderSettings($this->_params['provider'],'allowMandatoryReservation')=='Y') {
			$ret = str_replace('{resourceMandatoryReservation}', '<div class="line" id="{prefix}editReservationResource_line_mandatoryReservation">
          <span class="bold">{__label.calendar_editReservation_mandatoryReservation}: </span>
          <input type="checkbox" id="{prefix}editReservationResource_mandatoryReservation"/>
        </div>', $ret);
			$ret = str_replace('{eventMandatoryReservation}', '<div class="line" id="{prefix}editReservationEvent_line_mandatoryReservation">
          <span class="bold">{__label.calendar_editReservation_mandatoryReservation}: </span>
          <input type="checkbox" id="{prefix}editReservationEvent_mandatoryReservation"/>
        </div>', $ret);
		} else {
			$ret = str_replace('{resourceMandatoryReservation}', '', $ret);
			$ret = str_replace('{eventMandatoryReservation}', '', $ret);
		}
    
    $this->_guiTemplate = $ret;
  }
  
  private function _prepareCallUrl() {
    $this->_guiVars['prefix'] = $this->_params['prefix'];
    
    if (!strcmp(get_class($this->_app),'FlexBook')&&$this->_app->auth->haveRight('reservation_admin', $this->_params['provider'])) {
      $this->_guiVars['calAd'] = 'true';
    } else {
      $this->_guiVars['calAd'] = 'false';
    }
    if (BCustomer::getProviderSettings($this->_params['provider'],'organiserMandatoryReservation')=='Y') {
			$this->_guiVars['orgMR'] = 'true';
		} else {
			$this->_guiVars['orgMR'] = 'false';
		}

    if (isset($this->_params['organiserShowReservationAttendee'])&&!$this->_params['organiserShowReservationAttendee']) {
			$this->_guiVars['orgHA'] = 'true';
		} else {
			$this->_guiVars['orgHA'] = 'false';
		}

		if (BCustomer::getProviderSettings($this->_params['provider'],'disableCash')=='N') {
			$this->_guiVars['allowedPaymentCash'] = 'true';
		} else {
			$this->_guiVars['allowedPaymentCash'] = 'false';
		}
    
    global $PAYMENT_GATEWAY;
    $this->_guiVars['paymentUrl'] = sprintf($PAYMENT_GATEWAY['initUrl'], ifsetor($this->_params['language'],'cz'), $this->_app->session->getId(), $this->_params['provider']);

    if (isset($this->_params['organiserCanReserveOnBehalf'])) {
			$this->_guiVars['orgSR'] = 'true';
			if (is_array($this->_params['organiserCanReserveOnBehalf'])) {
				if (isset($this->_params['organiserCanReserveOnBehalf']['usersHavingReservationsOnEventWithTag'])) $this->_guiVars['organiserCanReserveOnBehalf'] = '&scope=event&scopeEvent='.$this->_params['organiserCanReserveOnBehalf']['usersHavingReservationsOnEventWithTag'];
				if (isset($this->_params['organiserCanReserveOnBehalf']['usersHavingPaidReservationsOnEventWithTag'])) $this->_guiVars['organiserCanReserveOnBehalf'] = '&scope=paidEvent&scopeEvent='.$this->_params['organiserCanReserveOnBehalf']['usersHavingPaidReservationsOnEventWithTag'];
			} elseif (!strcmp($this->_params['organiserCanReserveOnBehalf'], 'allUsers')) $this->_guiVars['organiserCanReserveOnBehalf'] = '&scope=all';

			if (isset($this->_params['organiserCanReserveOnBehalfCustomColumn']['value'])) $this->_guiVars['organiserCanReserveOnBehalf'] .= '&customColumn='.$this->_params['organiserCanReserveOnBehalfCustomColumn']['value'];
			$this->_guiVars['organiserCanReserveOnBehalfCustomColumnTitle'] = ifsetor($this->_params['organiserCanReserveOnBehalfCustomColumn']['title'], $this->_app->textStorage->getText('label.calendar_editUser_email'));
			$this->_guiVars['organiserCanReserveOnBehalfCustomColumnValue'] = ifsetor($this->_params['organiserCanReserveOnBehalfCustomColumn']['value'], 'email');
		} else {
			$this->_guiVars['orgSR'] = 'false';
    	$this->_guiVars['organiserCanReserveOnBehalf'] = '';
			$this->_guiVars['organiserCanReserveOnBehalfCustomColumnTitle'] = $this->_app->textStorage->getText('label.calendar_editUser_email');
			$this->_guiVars['organiserCanReserveOnBehalfCustomColumnValue'] = 'email';
		}
  }
  
  private function _initDefaultParams() {
    if (isset($this->_params['resourceId'])) $this->_params['resourceId'] = str_replace($this->_params['prefix'],'',$this->_params['resourceId']);
    if (!isset($this->_params['renderText'])) $this->_params['renderText'] = array('name','description','price','attribute');
    if (!isset($this->_params['format']['eventTitle'])) $this->_params['format']['eventTitle'] = '@@name';
  }
  
  private function _initEvent() {
    $this->_event = true;
    
    $this->_guiVars['id'] = randomString(10);
    $this->_guiVars['commodity_id'] = sprintf('provider: %s', $this->_params['provider']);
    if (isset($this->_params['center'])) {
			if ($this->_guiVars['commodity_id']) $this->_guiVars['commodity_id'] .= ', ';
      $this->_guiVars['commodity_id'] = sprintf('center: %s', json_encode($this->_params['center']));
    }
    if (isset($this->_params['tag'])) {
      if ($this->_guiVars['commodity_id']) $this->_guiVars['commodity_id'] .= ', ';
      $this->_guiVars['commodity_id'] .= sprintf('tag: %s', json_encode($this->_params['tag']));
    }
    if ($this->_guiVars['commodity_id']) $this->_guiVars['commodity_id'] .= ', ';
    
    $this->_guiVars['resource_id'] = '';
    $this->_guiVars['commodity_type'] = 'event';
		$this->_guiVars['commodity_allowedPayment'] = '0';
    $this->_guiVars['resourcepool_id'] = '';
    $this->_guiVars['pool'] = '';
    $this->_guiVars['calResource'] = '';
    
    $this->_guiVars['unit'] = ifsetor($this->_params['timeSlot'],'30');
    $this->_guiVars['minimum_quantity'] = '';
    $this->_guiVars['maximum_quantity'] = '';
    $this->_guiVars['time_alignment_from'] = '';
    $this->_guiVars['time_alignment_to'] = '';
    $this->_guiVars['time_alignment_grid'] = '';
		$this->_guiVars['time_end_from'] = '';
		$this->_guiVars['time_end_to'] = '';
  }
  
  private function _initResource() {
    // ktere zdroje se budou zobrazovat
    $this->_id = ifsetor($this->_params['resourceId']);
    $this->_asset = ifsetor($this->_params['resourceAssetId']);
    if ($this->_id) {
     if (!is_array($this->_id)) $this->_id = array($this->_id);
    } elseif ($this->_asset) {
      if (!is_array($this->_asset)) $this->_asset = array($this->_asset);
      foreach ($this->_asset as $index=>$a) {
        $this->_asset[$index] = sprintf("'%s'", $a); 
      }
    } else {
      $this->_id = ifsetor($this->_params['resourcePoolId']);
      $this->_asset = ifsetor($this->_params['resourcePoolAssetId']);
    
      if ($this->_id||$this->_asset) $this->_pool = true;
    }
    
    if (!$this->_pool) $this->_prepareResource();
    else $this->_prepareResourcePool();

    if (isset($this->_guiVars['commodity_allowedPayment'])) {
			$providerSettings = BCustomer::getProviderSettings($this->_params['provider'], array('disableCredit', 'disableTicket', 'disableOnline'));
			if ($providerSettings['disableCredit']=='Y') $this->_guiVars['commodity_allowedPayment'] -= 1;
			if ($providerSettings['disableTicket']=='Y') $this->_guiVars['commodity_allowedPayment'] -= 10;
			if ($providerSettings['disableOnline']=='Y') $this->_guiVars['commodity_allowedPayment'] -= 100;
		}
  }

  private function _prepareResource() {
    $s = new SResource;
    
    if (isset($this->_params['center'])) $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf('%%s IN (%s)', $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    
    if ($this->_id) $s->addStatement(new SqlStatementMono($s->columns['resource_id'], sprintf('%%s IN (%s)', implode(',',$this->_id))));
    elseif ($this->_asset) $s->addStatement(new SqlStatementMono($s->columns['external_id'], sprintf('%%s IN (%s)', implode(',',$this->_asset))));

		if (isset($this->_params['organiser'])) {
			if (!strcmp($this->_params['organiser'],'loggedInUser')) $s->addStatement(new SqlStatementBi($s->columns['organiser'], $this->_app->auth->getUserId(), '%s=%s'));
			else $s->addStatement(new SqlStatementBi($s->columns['organiser_email'], $this->_params['organiser'], '%s=%s'));
		}
    
    if (isset($this->_params['tag'])&&$this->_params['tag']) {
      $tag = $this->_params['tag'];
      foreach ($tag as $key=>$value) {
        $tag[$key] = sprintf("'%s'", $value);
      }
      $s->addStatement(new SqlStatementMono($s->columns['tag_name'], sprintf("%%s IN (%s)", implode(',',$tag))));
    }
    
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resource_id','name','description','price',
                             'unitprofile','unit','unit_rounding','minimum_quantity','maximum_quantity',
                             'time_alignment_from','time_alignment_to','time_alignment_grid',
                             'time_end_from','time_end_to','fe_allowed_payment'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_app->db->getRowsNumber($res)) {
    	$this->_empty = true;
    	return;
		}

    $unitProfile = null;
    while ($r = $this->_app->db->fetchAssoc($res)) {
      if ($unitProfile&&($unitProfile!=$r['unitprofile'])) throw new ExceptionUser('FLB error: invalid combination of unit profile for multi-resource calendar!');
      else $unitProfile = $r['unitprofile'];
      
      $base = $this->_app->textStorage->getText('label.minute');
      $multiplier = $r['unit'];
      if (!strcmp($r['unit_rounding'],'day')) { $multiplier = $r['unit']/1440; $base = $this->_app->textStorage->getText('label.day'); }
      elseif (!strcmp($r['unit_rounding'],'night')) { $multiplier = $r['unit']/1440; $base = $this->_app->textStorage->getText('label.night'); }
      elseif ($r['unit']%60 === 0) { $multiplier = $r['unit']/60; $base = $this->_app->textStorage->getText('label.hour'); }
      $r['priceUnit'] = sprintf('%s %s', $multiplier, $base);
      $r['unitRounding'] = $r['unit_rounding'];
			$r['description'] = formatCommodityDescription($r['description']);
      
      $this->_resource[$r['resource_id']] = array(
                    'id'          				=> $r['resource_id'],
                    'name'        				=> $r['name'],
                    'description' 				=> $r['description'],
                    'price'       				=> $r['price'],
                    'priceUnit'   				=> $r['unit'],
				       			'allowedPayment'			=> $r['fe_allowed_payment'],
                    );
      
      $this->_guiVars = $r;
    }
    
    $this->_multi = count($this->_resource)>1;
    
    $this->_guiVars['resource_id'] = json_encode($this->_multi?array_keys($this->_resource):array_keys($this->_resource)[0]);
    $this->_guiVars['commodity_id'] = sprintf('id: %s, ', json_encode($this->_multi?array_keys($this->_resource):array_keys($this->_resource)[0]));
    $this->_guiVars['commodity_type'] = 'resource';
		$this->_guiVars['commodity_allowedPayment'] = $this->_multi?'111':array_values($this->_resource)[0]['allowedPayment'];
    $this->_guiVars['resourcepool_id'] = '';
    $this->_guiVars['pool'] = '';
    
    $this->_guiVars['calResource'] = '';
    if ($this->_multi) {
      $tmp1 = '';
      $tmp2 = '';
      foreach ($this->_resource as $res) {
        if ($tmp1) $tmp1 .= ",\n";
        $tmp1 .= sprintf("{ id: %s, title: '%s' }", $res['id'], addslashes($res['name']));
        
        if ($tmp2) $tmp2 .= "_";
        $tmp2 .= $res['id'];
      }
      $this->_guiVars['calResource'] .= sprintf('resources: [ %s ],', $tmp1);
      $this->_guiVars['id'] = $tmp2;
    } else {
      $this->_guiVars['id'] = array_keys($this->_resource)[0];
    }
  }
  
  private function _prepareResourcePool() {
    $s = new SResourcePool;
    
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    if ($this->_id) $s->addStatement(new SqlStatementBi($s->columns['resourcepool_id'], $this->_id, '%s=%s'));
    elseif ($this->_asset) $s->addStatement(new SqlStatementBi($s->columns['external_id'], $this->_asset, '%s=%s'));  
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->setColumnsMask(array('resourcepool_id','name','description'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_app->db->getRowsNumber($res)) {
			$this->_empty = true;
			return;
		}
    $row = $this->_app->db->fetchAssoc($res);
    
    $s = new SResourcePoolItem;
    $s->addStatement(new SqlStatementBi($s->columns['resourcepool'], $row['resourcepool_id'], '%s=%s'));
    $s->setColumnsMask(array('resource_id','resource_name','resource_description','resource_price',
                             'unitprofile_unit','unitprofile_unit_rounding','unitprofile_minimum_quantity','unitprofile_maximum_quantity',
                             'unitprofile_time_alignment_from','unitprofile_time_alignment_to','unitprofile_time_alignment_grid',
                             'unitprofile_time_end_from','unitprofile_time_end_to'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$itemCount = $this->_app->db->getRowsNumber($res)) {
			$this->_empty = true;
			return;
		}

    $poolItem = null;
    while ($r = $this->_app->db->fetchAssoc($res)) {
      $base = $this->_app->textStorage->getText('label.minute');
      $multiplier = $r['unitprofile_unit'];
      if (!strcmp($r['unitprofile_unit_rounding'],'day')) { $multiplier = $r['unitprofile_unit']/1440; $base = $this->_app->textStorage->getText('label.day'); }
      elseif (!strcmp($r['unitprofile_unit_rounding'],'night')) { $multiplier = $r['unitprofile_unit']/1440; $base = $this->_app->textStorage->getText('label.night'); }
      elseif ($r['unitprofile_unit']%60 === 0) { $multiplier = $r['unitprofile_unit']/60; $base = $this->_app->textStorage->getText('label.hour'); }
      $r['priceUnit'] = sprintf('%s %s', $multiplier, $base);
      
      $this->_resource[$r['resource_id']] = array(
                    'id'          => $r['resource_id'],
                    'name'        => $r['resource_name'],
                    'description' => formatCommodityDescription($r['resource_description']),
                    'price'       => $r['resource_price'],
                    'priceUnit'   => $r['unitprofile_unit'],
                    );
      
      $poolItem = $r;
    }
    
    $this->_pool = true;
    
    $this->_guiVars['resourcepool_id'] = $row['resourcepool_id'];
    $this->_guiVars['commodity_id'] = sprintf('id: %s, ', $row['resourcepool_id']);
    $this->_guiVars['commodity_type'] = 'pool';
		$this->_guiVars['commodity_allowedPayment'] = '111';
    $this->_guiVars['resource_id'] = '';
    $this->_guiVars['pool'] = ' flb_calendar_pool';
    
    $this->_guiVars['id'] = $row['resourcepool_id'];
    $this->_guiVars['name'] = $row['name'];
    $this->_guiVars['description'] = formatCommodityDescription($row['description']);
    $this->_guiVars['price'] = $itemCount?$poolItem['resource_price']:'';
    $this->_guiVars['priceUnit'] = $itemCount?$poolItem['priceUnit']:'';
    $this->_guiVars['calResource'] = '';
    
    $this->_guiVars['unit'] = $itemCount?$poolItem['unitprofile_unit']:'';
    $this->_guiVars['unitRounding'] = $itemCount?$poolItem['unitprofile_unit_rounding']:'';
    $this->_guiVars['minimum_quantity'] = $itemCount?$poolItem['unitprofile_minimum_quantity']:'';
    $this->_guiVars['maximum_quantity'] = $itemCount?$poolItem['unitprofile_maximum_quantity']:'';
    $this->_guiVars['time_alignment_from'] = $itemCount?$poolItem['unitprofile_time_alignment_from']:'';
    $this->_guiVars['time_alignment_to'] = $itemCount?$poolItem['unitprofile_time_alignment_to']:'';
    $this->_guiVars['time_alignment_grid'] = $itemCount?$poolItem['unitprofile_time_alignment_grid']:'';
    $this->_guiVars['time_end_from'] = $itemCount?$poolItem['unitprofile_time_end_from']:'';
    $this->_guiVars['time_end_to'] = $itemCount?$poolItem['unitprofile_time_end_to']:'';
  }
  
  private function _prepareTimeAttributes() {
    // casove omezeni kalendare
    $minTime = '23:59';
    $maxTime = '00:00';
    foreach ($this->_resource as $id=>$res) {
      $b = new BResource($id);
      $bAvailability = $b->getAvailabilityProfileData();
      //adump($bAvailability);
      foreach ($bAvailability as $weekDay=>$dayAvailability) {
        if ($dayAvailability['from']<$minTime) $minTime = substr($dayAvailability['from'],0,5);
        if ($maxTime<$dayAvailability['to']) $maxTime = substr($dayAvailability['to'],0,5);
      }
    }
    if ($minTime>$maxTime) { $minTime = '00:00'; $maxTime = '23:49'; }
    
    if ($this->_guiVars['time_alignment_from']) $minTime = $this->_app->regionalSettings->convertTimeToHuman($this->_guiVars['time_alignment_from'],'h:m');
    $this->_guiVars['minTime'] = isset($this->_params['timeMin'])?$this->_params['timeMin']:$minTime;
    $this->_guiVars['maxTime'] = isset($this->_params['timeMax'])?$this->_params['timeMax']:$maxTime;
    
    $this->_guiVars['alignmentStart'] = $this->_guiVars['time_alignment_from'];
    $this->_guiVars['alignmentEnd'] = $this->_guiVars['time_alignment_to'];
    $this->_guiVars['alignmentGrid'] = $this->_guiVars['time_alignment_grid']?$this->_guiVars['time_alignment_grid']:0;
    $this->_guiVars['timeEndFrom'] = $this->_guiVars['time_end_from'];
    $this->_guiVars['timeEndTo'] = $this->_guiVars['time_end_to'];

    // min. rezervacni  jednotka
    $this->_guiVars['timeSlot'] = ifsetor($this->_params['timeSlot'], !$this->_guiVars['alignmentGrid']||($this->_guiVars['unit']<$this->_guiVars['alignmentGrid'])?$this->_guiVars['unit']:$this->_guiVars['alignmentGrid']);
    $this->_guiVars['minimumUnitQuantity'] = $this->_guiVars['minimum_quantity'];
    $this->_guiVars['maximumUnitQuantity'] = $this->_guiVars['maximum_quantity']?$this->_guiVars['maximum_quantity']:0;
  }
  
  private function _prepareView() {
    // vyber VIEW kalendare
    if ((isset($this->_params['view'])&&!strcmp($this->_params['view'],'month'))||($this->_guiVars['unit']==1440)) {
      $this->_guiVars['viewType'] = 'month';
    } elseif (!$this->_multi) {
      $this->_guiVars['viewType'] = 'agendaWeek';
      if (isset($this->_params['view'])) $this->_guiVars['viewType'] = 'agenda'.ucfirst($this->_params['view']);
    } else {
      $this->_guiVars['viewType'] = 'timelineDay';
      if (isset($this->_params['viewDirection'])) {
        if (!strcmp($this->_params['viewDirection'],'vertical')) $this->_guiVars['viewType'] = 'agendaDay';
      }
    }
    
    // co se ma v kalendari zobrazit
    $this->_guiVars['shownData'] = sprintf(',eventTitle:\'%s\'', $this->_params['format']['eventTitle']);
    if (isset($this->_params['format']['reservationTitle'])) $this->_guiVars['shownData'] .= sprintf(',reservationTitle:\'%s\'', $this->_params['format']['reservationTitle']);
    if (isset($this->_params['render'])) {
      if (in_array('reservation',$this->_params['render'])) $this->_guiVars['shownData'] .= ',showReservation:1';
      else $this->_guiVars['shownData'] .= ',showReservation:0';
      if (in_array('event',$this->_params['render'])) $this->_guiVars['shownData'] .= ',showEvent:1';
      elseif (in_array('eventWithoutTitle',$this->_params['render'])) $this->_guiVars['shownData'] .= ',showEvent:1,showEventTitle:0';
      else $this->_guiVars['shownData'] .= ',showEvent:0';
      if (in_array('occupied',$this->_params['render'])) $this->_guiVars['shownData'] .= ',showOccupied:1';
      else $this->_guiVars['shownData'] .= ',showOccupied:0';
    }
    if (isset($this->_params['organiser'])) {
    	$this->_guiVars['shownData'] .= sprintf(',organiser:\'%s\'', $this->_params['organiser']);
		}
  }
  
  private function _prepareDesign() {
    // design parametry
    #$this->_guiVars['eventDialogWidth'] = (isset($this->_params['eventDialogWidth']))?$this->_params['eventDialogWidth']:'400';
    $this->_guiVars['designParams'] = '';
    if ($this->_multi) {
      if (isset($this->_params['ratio'])) $this->_guiVars['designParams'] .= sprintf('aspectRatio: %s,', $this->_params['ratio']);
      $this->_guiVars['designParams'] .= sprintf("resourceLabelText: '%s',", ifsetor($this->_params['resourceLabel'], $this->_app->textStorage->getText('label.calendar_resource')));
      if (isset($this->_params['resourceWidth'])) $this->_guiVars['designParams'] .= sprintf("resourceAreaWidth: '%s',", $this->_params['resourceWidth']);
      if (isset($this->_params['slotWidth'])) $this->_guiVars['designParams'] .= sprintf("slotWidth: '%s',", $this->_params['slotWidth']);
    }
    
    if ($this->_app->language->getLanguage()=='cz') $this->_guiVars['language'] = 'cs';
    elseif ($this->_app->language->getLanguage()=='en') $this->_guiVars['language'] = 'en';
  }
  
  private function _prepareControl() {
    // ovladani kalendare (vyber datumu)
    $this->_guiVars['defaultDate'] = '';
    if (isset($this->_params['dateStart'])) $this->_guiVars['defaultDate'] = sprintf("\ndefaultDate: '%s',\n", $this->_params['dateStart']);
    if (isset($this->_params['showToday'])&&!strcmp($this->_params['showToday'],'false')) $this->_guiVars['today'] = '';
    else $this->_guiVars['today'] = ' today';
    $this->_guiVars['parsePrevButton'] = '';
    if (isset($this->_params['dateMin']))
       $this->_guiVars['parsePrevButton'] = sprintf("
          if (view.start.format('YYYY-MM-DD')<='%s') { $('#%scal_%s .fc-prev-button').prop('disabled', true); $('#%scal_%s .fc-prev-button').addClass('fc-state-disabled'); }
          else { $('#%scal_%s .fc-prev-button').prop('disabled', false); $('#%scal_%s .fc-prev-button').removeClass('fc-state-disabled'); }
          ", $this->_params['dateMin'], $this->_guiVars['prefix'], $this->_guiVars['id'], $this->_guiVars['prefix'], $this->_guiVars['id'], $this->_guiVars['prefix'],
          $this->_guiVars['id'], $this->_guiVars['prefix'], $this->_guiVars['id']);
    $this->_guiVars['parseNextButton'] = '';
    if (isset($this->_params['dateMax']))
       $this->_guiVars['parseNextButton'] = sprintf("
          if (view.end.format('YYYY-MM-DD')>'%s') { $('#%scal_%s .fc-next-button').prop('disabled', true); $('#%scal_%s .fc-next-button').addClass('fc-state-disabled'); }
          else { $('#%scal_%s .fc-next-button').prop('disabled', false); $('#%scal_%s .fc-next-button').removeClass('fc-state-disabled'); }
          ", $this->_params['dateMax'], $this->_guiVars['prefix'], $this->_guiVars['id'], $this->_guiVars['prefix'], $this->_guiVars['id'], $this->_guiVars['prefix'],
          $this->_guiVars['id'], $this->_guiVars['prefix'], $this->_guiVars['id']);
    if ($this->_multi) $this->_guiVars['selectDate'] = ' selectDate';
    else $this->_guiVars['selectDate'] = '';
    
    // moznost vybrat termin   
    $this->_guiVars['selectable'] = 'true';
    if (($this->_params['calendarType']=='event')||
			  isset($this->_params['disableResourceReservation'])&&evaluateLogicalValue($this->_params['disableResourceReservation'])) {
    	$this->_guiVars['selectable'] = 'false';
		}
    
    // zobrazeni tlacitka "Zpet"
    $this->_guiVars['backButton'] = '';
    if (isset($this->_params['backButton'])&&$this->_params['backButton']) {
      $this->_guiVars['backButton'] .= sprintf('<input type="button" id="%sflb_calendar_back" value="%s" />',
                                    $this->_guiVars['prefix'], $this->_app->textStorage->getText('button.back'));
    }

    // poducty
		$this->_useUserSubaccount = BCustomer::getProviderSettings($this->_params['provider'],'userSubaccount')=='Y';
    if ($this->_useUserSubaccount) {
			$this->_guiVars['useUserSubaccount'] = 1;
			$this->_guiVars['subaccountHiddenTitle'] = ' style="display:none;"';
		} else {
			$this->_guiVars['useUserSubaccount'] = 0;
			$this->_guiVars['subaccountHiddenTitle'] = '';
		}
    
    // tlacitka pro placeni pres platebni branu
    $this->_guiVars['removeResourcePaymentGatewayButton'] = '';
    $this->_guiVars['removeEventPaymentGatewayButton'] = '';
    global $PAYMENT_GATEWAY;
    foreach ($PAYMENT_GATEWAY['source'] as $gateway=>$struct) {
      $this->_guiVars['removeResourcePaymentGatewayButton'] .= sprintf("$('#%seditReservationResource_form').dialog('removebutton', '%s %s');\n",
                      $this->_guiVars['prefix'],                                           
                      $this->_app->textStorage->getText('button.calendar_editReservation_savePayGW'),
                      $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gateway));
      $this->_guiVars['removeResourcePaymentGatewayButton'] .= sprintf("$('#%seditReservationResource_form').dialog('removebutton', '%s %s');\n",
                      $this->_guiVars['prefix'], 
                      $this->_app->textStorage->getText('button.calendar_editReservation_payGW'),
                      $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gateway));
      
      $this->_guiVars['removeEventPaymentGatewayButton'] .= sprintf("$('#%seditReservationEvent_form').dialog('removebutton', '%s %s');\n",
                      $this->_guiVars['prefix'], 
                      $this->_app->textStorage->getText('button.calendar_editReservation_savePayGW'),
                      $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$gateway));
    }
    
    $this->_guiVars['addResourcePaymentGatewayPayButton'] = '';
    $this->_guiVars['addResourcePaymentGatewaySavePayButton'] = '';
    $this->_guiVars['addEventPaymentGatewaySavePayButton'] = '';
    $s = new SProviderPaymentGateway;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
		$s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('providerpaymentgateway_id','gateway_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if (isset($PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount'])) {
        $this->_guiVars['addResourcePaymentGatewayPayButton'] .= sprintf('if (parseInt(%s)<=parseFloat(reservationPrice)) ', $PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount']);
      }
      $this->_guiVars['addResourcePaymentGatewayPayButton'] .=
          sprintf("$('#%seditReservationResource_form').dialog('addbutton', '%s %s', '%seditReservationResourceButtonPayGW_%s', '', function() {
              flbPaymentGateway('%s','%s','RESERVATION',$('#%seditReservationResource_id').val(),null,null,
              \"$('#%seditReservationResource_form').dialog('close');$('#%scal_%s').fullCalendar('refetchEvents');\");
            });", $this->_guiVars['prefix'], $this->_app->textStorage->getText('button.calendar_editReservation_payGW'), $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$row['gateway_name']),
            $this->_guiVars['prefix'], $row['gateway_name'], $this->_guiVars['paymentUrl'], $row['gateway_name'],
            $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['id']);

      if (isset($PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount'])) {
        $this->_guiVars['addResourcePaymentGatewaySavePayButton'] .= sprintf('if (parseInt(%s)<=parseFloat(reservationPrice)) ', $PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount']);
      }
      $this->_guiVars['addResourcePaymentGatewaySavePayButton'] .=     
          sprintf("$('#%seditReservationResource_form').dialog('addbutton', '%s %s', '%seditReservationResourceButtonSavePayGW_%s', 'flb_primaryButton', function() {
              var params = { id: $('#%seditReservationResource_id').val(), user: $('#%seditReservationResource_userId').val(), paymentOnline: '%s' };
              if (!$('#%seditReservationResource_id').val()) {
                params.resource = getResourceForReservation('%s');
                params.start = $('#%seditReservationResource_from').val();
                params.end = $('#%seditReservationResource_to').val();
                params.attribute = reservationAttributePrepare('#%seditReservationResource_form');
              }
              if (%scalAd&&($('#%seditReservationResource_userNameSelected').val()!=$('#%seditReservationResource_userName').val())) {
                alert('%s');
              } else if (id=reservationResourceSave(params)) {
                flbPaymentGateway('%s','%s','RESERVATION',id,null,null,
                \"$('#%seditReservationResource_form').dialog('close');$('#%scal_%s').fullCalendar('refetchEvents');\");
              }
            });", $this->_guiVars['prefix'], $this->_app->textStorage->getText('button.calendar_editReservation_savePayGW'), $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$row['gateway_name']),
            $this->_guiVars['prefix'], $row['gateway_name'], $this->_guiVars['prefix'], $this->_guiVars['prefix'], $row['gateway_name'], $this->_guiVars['prefix'], 
            $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['prefix'],
            $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_app->textStorage->getText('label.calendar.editReservation_unknownUser'),
            $this->_guiVars['paymentUrl'], $row['gateway_name'],
            $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['id']);

      if (isset($PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount'])) {
        $this->_guiVars['addEventPaymentGatewaySavePayButton'] .= sprintf('if (parseInt(%s)<=parseFloat(reservationPrice)) ', $PAYMENT_GATEWAY['source'][$row['gateway_name']]['minimalAmount']);
      }
      $this->_guiVars['addEventPaymentGatewaySavePayButton'] .=     
          sprintf("$('#%seditReservationEvent_form').dialog('addbutton', '%s %s', '%seditReservationEventButtonSavePayGW_%s', 'flb_primaryButton', function() {
              var params = { id: $('#%seditReservationEvent_id').val(), user: $('#%seditReservationEvent_userId').val(), paymentOnline: '%s', 
                           places: $('#%seditReservationEvent_places').val(), event: $('#%seditReservationEvent_event').val(), pack: $('#%seditReservationEvent_pack').val() };
              if (%scalAd&&($('#%seditReservationEvent_userNameSelected').val()!=$('#%seditReservationEvent_userName').val())) {
                alert('%s');
              } else if (resId=reservationEventSave(params)) {
                flbPaymentGateway('%s','%s','RESERVATION',resId,null,null,\"\");
              }
            });", $this->_guiVars['prefix'], $this->_app->textStorage->getText('button.calendar_editReservation_savePayGW'), $this->_app->textStorage->getText('label.ajax_paymentGateway_'.$row['gateway_name']),
            $this->_guiVars['prefix'], $row['gateway_name'], $this->_guiVars['prefix'], $this->_guiVars['prefix'], $row['gateway_name'], $this->_guiVars['prefix'],
            $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_guiVars['prefix'],
            $this->_guiVars['prefix'], $this->_guiVars['prefix'], $this->_app->textStorage->getText('label.calendar.editReservation_unknownUser'),
            $this->_guiVars['paymentUrl'], $row['gateway_name']);

    }
  }
  
  protected function _prepareResourceAttribute() {
    if ($this->_multi||$this->_pool||$this->_event) return '';
    
    $ret = '';
    
    $b = new BResource($this->_guiVars['id']);
    $attributes = $b->getAttribute();
    
    $category = '';
    foreach ($attributes as $id=>$attribute) {
      if (isset($this->_params['showAttribute'])&&$this->_params['showAttribute']) {
        if (!in_array($attribute['category'], $this->_params['showAttribute'])) continue;
      }
      // atributy jsou uzavreny do DIVu kategorie
      if (strcmp($category,$attribute['category'])) {
        if ($category) $ret .= '</div>';
        if ($attribute['category']) $ret .= sprintf('<div class="flb_commodity_attributecategory_name">%s</div><div class="flb_commodity_attributecategory" id="flb_commodity_attributecategory_%s">', $attribute['category'], htmlize($attribute['category']));
      }
      switch ($attribute['type']) {
        case 'NUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value']); break;
        case 'DECIMALNUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2); break;
        case 'TIME':
          $value = $this->_app->regionalSettings->convertTimeToHuman($attribute['value'],'h:m');
          if (isset($this->_params['format']['time'])) $value = date($this->_params['format']['time'], strtotime($value));
          break;
        case 'DATETIME':
          $value = $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']);
          if (isset($this->_params['format']['datetime'])) $value = date($this->_params['format']['datetime'], strtotime($value));
          break;
        case 'DATE':
          $value = $this->_app->regionalSettings->convertDateToHuman($attribute['value']);
          if (isset($this->_params['format']['date'])) $value = date($this->_params['format']['date'], strtotime($value));
          break;
        case 'FILE':
          global $AJAX;
          $value = sprintf('<a target="_attributeFile" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), $attribute['valueId'], $attribute['value']);
          break;
        default: $value = $attribute['value'];
      }
      
      $attrHtml = sprintf('<div id="flb_commodity_attribute_%s" class="flb_commodity_attribute"><div class="label">%s:</div><div class="value flb_commodity_attributevalue">%s</div></div>',
				$id, formatAttributeName($attribute['name'], $attribute['url']), $value);
      
      $ret .= $attrHtml;
      
      $category = $attribute['category'];
    }
    if ($category) $ret .= '</div>';
    
    $this->_guiVars['attribute'] = $ret;
  }

  protected function _userRender() {
    if (!strcmp(get_class($this->_app),'FlexBook')) $this->_app->document->addJavascriptFile('flbv2fn.js');

    $this->_initDefaultParams();
    
    if (!strcmp($this->_params['calendarType'],'resource')) $this->_initResource();
    elseif (!strcmp($this->_params['calendarType'],'event')) $this->_initEvent();

    if ($this->_empty) {
			$this->setTemplateString(sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData')));
		} else {
			$this->_prepareResourceAttribute();
			$this->_prepareCallUrl();
			$this->_prepareTimeAttributes();
			$this->_prepareView();
			$this->_prepareDesign();
			$this->_prepareControl();

			// funkcni parametry JS
			$this->_guiVars['params'] = encodeAjaxParams($this->_params);

			$this->_createTemplate();

			$this->setTemplateString($this->_guiTemplate);

			foreach ($this->_guiVars as $key => $value) {
				$this->insertTemplateVar($key, $value, false);
			}
		}
  }
}

?>