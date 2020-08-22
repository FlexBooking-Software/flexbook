<?php

include_once(dirname(__FILE__).'/config_local.php');

$SESSION = array(
	'maxLife'         => 60*60*24,
	'useCookie'       => false,
	'destroyExpired'  => false,
);

$ONPAGE = array(
	'listUser'                  => 20,
	'listCustomer'              => 100,
	'listProvider'              => 20,
	'listResource'              => 20,
	'listResourcePool'          => 20,
	'listEvent'                 => 20,
	'listAvailProfile'          => 20,
	'listAvailExProfile'        => 20,
	'listUnitProfile'           => 20,
	'listReservationCondition'  => 20,
	'listNotificationTemplate'  => 20,
	'listDocumentTemplate'  		=> 20,
	'listPortalTemplate'        => 20,
	'listPageTemplate'          => 20,
	'listProviderPortal'        => 20,
	'listProviderInvoice'       => 20,
	'listProviderTextStorage'   => 20,
	'listProviderPortalPage'    => 20,
	'listPriceList'             => 20,
	'listProviderAccountType'   => 20,
	'listTicket'                => 20,
	'listVoucher'               => 20,
	'listTag'                   => 20,
	'listSimilarTag'            => 10,
	'listReservation'           => 20,
	'listInvoice'               => 20,
	'listDocument'              => 20,
	'listOnlinePayment'         => 20,
);

$RIGHTS = array('admin','user_admin','customer_admin','commodity_admin','reservation_admin','credit_admin');

$GOOGLE_MAP_URL = 'http://maps.googleapis.com/maps/api/staticmap?key=AIzaSyAa8iblOQSCXAOVjrUsqwna-SzzkMocr1g&zoom=15&size={dims}&sensor=false&markers=color:red|{coords}';

$FACEBOOK = array(
      'apiSrc'        => dirname(__FILE__).'/../../flexbook.lib/facebook_api/autoload.php',
      'appId'         => '145005125930241',
      'appSecret'     => '98e513995a96751ce7a90a3a91416cbd',
      'backLoginUrl'  => "$NODE_URL/index.php?action=eFacebookLogin",
      'backAssignUrl' => "$NODE_URL/index.php?action=eUserFacebookAssign",
      'permissions'   => array('email'),
      );

$GOOGLE = array(
      'apiSrc'        => dirname(__FILE__).'/../../flexbook.lib/google_api/autoload.php',
      'clientId'      => '799231701994-4i0so3hmanm2r972r6pp4gclnmj5emtk.apps.googleusercontent.com',
      'clientSecret'  => 'WCG9jU482euIWZTQfTGHz4TJ',
      'backLoginUrl'  => "$NODE_URL/index.php?action=eGoogleLogin",
      'backAssignUrl' => "$NODE_URL/index.php?action=eUserGoogleAssign",
      );

$TWITTER = array(
      'apiSrc'          => dirname(__FILE__).'/../../flexbook.lib/twitter_api/autoload.php',
      'consumerKey'     => 'hmhywm7ajA7RkPIMAsUE3M56S',
      'consumerSecret'  => 'PM3Uu63yNf2lPo26Eqmt9Y4R81F4Tnq8UvZOkdU6DtS0NV1h02',
      'backLoginUrl'    => "$NODE_URL/index.php?action=eTwitterLogin&sessid=%s",
      'backAssignUrl'   => "$NODE_URL/index.php?action=eUserTwitterAssign&sessid=%s",
      );

$XML_SETTINGS = array('log'=>dirname(__FILE__).'/../xml.log');

$AJAX = array(
  'version'           => '2020-05-24-002',
  'adminUrl'          => "$NODE_URL/aajax.php",
  'url'               => "$NODE_URL/ajax.php",
);

$RESOURCE_AVAILABILITY = array(
  'future'    => 365,  // pocet dnu do budoucna, pro ktere se udrzuje availabilita
);

$EVENT_REPEAT_CYCLE = array('DAY_1','WEEK_1','WEEK_2','WEEK_3','MONTH_1','MONTH_2','YEAR_1','INDIVIDUAL');
$EVENT_REPEAT_RESERVATION = array('SINGLE','PACK','BOTH');

$URL = array(
    'user_validation'   => "$NODE_URL/ajax.php?action=confirmUser&code=",
    );

$NOTIFICATION = array(
  'lockFile'              => $NOTIFICATION_LOCK_FILE,
  'errorCountToNotify'    => $NOTIFICATION_ERROR_COUNT_TO_NOTIFY,
  'adminEmail'            => $NOTIFICATION_ADMIN_EMAIL,
  'debugEmail'            => $NOTIFICATION_DEBUG_EMAIL,
  'smtpHost'              => $NOTIFICATION_SMTP_HOST,
	'smtpPort'              => $NOTIFICATION_SMTP_PORT,
	'smtpSecure'            => $NOTIFICATION_SMTP_SECURE,
  'smtpUser'              => $NOTIFICATION_SMTP_USER,
  'smtpPassword'          => $NOTIFICATION_SMTP_PASSWORD,
	'backupSmtp' => array(
		'smtpHost'              => 'mail.nvl.cz',
		'smtpUser'              => 'boss@nvl.cz',
		'smtpPassword'          => 'EmilkA!',
		'smtpSecure'            => 'tls',
	),
  'defaultAddressFrom'    => 'no-reply@flexbook.cz',
  'passwordTemplate' => array(
     'subject'       => 'Pristupove udaje/Access codes: @@PROVIDER_WWW',
     'body'          => "Zasilame Vam pristupove udaje do rezervacniho systemu poskytovatele: @@PROVIDER_NAME\n\n".
                        "Vase jmeno je: @@USER_LOGIN\nVase heslo je: @@USER_PASSWORD\n\n".
			                  "V pripade problemu ci dotazu nas nevahejte kontaktovat.\n\n".
			                  "E-mail: @@PROVIDER_EMAIL\nTelefon: @@PROVIDER_PHONE\nWWW: @@PROVIDER_WWW\n\n".
                        "------------\n\n".
                        "We are sending you your access code into the reservation system of provider: @@PROVIDER_NAME\n\n".
                        "Your username is: @@USER_LOGIN\nYour password: @@USER_PASSWORD\n\n".
			 									"Do not hesitate to contact us in case of any issue or additional questions.\n\n".
			 									"E-mail: @@PROVIDER_EMAIL\nTelefon: @@PROVIDER_PHONE\nWWW: @@PROVIDER_WWW"
  ),
  'registrationTemplate' => array(
     'subject'       => 'Potvrzeni registrace/Confirm registration: @@PROVIDER_WWW',
     'body'          => "Potvrzujeme Vasi registraci do rezervacniho systemu poskytovatele: @@PROVIDER_NAME\n\n".
			                  "Pro dokonceni registrace kliknete na nasledujici odkaz: @@USER_VALIDATION_URL\n\n".
			                  "V pripade problemu ci dotazu nas nevahejte kontaktovat.\n\n".
			                  "E-mail: @@PROVIDER_EMAIL\nTelefon: @@PROVIDER_PHONE\nWWW: @@PROVIDER_WWW\n\n".
			                  "------------\n\n".
			 									"We are confirming your registration into the reservation system of provider: @@PROVIDER_NAME\n\n".
			                  "To finish Your registration follow this url: @@USER_VALIDATION_URL".
			 									"Do not hesitate to contact us in case of any issue or additional questions.\n\n".
			 									"E-mail: @@PROVIDER_EMAIL\nTelefon: @@PROVIDER_PHONE\nWWW: @@PROVIDER_WWW"
  ),
);

$RESERVATION_NOTIFICATION = array('HOUR_1','HOUR_2','DAY_1','DAY_2','WEEK_1');

$REPORT_COLUMNS = array(
          'user'        => array('firstname','lastname','email','phone','street','city','postal_code','parent_user',
                                 'registration_timestamp','registration_advertising','validated','disabled','organiser',
                                ),
          'attendee'    => array('name','places','start','end','organiser_fullname',
                                 'reservation_number','subscription_time','failed',
                                 'person_firstname','person_lastname','person_email',
                                ),
          'reservation' => array('number','created','cancelled','description','note','mandatory','payed','mixed_accounttype_name',
                                 'total_price','price_timestamp','price_comment','price_user_name','payment_type','payment_id',
                                 'user_firstname','user_lastname','user_email','user_phone','user_street','user_city','user_postal_code',
                                 'mixed_resource_name','resource_from','resource_to',
                                 'event_name','event_places',/*'event_start','event_end',*/'eventattendee_event_start','eventattendee_event_end',
                                 'eventattendeeperson_firstname','eventattendeeperson_lastname','eventattendeeperson_email',
                                ),
          'credit'      => array('change_user_firstname','change_user_lastname','change_user_email','change_user_phone',
                                 'change_timestamp','amount','description',
                                ),
          );

$PROVIDER_PORTAL = array(
	'mod_rewrite'   => $MOD_REWRITE,
);

$INVOICE = array(
  'pdf_creator'                                 => dirname(__FILE__).'/../../flexbook.lib/mpdf/mpdf.php',
	'admin'            														=> explode(',',$NOTIFICATION_ADMIN_EMAIL),
  'number_prefix'                               => 'VF1-',
  'dueLength'                                   => 14,
  'itemLabel_monthFee'                          => 'Poplatek ze support (v období od %s do %s)',
  'itemLabel_reservationFee'                    => 'Poplatek za vytvořené rezervace (v období od %s do %s)',
  'itemLabel_paidReservationPriceFee'           => 'Poplatek z ceny zaplacených rezervací (v období od %s do %s)',
  'itemLabel_realisedReservationPriceFee'       => 'Poplatek z ceny realizovaných rezervací (v období od %s do %s)',
  'itemLabel_paidReservationPriceFeeBack'       => 'Refundace poplatku z ceny refundovaných rezervací (v období od %s do %s)',
  'itemLabel_realisedReservationPriceFeeBack'   => 'Refundace poplatku z ceny stornovaných rezervací (v období od %s do %s)',
  'itemLabel_overPayment'                       => 'Přeplatek z předchozího období (VS: %s)',
  'notificationSubject'                         => 'Nova vygenerovana faktura v systemu FLEXBOOK',
  'notificationBody'                            => "Dobry den,\n\nv rezervacnim systemu FLEXBOOK Vam byla vygenerovana nova faktura {invoiceNumber}.\nFakturu muzete stahnout zde: {invoiceUrl}\nFakturu take najdete v svem profilu. Navod na stazeni faktury naleznete zde: http://kb.flexbook.cz/2019/01/kb-10003-kde-najdu-faktury-za-pouzivani.html\n\nDekujeme za jeji vcasnou uhradu.\n\nVas tym Flexbook",
);
    
?>
