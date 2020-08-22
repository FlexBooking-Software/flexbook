<?php

class ModuleProviderPortalPageEdit extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator');
    if ($page = $this->_app->request->getParams('page')) {
      $s = new SProviderPortalPage;
      $s->addStatement(new SqlStatementbi($s->columns['providerportal'], $validator->getVarValue('id'), '%s=%s'));
      $s->addStatement(new SqlStatementbi($s->columns['providerportalpage_id'], $page, '%s=%s'));
      $s->setColumnsMask(array('from_template','short_name','name','content'));
      $res = $this->_app->db->doQuery($s->toString());
      if (!$row = $this->_app->db->fetchAssoc($res)) throw new ExcpetionUserTextStorage('error.editProviderPortalPage_invalidPage');
      
      $validator->setValues(array('pageId'=>$page,'pageFromTemplate'=>$row['from_template'],'pageShortName'=>$row['short_name'],'pageName'=>$row['name'],'pageContent'=>$row['content']));
    }

    return 'vProviderPortalPageEdit';
  }
}

?>
