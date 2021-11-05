{if !empty($enableDeliveryOptions)}
  <div class="myparcel-delivery-options-wrapper col-sm-12 {if !empty($carrier)}delivery-options-{$carrier.name}{/if}"></div>
{/if}

<script type="application/javascript">
{if !empty($carrier)}
window.addEventListener('load', () => {
  setTimeout(function() {
    $('label[for="delivery_option_{$carrier.id}"] .carrier-price').html('{$shipping_cost}');
  }, 100);
});
{/if}
</script>
