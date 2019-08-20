<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\Category;

use Yituo\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取所有目录
     * @return array
     */
    public function getCategories() {
        return $this->httpGet('/1/categories');
    }

    /**
     * 添加目录
     * @param string $name
     * @param array $options
     * @return array
     */
    public function addCategory($name, ...$options) {
        $allowParams = ['name', 'list_order', 'parent_number'];

        return $this->httpPost('/1/categories/add', $this->filterOptions($allowParams, [
            'name' => $name
        ], $options));

    }

    /**
     * 编辑目录
     * @param int $category_id
     * @param array $options
     * @return array
     */
    public function editCategory($category_id, ...$options) {
        $allowParams = ['category_id', 'list_order', 'name'];
        return $this->httpPost('/1/categories/edit', $this->filterOptions($allowParams, ['category_id' => $category_id], $options));
    }

    /**
     * 删除目录
     * @param int $category_id
     * @return array
     */
    public function deleteCategory($category_id) {
        return $this->httpPost('/1/categories/delete', [
            'category_id' => $category_id
        ]);
    }
}