<?php

class CRM_Auditanten_Membership {
  private const ORKESTLIDMAATSCHAP = 8;
  private const CURRENT_MEMBER = 2;

  public static function add($contactId) {
    $today = date('Y-m-d');

    \Civi\Api4\Membership::create(FALSE)
      ->addValue('membership_type_id', self::ORKESTLIDMAATSCHAP)
      ->addValue('contact_id', $contactId)
      ->addValue('join_date', $today)
      ->addValue('start_date', $today)
      ->addValue('status_id', self::CURRENT_MEMBER)
      ->execute();
  }
}