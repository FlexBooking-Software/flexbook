<?php

ini_set('memory_limit', '1000M');

require dirname(__FILE__).'/../init.php';

class FlexbookRobot extends Flexbook {

  protected function _initModules($params=array()) {
    $modules = array(
      'eResourceGenerateAvailability'     => dirname(__FILE__).'/eResourceGenerateAvailability.php',
      'eEventRotate'                      => dirname(__FILE__).'/eEventRotate.php',
      'eReservationRotate'                => dirname(__FILE__).'/eReservationRotate.php',
      'eReservationCancelNotPayed'        => dirname(__FILE__).'/eReservationCancelNotPayed.php',
      'eNotificationSend'                 => dirname(__FILE__).'/eNotificationSend.php',
      'eOnlinePaymentFinish'              => dirname(__FILE__).'/eOnlinePaymentFinish.php',
      'eInvoiceCreate'                    => dirname(__FILE__).'/eInvoiceCreate.php',
      'eUserReceiptGenerate'              => dirname(__FILE__).'/eUserReceiptGenerate.php',
      'eUserInvoiceGenerate'              => dirname(__FILE__).'/eUserInvoiceGenerate.php',
    );

    Application::_initModules($modules);
  }

  protected function _createAuth($params) {
    $this->auth = new FakeAuth($params);
  }

  protected function _testAction($action) { return $action; }
}

?>
