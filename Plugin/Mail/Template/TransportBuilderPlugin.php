<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Email\Plugin\Mail\Template;

use Magento\Framework\Mail\Template\TransportBuilder;
use AlbertMage\Email\Model\Store;

/**
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class TransportBuilderPlugin
{

    /** @var Store */
    protected $storeModel;

    /**
     * @param Store $storeModel
     */
    public function __construct(
        Store $storeModel
    ) {
        $this->storeModel = $storeModel;
    }

    /**
     * @param TransportBuilder $subject
     * @param $templateOptions
     * @return array
     */
    public function beforeSetTemplateOptions(
        TransportBuilder $subject,
        $templateOptions
    ) {
        if (array_key_exists('store', $templateOptions)) {
            $this->storeModel->setStoreId($templateOptions['store']);
        } else {
            $this->storeModel->setStoreId(null);
        }

        return [$templateOptions];
    }
}
