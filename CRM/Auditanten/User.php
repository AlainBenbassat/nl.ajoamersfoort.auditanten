<?php

class CRM_Auditanten_User {

  public static function create($contact) {
    if (empty($contact['email_primary.email'])) {
      CRM_Core_Session::setStatus('Kan geen login aanmaken want ' . $contact['display_name'] . ' heeft geen emailadres', '', 'warning');
      return 0;
    }

    $user = get_user_by('email', $contact['email_primary.email']);
    if ($user) {
      self::assertUserHasRoleOrkestlid($user);
      CRM_Core_Session::setStatus('Wordpress rol "Orkestlid" toegekend aan bestaande Wordpress gebruiker ' . $user->user_login, '', 'success');
    }
    else {
      $userId = wp_create_user($contact['display_name'], wp_generate_password(20), $contact['email_primary.email']);
      $user = new WP_User($userId);
      $user->add_role('orkestlid');
      retrieve_password($user->user_login); //sends password reset link
      CRM_Core_Session::setStatus('Nieuwe Wordpress gebruiker ' . $user->user_login . ' aangemaakt en rol "Orkestlid" toegekend', '', 'success');
    }

    return $user->ID;
  }

  private static function assertUserHasRoleOrkestlid($user) {
    if (!in_array('orkestlid', $user->roles)) {
      $user->add_role('orkestlid');
    }
  }
}