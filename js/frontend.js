(function($){
    $(document).ready(function(){
        var $form = $("form#add_address, form#add_adress");
        var str = '<p>Kies <span onclick="return pakjegemak();" style="cursor: pointer; text-decoration: underline">hier</span> uw locatie indien u het pakket op een PostNL afleverlocatie wil laten bezorgen.</p>';

        // Five-step checkout enabled and Guest checkout disabled
        if($form.length > 0){
            $form.before($(str));
        // Five-step checkout disabled or Guest checkout enabled
        } else {
            $form = $("form#new_account_form");
            if ($form.length > 0) {
                // Use els to define if One-page checkout or Five-step checkout enabled
                var els = $form.find("#customer_firstname");
                // Add pakjegemak before company field on Auth page if Guest checkout enabled
                var $field = $form.find('#company');
                // If One-page checkout enabled then add pakjegemak before first_name field on Checkout page
                if(els.length > 0){
                    $field = $form.find('#firstname');
                }
                $field.parent().before($(str));
            }
        }
    });
})(jQuery);

var pg_popup;
function pakjegemak()
{
    if(!pg_popup || pg_popup.closed)
    {
        pg_popup = window.open(MYPARCEL_PAKJEGEMAK_URL, 'myparcelpakjegemak', "width=980,height=680,dependent,resizable,scrollbars");
        if(window.focus) { pg_popup.focus(); }
    }
    else
    {
        pg_popup.focus();
    }
    return false;
}