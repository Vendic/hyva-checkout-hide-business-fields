# Vendic_HyvaCheckoutHideBusinessFields
This module adds a customer type field to the checkout and hides the business fields when the customer type is set to "consumer". Business fields can be configered via di.xml:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Vendic\HyvaCheckoutHideBusinessFields\Model\Form\HideBusinessFieldsForConsumers">
        <arguments>
            <argument name="businessFields" xsi:type="array">
                <item name="company" xsi:type="string">company</item>
                <item name="vat_id" xsi:type="string">vat_id</item>
            </argument>
        </arguments>
    </type>
</config>
```

### Installation
```
composer require vendic/hyva-checkout-hide-business-fields
```
