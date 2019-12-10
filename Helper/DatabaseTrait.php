<?php

namespace Ecomerciar\Moova\Helper;

trait DatabaseTrait
{
    /**
     * Find an order id by itemmeta value
     *
     * @param string $meta_value
     * @return int|false
     */
    public static function find_order_by_itemmeta_value(string $meta_value)
    {
        global $wpdb;

        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
        $query = "SELECT items.order_id
        FROM {$order_items_table} as items
        INNER JOIN {$order_itemmeta_table} as itemmeta ON items.order_item_id = itemmeta.order_item_id
        WHERE itemmeta.meta_value = '%s';";
        $row = $wpdb->get_row($wpdb->prepare($query, $meta_value), ARRAY_A);
        if (!empty($row)) {
            return (int) $row['order_id'];
        }
        return $row;
    }
}
