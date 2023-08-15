<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\HyvaCheckoutHideBusinessFields\Model\Form;

use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;

class HideBusinessFieldsForConsumers implements EntityFormModifierInterface
{
    public function __construct(private array $businessFields)
    {
    }


    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        // Initial state
        $form->registerModificationListener(
            'hideBusinessFieldsInitially',
            'form:init',
            [$this, 'applyHideBusinessFieldsForConsumers']
        );

        // Is triggered when the customer type is changed
        $form->registerModificationListener(
            'hideBusinessFieldsForConsumers',
            sprintf('form:shipping:%s:updated', AddCustomerTypeRadioButtons::FIELD_NAME),
            [$this, 'applyHideBusinessFieldsForConsumers']
        );

        return $form;
    }

    public function hideBusinessFieldsInitially(EntityFormInterface $form) : void
    {
        $this->hideFields($form);
    }

    public function applyHideBusinessFieldsForConsumers(EntityFormInterface $form) : void
    {
        $customerTypeField = $form->getField(AddCustomerTypeRadioButtons::FIELD_NAME);
        if ($customerTypeField->getValue() === AddCustomerTypeRadioButtons::TYPE_CONSUMER) {
            $this->hideFields($form);
        }

        if ($customerTypeField->getValue() === AddCustomerTypeRadioButtons::TYPE_BUSINESS) {
            $this->showFields($form);
        }
    }

    private function hideFields(EntityFormInterface $form): void
    {
        foreach ($this->businessFields as $fieldName) {
            $form->getField($fieldName) ? $form->getField($fieldName)->hide() : '';
        }
    }

    private function showFields(EntityFormInterface $form): void
    {
        foreach ($this->businessFields as $fieldName) {
            $form->getField($fieldName) ? $form->getField($fieldName)->show() : '';
        }
    }
}
