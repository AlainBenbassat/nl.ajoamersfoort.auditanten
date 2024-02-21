<?php

class CRM_Auditanten_Contact {

  public static function isAuditioner($contactId) {
    $groupContact = \Civi\Api4\GroupContact::get(FALSE)
      ->addWhere('group_id:label', '=', 'Auditanten')
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

  public static function convertToOrchestraMember($contactId, $orchestraGroupValue) {
    CRM_Auditanten_Group::moveContactToCurrentOrchestraMembers($contactId);
    self::setOrchestraGroup($contactId, $orchestraGroupValue);
    CRM_Core_Session::setStatus('Auditant toegevoegd aan groep huidige orkestleden');

    CRM_Auditanten_Membership::add($contactId);
    CRM_Core_Session::setStatus('Lidmaatschap aangemaakt voor auditant');

    // add parents
    $contact = self::getContactById($contactId);

    $parentContactId = self::createContactForParent($contact, 1);
    if ($parentContactId) {
      self::createParentChildRelationship($parentContactId, $contactId);
      CRM_Core_Session::setStatus('Ouder 1 aangemaakt als relatie');
    }

    $parentContactId = self::createContactForParent($contact, 2);
    if ($parentContactId) {
      self::createParentChildRelationship($parentContactId, $contactId);
      CRM_Core_Session::setStatus('Ouder 2 aangemaakt als relatie');
    }
  }

  public static function convertToExAuditioner($contactId) {
    CRM_Auditanten_Group::moveContactToExAuditioners($contactId);
  }

  private static function createContactForParent($contact, $parentNumber) {
    $firstName = $contact["Extra_orkestlid_info.Voornaam_ouder_$parentNumber"];
    $lastName = $contact["Extra_orkestlid_info.Naam_ouder_$parentNumber"];
    $phone = $contact["Extra_orkestlid_info.Telefoon_ouder_$parentNumber"];
    $email = $contact["Extra_orkestlid_info.E_mail_ouder_$parentNumber"];

    if (!empty($firstName . $lastName . $phone . $email)) {
      return self::getOrCreate($firstName, $lastName, $phone, $email);
    }
    else {
      return FALSE;
    }
  }

  private static function createParentChildRelationship($parentContactId, $contactId) {
    $childOfRelTypeId = 1;

    if (!self::existsRelationship($childOfRelTypeId, $contactId, $parentContactId)) {
      $results = \Civi\Api4\Relationship::create(TRUE)
        ->addValue('relationship_type_id', $childOfRelTypeId)
        ->addValue('contact_id_a', $contactId)
        ->addValue('contact_id_b', $parentContactId)
        ->execute();
    }
  }

  private static function existsRelationship($childOfRelTypeId, $contactId, $parentContactId) {
    $rel = \Civi\Api4\Relationship::get(TRUE)
      ->addWhere('relationship_type_id', '=', $childOfRelTypeId)
      ->addWhere('contact_id_a', '=', $contactId)
      ->addWhere('contact_id_b', '=', $parentContactId)
      ->execute()
      ->first();

    if ($rel) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function getContactById($contactId) {
    return \Civi\Api4\Contact::get(FALSE)
      ->addSelect('*', 'custom.*')
      ->addWhere('id', '=', $contactId)
      ->execute()
      ->first();
  }

  public static function getOrCreate($firstName, $lastName, $email, $phone) {
    $contactId = self::getContactByName($firstName, $lastName);
    if ($contactId) {
      return $contactId;
    }

    $contactId = self::getContactByName($lastName, $firstName);
    if ($contactId) {
      return $contactId;
    }

    return self::createContact($firstName, $lastName, $email, $phone);
  }

  public static function setOrchestraGroup($contactId, $ochestraGroupValue) {
    \Civi\Api4\Contact::update(FALSE)
      ->addValue('Extra_orkestlid_info.Orkestgrplst', $ochestraGroupValue)
      ->addWhere('id', '=', $contactId)
      ->execute();
  }

  private static function createContact($firstName, $lastName, $email, $phone) {
    $contactId = \Civi\Api4\Contact::create(FALSE)
      ->addValue('contact_type', 'Individual')
      ->addValue('contact_sub_type', ['Ouder'])
      ->addValue('first_name', $firstName)
      ->addValue('last_name', $lastName)
      ->execute()->first()['id'];

    if ($email) {
      \Civi\Api4\Email::create(FALSE)
        ->addValue('email', $email)
        ->addValue('location_type_id', 1)
        ->addValue('contact_id', $contactId)
        ->execute();
    }

    if ($phone) {
      \Civi\Api4\Phone::create(FALSE)
        ->addValue('phone', $phone)
        ->addValue('location_type_id', 1)
        ->addValue('phone_type_id', 1)
        ->addValue('contact_id', $contactId)
        ->execute();
    }

    CRM_Auditanten_Group::addToParentsGroup($contactId);

    return $contactId;
  }

  private static function getContactByName($firstName, $lastName) {
    $contact = \Civi\Api4\Contact::get(FALSE)
      ->addWhere('first_name', '=', $firstName)
      ->addWhere('last_name', '=', $lastName)
      ->execute()
      ->first();

    if ($contact) {
      return $contact['id'];
    }
    else {
      return FALSE;
    }
  }
}