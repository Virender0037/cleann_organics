{{--
    Shared by account-setting.blade.php's own address list/edit/add forms
    and checkout.blade.php's "add a new address" form — one definition of
    the Address field set, matching StoreAddressRequest/UpdateAddressRequest
    exactly, so the two never drift apart.

    Expects: $address (an Address to pre-fill from, or null for a blank
    "add new" form), $prefix (a unique string used only for id="" — never
    part of the field name(s), which stay exactly what the Form Requests
    expect regardless of how many of these partials are on the page at once).
--}}
<div class="contact-form__content">
    <div class="contact-form__content-group">
        <div class="contact-form-input">
            <label for="{{ $prefix }}-type">Address Type</label>
            <select id="{{ $prefix }}-type" name="type" required>
                <option value="shipping" @selected(old('type', $address?->type ?? 'shipping') === 'shipping')>Shipping</option>
                <option value="billing" @selected(old('type', $address?->type ?? 'shipping') === 'billing')>Billing</option>
            </select>
        </div>
        <div class="contact-form-input">
            <label for="{{ $prefix }}-name">Full Name</label>
            <input type="text" id="{{ $prefix }}-name" name="name" value="{{ old('name', $address?->name) }}" placeholder="Full name" required maxlength="255" />
        </div>
        <div class="contact-form-input">
            <label for="{{ $prefix }}-phone">Phone</label>
            <input type="text" id="{{ $prefix }}-phone" name="phone" value="{{ old('phone', $address?->phone) }}" placeholder="Phone number" required maxlength="20" />
        </div>
    </div>
    <div class="contact-form-input">
        <label for="{{ $prefix }}-address1">Address Line 1</label>
        <input type="text" id="{{ $prefix }}-address1" name="address_line_1" value="{{ old('address_line_1', $address?->address_line_1) }}" placeholder="House no., street, area" required maxlength="255" />
    </div>
    <div class="contact-form-input">
        <label for="{{ $prefix }}-address2">Address Line 2 <span>(optional)</span></label>
        <input type="text" id="{{ $prefix }}-address2" name="address_line_2" value="{{ old('address_line_2', $address?->address_line_2) }}" placeholder="Landmark, apartment, etc." maxlength="255" />
    </div>
    <div class="contact-form__content-group">
        <div class="contact-form-input">
            <label for="{{ $prefix }}-city">City</label>
            <input type="text" id="{{ $prefix }}-city" name="city" value="{{ old('city', $address?->city) }}" placeholder="City" required maxlength="255" />
        </div>
        <div class="contact-form-input">
            <label for="{{ $prefix }}-state">State</label>
            <input type="text" id="{{ $prefix }}-state" name="state" value="{{ old('state', $address?->state) }}" placeholder="State" required maxlength="255" />
        </div>
        <div class="contact-form-input">
            <label for="{{ $prefix }}-pincode">Pincode</label>
            <input type="text" id="{{ $prefix }}-pincode" name="pincode" value="{{ old('pincode', $address?->pincode) }}" placeholder="Pincode" required maxlength="10" />
        </div>
    </div>
    <div class="contact-form-input">
        <label for="{{ $prefix }}-country">Country</label>
        <input type="text" id="{{ $prefix }}-country" name="country" value="{{ old('country', $address?->country ?? 'India') }}" placeholder="Country" maxlength="255" />
    </div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <input type="checkbox" id="{{ $prefix }}-default" name="is_default" value="1" @checked(old('is_default', $address?->is_default))
            style="width:18px;height:18px;" />
        <label for="{{ $prefix }}-default" style="margin:0;">Set as default address</label>
    </div>
</div>
