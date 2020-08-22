<?php

class BProviderPortal extends BusinessObject {

  public function getLoaded() { return $this->_loaded; }

  private function _checkAccess() {
    $ret = false;
    $this->_load();
 
    while (true) {
      if ($user=$this->_app->auth->isUser()) {
        // normalni uzivatel nema pravo delat cokoliv s portalem
        break;
      } elseif ($this->_app->auth->isProvider()) {
        // kdyz je to provider a ma pravo pracovat se svyma portalama
        $allowedProvider = $this->_app->auth->getAllowedProvider('commodity_admin','array');
        if ($this->_id&&!in_array($this->_data['providerId'], $allowedProvider)) break;
        if (isset($params['providerId'])&&!in_array($params['providerId'], $allowedProvider)) break;
      } elseif ($this->_app->auth->haveRight('commodity_admin')) {
        // kdyz je to admin a ma pravo ukladat zdroje
      } else {
        break;
      }
      
      $ret = true;
      break;
    }
    
    return $ret;
  }
  
  private function _checkBeforeSave($params) {
    if (!$this->_id) {
      if (!isset($params['providerId'])) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyProviderId');
      if (!isset($params['name'])) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyName');
      if (!isset($params['urlName'])) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyUrlName');
      if (!isset($params['fromTemplate'])) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyFromTemplate');
    }
    
    // tyto nesmi byt prazdny nikdy
    if (isset($params['providerId'])&&!$params['providerId']) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyProviderId');
    if (isset($params['name'])&&!$params['name']) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyName');
    if (isset($params['urlName'])&&!$params['urlName']) throw new ExceptionUserTextStorage('error.saveProviderPortal_emptyUrlName');
  }
  
  private function _checkBeforePageSave($params) {
    $s = new SProviderPortalPage;
    if (isset($params['id'])&&$params['id']) $s->addStatement(new SqlStatementBi($s->columns['providerportalpage_id'], $params['id'], '%s<>%s'));
    $s->addStatement(new SqlStatementBi($s->columns['short_name'], $params['shortName'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_id, '%s=%s'));
    $s->setColumnsMask(array('providerportalpage_id'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($this->_app->db->getRowsNumber($res))
      throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.saveProviderPortalPage_notUnique'), $params['shortName'], '%s=%s'));
  }

  protected function _load() {
    if ($this->_id&&!$this->_loaded) {
      $oProviderPortal = new OProviderPortal($this->_id);
      $data = $oProviderPortal->getData();
      $returnData['id'] = $data['providerportal_id'];
      $returnData['providerId'] = $data['provider'];
      $returnData['fromTemplate'] = $data['from_template'];
      $returnData['active'] = $data['active'];
      $returnData['name'] = $data['name'];
      $returnData['urlName'] = $data['url_name'];
      $returnData['css'] = $data['css'];
      $returnData['javascript'] = $data['javascript'];
      $returnData['content'] = $data['content'];
      $returnData['homePage'] = $data['home_page'];
      
      $s = new SProviderPortalPage;
      $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('providerportalpage_id','name','short_name','from_template','content'));
      $res = $this->_app->db->doQuery($s->toString());
      $returnData['page'] = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['page'][$row['providerportalpage_id']] = array(
                          'id'            => $row['providerportalpage_id'],
                          'name'          => $row['name'],
                          'shortName'     => $row['short_name'],
                          'fromTemplate'  => $row['from_template'],
                          'content'       => $row['content']);
      }
      
      $s = new SProviderPortalMenu;
      $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['sequence_code']));
      $s->setColumnsMask(array('providerportalmenu_id','name','providerportalpage'));
      $res = $this->_app->db->doQuery($s->toString());
      $returnData['menu'] = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['menu'][$row['providerportalmenu_id']] = array(
                          'id'    => $row['providerportalmenu_id'],
                          'name'  => $row['name'],
                          'page'  => $row['providerportalpage']);
      }
      
      $s = new SProviderPortalFile;
      $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_id, '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['name']));
      $s->setColumnsMask(array('providerportalfile_id','name','type','size','file'));
      $res = $this->_app->db->doQuery($s->toString());
      $returnData['file'] = array();
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $returnData['file'][$row['providerportalfile_id']] = array(
                          'id'          => $row['providerportalfile_id'],
                          'name'        => $row['name'],
                          'type'        => $row['type'],
                          'size'        => $row['size'],
                          'sourceId'    => $row['file']);
      }
      
      $this->_data = $returnData;
      
      $this->_loaded = true;
    }
  }

  public function getData() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');

    $this->_load();

    return $this->_data;
  }
  
  public function savePage($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_app->db->beginTransaction();
    
    foreach ($params as $page) {
      $this->_checkBeforePageSave($page);
      
      $o = new OProviderPortalPage(isset($page['id'])&&$page['id']?$page['id']:null);
      $oData = array(
            'providerportal'    => $this->_id,
            'name'              => $page['name'],
            'short_name'        => $page['shortName'],
            'content'           => $page['content'],
            );
      if (isset($page['fromTemplate'])) $oData['from_template'] = $page['fromTemplate'];
      $o->setData($oData);
      $o->save();
    }
    
    $this->_app->db->commitTransaction();
    
    $this->_loaded = false;
  }
  
  private function _saveMenu($params) {
    if (isset($params['menu'])) {
      $idToSave = array();
      
      $count = 0;
      foreach ($params['menu'] as $menu) {
        $o = new OProviderPortalMenu(isset($menu['id'])&&$menu['id']?$menu['id']:null);
        $o->setData(array(
                'providerportal'      => $this->_id,
                'name'                => $menu['name'],
                'providerportalpage'  => $menu['page'],
                'sequence_code'       => 'ITEM_'.sprintf('%03d',++$count),
                ));
        $o->save();
        
        $idToSave[] = $o->getId();
      }
      
      $s = new SProviderPortalMenu;
      $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_id, '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['providerportalmenu_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('providerportalmenu_id'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OProviderPortalMenu($row['providerportalmenu_id']);
        $o->delete();
      }
    }
  }
  
  private function _saveFile($params) {
    if (isset($params['file'])) {
      $idToSave = array();
      
      foreach ($params['file'] as $file) {
        $oData = array(
                'providerportal'      => $this->_id,
                'name'                => $file['name'],
                'type'                => $file['type'],  
            );
        
        // ulozim soubor
        if (isset($file['newSource'])&&$file['newSource']) {
          $fileParams = array(
                    'name'    => $file['name'],
                    'file'    => $file['newSource']);
          $bF = new BFile(ifsetor($file['sourceId']));
          
          $oData['file'] = $bF->save($fileParams);
          $oData['size'] = $bF->getSize($file);
        }
        
        $o = new OProviderPortalFile(isset($file['id'])&&$file['id']?$file['id']:null);
        $o->setData($oData);
        $o->save();
        
        $idToSave[] = $o->getId();
      }
      
      $s = new SProviderPortalFile;
      $s->addStatement(new SqlStatementBi($s->columns['providerportal'], $this->_id, '%s=%s'));
      if (count($idToSave)) $s->addStatement(new SqlStatementMono($s->columns['providerportalfile_id'], sprintf('%%s NOT IN (%s)', implode(',',$idToSave))));
      $s->setColumnsMask(array('providerportalfile_id','file'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $o = new OProviderPortalFile($row['providerportalfile_id']);
        $o->delete();
        
        if ($row['file']) {
          $bF = new BFile($row['file']);
          $bF->delete();
        }
      }
    }
  }
  
  private function _save($params) {
    $this->_load();
    
    $this->_app->db->beginTransaction();

    $o = new OProviderPortal($this->_id?$this->_id:null);
    $oData = array();
    if (isset($params['providerId'])) $oData['provider'] = $params['providerId'];
    if (isset($params['fromTemplate'])) $oData['from_template'] = $params['fromTemplate'];
    if (isset($params['name'])) $oData['name'] = trim($params['name']);
    if (isset($params['urlName'])) $oData['url_name'] = trim($params['urlName']);
    if (isset($params['active'])) $oData['active'] = $params['active'];
    if (isset($params['homePage'])) $oData['home_page'] = $params['homePage']?$params['homePage']:null;
    if (isset($params['css'])) $oData['css'] = trim($params['css']);
    if (isset($params['javascript'])) $oData['javascript'] = trim($params['javascript']);
    if (isset($params['content'])) $oData['content'] = trim($params['content']);
    
    if (count($oData)) {
      $o->setData($oData);
      $o->save();
    }
    $this->_id = $o->getId();
    
    $this->_saveMenu($params);
    $this->_saveFile($params);
    
    $this->_app->db->commitTransaction();
    
    $this->_loaded = false;
  }

  public function save($params) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_checkBeforeSave($params);
    
    $this->_save($params);
  }
  
  private function _delete() {
    $this->_app->db->beginTransaction();
    
    $o = new OProviderPortal($this->_id);
    $o->delete();
    
    $this->_app->db->commitTransaction();
  }
  
  public function delete() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
  
    $this->_load();
    
    $ret = $this->_data['name'];
  
    $this->_delete();
    
    return $ret;
  }
  
  public function copy() {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $this->_load();
    
    $ret = $this->_data['name'];
    
    $this->_app->db->beginTransaction();
    
    $this->_data['name'] .= ' (kopie)';
    $this->_data['urlName'] .= '_copy';
    
    // menu se muze ulozit az po ulozeni stranek
    // do nazvu stranky si na chvili ulozim puvodni ID
    $newMenu = $this->_data['menu'];
    $this->_data['menu'] = array();
    foreach ($this->_data['page'] as $index=>$page) {
      $this->_data['page'][$index]['name'] = $this->_data['page'][$index]['id'].'_'.$this->_data['page'][$index]['name'];
      unset($this->_data['page'][$index]['id']);
    }
    
    $newProviderPortal = new BProviderPortal;
    $newProviderPortal->save($this->_data);
    $newProviderPortal->savePage($this->_data['page']);
    
    $this->_data = $newProviderPortal->getData();
    // upravim odkazy na stranky v menu
    foreach ($newMenu as $i=>$menu) {
      unset($newMenu[$i]['id']);
      
      foreach ($this->_data['page'] as $j=>$page) {
        $id = null;
        if (strpos($page['name'],'_')!==false) list($id,$name) = explode('_', $page['name']);
        if ($id==$menu['page']) {
          $newMenu[$i]['page'] = $page['id'];
          $this->_data['page'][$j]['name'] = $name;
          break;
        }
      }
    }
    $newProviderPortal->savePage($this->_data['page']);
    $newProviderPortal->_saveMenu(array('menu'=>$newMenu));
    
    $this->_app->db->commitTransaction();
    
    return $ret;
  }
  
  public function create($params) {
    $this->_checkBeforeSave($params);
    
    $this->_app->db->beginTransaction();
    
    $params['active'] = 'N';
    
    $b = new BPortalTemplate($params['fromTemplate']);
    $templateData = $b->getData();
    
    $params['css'] = $templateData['css'];
    $params['content'] = $templateData['content'];

    global $AJAX;

    $newPage = array();
    $newMenu = array();
    foreach ($templateData['page'] as $page) {
      $s = new SPortalTemplatePageTemplate;
      $s->addStatement(new SqlStatementBi($s->columns['portaltemplate'], $params['fromTemplate'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['pagetemplate'], $page, '%s=%s'));
      $s->setColumnsMask(array('name','menu_sequence_code','content'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      
      $newMenu[] = array('name'=>$row['name'],'sequence_code'=>$row['menu_sequence_code'],'pageTemplate'=>$page);
      
      $newPage[] = array('fromTemplate'=>$page,'name'=>$row['name'],'shortName'=>'PAGE_'.(count($newPage)+1), 'content'=>str_replace('@@NODE_URL', $AJAX['url'], $row['content']));
    }
    
    $this->save($params);
    
    // nejdriv musim ulozit stranky, pak menu
    $this->savePage($newPage);
    
    // upravim odkazy v menu
    $this->_load();
    foreach ($newMenu as $id=>$menu) {
      foreach ($this->_data['page'] as $page) {
        if ($page['fromTemplate']==$menu['pageTemplate']) {
          $newMenu[$id]['page'] = $page['id'];
          break;
        }
      }
    }
    $this->_saveMenu(array('menu'=>$newMenu));
    
    $this->_app->db->commitTransaction();
  }
  
  public function createPage($data) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $data['content'] = '';
    if ($data['fromTemplate']) {
      $s = new SPageTemplate;
      $s->addStatement(new SqlStatementBi($s->columns['pagetemplate_id'], $data['fromTemplate'], '%s=%s'));
      $s->setColumnsMask(array('content'));
      $res = $this->_app->db->doQuery($s->toString());
      if ($row = $this->_app->db->fetchAssoc($res)) $data['content'] = $row['content'];
    } else $data['fromTemplate'] = null;
    
    $this->savePage(array($data));
  }
  
  public function copyPage($page) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $s = new SProviderPortalPage;
    $s->addStatement(new SqlStatementBi($s->columns['providerportalpage_id'], $page, '%s=%s'));
    $s->setColumnsMask(array('from_template','name','content'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUserTextStorage('error.providerPortalCopyPage_invalidPage');
    
    $ret = $row['name'];
    
    $this->_app->db->beginTransaction();
    
    $row['name'] .= ' (kopie)';
    
    $o = new OProviderPortalPage;
    $oData = array(
        'providerportal'    => $this->_id,
        'from_template'     => $row['from_template'],
        'name'              => $row['name'],
        'content'           => $row['content'],
        );
    $o->setData($oData);
    $o->save();
    
    $this->_app->db->commitTransaction();
    
    return $ret;
  }
  
  public function deletePage($page) {
    if (!$this->_checkAccess()) throw new ExceptionUserTextStorage('error.accessDenied');
    
    $s = new SProviderPortalMenu;
    $s->addStatement(new SqlStatementBi($s->columns['providerportalpage'], $page, '%s=%s'));
    $s->setColumnsMask(array('providerportalmenu_id', 'name'));
    $res = $this->_app->db->doQuery($s->toString());
    if ($row = $this->_app->db->fetchAssoc($res)) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.providerPortalDeletePage_menuExists'), $row['name']));
    
    $o = new OProviderPortal($this->_id);
    $oData = $o->getData();
    if ($oData['home_page']==$page) throw new ExceptionUser(sprintf($this->_app->textStorage->getText('error.providerPortalDeletePage_homePageExists'), $row['name']));
    
    $ret = $row['name'];
    
    $this->_app->db->beginTransaction();
    
    $o = new OProviderPortalPage($page);
    $o->delete();
    
    $this->_app->db->commitTransaction();
    
    return $ret;
  }
}

?>
