<?php
$frameworkPath = dirname(__FILE__) .'/../../webcore/';
require $frameworkPath .'init.php';

require dirname(__FILE__).'/config.php';
require dirname(__FILE__).'/module.init.php';
require dirname(__FILE__).'/class.init.php';
require dirname(__FILE__).'/lib/utils.php';

class FlexBook extends Application {

  protected function _initLanguage($params=array()) {
    parent::_initLanguage(array(
          'accept' => array('cz','en'),
          'defaultLanguage'=> 'cz'));
  }

  protected function _getCreateTextStorageParams() {
    $params = parent::_getCreateTextStorageParams();

    $params['cz'] = array(dirname(__FILE__) .'/ts.cz.txt.utf8');
    if (is_file(dirname(__FILE__) .'/my.ts.cz.txt.utf8')) $params['cz'][] = dirname(__FILE__) .'/my.ts.cz.txt.utf8';

    $params['en'] = array(dirname(__FILE__) .'/ts.en.txt');
    if (is_file(dirname(__FILE__) .'/my.ts.en.txt')) $params['en'][] = dirname(__FILE__) .'/my.ts.en.txt';

    return $params;
  }
  
  protected function _initCharset() {
    $this->setCharset('UTF-8');
  }

  protected function _initSession($params=array()) {
    global $SESSION;

    if (isset($SESSION['useCookie'])&&$SESSION['useCookie']) {
      setcookie('test', 'yes', strtotime("+1 month")); 
      if (!isset($_COOKIE["test"])) {
        $SESSION['useCookie'] = false;
      }
    }

    $params = array(
        'maxAge'          => ifsetor($SESSION['maxLife'], 60*30),
        'useCookie'       => ifsetor($SESSION['useCookie'], false),
        'destroyExpired'  => ifsetor($SESSION['destroyExpired'], true),
        );
    if (isset($SESSION['tmpDir'])) $params['savePath'] = $SESSION['tmpDir'];

    parent::_initSession($params);
  }

  protected function _initModules($params=array()) {
    global $MODULES_LIST;

    parent::_initModules($MODULES_LIST);
  }

  protected function _initAutoloadClasses($params=array()) {
    global $AUTOLOAD_CLASSES;

    parent::_initAutoloadClasses($AUTOLOAD_CLASSES);
  }

  protected function _getCreateDbParams() {
    global $DB;
    $params = parent::_getCreateDbParams();
    $params['user'] = $DB['user'];
    $params['password'] = $DB['password'];
    $params['database'] = $DB['database'];
    if (isset($DB['server'])) $params['server'] = $DB['server'];
    if (isset($DB['socket'])) $params['socket'] = $DB['socket'];
    if (isset($DB['encoding'])) $params['encoding'] = $DB['encoding'];
    return $params;
  }

  protected function _createDb($params) {
    $this->db = new MysqlIDb($params);
  }

  protected function _getCreateAuthParams() { return array('md5Password'=>true); }

  protected function _createAuth($params) {
    $this->auth = new AdminAuth($params);
  }

  protected function _testAction($action) {
    $userId = $this->auth->getUserId();

    // kdyz neni prihlaseny uzivatel nebo vyprsela session, jsou povolene pouze nektere akce
    if ((!ifsetor($userId,0)||$this->session->getExpired()) && 
        !in_array($action, array('eCheckLogin','vLogin','eLogin','eReLogin','eLogout','eXmlRequest',
                                 'eFacebookCall','eFacebookLogin','eUserFacebookAssign',
                                 'eGoogleCall','eGoogleLogin','eUserGoogleAssign',
                                 'eTwitterCall','eTwitterLogin','eUserTwitterAssign',
                                 'ePaymentGatewayInit','ePaymentGatewayFinish','eComgateStatus'))) {
      $action = 'eCheckLogin';
    } elseif (in_array($action, array('eFacebookCall','eUserFacebookAssign','eGoogleCall','eUserGoogleAssign','eTwitterCall','eUserTwitterAssign'))) {
      // tyto akce muze delat kdokoliv (prihlaseny/neprihlaseny)
    } elseif  ($this->auth->isUser()) {
      // kdyz je prihlaseny uzivatel jenom "user" (muze se stat pri ukradnuti session)
      // tak ho zariznu
      die('Access denied!');
    }

    return $action;
  }
}

class AdminAjax extends FlexBook {
  
  protected function _testAction($action) {
    return $action;
  }

  protected function _initModules($params=array()) {
    Application::_initModules(array(
      'eAjaxRequest'            => dirname(__FILE__).'/modules/ajax/eAjaxRequest.php',
      'vUserPrepaymentInvoice'  => dirname(__FILE__).'/modules/user/vUserPrepaymentInvoice.php',
      'vReservationTicket'      => dirname(__FILE__).'/modules/reservation/vReservationTicket.php',
      'vReservationReceipt'     => dirname(__FILE__).'/modules/reservation/vReservationReceipt.php',
      'vReservationInvoice'     => dirname(__FILE__).'/modules/reservation/vReservationInvoice.php',
    ));
  }
}

class Ajax extends AdminAjax {
  
  protected function _createAuth($params) {
    $this->auth = new InPageAuth($params);
  }
  
  protected function _initRegionalSettings($params=array()) {
    $params['numberDelimiterThousand'] = '&nbsp;';
    
    parent::_initRegionalSettings($params);
  }
}

class ProviderPortal extends Application {

  protected function _initLanguage($params=array()) {
    parent::_initLanguage(array(
          'accept' => array() ));
  }

  protected function _initCharset() {
    $this->setCharset('UTF-8');
  }
  
  protected function _getCreateTextStorageParams() {
    $params = parent::_getCreateTextStorageParams();
    $params['cz'] = array(
        dirname(__FILE__) .'/ts.cz.txt.utf8',
        );
    $params['en'] = array(
        dirname(__FILE__) .'/ts.en.txt',
        );
    return $params;
  }

  protected function _initModules($params=array()) {
    parent::_initModules(array('vProviderPortalView'=>dirname(__FILE__).'/modules/providerportal/vProviderPortalView.php'));
  }

  protected function _initAutoloadClasses($params=array()) {
    global $AUTOLOAD_CLASSES;

    parent::_initAutoloadClasses($AUTOLOAD_CLASSES);
  }

  protected function _getCreateDbParams() {
    global $DB;
    $params = parent::_getCreateDbParams();
    $params['user'] = $DB['user'];
    $params['password'] = $DB['password'];
    $params['database'] = $DB['database'];
    if (isset($DB['server'])) $params['server'] = $DB['server'];
    if (isset($DB['socket'])) $params['socket'] = $DB['socket'];
    if (isset($DB['encoding'])) $params['encoding'] = $DB['encoding'];
    return $params;
  }

  protected function _createDb($params) {
    $this->db = new MysqlIDb($params);
  }
  
  protected function _initSession($params=array()) {
    global $SESSION;

    $params = array(
        'maxAge'          => ifsetor($SESSION['maxLife'], 60*30),
        'useCookie'       => true,
        'destroyExpired'  => ifsetor($SESSION['destroyExpired'], true),
        );
    if (isset($SESSION['tmpDir'])) $params['savePath'] = $SESSION['tmpDir'];

    parent::_initSession($params);
  }
}

class InPage extends FlexBook {
  protected $_provider;
  
  protected function _createAuth($params) {
    $this->auth = new InPageAuth($params);
  }

  protected function _testAction($action) {
    return $action;
  }
  
  public function getProvider() { return $this->_provider; }
  public function setProvider($provider) { $this->_provider = $provider; }
}

?>
