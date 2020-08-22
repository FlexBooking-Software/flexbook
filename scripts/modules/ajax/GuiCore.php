<?php

class AjaxGuiCore extends AjaxGuiAction {

  protected function _userRun() {
    $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
   
    $this->_result['output'] = sprintf('
          <div id="flb_core">
            <input type="hidden" id="flb_core_provider" value="%s" />
            <input type="hidden" id="flb_core_userid" value="" />
            <input type="hidden" id="flb_core_username" value="" />
            <input type="hidden" id="flb_core_useremail" value="" />
            <input type="hidden" id="flb_core_profile_incomplete" value="0" />
            <input type="hidden" id="flb_core_sessionid" value="" />
            <input type="hidden" id="flb_core_url" value="%s/ajax.php?" />
            <input type="hidden" id="flb_core_url_path" value="%s/" />
            <input type="hidden" id="flb_core_not_logged_use_externalaccount" value="%s" />
            <div id="flb_core_not_logged" class="flb_profile_extra"></div>
          </div>',
          $this->_params['provider'], $url, $url, ifsetor($this->_params['externalAccount'],1));
  }
}

?>
