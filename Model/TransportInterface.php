<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Email\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;

/**
 * Interface for transport.
 * @author Albert Shen <albertshen1206@gmail.com>
 */
interface TransportInterface
{
    /**
     * Send message.
     * 
     * @param MessageInterface | EmailMessageInterface $message
     * @return $this
     * @throws MailException
     */
    public function send(MessageInterface $message);
}