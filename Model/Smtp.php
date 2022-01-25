<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AlbertMage\Email\Model;

use AlbertMage\Email\Helper\Data;
use AlbertMage\Email\Model\Store;
use AlbertMage\Email\Model\TransportInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Phrase;
use Laminas\Mail\AddressList;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

/**
 * Class Smtp
 */
class Smtp implements TransportInterface
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Store
     */
    protected $storeModel;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper,
        Store $storeModel
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeModel = $storeModel;
        $this->dataHelper->setStoreId($storeModel->getStoreId());
    }

    /**
     * @inheritdoc
     */
    public function send(MessageInterface $message)
    {

        $message = $this->convertMessage($message);

        $this->setReplyToPath($message);

        $this->setSender($message);

        foreach ($message->getHeaders()->toArray() as $headerKey => $headerValue) {
            $mailHeader = $message->getHeaders()->get($headerKey);
            if ($mailHeader instanceof \Laminas\Mail\Header\HeaderInterface) {
                $this->updateMailHeader($mailHeader);
            } elseif ($mailHeader instanceof \ArrayIterator) {
                foreach ($mailHeader as $header) {
                    $this->updateMailHeader($header);
                }
            }
        }

        try {
            $transport = new SmtpTransport();
            $transport->setOptions($this->getSmtpOptions());
            $transport->send($message);
        } catch (\Exception $e) {
            throw new MailException(
                new Phrase($e->getMessage()),
                $e
            );
        }
    }

    /**
     * @param MessageInterface $message
     * @return Message
     */
    private function convertMessage(MessageInterface $message)
    {
        $encoding = 'utf-8';
        $message = Message::fromString($message->getRawMessage());
        $message->setEncoding($encoding);
        return $message;
    }

    /**
     * @param Message $message
     */
    private function setSender(Message $message)
    {

        //Set from address
        switch ($this->dataHelper->getConfigSetFrom()) {
            case 1:
                $setFromEmail = $message->getFrom()->count() ? $message->getFrom() : $this->getFromEmailAddress();
                break;
            case 2:
                $setFromEmail = $this->dataHelper->getConfigCustomFromEmail();
                break;
            default:
                $setFromEmail = null;
                break;
        }

        if ($setFromEmail !== null) {
            if (is_string($setFromEmail)) {
                $name = $this->getFromName();
                $message->setFrom(trim($setFromEmail), $name);
                $message->setSender(trim($setFromEmail), $name);
            } elseif ($setFromEmail instanceof AddressList) {
                foreach ($setFromEmail as $address) {
                    $message->setFrom($address);
                    $message->setSender($address);
                }
            }
        }

        if (!$message->getFrom()->count()) {
            $result = $this->storeModel->getFrom();
            $message->setFrom($result['email'], $result['name']);
        }
    }

    /**
     * @param Message $message
     */
    private function setReplyToPath(Message $message)
    {

        //Set reply-to path
        switch ($this->dataHelper->getConfigSetReturnPath()) {
            case 1:
                $returnPathEmail = $message->getFrom()->count() ? $message->getFrom() : $this->getFromEmailAddress();
                break;
            case 2:
                $returnPathEmail = $this->dataHelper->getConfigReturnPathEmail();
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if (!$message->getReplyTo()->count() && $this->dataHelper->getConfigSetReplyTo()) {
            if (is_string($returnPathEmail)) {
                $name = $this->getFromName();
                $message->setReplyTo(trim($returnPathEmail), $name);
            } elseif ($returnPathEmail instanceof AddressList) {
                foreach ($returnPathEmail as $address) {
                    $message->setReplyTo($address);
                }
            }
        }
    }

    /**
     * @return SmtpOptions
     */
    private function getSmtpOptions()
    {

        //set config
        $options   = new SmtpOptions([
            'name' => $this->dataHelper->getConfigName(),
            'host' => $this->dataHelper->getConfigSmtpHost(),
            'port' => $this->dataHelper->getConfigSmtpPort(),
        ]);

        $connectionConfig = [];

        $auth = strtolower($this->dataHelper->getConfigAuth());
        if ($auth != 'none') {
            $options->setConnectionClass($auth);

            $connectionConfig = [
                'username' => $this->dataHelper->getConfigUsername(),
                'password' => $this->dataHelper->getConfigPassword()
            ];
        }

        $ssl = $this->dataHelper->getConfigSsl();
        if ($ssl != 'none') {
            $connectionConfig['ssl'] = $ssl;
        }

        if (!empty($connectionConfig)) {
            $options->setConnectionConfig($connectionConfig);
        }

        return $options;
    }

    /**
     * @param $header
     */
    private function updateMailHeader($header)
    {
        if ($header instanceof \Laminas\Mail\Header\HeaderInterface) {
            if (\Laminas\Mime\Mime::isPrintable($header->getFieldValue())) {
                $header->setEncoding('ASCII');
            } else {
                $header->setEncoding('utf-8');
            }
        }
    }

    /**
     * @return string
     */
    private function getFromEmailAddress()
    {
        $result = $this->storeModel->getFrom();
        return $result['email'] ?? '';
    }

    /**
     * @return string
     */
    private function getFromName()
    {
        $result = $this->storeModel->getFrom();
        return $result['name'] ?? '';
    }
}
