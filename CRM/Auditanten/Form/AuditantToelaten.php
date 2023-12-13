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
        CRM_Auditanten_Contact::convertToOrchestraMember($values['contact_id']);
      }
      else {
        CRM_Auditanten_Contact::convertToExAuditioner($values['contact_id']);
      }
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

    $this->addYesNo('accept_candidate', "$candidateName toelaten?", TRUE, TRUE);
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
