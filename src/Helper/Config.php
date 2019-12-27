<?php

namespace Hampe\DemoLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{

    const CONFIG_PATH_IS_ACTIVE = 'admin/hmp_demologin/is_active';

    const CONFIG_PATH_USER = 'admin/hmp_demologin/user';

    public function isActive()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_IS_ACTIVE);
    }

    public function getUserId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_USER);
    }

}
