<?php
use CRM_AiaHelperAdvimport_ExtensionUtil as E;

class CRM_AiaHelperAdvimport_Page_DebugPage extends CRM_Core_Page {
// url de la page de debug : https://fresque-num.pec.symbiodev.xyz/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fdebug-page
  public function run() {
    $contributions = \Civi\Api4\Contribution::get(FALSE)
      ->addSelect('custom.*', '*')
      ->addWhere('id', '=', 434333)
      ->execute()
      ->first();

    echo '<pre>';
    var_dump($contributions);
    echo '</pre>';
    
    parent::run();
  }

}
