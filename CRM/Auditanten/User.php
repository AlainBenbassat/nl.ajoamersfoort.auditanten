<?php

class CRM_Auditanten_User {

  public static function create($contact) {
    if (empty($contact['email_primary.email'])) {
      CRM_Core_Session::setStatus('Kan geen login aanmaken want ' . $contact['display_name'] . ' heeft geen emailadres', '', 'warning');
      return 0;
    }

    $user = get_user_by('user_email', $contact['email_primary.email']);
    if ($user) {
      return $user->ID;
    }
    else {
      return wp_create_user($contact['display_name'], wp_generate_password(20), $contact['email_primary.email']);
    }
  }
}