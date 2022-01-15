<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace AlbertMage\Email\Model;

class Store
{
    /** @var int/null  */
    protected $storeId = null;

    /**
     * @var null
     */
    protected $from = null;

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string|array $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }
}
