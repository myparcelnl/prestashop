{*
 * 2017-2018 DM Productions B.V.
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
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<script type="text/javascript">
  (function () {
    function initMyParcelExport() {
      if (typeof window.MyParcelModule === 'undefined'
          // It takes a while before Internet Explorer recognizes the bulk actions button
        || document.querySelector('.btn-group.bulk-actions ul.dropdown-menu') == null
      ) {
        setTimeout(initMyParcelExport, 10);

        return;
      }

      //classList (IE9)
      /*! @license please refer to http://unlicense.org/ */
      /*! @author Eli Grey */
      /*! @source https://github.com/eligrey/classList.js */
      {literal};if("document" in self&&!("classList" in document.createElement("_"))){(function(j){"use strict";if(!("Element" in j)){return}var a="classList",f="prototype",m=j.Element[f],b=Object,k=String[f].trim||function(){return this.replace(/^\s+|\s+$/g,"")},c=Array[f].indexOf||function(q){var p=0,o=this.length;for(;p<o;p++){if(p in this&&this[p]===q){return p}}return -1},n=function(o,p){this.name=o;this.code=DOMException[o];this.message=p},g=function(p,o){if(o===""){throw new n("SYNTAX_ERR","An invalid or illegal string was specified")}if(/\s/.test(o)){throw new n("INVALID_CHARACTER_ERR","String contains an invalid character")}return c.call(p,o)},d=function(s){var r=k.call(s.getAttribute("class")||""),q=r?r.split(/\s+/):[],p=0,o=q.length;for(;p<o;p++){this.push(q[p])}this._updateClassName=function(){s.setAttribute("class",this.toString())}},e=d[f]=[],i=function(){return new d(this)};n[f]=Error[f];e.item=function(o){return this[o]||null};e.contains=function(o){o+="";return g(this,o)!==-1};e.add=function(){var s=arguments,r=0,p=s.length,q,o=false;do{q=s[r]+"";if(g(this,q)===-1){this.push(q);o=true}}while(++r<p);if(o){this._updateClassName()}};e.remove=function(){var t=arguments,s=0,p=t.length,r,o=false;do{r=t[s]+"";var q=g(this,r);if(q!==-1){this.splice(q,1);o=true}}while(++s<p);if(o){this._updateClassName()}};e.toggle=function(p,q){p+="";var o=this.contains(p),r=o?q!==true&&"remove":q!==false&&"add";if(r){this[r](p)}return !o};e.toString=function(){return this.join(" ")};if(b.defineProperty){var l={get:i,enumerable:true,configurable:true};try{b.defineProperty(m,a,l)}catch(h){if(h.number===-2146823252){l.enumerable=false;b.defineProperty(m,a,l)}}}else{if(b[f].__defineGetter__){m.__defineGetter__(a,i)}}}(self))};{/literal}

      function documentReady(fn) {
        if (document.readyState !== 'loading'){
          fn();
        } else if (document.addEventListener) {
          document.addEventListener('DOMContentLoaded', fn);
        } else {
          document.attachEvent('onreadystatechange', function() {
            if (document.readyState !== 'loading')
              fn();
          });
        }
      }

      documentReady(function () {
        window.MyParcelModule.misc = window.MyParcelModule.misc || {ldelim}{rdelim};
        window.MyParcelModule.misc.process_url = '{$myparcel_process_url|escape:'javascript':'UTF-8'}';
        window.MyParcelModule.misc.module_url = '{$myparcel_module_url|escape:'javascript':'UTF-8'}';
        window.MyParcelModule.misc.countries = {$jsCountries|json_encode};
        window.MyParcelModule.misc.icons = [];
        try {
          window.MyParcelModule.paperSize = {$paperSize|json_encode};
        } catch (e) {
          window.MyParcelModule.paperSize = false;
        }
        window.MyParcelModule.debug = {if Configuration::get(MyParcel::LOG_API)}true{else}false{/if};

        var paperSize = {$paperSize|json_encode};

        if (!paperSize) {
          paperSize = {
            size: 'standard',
            labels: {
              1: true,
              2: true,
              3: true,
              4: true
            }
          };
        }

        new MyParcelModule.ordergrid({
          paperSize: paperSize
        },
        {include file="../translations.tpl"}
        );
      });

    }

    initMyParcelExport();
  }());
</script>
