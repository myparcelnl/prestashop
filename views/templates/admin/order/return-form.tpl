<form method="post" action="{{ path('admin_myparcel_order_label_create') }}" data-bulk-inputs-id = "create_label_order_ids">
    <div id="create_label_order_ids" data-prototype="<input type=&quot;hidden&quot; id=&quot;create_label_order_ids___name__&quot; name=&quot;create_label[order_ids][__name__]&quot; required=&quot;required&quot; class=&quot;form-control&quot; />"></div>
    <label for="labels_amount">Amout of labels</label>
    <input id="labels_amount" name="number" value="1" type="number" min="1" class="form-control">
    <label for="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_PACKAGE_TYPE_CONFIGURATION_NAME') }}">Package type</label>
    <select name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_PACKAGE_TYPE_CONFIGURATION_NAME') }}" class="custom-select">
        <option value="1">Packet</option>
        <option value="2">Mailbox package</option>
        <option value="3">Letter</option>
        <option value="4">Digital stamp</option>
    </select>
    <label for="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_ONLY_RECIPIENT_CONFIGURATION_NAME') }}">Only to receipient</label>
    <input type="checkbox" value="1" id="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_ONLY_RECIPIENT_CONFIGURATION_NAME') }}" name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_ONLY_RECIPIENT_CONFIGURATION_NAME') }}">

    <label for="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_AGE_CHECK_CONFIGURATION_NAME') }}">Age check</label>
    <input type="checkbox" value="1" id="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_AGE_CHECK_CONFIGURATION_NAME') }}" name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_AGE_CHECK_CONFIGURATION_NAME') }}">
    <select name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_PACKAGE_FORMAT_CONFIGURATION_NAME') }}" class="custom-select">
        <option value="1">Normal</option>
        <option value="2">Large</option>
        <option value="3">Automatic</option>
    </select>
    <label for="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_RETURN_PACKAGE_CONFIGURATION_NAME') }}">Return package</label>
    <input type="checkbox" value="1" id="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_RETURN_PACKAGE_CONFIGURATION_NAME') }}" name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_RETURN_PACKAGE_CONFIGURATION_NAME') }}">

    <label for="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_SIGNATURE_REQUIRED_CONFIGURATION_NAME') }}">Signature</label>
    <input type="checkbox" value="1" id="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_SIGNATURE_REQUIRED_CONFIGURATION_NAME') }}" name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_SIGNATURE_REQUIRED_CONFIGURATION_NAME') }}">

    <label for="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_INSURANCE_CONFIGURATION_NAME') }}">Insurnance</label>
    <input type="checkbox" value="1" id="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_INSURANCE_CONFIGURATION_NAME') }}" name="{{ constant('Gett\\MyParcel\\Constant::MY_PARCEL_INSURANCE_CONFIGURATION_NAME') }}">
    <button type="submit">Submit</button>
</form>