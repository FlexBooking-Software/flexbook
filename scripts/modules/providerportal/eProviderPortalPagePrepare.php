<?php

class ModuleProviderPortalPagePrepare extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator');
    $validator->setValues(array('pageId'=>null,'pageShortName'=>null,'pageName'=>null,'pageFromTemplate'=>null,'pageContent'=>null));

    return 'vProviderPortalPagePrepare';
  }
}

?>
