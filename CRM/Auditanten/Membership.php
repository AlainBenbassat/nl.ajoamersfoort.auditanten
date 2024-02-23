<?php

class CRM_Auditanten_Membership {
  private const ORKESTLIDMAATSCHAP = 8;
  private const CURRENT_MEMBER = 2;

  public static function add($contactId) {
    if (self::hasActiveMembership($contactId)) {
      CRM_Core_Session::setStatus('Geen lidmaatschap aangemaakt omdat er al een bestaat.', '', 'warning');
      return;
    }

    $today = date('Y-m-d');

    \Civi\Api4\Membership::create(FALSE)
      ->addValue('membership_type_id', self::ORKESTLIDMAATSCHAP)
      ->addValue('contact_id', $contactId)
      ->addValue('join_date', $today)
      ->addValue('start_date', $today)
      ->addValue('status_id', self::CURRENT_MEMBER)
      ->execute();
  }

  private static function hasActiveMembership($contactId) {
    $today = date('Y-m-d');

    $membership = \Civi\Api4\Membership::get(FALSE)
      ->addWhere('contact_id', '=', $contactId)
      ->addWhere('end_date', '>=', $today)
      ->execute()
      ->first();

    if ($membership) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}