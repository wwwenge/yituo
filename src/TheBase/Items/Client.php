<?php
/**
 * This file is part of the wengo/basesdk.
 *
 * (c) basesdk <398711943@qq.com>
 *
 */

namespace Yituo\TheBase\Items;


use Yituo\Core\BaseClient;

class Client extends BaseClient
{
    /**
     * 获取所有产品
     * @param  array $options
     * @return array
     */
    public function getItems(...$options) {
        $allowParams = ['order', 'sort', 'limit', 'offset', 'max_image_no', 'image_size'];
        return $this->httpGet('/1/items', $this->filterOptions($allowParams, $options));
    }

    /**
     * 搜索产品
     * @param  string $keyword
     * @param  array $options
     * @return array
     */
    public function search(string $keyword, ...$options) {
        $allowParams = ['q', 'order', 'sort', 'limit', 'offset', 'fields'];
        return $this->httpGet('/1/items/search', $this->filterOptions($allowParams, ['q' => $keyword], $options));
    }

    /**
     * 获取产品信息
     * @param int $item_id
     * @return array
     */
    public function getItem(int $item_id) {
        return $this->httpGet(sprintf('/1/items/detail/%s', $item_id));
    }

    /**
     * 添加产品
     * @param string $title
     * @param string $price
     * @param string $stock
     * @param array $options
     * @return array
     */
    public function addItem(string $title, string $price, string $stock, ...$options) {
        $allowParams = ['title', 'detail', 'price', 'stock', 'visible', 'identifier', 'list_order', 'variation', 'variation_stock', 'variation_identifier'];
        return $this->httpPost('/1/items/add', $this->filterOptions($allowParams, [
            'title' => $title,
            'price' => $price,
            'stock' => $stock], $options));
    }

    /**
     * 编辑产品
     * @param int $item_id
     * @param array $options
     * @return array
     */
    public function editItem(int $item_id, ...$options) {
        $allowParams = ['item_id', 'title', 'detail', 'price', 'stock', 'visible', 'identifier', 'list_order', 'variation_id', 'variation', 'variation_stock', 'variation_identifier'];
        return $this->httpPost('/1/items/edit', $this->filterOptions($allowParams, ['item_id' => $item_id], $options));
    }

    /**
     * 删除产品
     * @param int $item_id
     * @return array
     */
    public function deleteItem(int $item_id) {
        return $this->httpPost('/1/items/delete', $item_id);
    }

    /**
     * 删除产品
     * @param int $item_id
     * @param string $image_no
     * @param string $image_url
     * @return array
     */
    public function addImage(int $item_id, string $image_no, string $image_url) {
        $options = array(
            'item_id'  => $item_id,
            'image_no'  => $image_no,
            'image_url'  => $image_url
        );

        return $this->httpPost('/1/items/add_image', $options);
    }

    /**
     * 删除产品
     * @param int $item_id
     * @param string $image_no
     * @return array
     */
    public function deleteImage(int $item_id, string $image_no) {
        $options = array(
            'item_id'  => $item_id,
            'image_no'  => $image_no,
        );

        return $this->httpPost('/1/items/delete_image', $options);
    }

    /**
     * 编辑库存
     * @param int $item_id
     * @param int $stock
     * @param int $variation_id
     * @param int $variation_stock
     * @return array
     */
    public function editStock(int $item_id, int $stock, int $variation_id, int $variation_stock) {
        $options = array(
            'item_id'  => $item_id,
            'stock'  => $stock,
            'variation_id'  => $variation_id,
            'variation_stock'  => $variation_stock,
        );

        return $this->httpPost('/1/items/edit_stock', $options);
    }

    /**
     * 删除变体
     * @param int $item_id
     * @param int $variation_id
     * @return array
     */
    public function deleteVariation(int $item_id, int $variation_id) {
        $options = array(
            'item_id'  => $item_id,
            'variation_id'  => $variation_id,
        );

        return $this->httpPost('/1/items/delete_variation', $options);
    }
}