<?php

class ModuleResourcePoolResourceSelect extends ExecModule {
  
  protected function _userRun() {
    $validator = Validator::get('resourcePool','ResourcePoolValidator');
    $validatorData = $validator->getValues();
    
    $ids = $this->_app->request->getParams('id');
    #adump($ids);die;
    
    $center = $validatorData['centerId'];
    foreach (explode(',',$ids) as $id) {
      if (!in_array($id,array_keys($validatorData['resourceId']))) {
        $s = new SResource;
        $s->addStatement(new SqlStatementBi($s->columns['resource_id'],$id,'%s=%s'));
        $s->setColumnsMask(array('resource_id','name','center','unitprofile_name','price','pricelist_name'));
        $res = $this->_app->db->doQuery($s->toString());
        $row = $this->_app->db->fetchAssoc($res);
        
        if (!$center) $center = $row['center'];
        if ($center!=$row['center']) throw new ExceptionUserTextStorage('error.saveResourcePool_invalidCenterCombination');
        
        $validatorData['resource'][$row['resource_id']] = array('id'=>$row['resource_id'],'name'=>$row['name'],'unitprofile'=>$row['unitprofile_name'],'pricelist'=>$row['pricelist_name'],
                                                                'price' => sprintf('%s %s', $this->_app->regionalSettings->convertNumberToHuman($row['price'],2), $this->_app->textStorage->getText('label.currency_CZK')));
        $validatorData['resourceId'][] = $row['resource_id'];
      }
    }
    
    $vData = array('centerId'=>$center,'resource'=>$validatorData['resource'],'resourceId'=>$validatorData['resourceId']);
    $validator->setValues($vData);

    return 'eBack';
  }
}

?>
