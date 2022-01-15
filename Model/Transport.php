<?php
/**
 *
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AlbertMage\Email\Model;

use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\App\ObjectManager;
use AlbertMage\Email\Helper\Data;

/**
 * Interface for transport.
 * @api
 * @since 100.0.2
 */
class Transport implements TransportInterface
{

    /**
     * @var TransportInterface
     */
    private $provider;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param array
     */
    public function __construct(
        Data $dataHelper,
        array $providers
    )
    {
        //var_dump($dataHelper->getConfigUsername());exit;
        $transport = '';
        if (isset($providers[$transport])) {
            $provider = ObjectManager::getInstance()->get($providers[$transport]);
            if (!$provider instanceof TransportInterface) {
                throw new \InvalidArgumentException(
                    __('provider should be an instance of TransportInterface.')
                );
            }
            $this->provider = $provider;
        } else {
            $this->provider = ObjectManager::getInstance()->get($providers['smtp']);
        }

    }

    /**
     * @inheritdoc
     */
    public function send(MessageInterface $message)
    {
        $this->provider->send($message);
    }
}