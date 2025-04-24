<?php
  use CRM_AiaHelperAdvimport_ExtensionUtil as E;
  
  class CRM_AiaHelperAdvimport_Utils
  {
    public static function getTarif($id,$priceFieldValueId) {
      $priceFieldValues = \Civi\Api4\PriceFieldValue::get(FALSE)
        ->addSelect('membership_type_id.*', '*', 'custom.*', 'price_field_id.*', 'financial_type_id:name')
        ->addWhere('price_field_id.price_set_id', '=', $id)
        ->addWhere('price_field_id', '=', $priceFieldValueId)
        ->execute();
      
      return $priceFieldValues;
    }
    
    public static function getMembershipTypeId($membershipId) {
      $membershipType = \Civi\Api4\Membership::get(FALSE)
        ->addSelect('membership_type_id.id')
        ->addWhere('id', '=', $membershipId)
        ->execute();
      
      return $membershipType[0]['membership_type_id.id'];
    }
    
    public static function getMembershipDataForContact($contactId, $membershipId) {
      $membershipData = \Civi\Api4\Membership::get(FALSE)
        ->addSelect('*', 'custom.*', 'membership_type_id.*')
        ->addWhere('id', '=', $membershipId)
        ->addWhere('contact_id', '=', $contactId)
        ->execute()
        ->first();
      
      return $membershipData;
    }
    
    public static function getMoyenDePaiement($label) {
      $value = '';
      $optionValues = \Civi\Api4\OptionValue::get(FALSE)
        ->addSelect('*', 'custom.*')
        ->addWhere('option_group_id', '=', 10) // moyen de paiement
        ->addWhere('label', '=', $label)
        ->execute()
        ->first();
      
      $value = $optionValues['value'];
      return $value;
    }
  }