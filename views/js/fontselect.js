/*
 * ~~jQuery~~.fontselect - A font selector for the Google Web Fonts api
 * Tom Moor, http://tommoor.com
 * Copyright (c) 2011 Tom Moor
 * MIT Licensed
 * @version 0.1
 */

(function () {
  function addEventListener(el, eventName, handler) {
    if (el.addEventListener) {
      el.addEventListener(eventName, handler);
    } else {
      el.attachEvent('on' + eventName, function() {
        handler.call(el);
      });
    }
  }

  //classList (IE9)
  /*! @license please refer to http://unlicense.org/ */
  /*! @author Eli Grey */
  /*! @source https://github.com/eligrey/classList.js */
  ;if("document" in self&&!("classList" in document.createElement("_"))){(function(j){"use strict";if(!("Element" in j)){return}var a="classList",f="prototype",m=j.Element[f],b=Object,k=String[f].trim||function(){return this.replace(/^\s+|\s+$/g,"")},c=Array[f].indexOf||function(q){var p=0,o=this.length;for(;p<o;p++){if(p in this&&this[p]===q){return p}}return -1},n=function(o,p){this.name=o;this.code=DOMException[o];this.message=p},g=function(p,o){if(o===""){throw new n("SYNTAX_ERR","An invalid or illegal string was specified")}if(/\s/.test(o)){throw new n("INVALID_CHARACTER_ERR","String contains an invalid character")}return c.call(p,o)},d=function(s){var r=k.call(s.getAttribute("class")||""),q=r?r.split(/\s+/):[],p=0,o=q.length;for(;p<o;p++){this.push(q[p])}this._updateClassName=function(){s.setAttribute("class",this.toString())}},e=d[f]=[],i=function(){return new d(this)};n[f]=Error[f];e.item=function(o){return this[o]||null};e.contains=function(o){o+="";return g(this,o)!==-1};e.add=function(){var s=arguments,r=0,p=s.length,q,o=false;do{q=s[r]+"";if(g(this,q)===-1){this.push(q);o=true}}while(++r<p);if(o){this._updateClassName()}};e.remove=function(){var t=arguments,s=0,p=t.length,r,o=false;do{r=t[s]+"";var q=g(this,r);if(q!==-1){this.splice(q,1);o=true}}while(++s<p);if(o){this._updateClassName()}};e.toggle=function(p,q){p+="";var o=this.contains(p),r=o?q!==true&&"remove":q!==false&&"add";if(r){this[r](p)}return !o};e.toString=function(){return this.join(" ")};if(b.defineProperty){var l={get:i,enumerable:true,configurable:true};try{b.defineProperty(m,a,l)}catch(h){if(h.number===-2146823252){l.enumerable=false;b.defineProperty(m,a,l)}}}else{if(b[f].__defineGetter__){m.__defineGetter__(a,i)}}}(self))};

  var deepExtend = function(out) {
    out = out || {};

    for (var i = 1; i < arguments.length; i++) {
      var obj = arguments[i];

      if (!obj)
        continue;

      for (var key in obj) {
        if (obj.hasOwnProperty(key)) {
          if (typeof obj[key] === 'object')
            out[key] = deepExtend(out[key], obj[key]);
          else
            out[key] = obj[key];
        }
      }
    }

    return out;
  };

  var fonts = [
    'ABeeZee',
    'Abel',
    'Abhaya Libre',
    'Abril Fatface',
    'Aclonica',
    'Acme',
    'Actor',
    'Adamina',
    'Advent Pro',
    'Aguafina Script',
    'Akronim',
    'Aladin',
    'Aldrich',
    'Alef',
    'Alegreya',
    'Alegreya SC',
    'Alegreya Sans',
    'Alegreya Sans SC',
    'Alex Brush',
    'Alfa Slab One',
    'Alice',
    'Alike',
    'Alike Angular',
    'Allan',
    'Allerta',
    'Allerta Stencil',
    'Allura',
    'Almendra',
    'Almendra Display',
    'Almendra SC',
    'Amarante',
    'Amaranth',
    'Amatic SC',
    'Amatica SC',
    'Amethysta',
    'Amiko',
    'Amiri',
    'Amita',
    'Anaheim',
    'Andada',
    'Andika',
    'Angkor',
    'Annie Use Your Telescope',
    'Anonymous Pro',
    'Antic',
    'Antic Didone',
    'Antic Slab',
    'Anton',
    'Arapey',
    'Arbutus',
    'Arbutus Slab',
    'Architects Daughter',
    'Archivo Black',
    'Archivo Narrow',
    'Aref Ruqaa',
    'Arial',
    'Arima Madurai',
    'Arimo',
    'Arizonia',
    'Armata',
    'Arsenal',
    'Artifika',
    'Arvo',
    'Arya',
    'Asap',
    'Asar',
    'Asset',
    'Assistant',
    'Astloch',
    'Asul',
    'Athiti',
    'Atma',
    'Atomic Age',
    'Aubrey',
    'Audiowide',
    'Autour One',
    'Average',
    'Average Sans',
    'Averia Gruesa Libre',
    'Averia Libre',
    'Averia Sans Libre',
    'Averia Serif Libre',
    'Bad Script',
    'Bahiana',
    'Baloo',
    'Baloo Bhai',
    'Baloo Bhaina',
    'Baloo Chettan',
    'Baloo Da',
    'Baloo Paaji',
    'Baloo Tamma',
    'Baloo Thambi',
    'Balthazar',
    'Bangers',
    'Barrio',
    'Basic',
    'Battambang',
    'Baumans',
    'Bayon',
    'Belgrano',
    'Bellefair',
    'Belleza',
    'BenchNine',
    'Bentham',
    'Berkshire Swash',
    'Bevan',
    'Bigelow Rules',
    'Bigshot One',
    'Bilbo',
    'Bilbo Swash Caps',
    'BioRhyme',
    'BioRhyme Expanded',
    'Biryani',
    'Bitter',
    'Black Ops One',
    'Bokor',
    'Bonbon',
    'Boogaloo',
    'Bowlby One',
    'Bowlby One SC',
    'Brawler',
    'Bree Serif',
    'Bubblegum Sans',
    'Bubbler One',
    'Buenard',
    'Bungee',
    'Bungee Hairline',
    'Bungee Inline',
    'Bungee Outline',
    'Bungee Shade',
    'Butcherman',
    'Butterfly Kids',
    'Cabin',
    'Cabin Condensed',
    'Cabin Sketch',
    'Caesar Dressing',
    'Cagliostro',
    'Cairo',
    'Calligraffitti',
    'Cambay',
    'Cambo',
    'Candal',
    'Cantarell',
    'Cantata One',
    'Cantora One',
    'Capriola',
    'Cardo',
    'Carme',
    'Carrois Gothic',
    'Carrois Gothic SC',
    'Carter One',
    'Catamaran',
    'Caudex',
    'Caveat',
    'Caveat Brush',
    'Cedarville Cursive',
    'Ceviche One',
    'Changa',
    'Changa One',
    'Chango',
    'Chathura',
    'Chau Philomene One',
    'Chela One',
    'Chelsea Market',
    'Chenla',
    'Cherry Cream Soda',
    'Cherry Swash',
    'Chewy',
    'Chicle',
    'Chivo',
    'Chonburi',
    'Cinzel',
    'Cinzel Decorative',
    'Clicker Script',
    'Coda',
    'Coda Caption',
    'Codystar',
    'Coiny',
    'Combo',
    'Comfortaa',
    'Comic Sans MS',
    'Coming Soon',
    'Concert One',
    'Condiment',
    'Content',
    'Contrail One',
    'Convergence',
    'Cookie',
    'Copse',
    'Corben',
    'Cormorant',
    'Cormorant Garamond',
    'Cormorant Infant',
    'Cormorant SC',
    'Cormorant Unicase',
    'Cormorant Upright',
    'Courgette',
    'Cousine',
    'Coustard',
    'Covered By Your Grace',
    'Crafty Girls',
    'Creepster',
    'Crete Round',
    'Crimson Text',
    'Croissant One',
    'Crushed',
    'Cuprum',
    'Cutive',
    'Cutive Mono',
    'Damion',
    'Dancing Script',
    'Dangrek',
    'David Libre',
    'Dawning of a New Day',
    'Days One',
    'Dekko',
    'Delius',
    'Delius Swash Caps',
    'Delius Unicase',
    'Della Respira',
    'Denk One',
    'Devonshire',
    'Dhurjati',
    'Didact Gothic',
    'Diplomata',
    'Diplomata SC',
    'Domine',
    'Donegal One',
    'Doppio One',
    'Dorsa',
    'Dosis',
    'Dr Sugiyama',
    'Droid Sans',
    'Droid Sans Mono',
    'Droid Serif',
    'Duru Sans',
    'Dynalight',
    'EB Garamond',
    'Eagle Lake',
    'Eater',
    'Economica',
    'Eczar',
    'Ek Mukta',
    'El Messiri',
    'Electrolize',
    'Elsie',
    'Elsie Swash Caps',
    'Emblema One',
    'Emilys Candy',
    'Engagement',
    'Englebert',
    'Enriqueta',
    'Erica One',
    'Esteban',
    'Euphoria Script',
    'Ewert',
    'Exo',
    'Exo 2',
    'Expletus Sans',
    'Fanwood Text',
    'Farsan',
    'Fascinate',
    'Fascinate Inline',
    'Faster One',
    'Fasthand',
    'Fauna One',
    'Federant',
    'Federo',
    'Felipa',
    'Fenix',
    'Finger Paint',
    'Fira Mono',
    'Fira Sans',
    'Fira Sans Condensed',
    'Fira Sans Extra Condensed',
    'Fjalla One',
    'Fjord One',
    'Flamenco',
    'Flavors',
    'Fondamento',
    'Fontdiner Swanky',
    'Forum',
    'Francois One',
    'Frank Ruhl Libre',
    'Freckle Face',
    'Fredericka the Great',
    'Fredoka One',
    'Freehand',
    'Fresca',
    'Frijole',
    'Fruktur',
    'Fugaz One',
    'GFS Didot',
    'GFS Neohellenic',
    'Gabriela',
    'Gafata',
    'Galada',
    'Galdeano',
    'Galindo',
    'Gentium Basic',
    'Gentium Book Basic',
    'Geo',
    'Geostar',
    'Geostar Fill',
    'Germania One',
    'Gidugu',
    'Gilda Display',
    'Give You Glory',
    'Glass Antiqua',
    'Glegoo',
    'Gloria Hallelujah',
    'Goblin One',
    'Gochi Hand',
    'Gorditas',
    'Goudy Bookletter 1911',
    'Graduate',
    'Grand Hotel',
    'Gravitas One',
    'Great Vibes',
    'Griffy',
    'Gruppo',
    'Gudea',
    'Gurajada',
    'Habibi',
    'Halant',
    'Hammersmith One',
    'Hanalei',
    'Hanalei Fill',
    'Handlee',
    'Hanuman',
    'Happy Monkey',
    'Harmattan',
    'Headland One',
    'Heebo',
    'Helvetica',
    'Henny Penny',
    'Herr Von Muellerhoff',
    'Hind',
    'Hind Guntur',
    'Hind Madurai',
    'Hind Siliguri',
    'Hind Vadodara',
    'Holtwood One SC',
    'Homemade Apple',
    'Homenaje',
    'IM Fell DW Pica',
    'IM Fell DW Pica SC',
    'IM Fell Double Pica',
    'IM Fell Double Pica SC',
    'IM Fell English',
    'IM Fell English SC',
    'IM Fell French Canon',
    'IM Fell French Canon SC',
    'IM Fell Great Primer',
    'IM Fell Great Primer SC',
    'Iceberg',
    'Iceland',
    'Imprima',
    'Inconsolata',
    'Inder',
    'Indie Flower',
    'Inika',
    'Inknut Antiqua',
    'Irish Grover',
    'Istok Web',
    'Italiana',
    'Italianno',
    'Itim',
    'Jacques Francois',
    'Jacques Francois Shadow',
    'Jaldi',
    'Jim Nightshade',
    'Jockey One',
    'Jolly Lodger',
    'Jomhuria',
    'Josefin Sans',
    'Josefin Slab',
    'Joti One',
    'Judson',
    'Julee',
    'Julius Sans One',
    'Junge',
    'Jura',
    'Just Another Hand',
    'Just Me Again Down Here',
    'Kadwa',
    'Kalam',
    'Kameron',
    'Kanit',
    'Kantumruy',
    'Karla',
    'Karma',
    'Katibeh',
    'Kaushan Script',
    'Kavivanar',
    'Kavoon',
    'Kdam Thmor',
    'Keania One',
    'Kelly Slab',
    'Kenia',
    'Khand',
    'Khmer',
    'Khula',
    'Kite One',
    'Knewave',
    'Kotta One',
    'Koulen',
    'Kranky',
    'Kreon',
    'Kristi',
    'Krona One',
    'Kumar One',
    'Kumar One Outline',
    'Kurale',
    'La Belle Aurore',
    'Laila',
    'Lakki Reddy',
    'Lalezar',
    'Lancelot',
    'Lateef',
    'Lato',
    'League Script',
    'Leckerli One',
    'Ledger',
    'Lekton',
    'Lemon',
    'Lemonada',
    'Libre Baskerville',
    'Libre Franklin',
    'Life Savers',
    'Lilita One',
    'Lily Script One',
    'Limelight',
    'Linden Hill',
    'Lobster',
    'Lobster Two',
    'Londrina Outline',
    'Londrina Shadow',
    'Londrina Sketch',
    'Londrina Solid',
    'Lora',
    'Love Ya Like A Sister',
    'Loved by the King',
    'Lovers Quarrel',
    'Luckiest Guy',
    'Lusitana',
    'Lustria',
    'Macondo',
    'Macondo Swash Caps',
    'Mada',
    'Magra',
    'Maiden Orange',
    'Maitree',
    'Mako',
    'Mallanna',
    'Mandali',
    'Marcellus',
    'Marcellus SC',
    'Marck Script',
    'Margarine',
    'Marko One',
    'Marmelad',
    'Martel',
    'Martel Sans',
    'Marvel',
    'Mate',
    'Mate SC',
    'Maven Pro',
    'McLaren',
    'Meddon',
    'MedievalSharp',
    'Medula One',
    'Meera Inimai',
    'Megrim',
    'Meie Script',
    'Merienda',
    'Merienda One',
    'Merriweather',
    'Merriweather Sans',
    'Metal',
    'Metal Mania',
    'Metamorphous',
    'Metrophobic',
    'Michroma',
    'Milonga',
    'Miltonian',
    'Miltonian Tattoo',
    'Miniver',
    'Miriam Libre',
    'Mirza',
    'Miss Fajardose',
    'Mitr',
    'Modak',
    'Modern Antiqua',
    'Mogra',
    'Molengo',
    'Molle',
    'Monda',
    'Monofett',
    'Monoton',
    'Monsieur La Doulaise',
    'Montaga',
    'Montez',
    'Montserrat',
    'Montserrat Alternates',
    'Montserrat Subrayada',
    'Moul',
    'Moulpali',
    'Mountains of Christmas',
    'Mouse Memoirs',
    'Mr Bedfort',
    'Mr Dafoe',
    'Mr De Haviland',
    'Mrs Saint Delafield',
    'Mrs Sheppards',
    'Mukta Vaani',
    'Muli',
    'Mystery Quest',
    'NTR',
    'Neucha',
    'Neuton',
    'New Rocker',
    'News Cycle',
    'Niconne',
    'Nixie One',
    'Nobile',
    'Nokora',
    'Norican',
    'Nosifer',
    'Nothing You Could Do',
    'Noticia Text',
    'Noto Sans',
    'Noto Serif',
    'Nova Cut',
    'Nova Flat',
    'Nova Mono',
    'Nova Oval',
    'Nova Round',
    'Nova Script',
    'Nova Slim',
    'Nova Square',
    'Numans',
    'Nunito',
    'Nunito Sans',
    'Odor Mean Chey',
    'Offside',
    'Old Standard TT',
    'Oldenburg',
    'Oleo Script',
    'Oleo Script Swash Caps',
    'Open Sans',
    'Open Sans Condensed',
    'Oranienbaum',
    'Orbitron',
    'Oregano',
    'Orienta',
    'Original Surfer',
    'Oswald',
    'Over the Rainbow',
    'Overlock',
    'Overlock SC',
    'Overpass',
    'Overpass Mono',
    'Ovo',
    'Oxygen',
    'Oxygen Mono',
    'PT Mono',
    'PT Sans',
    'PT Sans Caption',
    'PT Sans Narrow',
    'PT Serif',
    'PT Serif Caption',
    'Pacifico',
    'Padauk',
    'Palanquin',
    'Palanquin Dark',
    'Pangolin',
    'Paprika',
    'Parisienne',
    'Passero One',
    'Passion One',
    'Pathway Gothic One',
    'Patrick Hand',
    'Patrick Hand SC',
    'Pattaya',
    'Patua One',
    'Pavanam',
    'Paytone One',
    'Peddana',
    'Peralta',
    'Permanent Marker',
    'Petit Formal Script',
    'Petrona',
    'Philosopher',
    'Piedra',
    'Pinyon Script',
    'Pirata One',
    'Plaster',
    'Play',
    'Playball',
    'Playfair Display',
    'Playfair Display SC',
    'Podkova',
    'Poiret One',
    'Poller One',
    'Poly',
    'Pompiere',
    'Pontano Sans',
    'Poppins',
    'Port Lligat Sans',
    'Port Lligat Slab',
    'Pragati Narrow',
    'Prata',
    'Preahvihear',
    'Press Start 2P',
    'Pridi',
    'Princess Sofia',
    'Prociono',
    'Prompt',
    'Prosto One',
    'Proza Libre',
    'Puritan',
    'Purple Purse',
    'Quando',
    'Quantico',
    'Quattrocento',
    'Quattrocento Sans',
    'Questrial',
    'Quicksand',
    'Quintessential',
    'Qwigley',
    'Racing Sans One',
    'Radley',
    'Rajdhani',
    'Rakkas',
    'Raleway',
    'Raleway Dots',
    'Ramabhadra',
    'Ramaraja',
    'Rambla',
    'Rammetto One',
    'Ranchers',
    'Rancho',
    'Ranga',
    'Rasa',
    'Rationale',
    'Ravi Prakash',
    'Redressed',
    'Reem Kufi',
    'Reenie Beanie',
    'Revalia',
    'Rhodium Libre',
    'Ribeye',
    'Ribeye Marrow',
    'Righteous',
    'Risque',
    'Roboto',
    'Roboto Condensed',
    'Roboto Mono',
    'Roboto Slab',
    'Rochester',
    'Rock Salt',
    'Rokkitt',
    'Romanesco',
    'Ropa Sans',
    'Rosario',
    'Rosarivo',
    'Rouge Script',
    'Rozha One',
    'Rubik',
    'Rubik Mono One',
    'Ruda',
    'Rufina',
    'Ruge Boogie',
    'Ruluko',
    'Rum Raisin',
    'Ruslan Display',
    'Russo One',
    'Ruthie',
    'Rye',
    'Sacramento',
    'Sahitya',
    'Sail',
    'Salsa',
    'Sanchez',
    'Sancreek',
    'Sansita',
    'Sarala',
    'Sarina',
    'Sarpanch',
    'Satisfy',
    'Scada',
    'Scheherazade',
    'Schoolbell',
    'Scope One',
    'Seaweed Script',
    'Secular One',
    'Sevillana',
    'Seymour One',
    'Shadows Into Light',
    'Shadows Into Light Two',
    'Shanti',
    'Share',
    'Share Tech',
    'Share Tech Mono',
    'Shojumaru',
    'Short Stack',
    'Shrikhand',
    'Siemreap',
    'Sigmar One',
    'Signika',
    'Signika Negative',
    'Simonetta',
    'Sintony',
    'Sirin Stencil',
    'Six Caps',
    'Skranji',
    'Slabo 13px',
    'Slabo 27px',
    'Slackey',
    'Smokum',
    'Smythe',
    'Sniglet',
    'Snippet',
    'Snowburst One',
    'Sofadi One',
    'Sofia',
    'Sonsie One',
    'Sorts Mill Goudy',
    'Source Code Pro',
    'Source Sans Pro',
    'Source Serif Pro',
    'Space Mono',
    'Special Elite',
    'Spectral',
    'Spicy Rice',
    'Spinnaker',
    'Spirax',
    'Squada One',
    'Sree Krushnadevaraya',
    'Sriracha',
    'Stalemate',
    'Stalinist One',
    'Stardos Stencil',
    'Stint Ultra Condensed',
    'Stint Ultra Expanded',
    'Stoke',
    'Strait',
    'Sue Ellen Francisco',
    'Suez One',
    'Sumana',
    'Sunshiney',
    'Supermercado One',
    'Sura',
    'Suranna',
    'Suravaram',
    'Suwannaphum',
    'Swanky and Moo Moo',
    'Syncopate',
    'Tangerine',
    'Taprom',
    'Tauri',
    'Taviraj',
    'Teko',
    'Telex',
    'Tenali Ramakrishna',
    'Tenor Sans',
    'Text Me One',
    'The Girl Next Door',
    'Tienne',
    'Tillana',
    'Times New Roman',
    'Timmana',
    'Tinos',
    'Titan One',
    'Titillium Web',
    'Trade Winds',
    'Trirong',
    'Trocchi',
    'Trochut',
    'Trykker',
    'Tulpen One',
    'Ubuntu',
    'Ubuntu Condensed',
    'Ubuntu Mono',
    'Ultra',
    'Uncial Antiqua',
    'Underdog',
    'Unica One',
    'Unkempt',
    'Unlock',
    'Unna',
    'VT323',
    'Vampiro One',
    'Varela',
    'Varela Round',
    'Vast Shadow',
    'Vesper Libre',
    'Vibur',
    'Vidaloka',
    'Viga',
    'Voces',
    'Volkhov',
    'Vollkorn',
    'Voltaire',
    'Waiting for the Sunrise',
    'Wallpoet',
    'Walter Turncoat',
    'Warnes',
    'Wellfleet',
    'Wendy One',
    'Wire One',
    'Work Sans',
    'Yanone Kaffeesatz',
    'Yantramanav',
    'Yatra One',
    'Yellowtail',
    'Yeseva One',
    'Yesteryear',
    'Yrsa',
    'Zeyada',
    'Zilla Slab'
  ];

  /**
   *
   * @param {string} t Target
   * @param {object} o options
   * @constructor
   */
  var Fontselect = function (t, o) {
    var settings = {
      style: 'font-select',
      placeholder: 'Exo',
      lookahead: 5,
      api: '//fonts.googleapis.com/css?family='
    };

    this.select = t;
    if (typeof this.select !== 'object') {
      this.select = document.getElementById(this.select);
    }

    this.options = deepExtend({}, settings, o);
    this.options.font = this.options.placeholder;
    this.active = false;
    this.setupHtml();
    this.getVisibleFonts();
    this.bindEvents();

    var font = this.options.font;
    if (font) {
      this.updateSelected();
      this.addFontLink(font);
    }
  };

  Fontselect.prototype.bindEvents = function () {
    var _this = this;

    Array.prototype.slice.call(this.results.querySelectorAll('li')).forEach(function (li) {
      addEventListener(li, 'click', _this.selectFont.bind(_this));
      addEventListener(li, 'mouseenter', _this.activateFont.bind(_this));
      addEventListener(li, 'mouseleave', _this.deactivateFont.bind(_this));
    });

    addEventListener(this.select.parentNode, 'click', this.toggleDrop.bind(this));
    addEventListener(window, 'click', function (e) {
      if (!_this.select.parentNode.contains(e.target)) {
        var active = _this.active;
        _this.hideDrop();
        if (active) {
          _this.select.focus();
        } else {
          _this.select.blur();
        }
      }
    });
  };

  Fontselect.prototype.toggleDrop = function (ev) {
    if (ev.target.nodeName === 'LI' || typeof this.element === 'undefined') {
      return;
    }

    if (typeof ev !== 'undefined') {
      ev.preventDefault();
    }

    if (this.active) {
      this.element.classList.remove('font-select-active');
      this.drop.style.display = 'none';
      clearInterval(this.visibleInterval);
    } else {
      this.element.classList.add('font-select-active');
      this.drop.style.display = 'block';
      this.moveToSelected();
      this.visibleInterval = setInterval(this.getVisibleFonts.bind(this), 500);
    }

    this.select.focus();
    this.active = !this.active;
  };

  Fontselect.prototype.hideDrop = function () {
    if (this.active) {
      this.element.classList.remove('font-select-active');
      this.drop.style.display = 'none';
      clearInterval(this.visibleInterval);

      this.active = false;
    }
  };

  Fontselect.prototype.setFont = function (font) {
    this.options.font = font;
    this.updateSelected();
    this.hideDrop();
  };

  Fontselect.prototype.selectFont = function () {
    this.options.font = this.element.querySelector('li.active').getAttribute('data-value');
    this.updateSelected();
    this.hideDrop();
    this.select.focus();
  };

  Fontselect.prototype.moveToSelected = function () {
    var li, font = this.options.font;

    if (font) {
      li = this.results.querySelector('li[data-value="' + font + '"]');
    } else {
      li = this.results.querySelector('li');
    }
    li.classList.add('active');
    this.results.scrollTop = li.offsetTop;
  };

  Fontselect.prototype.activateFont = function (ev) {
    var activeLi = this.results.querySelector('li.active');
    if (activeLi != null) {
      activeLi.classList.remove('active');
    }

    ev.currentTarget.classList.add('active');
  };

  Fontselect.prototype.deactivateFont = function (ev) {
    ev.currentTarget.classList.remove('active');
  };

  Fontselect.prototype.updateSelected = function () {
    var font = this.options.font;
    var option = document.createElement('option');
    option.value = font;
    option.label = this.toReadable(font);
    option.textContent = this.toReadable(font);
    this.select.style.fontFamily = font;

    this.select.innerHTML = option.outerHTML;
  };

  Fontselect.prototype.setupHtml = function () {
    this.element = document.createElement('div');
    this.element.classList.add(this.options.style);

    this.select.style.pointerEvents = 'none';
    this.select.style.fontFamily = this.toReadable(this.options.font);
    this.select.innerHTML = '<option label="' + this.options.placeholder + '">' + this.options.placeholder + '</option>';

    var drop = document.createElement('div');
    drop.classList.add('fs-drop');
    drop.classList.add('fixed-width-xxl');
    this.drop = drop;

    this.results = document.createElement('ul');
    this.results.classList.add('fs-results');
    this.results.innerHTML = this.fontsAsHtml();
    this.element.appendChild(this.drop);
    this.select.parentNode.appendChild(this.element);

    this.drop.appendChild(this.results);
    this.drop.style.display = 'none';
  };

  Fontselect.prototype.fontsAsHtml = function () {
    var l = fonts.length;
    var r, s, h = '';

    for (var i = 0; i < l; i++) {
      r = this.toReadable(fonts[i]);
      s = this.toStyle(fonts[i]);
      h += '<li data-value="' + fonts[i] + '" style="font-family: ' + s['font-family'] + '; font-weight: ' + s['font-weight'] + '">' + r + '</li>';
    }

    return h;
  };

  Fontselect.prototype.toReadable = function (font) {
    return font.replace(/[\+|:]/g, ' ');
  };

  Fontselect.prototype.toStyle = function (font) {
    var t = font.split(':');
    return {
      'font-family': this.toReadable(t[0]),
      'font-weight': (t[1] || 400)
    };
  };

  Fontselect.prototype.getVisibleFonts = function () {
    if (!this.active) {
      return;
    }

    var fs = this;
    var top = this.results.scrollTop;
    var bottom = top + this.results.offsetHeight;
    if (this.options.lookahead) {
      var li = this.results.querySelector('li').offsetHeight;
      bottom += li * this.options.lookahead;
    }

    var visible = [];
    Array.prototype.slice.call(this.results.querySelectorAll('li')).forEach(function (item) {
      var ft = item.offsetTop;
      var fb = ft + item.offsetHeight;
      var font = item.getAttribute('data-value');

      if ((fb >= top) && (ft <= bottom)) {
        visible.push(font);
      }
    });
    visible.forEach(function (item) {
      fs.addFontLink(item);
    });
  };

  Fontselect.prototype.addFontLink = function (font) {
    if (['Arial', 'Comic Sans MS', 'Helvetica', 'Times New Roman'].indexOf(font) > -1) {
      return;
    }

    var link = this.options.api + font;

    if (document.querySelector("link[href*='" + font + "']") == null) {
      var links = Array.prototype.slice.call(document.querySelectorAll('link'));
      var lastLink = links[links.length - 1];
      lastLink.insertAdjacentHTML('afterend', '<link href="' + link + '" rel="stylesheet" type="text/css">');
    }
  };

  window.Fontselect = Fontselect;
}());
