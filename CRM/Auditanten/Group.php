<?php

class CRM_Auditanten_Group {
  public const GROUP_Auditanten = 4;
  public const GROUP_Toegelaten_auditanten = 62;
  public const GROUP_Ouders = 30;
  public const GROUP_Afgewezen_auditanten = 64;
  public const GROUP_Orkestleden_huidige = 6;

  public static function moveContactToExAuditioners($contactId) {
    self::changeGroupMemberStatus($contactId, self::GROUP_Auditanten, 'Removed');
    self::addToGroup($contactId, self::GROUP_Afgewezen_auditanten);
  }

  public static function moveContactToCurrentOrchestraMembers($contactId) {
    if (self::isGroupMember($contactId, self::GROUP_Orkestleden_huidige)) {
      self::changeGroupMemberStatus($contactId, self::GROUP_Orkestleden_huidige, 'Added');
      self::changeGroupMemberStatus($contactId, self::GROUP_Auditanten, 'Removed');
    }
    else {
      self::addToGroup($contactId, self::GROUP_Orkestleden_huidige);
      self::changeGroupMemberStatus($contactId, self::GROUP_Auditanten, 'Removed');
    }

    if (!self::isGroupMember($contactId, self::GROUP_Toegelaten_auditanten)) {
      self::addToGroup($contactId, self::GROUP_Toegelaten_auditanten);
    }
  }

  private static function isGroupMember($contactId, $group) {
    $groupContact = \Civi\Api4\GroupContact::get(FALSE)
      ->addWhere('group_id', '=', $group)
      ->addWhere('contact_id', '=', $contactId)
      ->execute()
      ->first();

    if ($groupContact) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function changeGroupMemberStatus($contactId, $group, $status) {
    \Civi\Api4\GroupContact::update(FALSE)
      ->addValue('status', $status)
      ->addWhere('group_id', '=', $group)
      ->addWhere('contact_id', '=', $contactId)
      ->execute();
  }

  private static function removeFromGroup($contactId, $group) {
    \Civi\Api4\GroupContact::delete(FALSE)
      ->addWhere('contact_id', '=', $contactId)
      ->addWhere('group_id', '=', $group)
      ->execute();
  }

  private static function addToGroup($contactId, $group) {
    \Civi\Api4\GroupContact::create(FALSE)
      ->addValue('status', 'Added')
      ->addValue('group_id', $group)
      ->addValue('contact_id', $contactId)
      ->execute();
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