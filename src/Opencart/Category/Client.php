<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\Opencart\Category;

use Yituo\Opencart\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取所有目录
     * @return array
     */
    public function getCategories() {
        return $this->httpGet('', ['r' => 'Category']);
    }

    /**
     * 添加目录
     * @param array $options
     * @return array
     */
    public function addCategory(...$options) {
        $allowParams = ['name', 'list_order', 'parent_number'];

        return $this->httpPost('', $this->filterOptions($allowParams, $options), ['r' => 'Category.Add']);
    }

    /**
     * 编辑目录
     * @param int $category_id
     * @param array $options
     * @return array
     */
    public function editCategory($categoryId, ...$options) {
        $allowParams = ['category_id', 'list_order', 'name'];
        return $this->httpPost('', $this->filterOptions($allowParams, ['category_id' => $categoryId], $options), ['r' => 'Category.Edit']);
    }

    /**
     * 删除目录
     * @param int $category_id
     * @return array
     */
    public function deleteCategory($categoryId) {
        return $this->httpPost('', ['category_id' => $categoryId], ['r' => 'Category.Delete']);
    }
}