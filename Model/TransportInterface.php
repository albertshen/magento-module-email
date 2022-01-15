<?php
/**
 *
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AlbertMage\Email\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;

/**
 * Interface for transport.
 * @api
 * @since 100.0.2
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