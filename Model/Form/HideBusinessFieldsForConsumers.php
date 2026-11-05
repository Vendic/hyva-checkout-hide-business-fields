<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\HyvaCheckoutHideBusinessFields\Model\Form;

use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use Vendic\HyvaCheckoutHideBusinessFields\Model\Config;

class HideBusinessFieldsForConsumers implements EntityFormModifierInterface
{
    public function __construct(
        private Config $config,
        private array $businessFields
    ) {
    }

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        if (!$this->config->isEnabled()) {
            return $form;
        }
        
        // Initial state
        $form->registerModificationListener(
            'hideBusinessFieldsInitially',
            'form:init',
            [$this, 'applyHideBusinessFieldsForConsumers']
        );

        // Is triggered when the customer type is changed
        $form->registerModificationListener(
            'hideBusinessFieldsForConsumers',
            sprintf('form:%s:updated', AddCustomerTypeRadioButtons::FIELD_NAME),
            [$this, 'applyHideBusinessFieldsForConsumers']
        );

        // Is triggered before rendering, after the form was filled with the address. The initial state
        // comes from the customer type in the checkout session, which can differ from the address that
        // is shown (e.g. a guest chose "business" and then logged in): follow the field value instead.
        $form->registerModificationListener(
            'hideBusinessFieldsOnBuild',
            'form:build:magewire',
            [$this, 'applyHideBusinessFieldsForSelectedCustomerType']
        );

        return $form;
    }

    public function hideBusinessFieldsInitially(EntityFormInterface $form) : void
    {
        $this->hideFields($form);
    }

    public function applyHideBusinessFieldsForSelectedCustomerType(EntityFormInterface $form) : void
    {
        $customerTypeField = $form->getField(AddCustomerTypeRadioButtons::FIELD_NAME);
        if ($customerTypeField === null) {
            return;
        }

        // An address without a customer type (e.g. a new address) is shown as a consumer by the
        // customer_type template, so hide the business fields for it as well.
        if (in_array($customerTypeField->getValue(), [null, ''], true)) {
            $this->hideFields($form);
            return;
        }

        $this->applyHideBusinessFieldsForConsumers($form);
    }

    public function applyHideBusinessFieldsForConsumers(EntityFormInterface $form) : void
    {
        $customerTypeField = $form->getField(AddCustomerTypeRadioButtons::FIELD_NAME);
        if ($customerTypeField === null) {
            return;
        }

        if ($customerTypeField->getValue() === AddCustomerTypeRadioButtons::TYPE_CONSUMER) {
            $this->hideFields($form);
            return;
        }

        $this->showFields($form);
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
