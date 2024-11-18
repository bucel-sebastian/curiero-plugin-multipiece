<?php


class Cargus_Shipping_Method_Multipiece
{
    function __construct()
    {
        add_filter('curiero_awb_details', [$this, 'modify_awb_details'], 10, 3);

        add_filter('woocommerce_settings_api_form_fields_urgentcargus_courier', [$this, 'add_multipiece_settings_form_fields']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function modify_awb_details($awbsDetails, $public_name, $order)
    {
        if ($public_name !== 'Cargus') {
            return $awbsDetails;
        }

        ['weight' => $weight] = curiero_extract_order_items_details($order);

        $service_type_id = get_option('uc_tip_serviciu');
        $numar_colete = get_option('uc_nr_colete');

        $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];

        $shipping_method_parcel_types_encoded =  $shipping_method->get_option('parcel_types');
        $shipping_method_parcel_types = json_decode($shipping_method_parcel_types_encoded);

        if (sizeof($shipping_method_parcel_types) !== 0) {
            usort($shipping_method_parcel_types, function ($a, $b) {
                return $b->max_weight - $a->max_weight;
            });

            $max_weight_parcel = null;
            foreach ($shipping_method_parcel_types as $parcel_type) {
                if (null === $max_weight_parcel || $parcel_type->max_weight > $max_weight_parcel->max_weight) {
                    $max_weight_parcel = $parcel_type;
                }
            }


            if ($weight > $max_weight_parcel->max_weight) {
                $service_type_id = 39;

                $colete_parcel_codes = [];
                $remaining_weight = $weight;
                while ($remaining_weight > 0) {
                    foreach ($shipping_method_parcel_types as $parcel_type) {
                        if ($remaining_weight > $parcel_type->max_weight) {
                            $colete_parcel_codes[] = [
                                'Code' => "0",
                                'Type' => 1,
                                'Length' => $parcel_type->length,
                                'Width' => $parcel_type->width,
                                'Height' => $parcel_type->height,
                                'Weight' => $parcel_type->max_weight,
                            ];
                            $remaining_weight -= $parcel_type->max_weight;
                            break;
                        } else {
                            $colete_parcel_codes[] = [
                                'Code' => "0",
                                'Type' => 1,
                                'Length' => $parcel_type->length,
                                'Width' => $parcel_type->width,
                                'Height' => $parcel_type->height,
                                'Weight' => (int) $remaining_weight,
                            ];
                            $remaining_weight = 0;
                            break;
                        }
                    }
                }
                $numar_colete = count($colete_parcel_codes);
                $awbsDetails['Parcels'] = (int) $numar_colete;
                $awbsDetails['ParcelCodes'] = $colete_parcel_codes;
                $awbsDetails['ServiceId'] = (int) $service_type_id;
            }
        }

        return $awbsDetails;
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
}
