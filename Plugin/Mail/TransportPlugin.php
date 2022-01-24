<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AlbertMage\Email\Plugin\Mail;

use Closure;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\App\ObjectManager;
use AlbertMage\Email\Helper\Data;
use AlbertMage\Email\Model\Smtp;

class TransportPlugin
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param TransportInterface $subject
     * @param Closure $proceed
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        Closure $proceed
    ) {
        if ($this->dataHelper->isActive()) {
            
            $message = $subject->getMessage();

            if ($message instanceof Message || $message instanceof EmailMessageInterface) {
                $transport = ObjectManager::getInstance()->get(Smtp::class);
                $transport->send($message);
            } else {
                $proceed();
            }
        } else {
            $proceed();
        }
    }
}
