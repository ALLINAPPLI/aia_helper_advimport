<?php
    use CRM_AiaHelperAdvimport_ExtensionUtil as E;
    
    class CRM_AiaHelperAdvimport_Advimport_AddContributionToMembership extends CRM_Advimport_Helper_Csv {
        /**
         * Returns a human-readable name for this helper.
         */
        public function getHelperLabel() {
            return E::ts("Ajout de contribution sur des adhésions existantes - ASPAS");
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
              'financial_type_id' => [
                'label' => E::ts('Type d\'opération comptable'),
                'field' => 'financial_type_id',
              ],
              'contribution_status_id' => [
                'label' => E::ts('Statut de la contribution'),
                'field' => 'contribution_status_id',
              ],
              'price_set_id' => [
                'label' => E::ts('Price_set_id'),
                'field' => 'price_set_id',
              ],
              'price_field_value_id' => [
                'label' => E::ts('Price_field_value'),
                'field' => 'price_field_value_id',
              ],
              'end_date' => [
                'label' => E::ts('date de fin'),
                'field' => 'end_date',
              ],
            ];
        }
    
        /**
         * Import an item gotten from the queue.
         */
        function processItem($params) {
          $contact_id = null;
          $now = date('Y-m-d H:i:s');
          $contribution_id = NULL;
          $endDate = NULL;
          $membershipData = CRM_AiaHelperAdvimport_Utils::getMembershipDataForContact($params['contact_id'],$params['membership_id']);
          
          // calcul de la date de fin de la nouvelle adhésion en prenant la date de fin de l'adhésion existante du contact en ajoutant + 1 année
          // $endDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($membershipData['end_date'])) . " + " . $membershipData['membership_type_id.duration_interval'] . " " . $membershipData['membership_type_id.duration_unit']));
          
          // récupération du tarif selon le price_set_id et l'identifiant du champ tarif
          $tarif = CRM_AiaHelperAdvimport_Utils::getTarif($params['price_set_id'],$params['price_field_value_id']);
          
          // récupération du membership_type_id de l'adhésion
          $membershipTypeId = CRM_AiaHelperAdvimport_Utils::getMembershipTypeId($params['membership_id']);
          
          // contrôle si le membership_type_id est null
          if(empty($membershipTypeId)) {
            $message = 'Adhésion avec membership_type_id vide ou null : numéro de l\'adhésion : ' . $params['membership_id'] . ' -- ' . $params['contact_id'];
            CRM_Advimport_Utils::logImportWarning($params, $message);
          }
          
          if(empty($params['end_date'])) {
            $message = 'Date de fin obligatoire';
            CRM_Advimport_Utils::logImportWarning($params, $message);
          } else {
            $endDate = $params['end_date'];
          }
          
          // contrôle si l'identifiant de contact est présent en base
          // on retourne une erreur
          if (!empty($params['contact_id'])) {
            $contact_id = $params['contact_id'];
            $contact = \Civi\Api4\Contact::get(FALSE)
              ->addWhere('id', '=', $contact_id)
              ->execute();
            
            if ($contact->count() == 0) {
              throw new Exception('Contact not found');
            }
            
          }
          
          // requête API 4
          // https://civicrm.aspas-nature.org/civicrm/api4#/explorer/Membership/get?select=%5B%22membership_type_id.id%22%5D&where=%5B%5B%22id%22,%22%3D%22,%224803%22%5D%5D&limit=0&checkPermissions=0&debug=0
          // https://civicrm.aspas-nature.org/civicrm/api4#/explorer/PriceFieldValue/get?select=%5B%22membership_type_id.*%22,%22*%22,%22custom.*%22,%22price_field_id.*%22,%22financial_type_id:name%22%5D&limit=0&where=%5B%5B%22price_field_id.price_set_id%22,%22%3D%22,%2221%22%5D,%5B%22price_field_id%22,%22%3D%22,%2258%22%5D%5D
          
          // add contribution
          try {
            $params = [
              'contact_id' => $contact_id,
              'receive_date' => $now,
              'financial_type_id' => $tarif[0]['financial_type_id:name'],
              'contribution_status_id' => 'Pending',
              'total_amount' => $params['total_amount'],
              //'check_number' => ,
              'is_pay_later' => false,
              'payment_instrument_id' => 4, // chèque
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
                      'unit_price' => $params['total_amount'],
                      'line_total' => $params['total_amount'],
                      'financial_type_id' => $tarif[0]['financial_type_id:name'],
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
                    'end_date' => $endDate,
                    'status_override_end_date' => null,
                    'membership_type_id' => $membershipTypeId,
                    'contact_id' => $contact_id,
                  ],
                ],
              ],
            ];
            
            $result = civicrm_api3('Order', 'create', $params);
            $contribution_id = $result['id'];
            
          }
          catch (Exception $e) {
            throw new Exception('Failed with order API: ' . $e->getMessage());
          }
          
          // create payment
          try {
            $payment = civicrm_api3('Payment', 'create', [
              'contribution_id' => $contribution_id,
              'total_amount' => $params['total_amount'],
              'trxn_date' => $now,
              // 'check_number' => $checkNumber
            ]);
          } catch (Exception $e) {
            throw new Exception('Failed to create the contribution: ' . $e->getMessage());
          }
          
        }
    }