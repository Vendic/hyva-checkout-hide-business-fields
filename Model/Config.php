<?php

declare(strict_types=1);

namespace Vendic\HyvaCheckoutHideBusinessFields\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'customer_type/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }
}
