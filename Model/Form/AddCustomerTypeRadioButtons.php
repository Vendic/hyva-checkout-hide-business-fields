<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\HyvaCheckoutHideBusinessFields\Model\Form;

use Hyva\Checkout\Model\Form\EntityField\Input;
use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class AddCustomerTypeRadioButtons implements EntityFormModifierInterface
{
    public const FIELD_NAME = 'customer_type';
    public const TYPE_CONSUMER = 'consumer';
    public const TYPE_BUSINESS = 'business';

    private array $defaultCustomerTypeOptions = [
        ['label' => 'Private', 'value' => self::TYPE_CONSUMER],
        ['label' => 'Business', 'value' => self::TYPE_BUSINESS],
    ];

    public function __construct(
        private CheckoutSession $checkoutSession,
        private array $customCustomerTypeOptions = []
    ) {
    }

    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        $form->registerModificationListener(
            'addSelect',
            'form:init',
            [$this, 'addInitialSelectField']
        );

        $form->registerModificationListener(
            'saveSelect',
            sprintf('form:%s:updated', self::FIELD_NAME),
            [$this, 'saveSelectField']
        );

        $form->registerModificationListener(
            'addWireModel',
            'form:build:magewire',
            [$this, 'addWireModel']
        );

        return $form;
    }

    public function addWireModel(EntityFormInterface $form) : void
    {
        /** @var Input $select */
        $select = $form->getField(self::FIELD_NAME);
        $select->setAttribute('wire:model', sprintf('address.%s', self::FIELD_NAME));
        $select->removeAttribute('wire:model.defer');
    }

    public function saveSelectField(EntityFormInterface $form): void
    {
        $select = $form->getField(self::FIELD_NAME);

        // Let's not use magic method here, as it can potentially return the value with `getValue`
        $previousValue = $select->getData('previous_value');
        if ($select->getValue() !== $previousValue) {
            $this->setCustomerTypeInSession($form, $select->getValue());
        }
    }

    public function addInitialSelectField(EntityFormInterface $form): void
    {
        $customerTypeOptions = array_merge($this->defaultCustomerTypeOptions, $this->customCustomerTypeOptions);

        /** @var Input $select */
        $select = $form->createField(AddCustomerTypeRadioButtons::FIELD_NAME, 'select', [
            'data' => [
                'input' => 'select',
                'is_auto_save' => false,
                'label' => __('Customer Type')->render(),
                'value' => self::TYPE_CONSUMER,
                'position' => 0,
                'options' => $customerTypeOptions,
            ]
        ]);

        if ($this->getCustomerTypeFromSession($form) !== null) {
            $select->setValue($this->getCustomerTypeFromSession($form));
            // Set previous value back to null so Magewire can do it's thing and populate it with the actual previous value
            $select->setData(\Hyva\Checkout\Model\Form\EntityFieldInterface::PREVIOUS_VALUE, null);
        }

        $form->addField($select);
    }

    private function getCustomerTypeFromSession(EntityFormInterface $form) : ?string
    {
        return $this->checkoutSession->getData($form->getNamespace() . '_' . self::FIELD_NAME);
    }

    private function setCustomerTypeInSession(EntityFormInterface $form, string $value) : void
    {
        $this->checkoutSession->setData($form->getNamespace() . '_' . self::FIELD_NAME, $value);
    }
}
