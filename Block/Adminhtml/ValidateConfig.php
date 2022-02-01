<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AlbertMage\Email\Block\Adminhtml;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\App\ObjectManager;
use AlbertMage\Email\Helper\Data;
use Zend_Mail;
use Zend_Mail_Exception;
use Zend_Mail_Transport_Smtp;
use Zend_Validate_Exception;

class ValidateConfig extends Template
{

    const EMAIL_TEMPLATE_TPL_ID_ZEND_TEST  = 'albertmage_smtp_zend_email_test';

    const EMAIL_TEMPLATE_TPL_ID_MAGENTO_TEST  = 'albertmage_smtp_magento_email_test';

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var string
     */
    protected $toAddress;

    /**
     * @var string
     */
    protected $fromAddress;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $hash;

    /**
     * Remove values from global post and store values locally
     * @var array()
     */
    protected $configFields = [
        'active' => '',
        'name' => '',
        'auth' => '',
        'ssl' => '',
        'smtphost' => '',
        'smtpport' => '',
        'username' => '',
        'password' => '',
        'set_reply_to' => '',
        'set_from' => '',
        'set_return_path' => '',
        'return_path_email' => '',
        'custom_from_email' => '',
        'email' => '',
        'from_email' => ''
    ];

    /**
     * @var EmailAddress
     */
    protected $emailAddressValidator;

    /**
     * EmailTest constructor.
     * @param Context $context
     * @param Data $dataHelper
     * @param EmailAddress $emailAddressValidator
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        EmailAddress $emailAddressValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->emailAddressValidator = $emailAddressValidator;

        $this->init();
    }

    /**
     * @param $id
     * @return $this
     */
    public function setStoreId($id)
    {
        $this->_dataHelper->setStoreId($id);
        $this->storeId = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param null $key
     * @return array|mixed|string
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->configFields;
        } elseif (!array_key_exists($key, $this->configFields)) {
            return '';
        } else {
            return $this->configFields[$key];
        }
    }

    /**
     * @param null $key
     * @param string $value
     * @return array|mixed|string
     */
    public function setConfig($key, $value = null)
    {
        if (array_key_exists($key, $this->configFields)) {
            $this->configFields[$key] = $value;
        }

        return $this;
    }

    /**
     * Load default config if config is lock using "bin/magento config:set"
     */
    public function loadDefaultConfig()
    {
        $request = $this->getRequest();
        $formPostArray = (array) $request->getPost();

        $fields = array_keys($this->configFields);
        foreach ($fields as $field) {
            if (!array_key_exists($field, $formPostArray)) {
                $this->setConfig($field, $this->_dataHelper->getConfigValue($field));
            } else {
                $this->setConfig($field, $request->getPost($field));
            }
        }

        //if password mask (6 stars)
        if ($this->getConfig('password') === '******') {
            $password = $this->_dataHelper->getConfigPassword();
            $this->setConfig('password', $password);
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function init()
    {
        $request = $this->getRequest();

        $this->setStoreId($request->getParam('store', null));

        $this->loadDefaultConfig();

        $this->toAddress = $this->getConfig('email') ?? $this->getConfig('username');

        $this->fromAddress = trim($this->getConfig('from_email'));

        if (!$this->emailAddressValidator->isValid($this->fromAddress)) {
            $this->fromAddress = $this->toAddress;
        }

        $this->hash = time() . '.' . rand(300000, 900000);
    }

    /**
     * @return array
     */
    public function verify()
    {
        $settings = [
            'server_email' => 'validateServerEmailSetting',
            'magento_email_setting' => 'validateMagentoEmailStatus',
            'module_email_setting' => 'validateModuleEmailStatus',
            'magento_email' => 'validateMagentoEmailSetting'
        ];

        $result = $this->error();
        $hasError = false;

        foreach ($settings as $functionName) {
            $result = $this->$functionName();

            if (array_key_exists('has_error', $result)) {
                if ($result['has_error'] === true) {
                    $hasError = true;
                    break;
                }
            } else {
                $hasError = true;
                $result = $this->error(true, 'MP103 - Unknown Error');
                break;
            }
        }

        if (!$hasError) {
            $result['msg'] = __('Please check your email') . ' ' . $this->toAddress . ' ' .
                __('and flush your Magento cache');
        }

        return [$result];
    }

    /**
     * Todo: update to new Zend Framework SMTP
     * @return array
     * @throws Zend_Mail_Exception
     * @throws Zend_Validate_Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function validateServerEmailSetting()
    {
        $request = $this->getRequest();

        $username = $this->getConfig('username');
        $password = $this->getConfig('password');

        $auth = strtolower($this->getConfig('auth'));

        //if default view
        //see https://github.com/magento/magento2/issues/3019
        if (!$request->getParam('store', false)) {
            if ($auth != 'none' && (empty($username) || empty($password))) {
                return $this->error(
                    true,
                    __('Please enter a valid username/password')
                );
            }
        }

        //SMTP server configuration
        $smtpHost = $this->getConfig('smtphost');

        $smtpConf = [
            'name' => $this->getConfig('name'),
            'port' => $this->getConfig('smtpport')
        ];

        if ($auth != 'none') {
            $smtpConf['auth'] = $auth;
            $smtpConf['username'] = $username;
            $smtpConf['password'] = $password;
        }

        $ssl = $this->getConfig('ssl');
        if ($ssl != 'none') {
            $smtpConf['ssl'] = $ssl;
        }

        $transport = new Zend_Mail_Transport_Smtp($smtpHost, $smtpConf);

        $from = trim($this->getConfig('from_email'));
        $from = filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : $username;
        $this->fromAddress = $from;

        //Create email
        $name = 'Test from AlbertMage SMTP';
        $mail = new Zend_Mail();
        $mail->setFrom($this->fromAddress, $name);
        $mail->addTo($this->toAddress, $this->toAddress);
        $mail->setSubject('Hello from AlbertMage SMTP');
        $htmlBody = $this->getEmailBody(['hash' => $this->hash]);

        $mail->setBodyHtml($htmlBody);

        $result = $this->error();

        try {
            //only way to prevent zend from giving a error
            if (!$mail->send($transport) instanceof Zend_Mail) {
                $result =  $this->error(true, __('Invalid class, not instance of Zend Mail'));
            }
        } catch (Exception $e) {
            $result =  $this->error(true, __($e->getMessage()));
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function validateMagentoEmailSetting()
    {
        $result = $this->error();

        $this->_dataHelper->setTestMode(true);
        $this->_dataHelper->setTestConfig($this->getConfig());

        try {
            $transportBuilder = ObjectManager::getInstance()->create(\Magento\Framework\Mail\Template\TransportBuilder::class);
            $transportBuilder->setTemplateIdentifier(self::EMAIL_TEMPLATE_TPL_ID_MAGENTO_TEST)
                ->setTemplateOptions(
                    [
                        'area' => 'adminhtml',
                        'store' => 1,
                    ]
                )
                ->setTemplateVars(['hash' => $this->hash])
                ->setFrom(['email' => $this->toAddress, 'name' => $this->toAddress])
                ->addTo($this->toAddress, $this->toAddress)
                ->getTransport()
                ->sendMessage();
        } catch (Exception $e) {
            $result = $this->error(true, __($e->getMessage()));
        }

        $this->_dataHelper->setTestMode(false);

        return $result;
    }

    /**
     * Get template
     *
     * @return TemplateInterface
     */
    protected function getEmailBody($templateVars)
    {

        $templateFactory = ObjectManager::getInstance()->create(\Magento\Framework\Mail\Template\FactoryInterface::class);
        return $templateFactory->get(self::EMAIL_TEMPLATE_TPL_ID_ZEND_TEST)
            ->setVars($templateVars)
            ->setOptions([
                'area' => 'adminhtml',
                'store' => 1
            ])
            ->processTemplate();
    }

    /**
     * @return array
     */
    public function validateMagentoEmailStatus()
    {
        $result = $this->error();
        // system_smtp_disable

        if ($this->_dataHelper->getScopeConfigValue('system/smtp/disable')) {
            $result = $this->error(
                true,
                __('"Disable Email Communications" is set is "Yes", please set to "NO" in "Mail Sending Setting"')
            );
        }

        return $result;
    }

    /**
     * @return array
     */
    public function validateModuleEmailStatus()
    {
        $result = $this->error();

        if (!$this->getConfig('active')) {
            $result = $this->error(
                true,
                __('SMTP module is not enabled')
            );
        }

        return $result;
    }

    /**
     * Format error msg
     * @param string $s
     * @return string
     */
    public function formatErrorMsg($s)
    {
        return preg_replace(
            '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
            '<a href="https://www.phpdigital.com/help/docs/smtp-magento/" target="_blank">$1</a>',
            nl2br($s)
        );
    }

    /**
     * @param bool $hasError
     * @param string $msg
     * @return array
     */
    public function error($hasError = false, $msg = '')
    {
        return [
            'has_error' => (bool) $hasError,
            'msg' => (string) $msg
        ];
    }
}
