{*
 * 2017-2019 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if $current_lang_iso == 'nl'}
  <script type="text/javascript">
    {literal}
    (function () {
      function initMoment() {
        if (typeof $ === 'undefined' || typeof $.datepicker !== 'object') {
          setTimeout(initMoment, 100);

          return;
        }

        $(document).ready(function () {
          $.datepicker.regional['nl'].timeOnlyTitle = 'Kies tijdstip';
          $.datepicker.regional['nl'].timeText = 'Tijd';
          $.datepicker.regional['nl'].hourText = 'Uur';
          $.datepicker.regional['nl'].minuteText = 'Minuut';
          $.datepicker.regional['nl'].secondText = 'Seconde';
          $.datepicker.regional['nl'].currentText = 'Nu';

          $.timepicker.setDefaults($.datepicker.regional['nl']);
        });
      }

      initMoment();
    })();
    {/literal}
  </script>
{/if}
{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6.0.9'}
  <script type="text/javascript">
    {literal}
    $(document).ready(function () {
      if ($('#timeframes_off').is(':checked') && $('#pickup_off').is(':checked') && $('#packagedispensers_off').is(':checked')) {
        $('#fieldset_1').hide();
      }
      if ($('#sameday_off').is(':checked')) {
        $('#fieldset_2').hide();
      }
      $('#sameday_off, #sameday_on').change(function () {
        if ($('#sameday_off').is(':checked')) {
          $('#fieldset_2').hide();
        } else {
          $('#fieldset_2').show();
        }
      });
      $('#timeframes_off, #timeframes_on, #pickup_on, #pickup_off, #packagedispensers_on, #packagedispensers_off').change(function () {
        if ($('#timeframes_off').is(':checked') && $('#pickup_off').is(':checked') && $('#packagedispensers_off').is(':checked')) {
          $('#fieldset_1').hide();
        } else {
          $('#fieldset_1').show();
        }
      });
    });
    {/literal}
  </script>
{else}
  <script type="text/javascript">
    {literal}
    $(document).ready(function () {
      if ($('#timeframes_off').is(':checked') && $('#pickup_off').is(':checked') && $('#packagedispensers_off').is(':checked')) {
        $('#fieldset_1_1').hide();
      }
      if ($('#sameday_off').is(':checked')) {
        $('#fieldset_2_2').hide();
      }
      $('#sameday_off, #sameday_on').change(function () {
        if ($('#sameday_off').is(':checked')) {
          $('#fieldset_2_2').hide();
        } else {
          $('#fieldset_2_2').show();
        }
      });
      $('#timeframes_off, #timeframes_on, #pickup_on, #pickup_off, #packagedispensers_on, #packagedispensers_off').change(function () {
        if ($('#timeframes_off').is(':checked') && $('#pickup_off').is(':checked') && $('#packagedispensers_off').is(':checked')) {
          $('#fieldset_1_1').hide();
        } else {
          $('#fieldset_1_1').show();
        }
      });
    });
    {/literal}
  </script>
{/if}
