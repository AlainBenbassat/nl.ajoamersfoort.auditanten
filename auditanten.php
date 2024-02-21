<?php

require_once 'auditanten.civix.php';
// phpcs:disable
use CRM_Auditanten_ExtensionUtil as E;
// phpcs:enable

function auditanten_civicrm_summaryActions(&$actions, $contactID) {
  // remove actions AJO does not use
  $unusedActions = ['activity', 'view', 'add', 'delete', 'participant', 'contribution', 'rel', 'note', 'group', 'tag', 'membership'];
  foreach ($unusedActions as $unusedAction) {
    unset($actions[$unusedAction]);
  }
  unset($actions['otherActions']['print']);
  unset($actions['otherActions']['dashboard']);
  unset($actions['otherActions']['vcard']);

  if (CRM_Auditanten_Contact::isAuditioner($contactID)) {
    // add menu
    $actions['otherActions']['auditant_toelaten'] = [
      'title' => 'Auditant toelaten?',
      'weight' => 60,
      'ref' => 'auditant_toelaten',
      'key' => 'auditant_toelaten',
      'href' => CRM_Utils_System::url('civicrm/auditant-toelaten', 'reset=1&cid=' . $contactID),
    ];
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function auditanten_civicrm_config(&$config): void {
  _auditanten_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function auditanten_civicrm_install(): void {
  _auditanten_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function auditanten_civicrm_enable(): void {
  _auditanten_civix_civicrm_enable();
}
