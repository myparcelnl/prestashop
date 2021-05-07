<form action="{$labelUrl}" method="post" id="return_label_form">
  <input name="id_order_label" type="hidden" value="">
  <div class="form-group row">
    <label class="col-lg-3 col-form-label">{l s='Customer Name' mod='myparcelnl'}<sup>*</sup></label>
    <div class="col-lg-9">
      <input class="form-control" name="label_name" type="text" value="{$customerName}" />
    </div>
  </div>
  <div class="form-group row">
    <label class="col-lg-3 col-form-label">{l s='Email' mod='myparcelnl'}<sup>*</sup></label>
    <div class="col-lg-9">
      <input class="form-control" name="label_email" type="text" value="{$customerEmail}" />
    </div>
  </div>
  <div class="form-group row custom-label-return-description">
    <label class="col-lg-3 col-form-label">{l s='Custom Label' mod='myparcelnl'}</label>
    <div class="col-lg-9">
      <input class="form-control" name="label_description" type="text" value="" />
    </div>
  </div>
  <div class="form-group row">
    <label class="col-lg-3 col-form-label">{l s='Package type' mod='myparcelnl'}<sup>*</sup></label>
    <div class="col-lg-6">
      <select name="packageType" class="form-control custom-select">
        {if !empty($carrierSettings.return.packageType[1])}
          <option value="1">{l s='Parcel' mod='myparcelnl'}</option>
        {/if}
        {if !empty($carrierSettings.return.packageType[2])}
          <option value="2">{l s='Mailbox package' mod='myparcelnl'}</option>
        {/if}
        {if !empty($carrierSettings.return.packageType[3])}
          <option value="3">{l s='Letter' mod='myparcelnl'}</option>
        {/if}
        {if !empty($carrierSettings.return.packageType[4])}
          <option value="4">{l s='Digital stamp' mod='myparcelnl'}</option>
        {/if}
      </select>
    </div>
  </div>
  <div class="form-group row">
    <label class="col-lg-3 col-form-label">{l s='Package format' mod='myparcelnl'}</label>
    <div class="col-lg-6">
      <select name="packageFormat" class="form-control custom-select">
        {if !empty($carrierSettings.return.packageFormat[1])}
          <option value="1">{l s='Normal' mod='myparcelnl'}</option>
        {/if}
        {if !empty($carrierSettings.return.packageFormat[2])}
          <option value="2">{l s='Large' mod='myparcelnl'}</option>
        {/if}
        {if !empty($carrierSettings.return.packageFormat[3])}
          <option value="3">{l s='Automatic' mod='myparcelnl'}</option>
        {/if}
      </select>
    </div>
  </div>
  <div class="form-group row label-delivery-options">
    <div class="col-sm-9 offset-sm-3">
      {if $carrierSettings.return.onlyRecipient}
        <div class="form-check">
          <input class="form-check-input" name="onlyRecipient" type="checkbox" id="label_return_recipient_only" value="1" />
          <label class="form-check-label" for="label_return_recipient_only">
            {l s='Recipient only' mod='myparcelnl'}
          </label>
        </div>
      {/if}
      {if $carrierSettings.return.signatureRequired}
        <div class="form-check">
          <input class="form-check-input" name="signatureRequired" type="checkbox" id="label_return_require_signature" value="1" />
          <label class="form-check-label" for="label_return_require_signature">
            {l s='Requires a signature' mod='myparcelnl'}
          </label>
        </div>
      {/if}
      {if $carrierSettings.return.returnUndelivered}
        <div class="form-check">
          <input class="form-check-input" name="returnUndelivered" type="checkbox" id="label_return_return" value="1" />
          <label class="form-check-label" for="label_return_return">
            {l s='Return when undeliverable' mod='myparcelnl'}
          </label>
        </div>
      {/if}
      {if $carrierSettings.return.ageCheck}
        <div class="form-check">
          <input class="form-check-input" name="ageCheck" type="checkbox" id="label_return_age_check" value="1" />
          <label class="form-check-label" for="label_return_age_check">
            {l s='Age check 18+' mod='myparcelnl'}
          </label>
        </div>
      {/if}
      {if $carrierSettings.return.insurance}
        <div class="form-check">
          <input class="form-check-input" name="insurance" type="checkbox" id="label_return_insurance" value="1" />
          <label class="form-check-label" for="label_return_insurance">
            {l s='Insurance' mod='myparcelnl'}
          </label>
        </div>
        <div class="insurance-values">
          <label class="col-form-label mr-2" for="return_insurance_amount_100">
            <input name="returnInsuranceAmount" type="radio" id="return_insurance_amount_100" value="amount100" />
            {l s='Up to € 100' mod='myparcelnl'}
          </label>
          <label class="col-form-label mr-2" for="return_insurance_amount_250">
            <input name="returnInsuranceAmount" type="radio" id="return_insurance_amount_250" value="amount250" />
            {l s='Up to € 250' mod='myparcelnl'}
          </label>
          <label class="col-form-label mr-2" for="return_insurance_amount_500">
            <input name="returnInsuranceAmount" type="radio" id="return_insurance_amount_500" value="amount500" />
            {l s='Up to € 500' mod='myparcelnl'}
          </label>
          {if !$isBE}
            <div class="form-check mt-1 d-flex align-items-center return-insurance-amount-custom">
              <input
                      class="form-check-input mt-0"
                      name="returnInsuranceAmount"
                      type="radio"
                      id="return_insurance_amount_custom"
                      value="-1"
              />
              <label class="form-check-label" for="return_insurance_amount_custom">
                <span class="d-flex pl-0 align-items-center">
                  <span class="mr-2">{l s='More than € 500' mod='myparcelnl'}</span>
                  <span class="input-group col-5">
                    <span class="input-group-prepend">
                      <span class="input-group-text">{$currencySign}</span>
                    </span>
                    <input
                            class="form-control fixed-width-sm"
                            type="text"
                            id="return-insurance-amount-custom-value"
                            name="insurance-amount-custom-value"
                            value="1000"
                    />
                  </span>
                </span>
              </label>
            </div>
          {/if}
        </div>
      {/if}
    </div>
  </div>
</form>
