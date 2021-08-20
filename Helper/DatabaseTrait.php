<?php

namespace Moova\Helper;

trait DatabaseTrait
{
    /**
     * Get all orders by itemmeta value
     *
     * @param string $meta_value
     * @return array|false
     */
    public static function get_orders_by_itemmeta_value(string $meta_value)
    {
        global $wpdb;

        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';

        $query = "SELECT items.order_id FROM {$order_items_table} as items
        INNER JOIN {$order_itemmeta_table} as itemmeta ON items.order_item_id = itemmeta.order_item_id
        WHERE itemmeta.meta_value = '$meta_value'
        ORDER BY items.order_id DESC;";
        return $wpdb->get_results($query);
    }

    /**
     * Get all child orders by parent id
     *
     * @param string $parent_id
     * @return array|false
     */
    public static function get_orders_by_parent_id($order_id)
    {
        global $wpdb;

        $posts_table = $wpdb->prefix . 'posts';
        $query = "SELECT id , post_status FROM $posts_table WHERE POST_PARENT ={$order_id}";
        return $wpdb->get_results($query);
    }
}
