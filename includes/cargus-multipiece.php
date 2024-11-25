<?php

class Cargus_Shipping_Method_Multipiece
{
    function __construct()
    {
        add_filter('curiero_awb_details', [$this, 'modify_awb_details'], 10, 3);
        add_filter('curiero_awb_details_overwrite', [$this, 'modify_awb_details_overwrite'], 10, 3);

        add_filter('wc_get_template', [$this, 'modify_awb_page_template'], 10, 5);
        add_filter('woocommerce_settings_api_form_fields_urgentcargus_courier', [$this, 'add_multipiece_settings_form_fields']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_multipiece_settings_form_fields($form_fields)
    {
        $form_fields['parcel_types_ui'] = [
            'title'       => __('Tipuri de colete', 'curiero-plugin'),
            'type'        => 'title',
            'description' => '<button id="add-parcel" type="button" class="button button-secondary">Adaugă colet</button>
                <div id="parcel-settings"></div>',
        ];
        $form_fields['parcel_types'] = [
            'type'        => 'hidden',
            'default'     => '[]',
            'css'         => 'display:none;'
        ];

        return $form_fields;
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('parcel-settings-script', CURIERO_MULTIPIECE_PLUGIN_URL . 'assets/js/parcel-settings.js', ['jquery'], '1.0', true);
    }

    public function modify_awb_details($awbDetails, $public_name, $order)
    {
        if ($public_name !== 'Cargus') {
            return $awbDetails;
        }

        ['weight' => $weight] = curiero_extract_order_items_details($order);

        $service_type_id = get_option('uc_tip_serviciu');
        $numar_colete = get_option('uc_nr_colete');

        $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];

        $shipping_method_parcel_types_encoded =  $shipping_method->get_option('parcel_types');
        $shipping_method_parcel_types = json_decode($shipping_method_parcel_types_encoded);

        if (count($shipping_method_parcel_types) !== 0) {
            usort($shipping_method_parcel_types, function ($a, $b) {
                return $b->max_weight - $a->max_weight;
            });

            $max_weight_parcel = null;
            foreach ($shipping_method_parcel_types as $parcel_type) {
                if (null === $max_weight_parcel || $parcel_type->max_weight > $max_weight_parcel->max_weight) {
                    $max_weight_parcel = $parcel_type;
                }
            }

            if ($weight >  $max_weight_parcel->max_weight) {
                $service_type_id = 39;
                $colete_parcel_codes = [];

                $remaining_weight = $weight;

                $cart_items = $order->get_items();
                $products = [];
                $total_products = 0;

                foreach ($cart_items as $item_id => $item) {
                    $product = $item->get_product();

                    if ($product) {
                        $product_weight = (float) $product->get_weight();

                        $quantity = $item['quantity'];
                        $total_products += $quantity;

                        $products[] = ['weight' => $product_weight, 'quantity' => $quantity];
                    }
                }

                usort($products, function ($a, $b) {
                    return $b['weight'] - $a['weight'];
                });

                foreach ($shipping_method_parcel_types as $parcel_index => $parcel_type) {
                    $parcel_type_max_weight = $parcel_type->max_weight;
                    $number_of_parcels_of_type = ceil($remaining_weight / $parcel_type_max_weight);

                    $remaining_weight_in_parcel_type = $parcel_type_max_weight * $number_of_parcels_of_type;

                    $fits_in_smaller_parcel = false;

                    for ($i = 1; $i <= $number_of_parcels_of_type; $i++) {
                        $remaining_weight_in_parcel = $parcel_type->max_weight;

                        $products_in_current_parcel = [];

                        if (!empty($products)) {
                            foreach ($products as &$product) {
                                if ((int) $product['quantity'] > 0) {
                                    $quantity_in_parcel = floor($remaining_weight_in_parcel / $product['weight']);

                                    if ((int) ($product['quantity'] - $quantity_in_parcel) < 0) {
                                        $quantity_in_parcel = $product['quantity'];
                                    }

                                    $remaining_weight_in_parcel -= $quantity_in_parcel * $product['weight'];
                                    $remaining_weight_in_parcel_type -= $quantity_in_parcel * $product['weight'];

                                    $products_in_current_parcel[] = ['weight' => $product['weight'], 'quantity' => $quantity_in_parcel];

                                    $product['quantity'] -= $quantity_in_parcel;
                                    if ((int) $remaining_weight_in_parcel === 0) {
                                        break;
                                    }
                                }
                            }
                        }

                        $parcel_weight = (int) $parcel_type->max_weight - $remaining_weight_in_parcel;

                        if ((int) $i === (int) $number_of_parcels_of_type && isset($shipping_method_parcel_types[$parcel_index + 1])) {
                            if (
                                $parcel_weight <= $shipping_method_parcel_types[$parcel_index + 1]->max_weight
                            ) {
                                $fits_in_smaller_parcel = true;
                            }
                        }

                        if (!$fits_in_smaller_parcel) {
                            $colete_parcel_codes[] = [
                                'Code' => "0",
                                'Type' => 1,
                                'Length' => $parcel_type->length,
                                'Width' => $parcel_type->width,
                                'Height' => $parcel_type->height,
                                'Weight' => $parcel_weight,
                            ];
                        } else {
                            foreach ($products_in_current_parcel as $product_in_parcel) {
                                for ($j = 0; $j < count($products); $j++) {
                                    if ($products[$j]['weight'] === $product_in_parcel['weight']) {
                                        $products[$j]['quantity'] += $product_in_parcel['quantity'];
                                    }
                                }
                            }
                        }
                    }

                    if ($fits_in_smaller_parcel) {
                        $remaining_weight -= (($number_of_parcels_of_type - 1) * $parcel_type->max_weight);
                    } else {
                        $remaining_weight -= ($number_of_parcels_of_type * $parcel_type->max_weight) - $remaining_weight_in_parcel_type;
                    }
                }

                $numar_colete = count($colete_parcel_codes);
                $awbDetails['Parcels'] = (int) $numar_colete;
                $awbDetails['ParcelCodes'] = $colete_parcel_codes;
                $awbDetails['ServiceId'] = (int) $service_type_id;
            }
        }

        return $awbDetails;
    }

    public function modify_awb_details_overwrite($awbDetails, $public_name, $order_id)
    {
        if ($public_name !== 'Cargus') {
            return $awbDetails;
        }

        $order = wc_get_order($order_id);

        return $this->modify_awb_details($awbDetails, $public_name, $order);
    }

    public function modify_awb_page_template($template, $template_name, $args, $template_path, $default_path)
    {
        if ($template_name === 'templates/generate_awb_page.php' && $args['courier_name'] === 'Cargus') {
            $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];

            $shipping_method_parcel_types_encoded =  $shipping_method->get_option('parcel_types');
            $shipping_method_parcel_types = json_decode($shipping_method_parcel_types_encoded);

            if (count($shipping_method_parcel_types) > 0) {
                wc_get_template(
                    'templates/generate_awb_page_multipiece.php',
                    $args,
                    'includes/',
                    plugin_dir_path(__FILE__)
                );

                return false;
            } else {
                return $template;
            }
        }
        return $template;
    }
}
