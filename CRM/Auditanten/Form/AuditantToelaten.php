<?php

use CRM_Auditanten_ExtensionUtil as E;

class CRM_Auditanten_Form_AuditantToelaten extends CRM_Core_Form {

  public function buildQuickForm(): void {
    $this->setTitle('Auditant toelaten?');

    $contactId = $this->getContactIdFromQueryParam();

    $this->addFormElements($contactId);
    $this->addFormButtons();

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess(): void {
    try {
      $values = $this->exportValues();
      if ($values['accept_candidate'] == "1") {
        $contact = CRM_Auditanten_Contact::convertToOrchestraMember($values['contact_id'], $values['orchestra_group']);
        $userId = CRM_Auditanten_User::create($contact);
        CRM_Auditanten_Contact::setLinkBetweenUserAndContact($userId, $values['contact_id']);
        CRM_Auditanten_Contact::sendMailAdmitted($values['contact_id']);
      }
      else {
        CRM_Auditanten_Contact::convertToExAuditioner($values['contact_id']);
        CRM_Auditanten_Contact::sendMailRejected($values['contact_id']);
      }

      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $values['contact_id']));

      parent::postProcess();
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(),'Fout', 'error');
    }
  }

  private function addFormElements($contactId) {
    $candidateName = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('display_name')
      ->addWhere('id', '=', $contactId)
      ->execute()
      ->single()['display_name'];

    $this->addYesNo('accept_candidate', "$candidateName toelaten als orkestlid?", TRUE, TRUE);

    $orkestGroepen = [];
    $optionValues = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('option_group_id:name', '=', 'orkestgrplst_20160217162031')
      ->addWhere('is_active', '=', TRUE)
      ->execute();
    foreach ($optionValues as $optionValue) {
      echo $optionValue['name'];
      $orkestGroepen[$optionValue['value']] = $optionValue['label'];
    }
    $this->add('select', 'orchestra_group', 'Zo ja, in welke orkestgroep?', $orkestGroepen);

    $this->add('hidden', 'contact_id', $contactId);
  }

  private function addFormButtons() {
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);
  }

  private function getContactIdFromQueryParam() {
    return CRM_Utils_Request::retrieve('cid', 'Integer', $this, TRUE);
  }

  private function getRenderableElementNames(): array {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
