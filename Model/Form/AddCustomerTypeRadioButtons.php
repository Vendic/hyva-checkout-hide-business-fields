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

    public function __construct(
        private CheckoutSession $checkoutSession
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
            sprintf('form:shipping:%s:updated', self::FIELD_NAME),
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
        $select = $form->getField(self::FIELD_NAME);
        $select->setAttribute('wire:model', sprintf('address.%s', self::FIELD_NAME));
    }

    public function saveSelectField(EntityFormInterface $form): void
    {
        $select = $form->getField(self::FIELD_NAME);
        if ($select->getValue() !== $select->getPreviousValue()) {
            $this->checkoutSession->setData(self::FIELD_NAME, $select->getValue());
        }
    }

    public function addInitialSelectField(EntityFormInterface $form): void
    {
        $emailFieldPosition = isset($form->getFields()['email']) ? $form->getFields()['email']->getPosition() : 1;

        /** @var Input $select */
        $select = $form->createField(AddCustomerTypeRadioButtons::FIELD_NAME, 'select', [
            'data' => [
                'input' => 'select',
                'is_auto_save' => false,
                'label' => __('Customer Type')->render(),
                'value' => self::TYPE_CONSUMER,
                'position' => $emailFieldPosition + 1,
                'options' => [
                    ['label' => __('Consumer'), 'value' => self::TYPE_CONSUMER],
                    ['label' => __('Business'), 'value' => self::TYPE_BUSINESS],
                ],
            ]
        ]);

        if ($this->checkoutSession->getData(self::FIELD_NAME) !== null) {
            $select->setValue($this->checkoutSession->getData(self::FIELD_NAME));
        }

        $form->addField($select);
    }
}
