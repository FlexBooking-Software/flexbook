<?php

class ModuleInPage extends InPageModule {

  protected function _userInsert() {
    $validator = Validator::get('inpage', 'InPageValidator');
    if ($validator->getVarValue('providerId')=='14') {
      $this->insert(new GuiCzechTourismMain);
    } else {
      $this->insert(new GuiInPageMain); 
    }
  }
  
  protected function _insertDescription() {
    $this->insertTemplateVar('description', 'Vážení partneři,
<br/>vítejte v registračním systému na Travel Trade Day agentury CzechTourism!
<ul><li>Pro možnost rezervací je potřeba se zaregistravat a vyplnit všechny požadované informace.</li>
<li>Vyberte si akci, na kterou se chcete registrovat a proveďte rezervaci.</li>
<li>Seznam svých rezervací si může každý přihlášený uživatel vypsat a v případě potřeby i zrušit.</li>
<li>V den konání se prosím dostavte k prezenci 15 min před zahájením příslušné akce.</li>
<li>U zpoplatněných akcí Vám systém vygeneruje závaznou objednávku, na jejím základě Vám agentura vystaví fakturu. Úhradu faktury prosím proveďte neprodleně.</li>
<li>V případě nejasností nás kontaktujte na: ozz@czechtourism.cz, tel: 221 580 618.</li>
</ul>
Těšíme se na setkání s Vámi!', false);
  }
}

?>
