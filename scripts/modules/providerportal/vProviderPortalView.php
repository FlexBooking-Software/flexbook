<?php

class ModuleProviderPortalView extends DocumentModule {
  private $_urlDir = null;
  
  private $_portalData = null;
  private $_pageData = null;
  
  private $_preview = false;
  
  private function _getMenu() {
    $ret = '<div class="portalMenu">';
    
    $s = new SProviderPortalMenu;
    $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_portalData['providerportal_id'], '%s=%s'));
    $s->addOrder(new SqlStatementAsc($s->columns['sequence_code']));
    $s->setColumnsMask(array('name', 'page_short_name'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      $item = sprintf('<div id="%s" class="portalMenuItem">%s</div>', $row['page_short_name'], $this->_getPageLink($row['page_short_name'], $row['name']));
      
      $ret .= $item;
    }
    
    $ret .= '</div>';
    
    return $ret;
  }
  
  private function _getPageLink($id, $label) {
    if ($this->_app instanceof ProviderPortal) {
      global $PROVIDER_PORTAL;
      if ($PROVIDER_PORTAL['mod_rewrite']) {
        $url = sprintf('<a href="%s/%s/%s/%s">%s</a>', $this->_urlDir,
                     $this->_portalData['provider_short_name'], $this->_portalData['url_name'], 
                     $id, $label);
      } else {
        $url = sprintf('<a href="%s/portal.php?id=%s&provider=%s&page=%s">%s</a>', $this->_urlDir,
                     $this->_portalData['url_name'], $this->_portalData['provider_short_name'],
                     $id, $label); 
      }
    } else {
      $url = sprintf('<a href="%s/index.php?action=vProviderPortalView&%sid=%s&provider=%s&page=%s%s">%s</a>', $this->_urlDir,
                     $this->_preview?'preview=1&':'',
                     $this->_portalData['url_name'], $this->_portalData['provider_short_name'],
                     $id, $this->_app->session->getTagForUrl(), $label);
    }
    
    return $url;
  }
  
  private function _findParseElement($text, &$position, &$element) {
    // vyhledavam vsechny povolene reference,
    // vratim tu, ktera je nejbliz zacatku
    $position = false;

    $min = false;
    foreach (array('@@PAGE','@@FILE','@@PROVIDER_ID') as $tag) {
      if ($i = strpos($text, $tag.'(')) {
        if (!$min||($i<$min)) {
          $min = $i;
          $position = $i;
          $element = $tag;
        }
      }
    }
  }

  
  private function _parseContent($content) {
    /*
     * @@PAGE(<ID>,<nazev odkazu>)
     * @@FILE(<ID>)
     * @@PROVIDER_ID()
     */
    $ret = '';
    
    $findFrom = 0; $lastCopied = 0;
    while (true) {
      $this->_findParseElement(substr($content, $findFrom), $elementPosition, $element);
      if ($elementPosition===false) break; // kdyz uz neni zadny odkaz, koncim

      $k = $elementPosition + $lastCopied + strlen($element.'(');
      $elementParams = '';
      while ($k < strlen($content) && $content[$k] != ')') { // parse parametru odkazu
        $elementParams .= $content[$k];
        $k++;
      }
      $findFrom = $k + 1;

      $elementParams = explode(',',$elementParams);
      if (count($elementParams)) {
        $id = $elementParams[0];
        $label = ifsetor($elementParams[1]);
        
        $replacement = '';
        switch ($element) {
          case '@@FILE':
            $s = new SProviderFile;
            $s->addStatement(new SqlStatementBi($s->columns['short_name'], $id, '%s=%s'));
            $s->addStatement(new SqlStatementBi($s->columns['provider'], ifsetor($this->_portalData['provider']), '%s=%s'));
            $s->setColumnsMask(array('hash'));
            $res = $this->_app->db->doQuery($s->toString());
            if ($row = $this->_app->db->fetchAssoc($res)) $replacement = sprintf('%s/getfile.php?id=%s',
                                          $this->_urlDir, $row['hash']);
            else $replacement = 'NOT_FOUND';
            
            break;
          case '@@PAGE':
            $s = new SProviderPortalPage;
            $s->addStatement(new SqlStatementBi($s->columns['short_name'], $id, '%s=%s'));
            $s->addStatement(new SqlStatementBi($s->columns['providerportal'], ifsetor($this->_portalData['providerportal_id']), '%s=%s'));
            $s->setColumnsMask(array('name'));
            $res = $this->_app->db->doQuery($s->toString());
            if ($row = $this->_app->db->fetchAssoc($res)) $replacement = $this->_getPageLink($id, $label?$label:$row['name']);
            else $replacement = 'NOT_FOUND';
            
            break;
          case '@@PROVIDER_ID':
            $replacement = $this->_portalData['provider'];
            
            break;
        }

        $ret .= substr($content, $lastCopied, $elementPosition).$replacement;
        $lastCopied = $findFrom;
      }
    }
    $ret .= substr($content, $lastCopied);

    return $ret;
  }
  
  private function _getPortalContent() {
    $ret = str_replace(array('@@MENU()','@@CONTENT()'), array($this->_getMenu(), $this->_parseContent($this->_pageData['content'])), $this->_parseContent($this->_portalData['content']));
    
    return $ret;
  }
  
  private function _insertPortal($provider, $portal) {
    $s = new SProviderPortal;
    $s->addStatement(new SqlStatementBi($s->columns['url_name'], $portal, 'LOWER(%s)=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['provider_short_name'], $provider, 'LOWER(%s)=%s'));
    if (!$this->_preview) $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('providerportal_id','provider','provider_short_name','url_name','name','css','javascript','content','home_page'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) $this->_portalData = $row;
    
    $s = new SProviderPortalPage;
    if ($page = $this->_app->request->getParams('page')) $s->addStatement(new SqlStatementBi($s->columns['short_name'], $page, '%s=%s'));
    else $s->addStatement(new SqlStatementBi($s->columns['providerportalpage_id'], ifsetor($this->_portalData['home_page']), '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['providerportal'], ifsetor($this->_portalData['providerportal_id']), '%s=%s'));
    $s->setColumnsMask(array('providerportalpage_id','name','content'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) $this->_pageData = $row;
    
    if (!$this->_portalData||!$this->_pageData) die('Invalid portal!');
    
    global $AJAX;
    #$this->_urlDir = dirname($AJAX['adminUrl']);
    #$this->_urlDir = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $AJAX['relativeUrlPath'];
    $this->_urlDir = dirname($AJAX['url']);
    
    $content = $this->_getPortalContent();
    
    $template = sprintf('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title>%s</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=11" />
<script src="%s/jq/jquery.js"></script>
<script src="%s/flbv2.js"></script>
<style>%s</style>
<script>%s</script>
</head>
<body>%s</body>
</html>', $this->_portalData['name'],
$this->_urlDir, $this->_urlDir,
$this->_parseContent($this->_portalData['css']), $this->_parseContent($this->_portalData['javascript']), $content);
    
    if ($this->_app->getDebug()) $template .= '<hr/>'.$this->_insertMessages();
    $this->_app->messages->reset();
    
    echo $template;
  }

  private function _insertProvider($provider) {
    $s = new SProvider;
    $s->addStatement(new SqlStatementBi($s->columns['short_name'], $provider, 'LOWER(%s)=%s'));
    $s->setColumnsMask(array('provider_id','short_name','phone_1','phone_2','www','bank_account_number','bank_account_suffix','name','ic','dic','email',
                             'street','city','postal_code'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) $this->_providerData = $row;
    else die('Unknown provider!');
    if ($this->_providerData['phone_2']) {
      if ($this->_providerData['phone_1']) $this->_providerData['phone_1'] .= ', ';
      $this->_providerData['phone_1'] .= $this->_providerData['phone_2'];
    }
    if ($this->_providerData['bank_account_suffix']) {
      if ($this->_providerData['bank_account_number']) $this->_providerData['bank_account_number'] .= '/';
      $this->_providerData['bank_account_number'] .= $this->_providerData['bank_account_suffix'];
    }
    $this->_providerData['portal'] = '<table><tr><th>{__label.viewProviderPortal_portalShort}</th><th>{__label.viewProviderPortal_portalName}</th><th>{__label.viewProviderPortal_portalUrl}</th></tr>';
    
    $s = new SProviderPortal;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_providerData['provider_id'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('url_name','name'));
    $res = $this->_app->db->doQuery($s->toString());
    global $AJAX;
    global $PROVIDER_PORTAL;
    while ($row = $this->_app->db->fetchAssoc($res)) {
      if ($PROVIDER_PORTAL['mod_rewrite']) $url = sprintf('%s/%s/%s', dirname($AJAX['url']), $provider, $row['url_name']);
      else $url = sprintf('%s/portal.php?provider=%s&id=%s', dirname($AJAX['url']), $provider, $row['url_name']);

      $this->_providerData['portal'] .= sprintf('<tr><td>%s</td><td>%s</td><td><a target="_blank" href="%s">%s</a></td></tr>', $row['url_name'],$row['name'],$url,$url);
    }
    $this->_providerData['portal'] .= '</table>';
    
    if ($this->_app->db->getRowsNumber($res)==1) header('Location: '.$url);
    
    $template = sprintf('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title>{__label.viewProviderPortal_provider}: %s</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div id="viewProvider">
  <div class="formItem title">
    <label>{__label.viewProviderPortal_provider}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_address}:</label>
    <label class="asInput">%s, %s %s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_ic}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_dic}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_phone}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_email}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_www}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_bank}:</label>
    <label class="asInput">%s</label>
  </div>
  <div class="formItem">
    <label>{__label.viewProviderPortal_portal}:</label>
    <div style="float:left">%s</div>
  </div>
</div>
</body>
</html>', $this->_providerData['name'],
$this->_providerData['name'], $this->_providerData['street'], $this->_providerData['city'], $this->_providerData['postal_code'],
$this->_providerData['ic'],$this->_providerData['dic'],
$this->_providerData['phone_1'], $this->_providerData['email'], $this->_providerData['www'],
$this->_providerData['bank_account_number'],
$this->_providerData['portal']);
    
    if ($this->_app->getDebug()) $template .= '<hr/>'.$this->_insertMessages();
    $this->_app->messages->reset();
    
    $gui = new GuiElement(array('template'=>$template));
    echo $gui->render();
  }
  
  protected function _userInsert() {
    if ($this->_preview = $this->_app->request->getParams('preview')) {
      $this->_app->history->getBackwards(1);
    }
    
    $portal = strtolower($this->_app->request->getParams('id'));
    $provider = strtolower($this->_app->request->getParams('provider'));
    if (!$provider) die('Invalid portal!');
    
    if ($portal) $this->_insertPortal($provider, $portal);
    else $this->_insertProvider($provider);
    
    die;
  }
}

?>
