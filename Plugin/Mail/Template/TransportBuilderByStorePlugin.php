<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Email\Plugin\Mail\Template;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use AlbertMage\Email\Model\Store;

/**
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class TransportBuilderByStorePlugin
{
    /**
     * @var Store
     */
    protected $storeModel;

    /**
     * Sender resolver.
     *
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @param Store $storeModel
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        Store $storeModel,
        SenderResolverInterface $senderResolver
    ) {
        $this->storeModel = $storeModel;
        $this->senderResolver = $senderResolver;
    }

    /**
     * @param TransportBuilderByStore $subject
     * @param $from
     * @param $store
     * @return array
     * @throws MailException
     */
    public function beforeSetFromByStore(
        TransportBuilderByStore $subject,
        $from,
        $store
    ) {var_dump($store);exit;
        if (!$this->storeModel->getStoreId()) {
            $this->storeModel->setStoreId($store);
        }

        $email = $this->senderResolver->resolve($from, $store);
        $this->storeModel->setFrom($email);

        return [$from, $store];
    }
}
