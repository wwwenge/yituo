<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\ItemCategories;


use Yituo\TheBase\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取目录产品对应关系
     * @param  int $item_id
     * @return array
     */
    public function getItemToCagegories($item_id) {
        return $this->httpGet(sprintf('/1/item_categories/detail/%s', $item_id));
    }

    /**
     * 添加目录产品对应关系
     * @param  int $item_id
     * @param  array $options
     * @return array
     */
    public function addItemToCagegories($item_id, ...$options) {
        $allowParams = ['item_id', 'category_id'];
        return $this->httpPost('/1/item_categories/add', $this->filterOptions($allowParams, ['item_id'  => $item_id], $options));
    }

    /**
     * 删除目录产品对应关系
     * @param  int $item_category_id
     * @return array
     */
    public function deleteItemToCagegories($item_category_id) {
        $options = array(
            'item_category_id'  => $item_category_id
        );

        return $this->httpPost('/1/item_categories/delete', $options);
    }
}