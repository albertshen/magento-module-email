<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Email\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class Authtype implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'none', 'label' => __('None')],
            ['value' => 'ssl', 'label' => 'SSL'],
            ['value' => 'tls', 'label' => 'TLS']
        ];
    }
}
