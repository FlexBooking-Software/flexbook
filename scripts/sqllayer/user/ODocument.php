<?php

class ODocument extends SqlObject {
  protected $_table = 'document';

	protected function _postDelete($ret=true) {
		$data = $this->getData();

		$o = new OFile($data['content']);
		$o->delete();

		return parent::_postDelete($ret);
	}
}

?>