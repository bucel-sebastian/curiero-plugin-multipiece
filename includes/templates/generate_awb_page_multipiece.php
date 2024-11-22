<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$courier_api = $instance->courier_api;
$locker_list = $instance->curiero_api->getPudoPoints();
$order = curiero_get_order($order_id);
$selected_locker_id = $order->get_meta('curiero_cargus_locker', true);

?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - <?php _e('Genereaza AWB', 'curiero-plugin'); ?> <?= $courier_name ?></h2>

    <?php if (!empty($selected_locker_id)) { ?>
        <div class="notice notice-info locker-info">
            <h4><?php _e('A fost selectata optiunea de Cargus Ship & Go pentru aceasta comanda. Recomandam selectarea unui serviciu compatibil', 'curiero-plugin'); ?>.</h4>
        </div>
    <?php } ?>
    <br>

    <form method="POST" action="<?= curiero_order_action_url('cargus', 'generate', $order_id) ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0"><?php _e('Expeditor', 'curiero-plugin'); ?></h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Punct de lucru', 'curiero-plugin'); ?></th>
                    <?php
                    $resultLocations = $courier_api->callMethod('PickupLocations/GetForClient', [], 'GET');
                    $resultMessage = $resultLocations['message'];
                    $arrayResultLocations = json_decode($resultMessage, true);
                    ?>
                    <td>
                        <select name="awb[Sender][LocationId]"> <?php
                                                                foreach ($arrayResultLocations as $location) {
                                                                ?><option value="<?= $location['LocationId']; ?>" <?= $awb_info['Sender']['LocationId'] == $location['LocationId'] ? 'selected="selected"' : ''; ?>><?= $location['Name']; ?></option><?php
                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                        ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Id Tarif', 'curiero-plugin'); ?></th>
                    <?php
                    $resultPriceTables = $courier_api->callMethod('PriceTables', [], 'GET');
                    $resultMessage = $resultPriceTables['message'];
                    $arrayPriceTables = json_decode($resultMessage, true);
                    if ($resultPriceTables['status'] == "200" && $resultMessage !== "Failed to authenticate!") {
                    ?> <td><select name="awb[PriceTableId]"> <?php
                                                                foreach ($arrayPriceTables as $price_table) {
                                                                ?><option value="<?= $price_table['PriceTableId']; ?>" <?= $awb_info['PriceTableId'] == $price_table['PriceTableId'] ? 'selected="selected"' : ''; ?>><?= $price_table['Name']; ?></option><?php
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                            ?> </select></td> <?php
                                                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                                                ?> <td><input type="text" name="awb[PriceTableId]" value="<?= $awb_info['PriceTableId']; ?>" size="50" /></td>
                    <?php } ?>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Tip serviciu', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[ServiceId]">
                            <option value="34" <?= $awb_info['ServiceId'] == '34' ? 'selected="selected"' : ''; ?>>Economic Standard</option>
                            <option value="35" <?= $awb_info['ServiceId'] == '35' ? 'selected="selected"' : ''; ?>>Standard Plus</option>
                            <option value="36" <?= $awb_info['ServiceId'] == '36' ? 'selected="selected"' : ''; ?>>Palet Standard</option>
                            <option value="39" <?= $awb_info['ServiceId'] == '39' ? 'selected="selected"' : ''; ?>>Multipiece / Economic Standard M</option>
                            <option value="40" <?= $awb_info['ServiceId'] == '40' ? 'selected="selected"' : ''; ?>>Economic Standard M Plus</option>
                            <option value="1" <?= $awb_info['ServiceId'] == '1' ? 'selected="selected"' : ''; ?>>Standard</option>
                            <option value="4" <?= $awb_info['ServiceId'] == '4' ? 'selected="selected"' : ''; ?>>Business Partener</option>
                            <option value="38" <?= $awb_info['ServiceId'] == '38' ? 'selected="selected"' : ''; ?>>Ship & Go / PUDO Delivery</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Punct Ship & Go', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[DeliveryPudoPoint]">
                            <option value=""></option>
                            <?php foreach ($locker_list as $locker) { ?>
                                <option <?= selected($locker['Id'], $selected_locker_id ?? null, true); ?> value="<?= esc_html($locker['Id']); ?>">
                                    <?= ucwords(strtolower($locker['City'])) . ' - ' . ucwords(strtolower($locker['Name'])); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0"><?php _e('Destinatar', 'curiero-plugin'); ?></h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Nume', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][Name]" value="<?= $awb_info['Recipient']['Name']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Judet', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][CountyName]" value="<?= $awb_info['Recipient']['CountyName']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Oras', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][LocalityName]" value="<?= $awb_info['Recipient']['LocalityName']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Adresa', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][AddressText]" value="<?= $awb_info['Recipient']['AddressText']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Cod Postal', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][CodPostal]" value="<?= $awb_info['Recipient']['CodPostal']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Persoana contact', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][ContactPerson]" value="<?= $awb_info['Recipient']['ContactPerson']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Telefon', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][PhoneNumber]" value="<?= $awb_info['Recipient']['PhoneNumber']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Email', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Recipient][Email]" value="<?= $awb_info['Recipient']['Email']; ?>"></td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0"><?php _e('Optiuni', 'curiero-plugin'); ?></h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Plicuri', 'curiero-plugin'); ?></th>
                    <td><input type="number" name="awb[Envelopes]" value="<?= $awb_info['Envelopes']; ?>" min="0" max="9"></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Colete', 'curiero-plugin'); ?></th>
                    <td><input type="number" min="0" name="awb[Parcels]" value="<?= $awb_info['Parcels']; ?>" readonly></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Valoare asigurata', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[DeclaredValue]" value="<?= $awb_info['DeclaredValue']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Ramburs cash', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[CashRepayment]" value="<?= $awb_info['CashRepayment']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Ramburs cont colector', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[BankRepayment]" value="<?= $awb_info['BankRepayment']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Servicii aditionale', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[OtherRepayment]">
                            <option value=""><?php _e('Alege o optiune', 'curiero-plugin'); ?></option>
                            <option value="retur" <?= $awb_info['OtherRepayment'] == 'retur' ? 'selected="selected"' : ''; ?>><?php _e('Retur', 'curiero-plugin'); ?></option>
                            <option value="confirmare de primire" <?= $awb_info['OtherRepayment'] == 'confirmare de primire' ? 'selected="selected"' : ''; ?>><?php _e('Confirmare de primire', 'curiero-plugin'); ?></option>
                            <option value="colet la schimb" <?= $awb_info['OtherRepayment'] == 'colet la schimb' ? 'selected="selected"' : ''; ?>><?php _e('Colet la schimb', 'curiero-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Platitor transport', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[ShipmentPayer]">
                            <option value="1" <?= esc_attr(get_option('uc_plata_transport')) == '1' ? 'selected="selected"' : ''; ?>><?php _e('Expeditor', 'curiero-plugin'); ?></option>
                            <option value="2" <?= esc_attr(get_option('uc_plata_transport')) == '2' ? 'selected="selected"' : ''; ?>><?php _e('Destinatar', 'curiero-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Plata comision ramburs', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[ShippingRepayment]">
                            <option value="1" <?= esc_attr(get_option('uc_plata_ramburs')) == '1' ? 'selected="selected"' : ''; ?>><?php _e('Expeditor', 'curiero-plugin'); ?></option>
                            <option value="2" <?= esc_attr(get_option('uc_plata_ramburs')) == '2' ? 'selected="selected"' : ''; ?>><?php _e('Destinatar', 'curiero-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Deschidere la livrare', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[OpenPackage]">
                            <option value="0" <?= esc_attr(get_option('uc_deschidere')) == '0' ? 'selected="selected"' : ''; ?>><?php _e('Nu', 'curiero-plugin'); ?></option>
                            <option value="1" <?= esc_attr(get_option('uc_deschidere')) == '1' ? 'selected="selected"' : ''; ?>><?php _e('Da', 'curiero-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Livrare sambata', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[SaturdayDelivery]">
                            <option value="0" <?= esc_attr(get_option('uc_sambata')) == '0' ? 'selected="selected"' : ''; ?>><?php _e('Nu', 'curiero-plugin'); ?></option>
                            <option value="1" <?= esc_attr(get_option('uc_sambata')) == '1' ? 'selected="selected"' : ''; ?>><?php _e('Da', 'curiero-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Livrare dimineata', 'curiero-plugin'); ?></th>
                    <td>
                        <select name="awb[MorningDelivery]">
                            <option value="0" <?= esc_attr(get_option('uc_matinal')) == '0' ? 'selected="selected"' : ''; ?>><?php _e('Nu', 'curiero-plugin'); ?></option>
                            <option value="1" <?= esc_attr(get_option('uc_matinal')) == '1' ? 'selected="selected"' : ''; ?>><?php _e('Da', 'curiero-plugin'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Observatii', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[Observations]" value="<?= $awb_info['Observations']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Continut', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[PackageContent]" value="<?= $awb_info['PackageContent']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Referinta Serie Client', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[CustomString]" value="<?= $awb_info['CustomString']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Referinta expeditor 1', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[SenderReference1]" value="<?= $awb_info['SenderReference1']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Referinta destinatar 1', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[RecipientReference1]" value="<?= $awb_info['RecipientReference1']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Referinta destinatar 2', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[RecipientReference2]" value="<?= $awb_info['RecipientReference2']; ?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Referinta facturare', 'curiero-plugin'); ?></th>
                    <td><input type="text" name="awb[InvoiceReference]" value="<?= $awb_info['InvoiceReference']; ?>"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <?= submit_button(__('Generează AWB', 'curiero-plugin')); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;">
                        <p>© Copyright <script>
                                document.write(new Date().getFullYear());
                            </script> | <?php _e('Un sistem prietenos de generare AWB-uri creat de', 'curiero-plugin'); ?> <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

<script>
    'function' === typeof window.jQuery && jQuery('form select:not([name="awb[DeliveryPudoPoint]"])').each(function() {
        const e = jQuery(this).find("option").length > 4 ? {} : {
            minimumResultsForSearch: 1 / 0
        };
        jQuery(this).selectWoo(e)
    });
</script>

<script>
    jQuery($ => {
        $('form select[name="awb[DeliveryPudoPoint]"]').selectWoo({
            placeholder: "<?php _e('Alege un punct Ship & Go', 'curiero-plugin'); ?>",
            allowClear: true
        });
        $("input[type=submit]").on("click", function() {
            $(this).addClass("disabled"), $(this).val("<?php _e('Se generează AWB', 'curiero-plugin'); ?>..."), setTimeout(() => {
                $(this).removeClass("disabled"), $(this).val("<?php _e('Generează AWB', 'curiero-plugin'); ?>")
            }, 5e3)
        });

        function template_row_fields(row_index) {
            return `
            <tr>
                <td>
                    <input type="hidden" name="awb[ParcelCodes][${row_index}][Code]" value="${row_index}">
                    <input type="hidden" name="awb[ParcelCodes][${row_index}][Type]" value="1">
                    <input type="number" name="awb[ParcelCodes][${row_index}][Length]" value="" required>
                </td>
                <td>
                    <input type="number" name="awb[ParcelCodes][${row_index}][Width]" value="" required>
                </td>
                <td>
                    <input type="number" name="awb[ParcelCodes][${row_index}][Height]" value="" required>
                </td>
                <td>
                    <input type="number" name="awb[ParcelCodes][${row_index}][Weight]" value="" required>
                </td>
            </tr>
        `;
        }

        $('input[name="awb[Parcels]"]').change(function() {
            let parcels = $(this).val(),
                current_rows = $('.urgent_parcel_size_table tr').length - 1;

            if (parcels < 1) {
                $('.urgent_parcel_size_table tr').first().hide();
            } else {
                $('.urgent_parcel_size_table tr').first().show();
            }

            if (current_rows > parcels) {
                $('.urgent_parcel_size_table tr').slice(parcels - current_rows).remove();
            }

            while (current_rows < parcels) {
                $('.urgent_parcel_size_table').append(template_row_fields(current_rows));
                current_rows++;
            }
        });
    });
</script>