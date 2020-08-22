<?php

class ONotificationFile extends SqlObject {
  protected $_table = 'notification_file';
  protected $_identity = false;

	protected function _postDelete($ret=true) {
		$data = $this->getData();

		$o = new OFile($data['file']);
		$o->delete();

		return parent::_postDelete($ret);
	}
}

?>