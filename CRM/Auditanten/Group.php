<?php

class CRM_Auditanten_Group {
  public const GROUP_Auditanten = 4;
  public const GROUP_Ouders = 30;
  public const GROUP_Afgewezen_auditanten = 64;
  public const GROUP_Orkestleden_huidige = 6;

  public static function moveContactToExAuditioners($contactId) {
    self::swapGroup($contactId, self::GROUP_Auditanten, self::GROUP_Afgewezen_auditanten);
  }

  public static function moveContactToCurrentOrchestraMembers($contactId) {
    self::swapGroup($contactId, self::GROUP_Auditanten, self::GROUP_Orkestleden_huidige);
  }

  private static function swapGroup($contactId, $oldGroup, $newGroup) {
    $sql = "
      update
        civicrm_group_contact
      set
        group_id = $newGroup
      where
        contact_id = $contactId
      and
        group_id = $oldGroup
      and
        status = 'Added'
    ";

    CRM_Core_DAO::executeQuery($sql);
  }

  public static function addToParentsGroup($contactId) {
    \Civi\Api4\GroupContact::create(FALSE)
      ->addValue('contact_id', $contactId)
      ->addValue('group_id', self::GROUP_Ouders)
      ->addValue('status', 'Added')
      ->execute();
  }

}