<?php
use CRM_AiaHelperAdvimport_ExtensionUtil as E;

class CRM_AiaHelperAdvimport_Page_DebugPage extends CRM_Core_Page {
// url de la page de debug : https://fresque-num.pec.symbiodev.xyz/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fdebug-page
  public function run() {
    $membership = \Civi\Api4\Membership::get(FALSE)
      ->addSelect('*', 'custom.*', 'membership_type.*', 'financial_type.*','price_set.*')
      ->addJoin('MembershipType AS membership_type', 'LEFT', ['membership_type_id', '=', 'membership_type.id'])
      ->addJoin('PriceSet AS price_set', 'LEFT', ['price_set.financial_type_id', '=', 'membership_type.financial_type_id'])
      ->addWhere('id', '=', 5)
      ->addWhere('contact_id', '=', 5)
      ->addWhere('price_set.id', '=', 17)
      ->execute();

    echo '<pre>';
    var_dump($membership);
    echo '</pre>';
    
    parent::run();
  }

}
