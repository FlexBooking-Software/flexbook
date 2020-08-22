<?php

class ONotification extends SqlObject {
  protected $_table = 'notification';

	protected function _preDelete($ret=true) {
		$data = $this->getData();

		$s = new SNotificationFile;
		$s->addStatement(new SqlStatementBi($s->columns['notification'], $data['notification_id'], '%s=%s'));
		$s->setColumnsMask(array('notification','file'));
		$ds = new SqlDataSource(new DataSourceSettings, $s);
		$ds->reset();
		while ($ds->currentData) {
			$o = new ONotificationFile(array('notification'=>$ds->currentData['notification'],'file'=>$ds->currentData['file']));
			$o->delete();
			$ds->nextData();
		}

		return parent::_preDelete($ret);
	}
}

?>