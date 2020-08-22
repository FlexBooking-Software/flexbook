<?php

// chyby XML
define('XML_FATAL_NO_XML', 1);
define('XML_FATAL_NO_REQUEST', 2);
define('XML_FATAL_NO_ACTION', 3);
define('XML_FATAL_INVALID_XML', 4);
define('XML_FATAL_UNSUPPORTED_ENCODING', 5);
define('XML_FATAL_INVALID_ACTION', 6);

define('XML_FATAL_INVALID_GPS', 101);
define('XML_FATAL_MISSING_GPS_RANGE', 102);
define('XML_FATAL_INVALID_FROM', 103);

define('XML_FATAL_MISSING_COMMODITY_TYPE', 201);
define('XML_FATAL_MISSING_COMMODITY_ID', 202);
define('XML_FATAL_INVALID_COMMODITY_TYPE', 203);
define('XML_FATAL_INVALID_COMMODITY', 204);

class ModuleXmlRequest extends ExecModule {
  private $_xmlVersion = '1.0';
  private $_xmlEncoding = 'UTF-8';
  private $_xmlHeaderBeginTag = '<?xml';
  private $_xmlHeaderEndTag = '?>';
  private $_requestLog = null;
  private $_action = null;
  private $_xml;
  private $_reqDoc;
  private $_respDoc;
  private $_respCode = 0;
  private $_respMessage = null;
  private $_respDesc;

  public function __construct() {
    global $XML_SETTINGS;
    
    parent::__construct();
  
    if (isset($XML_SETTINGS['version'])) $this->_xmlVersion = $XML_SETTINGS['version'];
    if (isset($XML_SETTINGS['encoding'])) $this->_xmlEncoding = $XML_SETTINGS['encoding'];
    if (isset($XML_SETTINGS['log'])) $this->_requestLog = $XML_SETTINGS['log'];
  }

  private function _createLogRecord($type, $content) {
    if ($this->_requestLog) {
      $handle = fopen($this->_requestLog, 'a');
      if ($handle) {
        $prefix = $type=='IN'?
          sprintf("%d: %s REQUEST FROM: %s ********************\n", getmypid(), date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']):
          sprintf("%d: %s RESPONSE TO: %s ********************\n", getmypid(), date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
        fwrite($handle, $prefix.$content."\n");
        fclose($handle);
      }
    }
  }
  
  public function createDebugRecord($content) {
    if ($this->_requestLog) {
      $handle = fopen($this->_requestLog, 'a');
      fwrite($handle, sprintf("%d: %s DEBUG FROM: %s *****************\n", getmypid(), date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']));
      fwrite($handle, sprintf("%s\n", $content));
      fclose($handle);
    }
  }
  
  public function getXMLEncoding() { return $this->_xmlEncoding; }

  public function getTSText($key) {
    return $this->convertOutput($this->_app->textStorage->getText($key));
  }

  public function convertOutput($text) {
    if ($this->_xmlEncoding == $this->_app->getCharset()) $ret = $text;
    else $ret = iconv($this->_app->getCharset(), $this->_xmlEncoding, $text);

    return $ret;
  }

  private function _getAction() {
    $requests = $this->_reqDoc->getElementsByTagName('request');
    if ($requests->length) {
      $request = $requests->item(0);
      $this->_action = $request->getAttribute('name');
      if ($this->_action) {
        $response = $this->_getResponseTag();
        $response->setAttribute('name', $this->_action);
        
        $this->createDebugRecord('Action: '.$this->_action);
      } else {
        throw new ExceptionUser(XML_FATAL_NO_ACTION);
      }
    } else { throw new ExceptionUser(XML_FATAL_NO_REQUEST); }
  }

  private function _readRequest() {    
    // beru xml bud z RAW_POST_DATA nebo z form parametru xml
    $xml = urldecode(file_get_contents('php://input'));
    if (!$xml) $xml = ifsetor($_GET['xml']);
    if (!$xml) $xml = ifsetor($_POST['xml']);

    $xml = str_replace('xml=','',$xml);

    $this->createDebugRecord('detected encoding: '.mb_detect_encoding($xml));

    // jeste pokud je jine kodovani nez utf-8 v hlavicce, tak to prekonvertim na utf8
    $headerBegin = strpos($xml, $this->_xmlHeaderBeginTag);
    $headerEnd = strpos($xml, $this->_xmlHeaderEndTag);
    if (($headerBegin!==false)&&($headerEnd!==false)) {
      $header = substr($xml, $headerBegin, $headerEnd-$headerBegin+2);
      $encodingBegin = strpos($header, 'encoding="');
      if ($encodingBegin) {
        $encodingEnd = strpos($header, '"', $encodingBegin+10);
        $encoding = strtoupper(substr($header, $encodingBegin+10, $encodingEnd-$encodingBegin-10));
        $this->createDebugRecord('encoding specified in xml: '.$encoding);
        if ($encoding!=$this->_xmlEncoding) {
          if (in_array($encoding, array('ISO-8859-1','ISO-8859-2','WINDOWS-1250'))) {
            $xml = iconv($encoding, $this->_xmlEncoding.'//IGNORE', $xml);
          } else {
            throw new ExceptionUser(XML_FATAL_UNSUPPORTED_ENCODING);
          }
        }
      }
    }
    
    if (!$this->_xml = $xml) throw new ExceptionUser(XML_FATAL_NO_XML);
    $this->_createLogRecord('IN', $this->_xml);
    
    $this->_reqDoc = new DOMDocument($this->_xmlVersion);
    if (!@$this->_reqDoc->loadHTML($this->_xml)) throw ExceptionUser(XML_FATAL_INVALID_XML);
    
    $this->_getAction();
  }

  private function _createResponse() {
    $this->_respDoc = new DOMDocument($this->_xmlVersion, $this->_xmlEncoding);
    $this->_respDoc->formatOutput = true;
    $this->_respDoc->encoding = $this->_xmlEncoding;
    $response = $this->_respDoc->createElement('response');
    $this->_respDoc->appendChild($response);
  }

  private function _getResponseTag() {
    $response = $this->_respDoc->getElementsByTagName('response');
    return $response->item(0);
  }
  
  private function _removeDataTag() {
    $data = $this->_respDoc->getElementsByTagName('data');
    if ($data->item(0)) $this->_getResponseTag()->removeChild($data->item(0));
  }

  private function _addStatus() {
    $response = $this->_getResponseTag();

    $status = $this->_respDoc->createElement('status');
    $response->appendChild($status);

    $code = $this->_respDoc->createElement('code', $this->_respCode);
    $status->appendChild($code);
    $message = $this->_respDoc->createElement('message', $this->_respMessage?$this->_respMessage:$this->getTSText('label.xml_response_'.$this->_respCode));
    $status->appendChild($message);
    if (is_array($this->_respDesc)) {
      foreach ($this->_respDesc as $d) {
        $desc = $this->_respDoc->createElement('description', $d);
        $status->appendChild($desc);
      }
    }
  }

  private function _addDebug() {
    if ($this->_app->getDebug()) {  
      $response = $this->_getResponseTag();

      $status = $this->_respDoc->createElement('debug');
      $response->appendChild($status);

      foreach ($this->_app->messages->getMessages() as $one) {
        $message = sprintf('%s:%d> %s', $one['type'], $one['level'], $this->_app->htmlspecialchars($one['message']));
        $m = $this->_respDoc->createElement('message', $this->convertOutput($message));
        $status->appendChild($m);
      }
    }
      
    $this->_app->messages->reset();
  }

  protected function _userRun() {
    try {
      $this->_createResponse();
  
      $this->_readRequest();
      //if ($this->_authenticate()) {

      switch ($this->_action) {
        case 'tag'    : $xmlAction = new XMLActionTag($this->_reqDoc, $this->_respDoc, $this); break;
        case 'search' : $xmlAction = new XMLActionSearch($this->_reqDoc, $this->_respDoc, $this); break;
        case 'detail' : $xmlAction = new XMLActionDetail($this->_reqDoc, $this->_respDoc, $this); break; 
        default: throw new ExceptionUser(XML_FATAL_INVALID_ACTION);
      }
      $this->createDebugRecord('Running class: '.get_class($xmlAction));
      list($this->_respDoc, $this->_respCode, $this->_respMessage, $this->_respDesc) = $xmlAction->getResponse();
    } catch (ExceptionUserTextStorage $e) {
      $this->_removeDataTag();
      
      $this->_respCode = -1;
      $this->_respMessage = $e->printMessage();
    } catch (ExceptionUser $e) {
      $this->_removeDataTag();
      
      $this->_respCode = $e->printMessage();
    } catch (Exception $e) {
      $this->createDebugRecord('Unexpected error: '.$this->convertOutput($e->printMessage()));
    }
    
    $this->_addStatus();
    $this->_addDebug();
    $this->_createLogRecord('OUT', $this->_respDoc->saveXML());
    header('Access-Control-Allow-Origin: *');
    header('Content-type: text/xml; charset='.$this->_xmlEncoding);
    echo $this->_respDoc->saveXML();
  }
}

?>
