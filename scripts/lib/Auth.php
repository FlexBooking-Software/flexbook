<?php

class AdminAuth extends DbAuth {
  protected $_userEmail;
  protected $_section;
  protected $_subSection;
  public $_rights = array();
  protected $_user;
  protected $_admin;
  protected $_provider;
  protected $_organiser;
  protected $_providerId;
  protected $_providerName;
  protected $_centerId;
  protected $_providerCustomer;
  protected $_allowedProvider = array();
  protected $_allowedCenter = array();
  
  protected function _loadAuth() {
    parent::_loadAuth();
    $this->_userEmail =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_user_email');
    $this->_section =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_section');
    $this->_subSection =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_subSection');
    $this->_rights =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_rights');
    $this->_user =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_user');
    $this->_admin =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_admin');
    $this->_provider =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_provider');
    $this->_organiser =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_organiser');
    $this->_providerId =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_providerId');
    $this->_providerName =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_providerName');
    $this->_centerId =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_centerId');
    $this->_providerCustomer =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_providerCustomer');
    $this->_allowedProvider =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_allowedProvider');
    $this->_allowedCenter =& Application::get()->session->getPtr($this->_nameSessionVar .'auth_allowedCenter');
  }

  protected function _getExecAuthenticateSql($params) {
    $app = Application::get();

    if (isset($params['provider'])&&$params['provider']) {
      if (!strcmp($params['provider'],'NULL')) $providerCond = 'AND ur.provider IS NULL';
      else $providerCond = 'AND ur.provider='.$app->db->escapeString($params['provider']);
    } else $providerCond = '';

    $userCond = isset($params['username'])&&$params['username']?sprintf("AND UPPER(u.username)='%s'", $app->db->escapeString(strtoupper($params['username']))):'';

    $facebookCond = isset($params['facebook'])&&$params['facebook']?'AND u.facebook_id='.$app->db->escapeString($params['facebook']):'';
    $googleCond = isset($params['google'])&&$params['google']?'AND u.google_id='.$app->db->escapeString($params['google']):'';
    $twitterCond = isset($params['twitter'])&&$params['twitter']?'AND u.twitter_id='.$app->db->escapeString($params['twitter']):'';

    $query = sprintf("SELECT u.user_id AS userId, u.username AS username, u.password AS password, CONCAT(u.firstname,' ',u.lastname) AS fullname, u.admin AS admin, 
                     ur.provider AS providerId, p.invoice_name AS providerName, p.short_name AS providerShortName 
                     FROM user AS u
                     LEFT JOIN userregistration AS ur ON ur.user=u.user_id AND (ur.admin='Y' OR ur.supervisor='Y' OR ur.reception='Y' OR ur.power_organiser='Y')
                     LEFT JOIN provider AS p ON ur.provider=p.provider_id
                     WHERE u.validated IS NOT NULL AND u.disabled='N' AND u.parent_user IS NULL
                     %s %s %s %s %s", $providerCond, $userCond, $facebookCond, $googleCond, $twitterCond);

    return $query;
  }

  protected function _addSimilarAccounts($accounts) {
    $app = Application::get();

    $usernames = $ids = array();
    foreach ($accounts as $account) {
      $usernames[] = sprintf("'%s'", $account['username']);
      $ids[] = $account['userId'];
    }

    $s = new SUser;
    $s->addStatement(new SqlStatementMono($s->columns['username'], sprintf('%%s IN (%s)', implode(',', $usernames))));
    $s->addStatement(new SqlStatementMono($s->columns['user_id'], sprintf('%%s NOT IN (%s)', implode(',', $ids))));
    $s->addStatement(new SqlStatementMono($s->columns['validated'], '%s IS NOT NULL'));
    $s->addStatement(new SqlStatementMono($s->columns['disabled'], "%s='N'"));
    $s->addStatement(new SqlStatementMono($s->columns['parent_user'], '%s IS NULL'));
    $s->addStatement(new SqlStatementQuad($s->columns['registration_admin'], $s->columns['registration_supervisor'], $s->columns['registration_power_organiser'],
      $s->columns['registration_reception'], "((%s='Y') OR (%s='Y') OR (%s='Y') OR (%s='Y'))"));
    $s->setColumnsMask(array('user_id','username','fullname','admin','provider_id','provider_invoice_name','provider_short_name'));
    $res = $app->db->doQuery($s->toString());
    while ($row = $app->db->fetchAssoc($res)) {
      $accounts[] = array(
        'userId'              => $row['user_id'],
        'username'            => $row['username'],
        'fullname'            => $row['fullname'],
        'admin'               => $row['admin'],
        'providerId'          => $row['provider_id'],
        'providerName'        => $row['provider_invoice_name'],
        'providerShortName'   => $row['provider_short_name'],
        'authenticated'       => 0
      );
    }

    return $accounts;
  }

  protected function _execAuthenticate($params) {
    if (!isset($params['username'])&&!isset($params['google'])&&!isset($params['twitter'])&&!isset($params['facebook'])) return false;

    $app = Application::get();
    $ret = false;

    // ziskam vsechny uzivatele s danym username (pripadne pro daneho poskytovatele)
    // heslo budu kontrolovat az pozdeji, protoze chci nabidnout login i na ucet se stejnym username ale jinym password
    $query = $this->_getExecAuthenticateSql($params);
    $result = $app->db->doQuery($query);

    if ($result&&($count=$app->db->getRowsNumber($result))) {
      if ($count>1) $this->reset();

      if (isset($params['password'])) $enteredPassword = $this->getMd5Password()?md5($app->db->escapeString($params['password'])):$app->db->escapeString($params['password']);
      else $enteredPassword = null;

      $ret = array();
      $anyAuthenticated = false;
      while ($row = $app->db->fetchAssoc($result)) {
        // pokud nema registraci s pozadovanymi pravy, tak ji nebudu nabizet na prihlaseni
        if (($row['admin']=='Y')||$row['providerId']) {
          if (($enteredPassword&&!strcmp($enteredPassword, $row['password']))||
            (isset($params['facebook'])&&$params['facebook'])||(isset($params['google'])&&$params['google'])||(isset($params['twitter'])&&$params['twitter'])) {
            $row['authenticated'] = 1;
            $anyAuthenticated = true;
          } else {
            $row['authenticated'] = 0;
          }
          unset($row['password']);

          $ret[] = $row;
        }
      }
      if (!count($ret)||!$anyAuthenticated) $ret = false;
      elseif ((isset($params['facebook'])&&$params['facebook'])||(isset($params['google'])&&$params['google'])||(isset($params['twitter'])&&$params['twitter'])) {
        // pokud je alepson jeden ucet overeny a prihlasuje se pres externi ucet, a nemi jeste vybran poskytovatel, pridam jeste ucty se stejnym emailem jako non-authenticated
        if (!isset($params['provider'])||!$params['provider']) $ret = $this->_addSimilarAccounts($ret);
      }
    } else {
      $this->reset();
    }

    return $ret;
  }

  public function authenticate($params, & $availAccount=null) {
    $ret = $this->_execAuthenticate($params);

    if ($ret===false) {
      $availAccount = null;
      return false;
    } elseif (count($ret)>1) {
      $availAccount = $ret;
      return false;
    } elseif (!$ret[0]['authenticated']) {
      $availAccount = null;
      return false;
    }

    $this->_saveAuth($ret[0]);

    $this->_providerId = null;
    $this->_providerName = null;
    $this->_providerCustomer = null;
    $this->_allowedProvider = array();
    $this->_allowedCenter = array();
    $this->_rights = array();

    #error_log($this->_userId);

  	$app = Application::get();
    $query = sprintf('SELECT u.email AS u_email, u.admin AS u_admin, 
                             ur.supervisor AS p_supervisor, ur.admin AS p_admin, ur.reception AS p_reception, ur.power_organiser AS p_power_organiser, ur.organiser AS p_organiser,
                             ur.role_center AS p_center, ur.provider AS p_provider,
                             c.customer_id AS p_customer, c.name AS p_name,
                             ps.show_company
                      FROM user AS u
                      LEFT JOIN userregistration AS ur ON u.user_id=ur.user
                      LEFT JOIN customer AS c ON c.provider=ur.provider
                      LEFT JOIN providersettings AS ps ON ur.provider=ps.provider 
                      WHERE u.user_id=%s', $this->_userId);
    $result = $app->db->doQuery($query);
    $row = $app->db->fetchAssoc($result);

    // kdyz neni admin ani nema prava na poskytovatele, neprihlasi se
    if (($row['u_admin']!='Y')&&($row['p_admin']!='Y')&&($row['p_supervisor']!='Y')&&($row['p_reception']!='Y')&&($row['p_power_organiser']!='Y')) return false;

    $this->_user = 'N';
    $this->_userEmail = $row['u_email'];
    $this->_admin = $row['u_admin'];
    if ($this->_admin == 'Y') {
      // kdyz je admin
      $this->_provider = 'N';
      $this->_organiser = 'N';

      $this->_providerId = null;
      $this->_providerName = null;
      $this->_centerId = null;

      $query = sprintf('SELECT provider_id FROM provider');
      $result = $app->db->doQuery($query);
      while ($row = $app->db->fetchAssoc($result)) {
        $this->_rights[$row['provider_id']] = array(
          'user_admin'        => true,
          'customer_admin'    => true,
          'provider_edit'     => true,
          'commodity_admin'   => true,
          'commodity_read'    => true,
          'reservation_admin' => true,
          'credit_admin'      => true,
          'credit_add'        => true,
          'settings_admin'    => true,
          'report_admin'      => true,
        );
        $this->_allowedProvider[] = $row['provider_id'];
      }

      $s = new SCenter;
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('center_id'));
      $res = $app->db->doQuery($s->toString());
      while ($row = $app->db->fetchAssoc($res)) {
        $this->_allowedCenter[] = $row['center_id'];
      }
    } else {
      // kdyz ma prava na poskytovatele
      $this->_provider = ($row['p_admin']=='Y')||($row['p_supervisor']=='Y')||($row['p_reception']=='Y')||($row['p_power_organiser']=='Y')?'Y':'N';
      $this->_organiser = ($row['p_organiser']=='Y')||($row['p_power_organiser']=='Y')?'Y':'N';

      $this->_providerId = $row['p_provider'];
      $this->_providerName = $row['p_name'];
      $this->_providerCustomer = $row['p_customer'];
      $this->_centerId = null;

      $this->_rights[$row['p_provider']] = array(
        'user_admin'        => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y')||($row['p_reception']=='Y'),
        'customer_admin'    => isset($row['show_company'])?($row['show_company']=='Y'):false,
        'provider_edit'     => ($row['p_admin']=='Y'),
        'commodity_admin'   => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y'),
        'commodity_read'    => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y')||($row['p_reception']=='Y')||($row['p_power_organiser']=='Y'),
        'reservation_admin' => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y')||($row['p_reception']=='Y'),
        'credit_admin'      => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y')||($row['p_reception']=='Y'),
        'credit_add'        => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y')||($row['p_reception']=='Y'),
        'report_admin'      => ($row['p_admin']=='Y')||($row['p_supervisor']=='Y'),
        'report_reception'  => ($row['p_reception']=='Y'),
        'organiser'         => ($row['p_organiser']=='Y')||($row['p_power_organiser']=='Y'),
        'power_organiser'   => ($row['p_power_organiser']=='Y'),
        'settings_admin'    => ($row['p_admin']=='Y'),
        'delete_record'     => ($row['p_admin']=='Y'),
      );
      $this->_allowedProvider[] = $row['p_provider'];

      // nastaveni aktualniho strediska + nacteni povolenych stredisek
      $row['p_center'] = explode(',', $row['p_center']);
      if (!in_array('ALL',$row['p_center'])) {
        $limitCenter = '';
        foreach ($row['p_center'] as $center) {
          if ($limitCenter) $limitCenter .= ',';
          $limitCenter .= $app->db->escapeString($center);
        }
      } else $limitCenter = false;

      $s = new SCenter;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_providerId, '%s=%s'));
      if ($limitCenter) $s->addStatement(new SqlStatementMono($s->columns['center_id'], sprintf('%%s in (%s)', $limitCenter)));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('center_id'));
      $res = $app->db->doQuery($s->toString());
      while ($row = $app->db->fetchAssoc($res)) {
        if (!$this->_centerId) $this->_centerId = $row['center_id'];
        $this->_allowedCenter[] = $row['center_id'];
      }
    }

    return $ret;
  }
  
  public function reset() {
    parent::reset();
    
    $this->_section = null;
    $this->_rights = array();
    $this->_user = null;
    $this->_admin = null;
    $this->_provider = null;
    $this->_organiser = null;
    $this->_providerId = null;
    $this->_centerId = null;
    $this->_providerName = null;
    $this->_providerCustomer = null;
    $this->_allowedProvider = array();
    $this->_allowedCenter = array();
  }
  
  public function getRights() { return $this->_rights; }

  public function getSection() { return $this->_section; }
  public function setSection($section) { $this->_section = $section; }
  
  public function getSubSection() { return $this->_subSection; }
  public function setSubSection($section) { $this->_subSection = $section; }
  
  public function clearRights() { $this->_rights = array(); }

  public function haveRight($rightname, $provider=null) {
    $ret = $this->isAdministrator();
    
    if (!$ret&&$provider) {
      if ($provider=='ANY') {
        foreach ($this->_rights as $provider=>$rights) {
          if (isset($rights[$rightname])&&$rights[$rightname]) {
            $ret = true;
            break;
          }
        }
      } elseif (isset($this->_rights[$provider][$rightname])) {
        $ret = $this->_rights[$provider][$rightname];
      }
    }
    
    return $ret;
  }

  public function isUser() { return $this->_user=='Y'; }
  public function isAdministrator() { return $this->_admin=='Y'; }
  public function isProvider() { return $this->_provider=='Y'; }
  public function isOrganiser() { return $this->_organiser=='Y'; }

  public function getEmail() { return $this->_userEmail; }
  public function getActualProvider() { return $this->_providerId; }
  public function getActualProviderName() { return $this->_providerName; }
  public function getActualProviderCustomer() { return $this->_providerCustomer; }
  public function setActualProvider($id,$name,$customer) { $this->_providerId = $id; $this->_providerName = $name; $this->_providerCustomer = $customer; }
  public function isProviderMultiple() { return count($this->_allowedProvider)>1; }
  
  public function getActualCenter() { return $this->_centerId; }
  public function setActualCenter($id) { $this->_centerId = $id; }

  public function getAllowedCenter($output='list') {
    if ($output=='list') return count($this->_allowedCenter)?implode(',', $this->_allowedCenter):'0';
    else return $this->_allowedCenter;
  }

  public function addAllowedCenter($center) { $this->_allowedCenter[] = $center; }
  
  public function getAllowedProvider($rightname=null,$output='list') {
    if (!$rightname) $ret = $this->_allowedProvider;
    else {
      $ret = array();

      foreach ($this->_rights as $provider => $rights) {
        if (isset($rights[$rightname]) && $rights[$rightname]) {
          $ret[] = $provider;
        }
      }
    }

    if ($output=='list') return count($ret)?implode(',', $ret):'0';
    else return $ret;
  }
}

class FakeAuth extends AdminAuth {
  
  public function haveRight($rightname,$provider=null) { return true; }
  public function isUser() { return true; }
  public function isAdministrator() { return true; }
  public function isProvider() { return true; }
  public function isOrganiser() { return true; }
  public function getUsername() { return 'petr.kos@flexbook.cz'; }
}

class InPageAuth extends AdminAuth {

  protected function _getExecAuthenticateSql($params) {
    $app = Application::get();

    $userCond = isset($params['username'])&&$params['username']?sprintf("AND UPPER(u.username)='%s' AND u.password='%s'",
      $app->db->escapeString(strtoupper($params['username'])), $this->getMd5Password()?md5($app->db->escapeString($params['password'])):$app->db->escapeString($params['password'])):'';

    $facebookCond = isset($params['facebook'])&&$params['facebook']?'AND u.facebook_id='.$app->db->escapeString($params['facebook']):'';
    $googleCond = isset($params['google'])&&$params['google']?'AND u.google_id='.$app->db->escapeString($params['google']):'';
    $twitterCond = isset($params['twitter'])&&$params['twitter']?'AND u.twitter_id='.$app->db->escapeString($params['twitter']):'';

    $query = sprintf("SELECT u.user_id AS userId, u.username AS username, CONCAT(u.firstname,' ',u.lastname) AS fullname, email AS email
                     FROM user AS u
                     JOIN userregistration AS ur ON ur.user=u.user_id
                     WHERE u.validated IS NOT NULL AND u.disabled='N' AND u.parent_user IS NULL AND ur.provider='%s'
                     %s %s %s %s", $app->db->escapeString($params['provider']), $userCond, $facebookCond, $googleCond, $twitterCond);

    return $query;
  }

	protected function _saveAuth($params) {
  	parent::_saveAuth($params);

		$this->_userEmail = $params['email'];
	}

  protected function _execAuthenticate($params) { return DbAuth::_execAuthenticate($params); }

  public function authenticate($params, & $availAccounts=null) {
    $ret = DbAuth::authenticate($params);

    if ($ret) {
      $app = Application::get();
      $query = sprintf('SELECT u.email AS u_email, 
                               ur.power_organiser AS p_power_organiser, ur.organiser AS p_organiser
                        FROM user AS u
                        LEFT JOIN userregistration AS ur ON u.user_id=ur.user 
                        WHERE u.user_id=%s', $this->_userId);
      $result = $app->db->doQuery($query);
      $row = $app->db->fetchAssoc($result);
      $this->_rights[$params['provider']] = array(
        'organiser'         => ($row['p_organiser']=='Y')||($row['p_power_organiser']=='Y'),
        'power_organiser'   => ($row['p_power_organiser']=='Y'),
      );

      $this->_user = 'Y';
      $this->_providerId = $params['provider'];
      $this->_userEmail = $row['u_email'];
    }

    return $ret;
  }
  
  public function getAllowedProvider($rightname=null,$output='list') {
    if ($output=='list') return '0';
    else return array();
  }
  //public function haveRight($rightname,$provider=null) { return false; }
  public function isUser() { return $this->_userId; }
  public function isAdministrator() { return false; }
  public function isProvider() { return false; }
}

?>
