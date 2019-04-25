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
<script type="text/javascript">
  (function () {
    var currentScripts = [].slice.call(document.querySelectorAll('SCRIPT'));
    var head = document.querySelector('HEAD');
    var scripts = {mypa_json_encode(MyParcel::getWebpackChunks('app')) nofilter};
    var key = 0;
    function loadNextChunk() {
      var scriptUrl = scripts[key];
      key++;
      if (!scriptUrl) {
        return;
      }
      var found = currentScripts.filter(function (script) {
        return script.src === scriptUrl;
      }).length;
      if (found <= 0) {
        var newScript = document.createElement('SCRIPT');
        newScript.type = 'text/javascript';
        head.appendChild(newScript);
        newScript.src = scriptUrl;
        newScript.onload = loadNextChunk;
      } else {
        loadNextChunk();
      }
    }

    window.MyParcelModule = window.MyParcelModule || {ldelim}{rdelim};
    window.MyParcelModule.urls = window.MyParcelModule.urls || {ldelim}{rdelim};
    window.MyParcelModule.urls.publicPath = '{MyParcel::getWebpackPublicPath()|escape:'javascript':'UTF-8' nofilter}';

    loadNextChunk();
  }());
</script>
