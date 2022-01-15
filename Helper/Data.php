<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AlbertMage\Email\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    /** 
     * @var bool $testMode 
     */
    protected $testMode = false;

    /** 
     * @var array $testConfig 
     */
    protected $testConfig = [];

    /**
     * @var $storeId
     */
    protected $storeId = null;

    /**
     * @param null $key
     * @return array|mixed|string
     */
    public function getTestConfig($key = null)
    {
        if ($key === null) {
            return $this->testConfig;
        } elseif (!array_key_exists($key, $this->testConfig)) {
            return '';
        } else {
            return $this->testConfig[$key];
        }
    }

    /**
     * @param null $fields
     * @return $this
     */
    public function setTestConfig($fields)
    {
        $this->testConfig = (array) $fields;
        return $this;
    }

    /**
     * @param null $store_id
     * @return bool
     */
    public function isActive()
    {
        return $this->scopeConfig->isSetFlag(
            'system/emailsmtp/active',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Get local client name
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigName()
    {
        return $this->getConfigValue('name');
    }

    /**
     * Get system config password
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigPassword()
    {
        return $this->getConfigValue('password');
    }

    /**
     * Get system config username
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigUsername()
    {
        return $this->getConfigValue('username');
    }

    /**
     * Get system config auth
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigAuth()
    {
        return $this->getConfigValue('auth');
    }

    /**
     * Get system config ssl
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigSsl()
    {
        return $this->getConfigValue('ssl');
    }

    /**
     * Get system config host
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigSmtpHost()
    {
        return $this->getConfigValue('smtphost');
    }

    /**
     * Get system config port
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigSmtpPort()
    {
        return $this->getConfigValue('smtpport');
    }

    /**
     * Get system config reply to
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return bool
     */
    public function getConfigSetReplyTo()
    {
        return $this->scopeConfig->isSetFlag(
            'system/emailsmtp/set_reply_to',
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * Get system config set return path
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return int
     */
    public function getConfigSetReturnPath()
    {
        return (int) $this->getConfigValue('set_return_path');
    }

    /**
     * Get system config return path email
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigReturnPathEmail()
    {
        return $this->getConfigValue('return_path_email');
    }

    /**
     * Get system config from
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigSetFrom()
    {
        return  (int) $this->getConfigValue('set_from');
    }

    /**
     * Get system config from
     *
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigCustomFromEmail()
    {
        return  $this->getConfigValue('custom_from_email');
    }

    /**
     * Get system config
     *
     * @param String path
     * @param ScopeInterface::SCOPE_STORE $store
     * @return string
     */
    public function getConfigValue($path)
    {
        //send test mail
        if ($this->isTestMode()) {
            return $this->getTestConfig($path);
        }

        //return value from core config
        return $this->getScopeConfigValue(
            "system/emailsmtp/{$path}"
        );
    }

    /**
     * @param String path
     * @param ScopeInterface::SCOPE_STORE $store
     * @return mixed
     */
    public function getScopeConfigValue($path)
    {

        //return value from core config
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }

    /**
     * @return int/null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param int/null $storeId
     */
    public function setStoreId($storeId = null)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return (bool) $this->testMode;
    }

    /**
     * @param bool $testMode
     * @return Data
     */
    public function setTestMode($testMode)
    {
        $this->testMode = (bool) $testMode;
        return $this;
    }
}
