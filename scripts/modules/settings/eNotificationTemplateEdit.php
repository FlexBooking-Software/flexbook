<?php

class ModuleNotificationTemplateEdit extends ExecModule {

  protected function _userRun() {
    $id = $this->_app->request->getParams('id');
    $validator = Validator::get('notificationTemplate','NotificationTemplateValidator',true);
    if ($id) $validator->loadData($id);

    return 'vNotificationTemplateEdit';
  }
}

?>
