<?php
    use CRM_AiaHelperAdvimport_ExtensionUtil as E;
    
    class CRM_AiaHelperAdvimport_Advimport_AddContributionToMembership extends CRM_Advimport_Helper_Csv {
        /**
         * Returns a human-readable name for this helper.
         */
        public function getHelperLabel() {
            return E::ts("Ajout de contribution sur des adhésions existantes");
        }
    
        /**
         * Available fields.
         */
        public function getMapping(&$form) {
            return [
              'contact_id' => [
                'label' => E::ts('Id. de contact'),
                'field' => 'contact_id',
              ],
              'membership_id' => [
                'label' => E::ts('Id. d’adhésion'),
                'field' => 'membership_id',
              ],
              'total_amount' => [
                'label' => E::ts('Montant total'),
                'field' => 'total_amount',
              ],
              'price_set_id' => [
                'label' => E::ts('Ensemble tarification'),
                'field' => 'price_set_id',
              ],
              'financial_type_id' => [
                'label' => E::ts('Type d\'opération comptable'),
                'field' => 'financial_type_id',
              ],
              'contribution_status_id' => [
                'label' => E::ts('Statut de la contribution'),
                'field' => 'contribution_status_id',
              ],
            ];
        }
    
        /**
         * Import an item gotten from the queue.
         */
        function processItem($params) {
          $contact_id = null;
          $now = date('Y-m-d H:i:s');
          $membershipData = CRM_AiaHelperAdvimport_Utils::getDataMembership($params['membership_id'], $params['contact_id'], $params['price_set_id']);
          $tarif = CRM_AiaHelperAdvimport_Utils::getTarif($params['price_set_id'],$params['total_amount']);
          
          if (!empty($params['contact_id'])) {
            $contact_id = $params['contact_id'];
            $contact = \Civi\Api4\Contact::get(FALSE)
              ->addWhere('id', '=', $contact_id)
              ->execute();
            
            if ($contact->count() == 0) {
              throw new Exception('Contact not found');
            }
            
          }
          
          // Mandatory fields
          /*$mandatory_fields = ['start_date', 'total_amount'];
          foreach ($mandatory_fields as $f) {
            if (empty($params[$f])) {
              throw new Exception('Missing: ' . $f);
            }
          }*/
          
          // https://fresque-num.pec.symbiodev.xyz/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fapi4#/explorer/PriceFieldValue/get?select=%5B%22price_field_id.*%22,%22*%22,%22custom.*%22,%22membership_type_id.*%22%5D&where=%5B%5B%22price_field_id.price_set_id%22,%22%3D%22,%2217%22%5D%5D
          // https://fresque-num.pec.symbiodev.xyz/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fapi4#/explorer/Membership/get?where=%5B%5B%22id%22,%22%3D%22,%225%22%5D,%5B%22contact_id%22,%22%3D%22,%225%22%5D%5D&checkPermissions=0&limit=0&select=%5B%22*%22,%22custom.*%22,%22membership_type.*%22,%22financial_type.*%22%5D&join=%5B%5B%22MembershipType%20AS%20membership_type%22,%22LEFT%22,null,%5B%22membership_type_id%22,%22%3D%22,%22membership_type.id%22%5D%5D,%5B%22PriceSet%20AS%20price_set%22,%22LEFT%22,null,%5B%22price_set.financial_type_id%22,%22%3D%22,%22membership_type.financial_type_id%22%5D%5D%5D
          try {
            $params = [
              'contact_id' => $contact_id,
              'receive_date' => $now,
              'financial_type_id' => 'Cotisation des membres',
              'contribution_status_id' => 'Pending',
              'total_amount' => $params['total_amount'],
              //'check_number' => ,
              'is_pay_later' => false,
              'payment_instrument_id' => 1,
              'source' => 'Adhesion',
              'line_items' => [
                '0' => [
                  'line_item' => [
                    '0' => [
                      'price_field_id' => $tarif[0]['price_field_id.price_set_id'],
                      'price_field_value_id' => $tarif[0]['price_field_id.id'],
                      'label' => $tarif[0]['price_field_id.label'],
                      'field_title' => $tarif[0]['price_field_id.label'],
                      'qty' => 1,
                      'unit_price' => $tarif[0]['amount'],
                      'line_total' => $tarif[0]['amount'],
                      'financial_type_id' => 'Cotisation des membres',
                      'entity_table' => 'civicrm_membership',
                      'membership_num_terms' => 1,
                      'non_deductible_amount' => 0,
                    ],
                  ],
                  'params' => [
                    'id' => $params['membership_id'],
                    'status_id' => 'Pending',
                    'source' => 'Adhesion',
                    'is_override' => 0,
                    //'end_date' => $endDate,
                    'status_override_end_date' => null,
                    'membership_type_id' => $tarif[0]['membership_type_id.id'],
                    'contact_id' => $contact_id,
                  ],
                ],
              ],
            ];
            Civi::log()->debug('--- $params : ' . print_r($params,1));
            $result = civicrm_api3('Order', 'create', $params);
            $contribution_id = $result['id'];
            Civi::log()->debug('--- $contribution_id order api : ' . print_r($contribution_id,1));
          }
          catch (Exception $e) {
            throw new Exception('Failed with order API: ' . $e->getMessage());
          }
          
        }
    }