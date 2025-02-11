<?php
  use CRM_AiaHelperAdvimport_ExtensionUtil as E;
  
  class CRM_AiaHelperAdvimport_Utils
  {
    public static function getDataMembership($idMembershipId, $contactId, $priceSetId) {
      $membership = \Civi\Api4\Membership::get(FALSE)
        ->addSelect('*', 'custom.*', 'membership_type.*', 'financial_type.*','price_set.*')
        ->addJoin('MembershipType AS membership_type', 'LEFT', ['membership_type_id', '=', 'membership_type.id'])
        ->addJoin('PriceSet AS price_set', 'LEFT', ['price_set.financial_type_id', '=', 'membership_type.financial_type_id'])
        ->addWhere('id', '=', $idMembershipId)
        ->addWhere('contact_id', '=', $contactId)
        ->addWhere('price_set.id', '=', $priceSetId)
        ->execute();
      
      return $membership;
    }
    
    public static function getTarif($id,$totalAmount) {
      $priceFieldValues = \Civi\Api4\PriceFieldValue::get(FALSE)
        ->addSelect('price_field_id.*', '*', 'custom.*', 'membership_type_id.*')
        ->addWhere('price_field_id.price_set_id', '=', $id)
        ->addWhere('amount', '=', $totalAmount)
        ->execute();
      Civi::log()->debug('--- getTarif $priceFieldValues : ' . print_r($priceFieldValues,1));
      return $priceFieldValues;
    }
  }