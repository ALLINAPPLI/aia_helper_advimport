<?php

require_once 'aia_helper_advimport.civix.php';

use CRM_AiaHelperAdvimport_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function aia_helper_advimport_civicrm_config(&$config): void {
  _aia_helper_advimport_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function aia_helper_advimport_civicrm_install(): void {
  _aia_helper_advimport_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function aia_helper_advimport_civicrm_enable(): void {
  _aia_helper_advimport_civix_civicrm_enable();
}
  
  /**
   * Implements hook_civicrm_advimport_helpers()
   */
  function aia_helper_advimport_civicrm_advimport_helpers(&$helpers)
  {
    $helpers[] = [
      'class' => 'CRM_AiaHelperAdvimport_Advimport_AddContributionToMembership',
      'label' => E::ts('Ajout de contribution sur des adhésions existantes'),
    ];
  }