<?php

class ModuleProviderPortalPrepare extends ExecModule {

  protected function _userRun() {
    $validator = Validator::get('providerPortal','ProviderPortalValidator',true);

    return 'vProviderPortalPrepare';
  }
}

?>
