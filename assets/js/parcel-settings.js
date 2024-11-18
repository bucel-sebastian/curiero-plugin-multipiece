jQuery(document).ready(function ($) {
  const parcelInput = $(
    'input[name="woocommerce_urgentcargus_courier_parcel_types"]'
  ); // Câmpul hidden pentru salvarea datelor
  console.log("Input parcel ", parcelInput.val());
  const parcelContainer = $("#parcel-settings"); // Container pentru toate tabelele

  // Funcția de a genera un tabel pentru un colet
  function addParcel(
    parcel = { name: "", length: "", width: "", height: "", max_weight: "" }
  ) {
    const tableId = `parcel-${Date.now()}`;
    const table = `
    <table class="form-table wp-list-table widefat striped" style="max-width: 850px;margin-bottom:10px;" id="${tableId}">
      <thead>
            <tr>
                <th class="wc-shipping-class-name"><strong>Colet: ${
                  parcel.name || "Nou"
                }</strong></th>
                <th class="wc-shipping-class-slug"><button type="button" class="button remove-parcel" style="float: right;">Șterge colet</button></th>
            </tr>
        </thead>
         <tbody class="wc-shipping-class-rows">
      
                <tr>
                    <td>Denumire Colet</td>
                    <td><input type="text" name="parcel_name[]" value="${
                      parcel.name
                    }" placeholder="Denumire" /></td>
                </tr>
                <tr>
                    <td>Lungime (cm)</td>
                    <td><input type="number" name="parcel_length[]" value="${
                      parcel.length
                    }" placeholder="Lungime" /></td>
                </tr>
                <tr>
                    <td>Lățime (cm)</td>
                    <td><input type="number" name="parcel_width[]" value="${
                      parcel.width
                    }" placeholder="Lățime" /></td>
                </tr>
                <tr>
                    <td>Înălțime (cm)</td>
                    <td><input type="number" name="parcel_height[]" value="${
                      parcel.height
                    }" placeholder="Înălțime" /></td>
                </tr>
                <tr>
                    <td>Greutate Maximă (kg)</td>
                    <td><input type="number" name="parcel_weight[]" value="${
                      parcel.max_weight
                    }" placeholder="Greutate maximă" /></td>
                </tr>
                 </tbody>
            </table>
        `;
    parcelContainer.append(table);
  }

  // Load existing parcels
  const savedParcels = JSON.parse(parcelInput.val() || "[]");
  savedParcels.forEach(addParcel);

  // Adaugă un colet nou
  $("#add-parcel").on("click", function () {
    addParcel();
  });

  // Șterge un tabel/colet
  parcelContainer.on("click", ".remove-parcel", function () {
    $(this).closest("table").remove();
    saveParcels();
  });

  // Salvăm toate coletele
  function saveParcels() {
    console.log("Se modifica");
    const parcels = [];
    parcelContainer.find("table").each(function () {
      parcels.push({
        name: $(this).find('input[name="parcel_name[]"]').val(),
        length: $(this).find('input[name="parcel_length[]"]').val(),
        width: $(this).find('input[name="parcel_width[]"]').val(),
        height: $(this).find('input[name="parcel_height[]"]').val(),
        max_weight: $(this).find('input[name="parcel_weight[]"]').val(),
      });
    });
    parcelInput.val(JSON.stringify(parcels));
  }

  // Salvează automat când se modifică
  parcelContainer.on("change", "input", saveParcels);
});
