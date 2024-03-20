# Changelog

All notable changes to this project will be documented in this file. See
[Conventional Commits](https://conventionalcommits.org) for commit guidelines.

## [2.0.0-beta.2](https://github.com/myparcelnl/prestashop/compare/v2.0.0-beta.1...v2.0.0-beta.2) (2024-03-20)


### :bug: Bug Fixes

* **migration:** fix type error in product settings migration ([a7a32b2](https://github.com/myparcelnl/prestashop/commit/a7a32b26c8e2397bcb95bb1022952e975fbe2344)), closes [#235](https://github.com/myparcelnl/prestashop/issues/235)
* **products:** improve handling of nonexistent products ([#229](https://github.com/myparcelnl/prestashop/issues/229)) ([efd8483](https://github.com/myparcelnl/prestashop/commit/efd84832d21985cf1fc4aa7d290c918ad9ef1dca)), closes [#228](https://github.com/myparcelnl/prestashop/issues/228)


### :sparkles: New Features

* **deps:** upgrade @myparcel-pdk/* ([2c88f49](https://github.com/myparcelnl/prestashop/commit/2c88f49689d7d0038f99251b0360ccfeb7779349))
* **deps:** upgrade myparcelnl/pdk to v2.33.2 ([1bd9d5d](https://github.com/myparcelnl/prestashop/commit/1bd9d5d9da24d8ea3da7692b36bb702bc4d32e75))
* support prestashop 1.7 ([#239](https://github.com/myparcelnl/prestashop/issues/239)) ([b16926c](https://github.com/myparcelnl/prestashop/commit/b16926c1bc1bbe80ac42a313598fe5754e134ca9)), closes [#232](https://github.com/myparcelnl/prestashop/issues/232)
* upgrade to delivery options v6.x ([35d2b05](https://github.com/myparcelnl/prestashop/commit/35d2b059f7839a167b0e3f3db924b141ea0bd116))

## [2.0.0-beta.1](https://github.com/myparcelnl/prestashop/compare/v1.8.1...v2.0.0-beta.1) (2023-11-30)


### ⚠ BREAKING CHANGES

* implement pdk

### :bug: Bug Fixes

* **account:** fix carrier mapping constraint error on account update ([21eb32f](https://github.com/myparcelnl/prestashop/commit/21eb32f2612813ffe56e73dbde374fa9c4f386e3))
* **admin:** fix all inputs appearing as required ([142f9f9](https://github.com/myparcelnl/prestashop/commit/142f9f97c5dc59774c5fb741e09fe1f791a4bac3))
* **admin:** fix broken import ([0c1c1ad](https://github.com/myparcelnl/prestashop/commit/0c1c1ad686e5a4f1e386652856f5de9ff3071f70))
* **admin:** fix error on pages without bulk actions ([9960cee](https://github.com/myparcelnl/prestashop/commit/9960ceead70e455d074254df3751fea90141c447))
* **admin:** fix scripts ([9c4ef00](https://github.com/myparcelnl/prestashop/commit/9c4ef00073465e1de2fac6de45299c7c9a00e2ec))
* **admin:** improve product settings appearance ([5682b73](https://github.com/myparcelnl/prestashop/commit/5682b7302550bc6bf1b4a9d3922af601f93c13ce))
* **admin:** improve product settings form ([84714ce](https://github.com/myparcelnl/prestashop/commit/84714cef3e7add58e0d88a2396c809396be1a2c0))
* **admin:** improve tri state input appearance ([d8186c7](https://github.com/myparcelnl/prestashop/commit/d8186c72674cc634eeb570c581de4d804e4991cf))
* **admin:** remove required indicators from inputs ([3e5bff7](https://github.com/myparcelnl/prestashop/commit/3e5bff75cbf7bc88b80dde77165819ad5032ddee))
* **admin:** update components ([8e73009](https://github.com/myparcelnl/prestashop/commit/8e7300947a98eda8440401a59ad9876e4be51b6e))
* **carriers:** add carrier migration and improve logic ([7e08891](https://github.com/myparcelnl/prestashop/commit/7e08891f0060aeb48cb044a541f376ec9893b2d5))
* **carrier:** set human as name instead of identifier ([c040806](https://github.com/myparcelnl/prestashop/commit/c0408061a087a3c611f71a94d6b6abfdfaa7044a))
* **carriers:** fix carriers and payment methods not being linked ([2228f71](https://github.com/myparcelnl/prestashop/commit/2228f7195ae37534d75705d3035a36191c2bb61b))
* **carriers:** update carrier creation logic ([dee2d3f](https://github.com/myparcelnl/prestashop/commit/dee2d3ff1dbfba3d3ef76aaab77bc2661974f968))
* **carriers:** update default values ([63d3077](https://github.com/myparcelnl/prestashop/commit/63d30770aab274ae20343b952e11232902f39ca5))
* **checkout:** do not load delivery options script if it's disabled ([713f566](https://github.com/myparcelnl/prestashop/commit/713f5664529e1af3f5fe7a0246a81f34517c3259))
* **checkout:** fix saving delivery options to cart ([64e9e86](https://github.com/myparcelnl/prestashop/commit/64e9e8668e011be7fc5f37d765ad4ffb2a163750))
* **checkout:** improve delivery options logic ([6d3fa93](https://github.com/myparcelnl/prestashop/commit/6d3fa93132b582100994431058a8a7067fca5390))
* **checkout:** retain specific configs when switching carriers ([3d3c223](https://github.com/myparcelnl/prestashop/commit/3d3c223b254237f94281e3f432c32523b58eabd6))
* **checkout:** update checkout ([7714efd](https://github.com/myparcelnl/prestashop/commit/7714efda7d3a6f5fe5ff559a8180a05c87328746))
* **checkout:** update checkout hooks ([0297ce0](https://github.com/myparcelnl/prestashop/commit/0297ce0886bf4d7bee3c7bf001dfd4fe0cc8aedb))
* **core:** improve core module error handling ([b15ed15](https://github.com/myparcelnl/prestashop/commit/b15ed15dad541b50097e9246a8786bab1f849a7d))
* **database:** allow creating index to fail if sql functionality is not supported ([6550654](https://github.com/myparcelnl/prestashop/commit/6550654446073e17d5abf4363855c8eacc70ad20))
* **database:** fix creating indexes on certain db drivers ([fbbc611](https://github.com/myparcelnl/prestashop/commit/fbbc6114acfdbfd1d46a82e5946d17823cdd92fc))
* **database:** fix database indexes and keys ([5ddb09d](https://github.com/myparcelnl/prestashop/commit/5ddb09da348ae4075d8485e9a631c07d5e93212f))
* **database:** only create indexes if they do not exist ([33b89fd](https://github.com/myparcelnl/prestashop/commit/33b89fd5c5f177450abdc52660971b073eb42a58))
* **deps:** upgrade myparcelnl/pdk to v2.30.3 ([26c6bc5](https://github.com/myparcelnl/prestashop/commit/26c6bc5f7f688d0f4a2c954be39597b55cc5285c))
* **deps:** upgrade myparcelnl/pdk to v2.30.4 ([217c239](https://github.com/myparcelnl/prestashop/commit/217c239d6a8a3177b989e6028dff815759ef0ed2))
* **deps:** upgrade myparcelnl/pdk to v2.31.2 ([2309ea4](https://github.com/myparcelnl/prestashop/commit/2309ea4292b79a84648c4c5a63945a904660e0cf))
* **entity:** update entities ([5d415e8](https://github.com/myparcelnl/prestashop/commit/5d415e827ea150b4bdc428cd8e79fd5c08a896b2))
* fix di container in production mode ([49874fc](https://github.com/myparcelnl/prestashop/commit/49874fc2844140620cd7614a7c0b81be805b5a71))
* fix error when loading webhook subscriptions ([7fab71c](https://github.com/myparcelnl/prestashop/commit/7fab71c716a615bf0a7794236a7928ecc5c38fa4))
* fix path to log directory ([0bef829](https://github.com/myparcelnl/prestashop/commit/0bef82920db1b0d6b2eb2e64b88a44f408a870b2))
* fix php errors ([8ab2f49](https://github.com/myparcelnl/prestashop/commit/8ab2f4912d353158c87793c233a0d646295fa6fe))
* **fulfilment:** add order notes ([e14fa32](https://github.com/myparcelnl/prestashop/commit/e14fa3201ee282301c8f484cf01e6bd6451afdf6))
* improve entities ([275265e](https://github.com/myparcelnl/prestashop/commit/275265e8e44c08e1f6a999a45585f88038ed6946))
* improve entities and carrier logic ([b61d197](https://github.com/myparcelnl/prestashop/commit/b61d1970fcc916ee1648fe5500da850e4f8eec6e))
* improve installation logging ([056aca4](https://github.com/myparcelnl/prestashop/commit/056aca45fbd7dd09615a54239eee1ea7a9343242))
* improve stability of installation flow ([8db8654](https://github.com/myparcelnl/prestashop/commit/8db8654183849a9472a5402d916c2f63ad149622))
* increase max ps version to 8.2 ([ffb2732](https://github.com/myparcelnl/prestashop/commit/ffb27329aa9a7e82325fdb90eae5b732f57f7db8))
* install module tab correctly ([19d3b33](https://github.com/myparcelnl/prestashop/commit/19d3b33681c794ce343a6715c8ec0de4cf837073))
* **installer:** clear sf2 cache on install ([533cda0](https://github.com/myparcelnl/prestashop/commit/533cda0cf53c0b014033e2030f833c7aea172a02))
* **installer:** fix prestashop context error on install ([003ca3b](https://github.com/myparcelnl/prestashop/commit/003ca3b5e7e2d64afbaf561b867b931eb7301f42))
* **installer:** prepare the ps entity manager before starting ([016c9ee](https://github.com/myparcelnl/prestashop/commit/016c9eee806c18cf642203687855cff26bc3198e))
* **installer:** properly delete account on uninstall ([bdf1d3a](https://github.com/myparcelnl/prestashop/commit/bdf1d3ae22e630ae67705dbe0ab8c49d5f8c4807))
* **installer:** save the installed version to database ([3f067f0](https://github.com/myparcelnl/prestashop/commit/3f067f0270ca308eea2085b46cb79d320bc9417f))
* **logger:** fix logger ([3ef0b51](https://github.com/myparcelnl/prestashop/commit/3ef0b51098f4083082e801fde132bd5a4f627254))
* **logging:** improve logging ([0caa9ef](https://github.com/myparcelnl/prestashop/commit/0caa9ef8f506ef8c64412725c5c8a0524099acf6))
* **logging:** include context in logs ([fc0723e](https://github.com/myparcelnl/prestashop/commit/fc0723e16a8a186ef810e759a0ac313ccfde02f7))
* **migration:** fix db constraint errors in migrations ([7ebc855](https://github.com/myparcelnl/prestashop/commit/7ebc8552dc36fe43806ab7f535ca8594629193ae))
* **migration:** fix type error ([12f4fad](https://github.com/myparcelnl/prestashop/commit/12f4fadb76867698def878684286bc29fc7d64ba))
* **migration:** harden error handling ([b75d277](https://github.com/myparcelnl/prestashop/commit/b75d27797bc6b91cc4a987b807d08cc9483e6d12))
* **migration:** improve order shipments migration ([f6f3b5e](https://github.com/myparcelnl/prestashop/commit/f6f3b5ee4f66f60f4d4ef6b08f6623d92230555b))
* **migration:** improve settings migration ([cc9e21c](https://github.com/myparcelnl/prestashop/commit/cc9e21cf8313e8a017561491a013c132951ad09b))
* **migration:** improve settings migration ([2aa8da9](https://github.com/myparcelnl/prestashop/commit/2aa8da9f35afabc203d4c87003c3f421d715b1de))
* **migration:** migrate migrations ([0263e9f](https://github.com/myparcelnl/prestashop/commit/0263e9feb1c6ab78fae906b2ec2f29d3da7a34a9))
* **migration:** run migrations when upgrading ([7534da7](https://github.com/myparcelnl/prestashop/commit/7534da708bbe4af5ec1c30dddff673a03d2677f4))
* **migrations:** improve migrations ([148fceb](https://github.com/myparcelnl/prestashop/commit/148fcebcb0641ec124a83115aacfa059b5137ac1))
* **migration:** update and test shipments migration ([f0f33ca](https://github.com/myparcelnl/prestashop/commit/f0f33ca7fadf1c1cad321d8c3523aff5bca716d2))
* **module:** move settings to dedicated page ([138ed5c](https://github.com/myparcelnl/prestashop/commit/138ed5c7449c7421ec4b5319169ea0f6be649f89))
* **order:** fix errors caused by missing dates ([54bfc96](https://github.com/myparcelnl/prestashop/commit/54bfc96d9821c35152dbc8f3a1ed43747d58e81d))
* **order:** fix saving data to orders ([cb3dbd9](https://github.com/myparcelnl/prestashop/commit/cb3dbd9db5cc1e009cd16d5636eec98a7e6dadad))
* **orders:** move order note logic to order note repository ([9b83b32](https://github.com/myparcelnl/prestashop/commit/9b83b32dab807468cb3dfa0c809239aeeebdbf8c))
* **orders:** prevent order id type error ([e59ab3c](https://github.com/myparcelnl/prestashop/commit/e59ab3cc0a9e37a5180768a238f7b8e9e63ae0de))
* **orders:** reset index on collection ([5e92f23](https://github.com/myparcelnl/prestashop/commit/5e92f23ad1bd845ddc9b979fa8afaa89d56d1321))
* **orders:** throw error on getting nonexistent order ([f0921ee](https://github.com/myparcelnl/prestashop/commit/f0921eea4ee2b618c614b576def372e835ad9674))
* **orders:** trim whitespace in person field ([2ab31a4](https://github.com/myparcelnl/prestashop/commit/2ab31a497c2f4bb625c6b6fe7e9e564b6c8fa99e))
* pass dev mode to bootstrapper ([ca5d58b](https://github.com/myparcelnl/prestashop/commit/ca5d58bbd74755d46b762893ac806c3f2df4c3db))
* **products:** fix product settings logic and migration ([96734a1](https://github.com/myparcelnl/prestashop/commit/96734a132c7844e66c17b0a875387e2ed7c251e8))
* **products:** fix saving product settings ([b6e3c89](https://github.com/myparcelnl/prestashop/commit/b6e3c899927fa1d1cd4550c00af92d6007457468))
* remove deprecation warning ([ade1951](https://github.com/myparcelnl/prestashop/commit/ade19515e4d105de0778553efb6d4881697285e9))
* **scripts:** improve script loading logic ([878403a](https://github.com/myparcelnl/prestashop/commit/878403a54f96589b61478b8c97c7b95c4d1b203d))
* **settings:** improve saving settings ([b313793](https://github.com/myparcelnl/prestashop/commit/b313793d05fe269fb1745573d47f183d9968c7f8))
* **settings:** remove settings that aren't available ([d413ff1](https://github.com/myparcelnl/prestashop/commit/d413ff1c31315ed76b46e03f625bee293baa09f2))
* **upgrade:** fix reference to facade class ([b580ffb](https://github.com/myparcelnl/prestashop/commit/b580ffb0735e5b6b7f80264801ae6b8b1327a588))
* **webhooks:** fix webhooks ([dad3abc](https://github.com/myparcelnl/prestashop/commit/dad3abc175e181661dfa2816e3bdc082f8cf2970))


### :sparkles: New Features

* **admin:** improve admin component appearance ([e97ccc3](https://github.com/myparcelnl/prestashop/commit/e97ccc362548d486814079cb08aeb15364e04b05))
* **admin:** improve look of components ([2df8009](https://github.com/myparcelnl/prestashop/commit/2df8009bf016e6e9ecc560215f90ff27b9e90f61))
* **carriers:** add carrier logos ([1ae56e6](https://github.com/myparcelnl/prestashop/commit/1ae56e6de6f50687dbd0d3a83ca5a135e48219fb))
* **carriers:** allow changing name and delivery speed texts ([ea19255](https://github.com/myparcelnl/prestashop/commit/ea192553985c81174d70f441687c0a6ff583ff36))
* **checkout:** calculate shipping costs per carrier ([e03a380](https://github.com/myparcelnl/prestashop/commit/e03a38035696f3bdb5bdfbc289c2245d6014e729))
* **deps:** upgrade @myparcel-pdk/* ([36cdc62](https://github.com/myparcelnl/prestashop/commit/36cdc628e9b3f1bc1512bc54f42a0a6295d90444))
* **deps:** upgrade myparcelnl/pdk to v2.30.2 ([885ead1](https://github.com/myparcelnl/prestashop/commit/885ead10dd6ee0b04a495f2bc35bf6a4fd5ae0c8))
* **deps:** upgrade myparcelnl/pdk to v2.31.0 ([f830d7b](https://github.com/myparcelnl/prestashop/commit/f830d7b65c1b2d8bf64a8b2f29b785dcf8dffa34))
* **fulfilment:** add order notes ([ae98602](https://github.com/myparcelnl/prestashop/commit/ae986020775968d7c6927aaf5b5339d654488aa7))
* implement pdk ([c497eb8](https://github.com/myparcelnl/prestashop/commit/c497eb82bbf8f2f1aa4429d43879624e2a2c8bfc))
* **order-grid:** add bulk order actions ([53b2a63](https://github.com/myparcelnl/prestashop/commit/53b2a63805e9f3abff782ae1cde46f8ef8d1b405))
* **order:** implement updating order notes ([935955d](https://github.com/myparcelnl/prestashop/commit/935955dbdf0a8d3a585a11fe8b952f99d227afa9))
* **orders:** implement automatic order status updates ([9af8725](https://github.com/myparcelnl/prestashop/commit/9af8725a566d0d66555d694f3fc54ce7ec5f1276))
* **settings:** enable/disable carriers based on settings ([7f558a4](https://github.com/myparcelnl/prestashop/commit/7f558a4c098743c57f318927ff6f09469d276158))
* **settings:** implement shipping method repository ([045a668](https://github.com/myparcelnl/prestashop/commit/045a66834de52e241d366c1a896dfa04119a94c0))

### [1.8.1](https://github.com/myparcelnl/prestashop/compare/v1.8.0...v1.8.1) (2023-02-08)


### :bug: Bug Fixes

* fix fatal error in weight service ([3b0eb49](https://github.com/myparcelnl/prestashop/commit/3b0eb498bdf6f4e92c4d74be73af1ffc3134f8ab)), closes [#209](https://github.com/myparcelnl/prestashop/issues/209)

## [1.8.0](https://github.com/myparcelnl/prestashop/compare/v1.7.2...v1.8.0) (2023-02-06)


### :bug: Bug Fixes

* allow upgrade from 1.3.0 without errors ([#211](https://github.com/myparcelnl/prestashop/issues/211)) ([fbfaef6](https://github.com/myparcelnl/prestashop/commit/fbfaef606110ce5fa27d25d494694e9c83b964f0))
* fix actions on cards and modals ([#208](https://github.com/myparcelnl/prestashop/issues/208)) ([0e9de9c](https://github.com/myparcelnl/prestashop/commit/0e9de9c3a8a39fd8b2bca88ae24ca1d26d443bef))


### :sparkles: New Features

* add insurance options for eu shipments ([#206](https://github.com/myparcelnl/prestashop/issues/206)) ([e934728](https://github.com/myparcelnl/prestashop/commit/e934728f09272dd880562269f3db86ecbd70896b))

### [1.7.2](https://github.com/myparcelnl/prestashop/compare/v1.7.1...v1.7.2) (2022-12-06)


### :bug: Bug Fixes

* **modal:** show package options ([#200](https://github.com/myparcelnl/prestashop/issues/200)) ([b376131](https://github.com/myparcelnl/prestashop/commit/b37613151a3c81604965cf092b66e4ce7d78d254))
* **shipments:** distinguish return labels correctly ([#201](https://github.com/myparcelnl/prestashop/issues/201)) ([917df89](https://github.com/myparcelnl/prestashop/commit/917df892ec4ec486f8e280e0a58668362fce1dd4))

### [1.7.1](https://github.com/myparcelnl/prestashop/compare/v1.7.0...v1.7.1) (2022-11-10)


### :bug: Bug Fixes

* allow export with older delivery date ([#189](https://github.com/myparcelnl/prestashop/issues/189)) ([de4692b](https://github.com/myparcelnl/prestashop/commit/de4692b9fdc55c431edd672424e36f13595aa1b1))
* correct package type at export ([#187](https://github.com/myparcelnl/prestashop/issues/187)) ([5bede86](https://github.com/myparcelnl/prestashop/commit/5bede867f137cccb8a677929dcc30dc911c6ffc5))
* fix package type selection ([#197](https://github.com/myparcelnl/prestashop/issues/197)) ([0bdba0c](https://github.com/myparcelnl/prestashop/commit/0bdba0c83db19728231c71314784c235619d1c89)), closes [#186](https://github.com/myparcelnl/prestashop/issues/186)
* update barcode after printing ([#198](https://github.com/myparcelnl/prestashop/issues/198)) ([b60f464](https://github.com/myparcelnl/prestashop/commit/b60f464fc9ee5578bc92888e32528a2de86378b9))

## [1.7.0](https://github.com/myparcelnl/prestashop/compare/v1.6.3...v1.7.0) (2022-08-08)


### :sparkles: New Features

* add real multicollo ([#165](https://github.com/myparcelnl/prestashop/issues/165)) ([1d4243e](https://github.com/myparcelnl/prestashop/commit/1d4243e051c08257e64ddb844f0704bad2873398))
* option concept shipments ([#174](https://github.com/myparcelnl/prestashop/issues/174)) ([d3fe89e](https://github.com/myparcelnl/prestashop/commit/d3fe89e3c66ff0d82f68ee5cc85a0788b8e6bbe4))


### :bug: Bug Fixes

* allow deliveryoptions changing in admin orderview ([#181](https://github.com/myparcelnl/prestashop/issues/181)) ([1d0d9ae](https://github.com/myparcelnl/prestashop/commit/1d0d9ae0d90ef704e2236de60c551d6ea2a16e6f))
* check countries before adding multicollo ([#166](https://github.com/myparcelnl/prestashop/issues/166)) ([d9405fd](https://github.com/myparcelnl/prestashop/commit/d9405fd209ade232774e85ab80d4688c0ab90804))
* checkout uses specifically set myparcelconfig ([#175](https://github.com/myparcelnl/prestashop/issues/175)) ([9783276](https://github.com/myparcelnl/prestashop/commit/9783276be5236f831b5899b0f2aa0ef73c752d0a))
* correct package type at label create form ([#182](https://github.com/myparcelnl/prestashop/issues/182)) ([e124ca9](https://github.com/myparcelnl/prestashop/commit/e124ca9f4be96c3f80e9e7292577c1cf1444b50f))
* empty variables abstractconsignment ([#178](https://github.com/myparcelnl/prestashop/issues/178)) ([2ed5dbe](https://github.com/myparcelnl/prestashop/commit/2ed5dbe32f59baee570710cb32bcb42f9f207447))
* fix exporting return shipments ([#176](https://github.com/myparcelnl/prestashop/issues/176)) ([70513e7](https://github.com/myparcelnl/prestashop/commit/70513e776de92f558b13d1f5a6d8308a97f693a2))
* fix multicollo label refresh ([#183](https://github.com/myparcelnl/prestashop/issues/183)) ([44fac82](https://github.com/myparcelnl/prestashop/commit/44fac829881f86a7c772c62a5766223d04e2fc2d))
* fix type error on ordering ([#169](https://github.com/myparcelnl/prestashop/issues/169)) ([7b33545](https://github.com/myparcelnl/prestashop/commit/7b33545d411b417d1c359d79ce62163e7b3cfb08))

### [1.6.3](https://github.com/myparcelnl/prestashop/compare/v1.6.2...v1.6.3) (2022-07-15)


### :bug: Bug Fixes

* correct date when exporting label ([#180](https://github.com/myparcelnl/prestashop/issues/180)) ([51c42a8](https://github.com/myparcelnl/prestashop/commit/51c42a885f8e46e5f87ff684b74a5694bc6fa94f))

### [1.6.2](https://github.com/myparcelnl/prestashop/compare/v1.6.1...v1.6.2) (2022-06-27)


### :bug: Bug Fixes

* retain pickup point and insurance during checkout ([#172](https://github.com/myparcelnl/prestashop/issues/172)) ([9a62786](https://github.com/myparcelnl/prestashop/commit/9a62786f09306c9ffdc64349d12269002b0e48e5))

### [1.6.1](https://github.com/myparcelnl/prestashop/compare/v1.6.0...v1.6.1) (2022-06-13)


### :bug: Bug Fixes

* allow checkout with insurance for all carriers ([#159](https://github.com/myparcelnl/prestashop/issues/159)) ([c617db4](https://github.com/myparcelnl/prestashop/commit/c617db4e989e0e8cb7c80feb7c6b1edf8c8e7fbb))
* show ordergrid with left joined carrier table ([#157](https://github.com/myparcelnl/prestashop/issues/157)) ([c641ec6](https://github.com/myparcelnl/prestashop/commit/c641ec6eb4bdb9dbdeab6106ba13728821d70ae9))

## [1.6.0](https://github.com/myparcelnl/prestashop/compare/v1.5.5...v1.6.0) (2022-06-02)


### :bug: Bug Fixes

* accomodate deleted customer ([#147](https://github.com/myparcelnl/prestashop/issues/147)) ([e3338ef](https://github.com/myparcelnl/prestashop/commit/e3338efa677c754c34046ff28bf0984b5dc4b298)), closes [#143](https://github.com/myparcelnl/prestashop/issues/143)
* digital stamp range weight ignored ([#155](https://github.com/myparcelnl/prestashop/issues/155)) ([b7edbee](https://github.com/myparcelnl/prestashop/commit/b7edbee9be137bf446d2638aaa722989276565ae))


### :sparkles: New Features

* add translations for delivery options ([#152](https://github.com/myparcelnl/prestashop/issues/152)) ([debffe3](https://github.com/myparcelnl/prestashop/commit/debffe33aea213e28a979104b7b5d0feeb97831c))
* allow insurance from nl to be ([#150](https://github.com/myparcelnl/prestashop/issues/150)) ([b9334bc](https://github.com/myparcelnl/prestashop/commit/b9334bcc1b251a4be7a3bf4d6eca25478930eaf2))
* distribute weight over multiple labels ([#145](https://github.com/myparcelnl/prestashop/issues/145)) ([90902b6](https://github.com/myparcelnl/prestashop/commit/90902b629bbfebbe9f569a2ae619ed58f4635f6e))

### [1.5.5](https://github.com/myparcelnl/prestashop/compare/v1.5.4...v1.5.5) (2022-05-17)


### :bug: Bug Fixes

* fix error on exporting labels ([#154](https://github.com/myparcelnl/prestashop/issues/154)) ([d4bb21c](https://github.com/myparcelnl/prestashop/commit/d4bb21c678ee1a2cee21a3ec2db484203d1b8596)), closes [#151](https://github.com/myparcelnl/prestashop/issues/151)

### [1.5.4](https://github.com/myparcelnl/prestashop/compare/v1.5.3...v1.5.4) (2022-04-22)


### :bug: Bug Fixes

* fix type errors caused by unsuccessful queries ([#135](https://github.com/myparcelnl/prestashop/issues/135)) ([5d45762](https://github.com/myparcelnl/prestashop/commit/5d457624eb7840588f79830dce017c7d3b694940))

### [1.5.3](https://github.com/myparcelnl/prestashop/compare/v1.5.2...v1.5.3) (2022-04-19)


### :bug: Bug Fixes

* error when performing search with no results ([#139](https://github.com/myparcelnl/prestashop/issues/139)) ([e7ac29d](https://github.com/myparcelnl/prestashop/commit/e7ac29db1a599db3c7ede8fb7befdb7be09dc9eb))
* fatal error due to nonexistent listener ([#141](https://github.com/myparcelnl/prestashop/issues/141)) ([7aca74d](https://github.com/myparcelnl/prestashop/commit/7aca74da87fd92224a99953903bb27734a693b90))
* fix insurance settings ([#128](https://github.com/myparcelnl/prestashop/issues/128)) ([0de46ae](https://github.com/myparcelnl/prestashop/commit/0de46ae54dee13702f51aeacaf342a609ad2193f))
* only send status change email if status actually changed ([#124](https://github.com/myparcelnl/prestashop/issues/124)) ([cb06ff1](https://github.com/myparcelnl/prestashop/commit/cb06ff1f2453e671503f3a3cf0681bc855ea29b5))
* resolve scripts to paths based on site url ([#134](https://github.com/myparcelnl/prestashop/issues/134)) ([a102ea3](https://github.com/myparcelnl/prestashop/commit/a102ea3771db43b46aae60345a6afe2a88b6bfa5))

### [1.5.2](https://github.com/myparcelnl/prestashop/compare/v1.5.1...v1.5.2) (2022-04-12)


### :bug: Bug Fixes

* retain pickup point during checkout ([#136](https://github.com/myparcelnl/prestashop/issues/136)) ([9c620e8](https://github.com/myparcelnl/prestashop/commit/9c620e84d4292a680ddd243380943ad6a8da5c26))

### [1.5.1](https://github.com/myparcelnl/prestashop/compare/v1.5.0...v1.5.1) (2022-03-24)


### :bug: Bug Fixes

* symfony route error on upgrading to 1.5.0 ([#131](https://github.com/myparcelnl/prestashop/issues/131)) ([28602ca](https://github.com/myparcelnl/prestashop/commit/28602ca776cdc5e47fbe06839021b8b501feea93)), closes [#129](https://github.com/myparcelnl/prestashop/issues/129)

## [1.5.0](https://github.com/myparcelnl/prestashop/compare/v1.4.1...v1.5.0) (2022-03-22)


### :bug: Bug Fixes

* **delivery-options:** fix not showing after switching shipping method ([#113](https://github.com/myparcelnl/prestashop/issues/113)) ([45590a9](https://github.com/myparcelnl/prestashop/commit/45590a9df8fb8d7b5db12b1b0f379946dcc8523f)), closes [#105](https://github.com/myparcelnl/prestashop/issues/105)
* **delivery-options:** no pickup when not chosen ([#118](https://github.com/myparcelnl/prestashop/issues/118)) ([ac86052](https://github.com/myparcelnl/prestashop/commit/ac860528cdab590ad490abd9ceb250a6b46c4221))
* fix error retrieving delivery settings ([#109](https://github.com/myparcelnl/prestashop/issues/109)) ([a28c116](https://github.com/myparcelnl/prestashop/commit/a28c11626563a0526b24bfaa671a971ba2a8d433))


### :sparkles: New Features

* improve entire admin backoffice ([#104](https://github.com/myparcelnl/prestashop/issues/104)) ([167a1d3](https://github.com/myparcelnl/prestashop/commit/167a1d36e503fcb71d1a4c8d5fb6e659319116f1))

### [1.4.1](https://github.com/myparcelnl/prestashop/compare/v1.4.0...v1.4.1) (2021-12-21)


### :bug: Bug Fixes

* fix accessibility error on getInstance in some cases ([#102](https://github.com/myparcelnl/prestashop/issues/102)) ([83b982c](https://github.com/myparcelnl/prestashop/commit/83b982cefea1eb48929803aaf873ba3cac652717)), closes [#101](https://github.com/myparcelnl/prestashop/issues/101)

## [1.4.0](https://github.com/myparcelnl/prestashop/compare/v1.3.0...v1.4.0) (2021-12-20)


### :sparkles: New Features

* allow exporting to myparcel on orders not linked to a carrier ([#77](https://github.com/myparcelnl/prestashop/issues/77)) ([788e6fb](https://github.com/myparcelnl/prestashop/commit/788e6fb9fb200f37bc286bd3edf07e08f2afa4f8))
* use default export settings when creating shipment ([#63](https://github.com/myparcelnl/prestashop/issues/63)) ([9546ec5](https://github.com/myparcelnl/prestashop/commit/9546ec55880314671ac2a3e0c5c92363ab26ca58))


### :bug: Bug Fixes

* always add client emailaddress to consignment in BE ([#82](https://github.com/myparcelnl/prestashop/issues/82)) ([df9489d](https://github.com/myparcelnl/prestashop/commit/df9489df1567019e46079ec8ca28cab22102f503))
* auto-translate older carrier ids to the current ones ([#60](https://github.com/myparcelnl/prestashop/issues/60)) ([b7ae0f6](https://github.com/myparcelnl/prestashop/commit/b7ae0f63a4062cc2b10befc032114bb6403bbf47))
* column `extra_options` for install migration ([a8774c8](https://github.com/myparcelnl/prestashop/commit/a8774c894227325a87f1a900d773c5f570cbd675))
* export company name to label ([#81](https://github.com/myparcelnl/prestashop/issues/81)) ([e6c97b6](https://github.com/myparcelnl/prestashop/commit/e6c97b621086b8a608f5e76c527a908bc497a890))
* order status not updating after printing in some cases ([#75](https://github.com/myparcelnl/prestashop/issues/75)) ([3adc1e8](https://github.com/myparcelnl/prestashop/commit/3adc1e86b575caf3d14f90e5ce820e0c5b9b5add))
* **regression:** delete pickup express option from carrier form ([#90](https://github.com/myparcelnl/prestashop/issues/90)) ([2ed04e2](https://github.com/myparcelnl/prestashop/commit/2ed04e26874d0205adc110e9454a5c51762dd269))
* **regression:** fix errors in order list ([#91](https://github.com/myparcelnl/prestashop/issues/91)) ([bd99240](https://github.com/myparcelnl/prestashop/commit/bd99240fe4c3985fb36339d10732facefd40678a))
* **regression:** get correct dropoff configuration in delivery options ([#87](https://github.com/myparcelnl/prestashop/issues/87)) ([69a7f3e](https://github.com/myparcelnl/prestashop/commit/69a7f3e4f93afc044fc4abf1ff0360f384c574e4))
* **regression:** pass cutoff time to delivery options ([#88](https://github.com/myparcelnl/prestashop/issues/88)) ([edca384](https://github.com/myparcelnl/prestashop/commit/edca38438a234766223b601c0332b8f502ae5b10))
* **regression:** price standard delivery falls back to 0 ([#86](https://github.com/myparcelnl/prestashop/issues/86)) ([af884a1](https://github.com/myparcelnl/prestashop/commit/af884a1087d53c702a5a443d1ca0b88af6ecf73b))
* **regression:** show fallback strings in delivery options ([#85](https://github.com/myparcelnl/prestashop/issues/85)) ([79b712e](https://github.com/myparcelnl/prestashop/commit/79b712e9cc002349b357c9e6af2d4adae362642b))
* **regression:** stabilize order status change ([#89](https://github.com/myparcelnl/prestashop/issues/89)) ([cb8bf47](https://github.com/myparcelnl/prestashop/commit/cb8bf47bb10ce9eb2f6cec6946188e5b9cf22dde))
* save several days in the Exception schedule ([#59](https://github.com/myparcelnl/prestashop/issues/59)) ([915754d](https://github.com/myparcelnl/prestashop/commit/915754dceb093db86271132f55c340bf8a49998d))
* show product once on row label ([#74](https://github.com/myparcelnl/prestashop/issues/74)) ([4a28765](https://github.com/myparcelnl/prestashop/commit/4a28765cf8fa96f62511bfce5c201e7f8c43bf07))
* updated logo and changed plugin author to 'MyParcel' ([#84](https://github.com/myparcelnl/prestashop/issues/84)) ([ec42f5d](https://github.com/myparcelnl/prestashop/commit/ec42f5d6eb06b7cf8d7fb6b09fb43ebcb42596b6))
* use correct table reference ([#76](https://github.com/myparcelnl/prestashop/issues/76)) ([79de1eb](https://github.com/myparcelnl/prestashop/commit/79de1eb50506701a8d8f852e15ab04f0c7470906))
* use rest of world countries from SDK ([2bbd0fa](https://github.com/myparcelnl/prestashop/commit/2bbd0fab7d26b0d4baf237b58cc590fba95a8a15))

## [1.3.0](https://github.com/myparcelnl/prestashop/compare/v1.2.0...v1.3.0) (2021-11-11)


### :sparkles: New Features

* add track trace in default prestashop field ([a274f3f](https://github.com/myparcelnl/prestashop/commit/a274f3fd16311fdfe65484e6571efb6b3f4c3add))
* export region field ([#58](https://github.com/myparcelnl/prestashop/issues/58)) ([0950fae](https://github.com/myparcelnl/prestashop/commit/0950fae6575e3066eb90269eae978fdbf872c4d5))


### :bug: Bug Fixes

* add second address line to first address line for delivery options ([#61](https://github.com/myparcelnl/prestashop/issues/61)) ([0ed533b](https://github.com/myparcelnl/prestashop/commit/0ed533baa5b06c0925beb84905e8f03552284cab))
* allow insurance options only for package type package ([#62](https://github.com/myparcelnl/prestashop/issues/62)) ([af7497c](https://github.com/myparcelnl/prestashop/commit/af7497c11d1445be73fa106fb791f7b39b18776b))
* correct 1.1.2 upgrade ([#52](https://github.com/myparcelnl/prestashop/issues/52)) ([4c33988](https://github.com/myparcelnl/prestashop/commit/4c3398822ace63887250af809f7977a55202317a))
* dpz weight classes automatically selected and retained ([#53](https://github.com/myparcelnl/prestashop/issues/53)) ([e398582](https://github.com/myparcelnl/prestashop/commit/e398582dc191ea0ef7e9ebbc30b70b6a4853841a))
* fix delivery options in frontend not loading fully sometimes ([#54](https://github.com/myparcelnl/prestashop/issues/54)) ([2ef3009](https://github.com/myparcelnl/prestashop/commit/2ef300906429c75424cd1a80b5cb4b31f7191d6c))
* fix delivery options not being persisted in ps 1.7.8.0 ([#57](https://github.com/myparcelnl/prestashop/issues/57)) ([d625781](https://github.com/myparcelnl/prestashop/commit/d625781411a4ea551dbf7af998daee3eb5fdd88e)), closes [#49](https://github.com/myparcelnl/prestashop/issues/49)
* fix large format and return not being exported ([#64](https://github.com/myparcelnl/prestashop/issues/64)) ([17c1e4d](https://github.com/myparcelnl/prestashop/commit/17c1e4dd5698d0ece6079e4b1c8c4c66c7d1dd47))
* fix track trace emails not being sent ([#66](https://github.com/myparcelnl/prestashop/issues/66)) ([dcebc16](https://github.com/myparcelnl/prestashop/commit/dcebc16651b43f1c6c4dfc899e96fa25653e0128))
* fix user agent not being sent correctly ([#65](https://github.com/myparcelnl/prestashop/issues/65)) ([801f00b](https://github.com/myparcelnl/prestashop/commit/801f00b9e5f95c080de1f2451b6d6bf94d9344c0))
* fix user agents not being sent ([607adc9](https://github.com/myparcelnl/prestashop/commit/607adc93797f49079662fb013bb06448f791f56a))
* make possible to export without insurance from order detail page ([2610e67](https://github.com/myparcelnl/prestashop/commit/2610e674d1cd7165e1c0931583580ea80025308d))
* migration will be to version 1.3.0 ([199a078](https://github.com/myparcelnl/prestashop/commit/199a078ff57ba128666d529a8f5da79cf45b9625))
* remove hard coded carrier from email templates and improve language ([23595c9](https://github.com/myparcelnl/prestashop/commit/23595c905872cfcd570c83f7cfb51e1595f8f82b))
* set nextDeliveryDate when deliveryOptions are empty ([#47](https://github.com/myparcelnl/prestashop/issues/47)) ([20ab7a7](https://github.com/myparcelnl/prestashop/commit/20ab7a7e4e8f4b120f0d67b4f89e0b901462a993))
* shipment status order for digital stamp ([20dbf91](https://github.com/myparcelnl/prestashop/commit/20dbf91613f42924c1956bf4825a92a84c8a49e1))
* update orderlabel status during export ([03a7690](https://github.com/myparcelnl/prestashop/commit/03a7690b0b3a7173c7c1408f494679c4cc27a63c))

## [1.2.0](https://github.com/myparcelnl/prestashop/compare/v1.1.3...v1.2.0) (2021-10-04)


### :sparkles: New Features

* add surcharge option to delivery options ([5d9e711](https://github.com/myparcelnl/prestashop/commit/5d9e711b7acfcc07e39759d534992ca579e61342))


### :bug: Bug Fixes

* cutofftime and dropoffdelay work according to current specifications ([48baf5e](https://github.com/myparcelnl/prestashop/commit/48baf5e1e0d633aea518b4533ae0d60709833ddf))
* fix error when loading order which has delivery options ([56722a4](https://github.com/myparcelnl/prestashop/commit/56722a44b4ad2c227f56a762e5cadacc055f4af2))
* fix status change webhook not triggering sometimes ([954bc18](https://github.com/myparcelnl/prestashop/commit/954bc187360b1fa96f3385bd05e085bd45c8a9db))
* make surcharge mode work as expected ([77ed3b7](https://github.com/myparcelnl/prestashop/commit/77ed3b7b675fc0d48a0275b24b97417c1dfcc269))
* no order found error ([b8a6eea](https://github.com/myparcelnl/prestashop/commit/b8a6eea1bf899981d976ba3c802e684b2128eb1f)), closes [#7](https://github.com/myparcelnl/prestashop/issues/7)
* open new tab and request inline pdf correctly according to setting ([1f1d154](https://github.com/myparcelnl/prestashop/commit/1f1d1545158a28ff1ed406c1b2b2fed27e60faf5))

### [1.1.2](https://github.com/myparcelnl/prestashop/compare/v1.1.3...v1.1.2) (2021-09-30)


### :sparkles: New Features

* add surcharge option to delivery options ([baebd38](https://github.com/myparcelnl/prestashop/commit/baebd381626d09bbe7ee815ab4e8e0891396c140))


### :bug: Bug Fixes

* cutofftime and dropoffdelay work according to current specifications ([572c778](https://github.com/myparcelnl/prestashop/commit/572c7789b152c125ecc888ef03553b4d16c4edf3))
* no order found error ([3a7e68c](https://github.com/myparcelnl/prestashop/commit/3a7e68c94fa18a53413b55544bc0a5097d16fd31)), closes [#7](https://github.com/myparcelnl/prestashop/issues/7)
* open new tab and request inline pdf correctly according to setting ([3ce9d87](https://github.com/myparcelnl/prestashop/commit/3ce9d87a1792f1a6d1aec68aa3bca129b5c297d9))

### [1.1.3](https://github.com/myparcelnl/prestashop/compare/v1.1.2...v1.1.3) (2021-09-01)


### :bug: Bug Fixes

* export ROW and EU shipments ([50cb1e7](https://github.com/myparcelnl/prestashop/commit/50cb1e76d9bc2d40cdea37277efdbcec2a39282e))
* use default HS code and country of origin for products that lack them ([a3b5da0](https://github.com/myparcelnl/prestashop/commit/a3b5da025a8744bd364cc5237f1c3379b8362ce4))

### [1.1.2](https://github.com/myparcelnl/prestashop/compare/v1.1.1...v1.1.2) (2021-08-19)


### :bug: Bug Fixes

* missing carrier type for upgrades ([caff3c9](https://github.com/myparcelnl/prestashop/commit/caff3c93d20be2a6713da0df76191fe1ca05a137))

### [1.1.1](https://github.com/myparcelnl/prestashop/compare/v1.1.0...v1.1.1) (2021-08-17)


### :sparkles: New Features

* add cart override ([e510832](https://github.com/myparcelnl/prestashop/commit/e510832e2e59f47ea7b16e25929400830f9dfe12))
* add postnl ([add4716](https://github.com/myparcelnl/prestashop/commit/add47169f1c58d44f9496aab1c977875e8849216))
* add ps carriers cost ([1f7e70e](https://github.com/myparcelnl/prestashop/commit/1f7e70e5be0b326bdcb0e575bdef47392b328521))
* add ps_carriers in the configuration ([fa88381](https://github.com/myparcelnl/prestashop/commit/fa883813afcc1794c995173618797673cf87c81b))
* added carriertype ([ec6f8af](https://github.com/myparcelnl/prestashop/commit/ec6f8af16b0b5673ef765fb958f221ea86e7ac60))
* allow to update carrier ([5370b87](https://github.com/myparcelnl/prestashop/commit/5370b871ff4ac76180b86f038a0c41028488a70c))
* override DeliveryOptionsFinder for prestashop carriers ([946c20d](https://github.com/myparcelnl/prestashop/commit/946c20d867a14dd2d8f1139d752d977faf01a32f))
* show carrier input if no selected ps carrier ([a36bf03](https://github.com/myparcelnl/prestashop/commit/a36bf03ed23f137a7a2b8c771b1816010f0b95d3))
* update method for carrier config ([7f24f4b](https://github.com/myparcelnl/prestashop/commit/7f24f4b676c8de4bf954eb40a03446f4fa7effa6))
* use carrierType ([6416306](https://github.com/myparcelnl/prestashop/commit/64163063a02842e5289c65bbd9d32b9a1af4d720))


### :bug: Bug Fixes

* 0 problem ([325ed7a](https://github.com/myparcelnl/prestashop/commit/325ed7aa6b1b0b511734db73cb9106c008c145df))
* add new custom carrier/ add PS carrier, ([1a3a6bf](https://github.com/myparcelnl/prestashop/commit/1a3a6bfd6cdaca3f1ec011dfcf3300c7d29b439a))
* delivery option removed ([21da4a2](https://github.com/myparcelnl/prestashop/commit/21da4a25788b4d438fd2e275e6206f440d20a2a6))
* deliverydays_windows ([0c2d1e8](https://github.com/myparcelnl/prestashop/commit/0c2d1e823db79e98ccb27306c5a8fa671f6d0869))
* export with the correct delivery type ([2dec77e](https://github.com/myparcelnl/prestashop/commit/2dec77ec4b9a6b6535014e9e003f0a84192edd19))
* if no carrier ([80aea3f](https://github.com/myparcelnl/prestashop/commit/80aea3f17950b5c1ca083d54f6eeb34ae7a1c5f5))
* import validation and db ([a354615](https://github.com/myparcelnl/prestashop/commit/a3546157e7d509b3ac83306237a5fd1eaa03ecab))
* load OrderLabel that is not in namespace anymore ([1b5a04a](https://github.com/myparcelnl/prestashop/commit/1b5a04af08806f58b07057d06f040e6ecd4da8ed))
* new carrier with redirect and flash message ([79bcc8e](https://github.com/myparcelnl/prestashop/commit/79bcc8e603c5d4a97049cae21e45c157912e2953))
* new carrier with redirect and flash message ([fb58ba7](https://github.com/myparcelnl/prestashop/commit/fb58ba749d8740afae5486bd6914a1ac2b69662e))
* newly added carrier ([d430d37](https://github.com/myparcelnl/prestashop/commit/d430d379b9557d143fa9c1ab98d3b0a55a4fa1c4))
* pass on the weights for all shipments ([1740f85](https://github.com/myparcelnl/prestashop/commit/1740f85386d97fe515ed93f9ebe61657be4aabb0))
* prestashop PrestaShopDatabaseException  problem ([644e078](https://github.com/myparcelnl/prestashop/commit/644e07807c23bebf41f818fdf1174297116048fa))
* put back insert for new carrier ([dbbbf39](https://github.com/myparcelnl/prestashop/commit/dbbbf39dc4a27f241d799e7e15729ad0f802319a))
* remove console.log ([680daf6](https://github.com/myparcelnl/prestashop/commit/680daf625eb677b08336e70566673ba22112de8c))
* remove legacy 'OrderLabel' classmap ([f4444b3](https://github.com/myparcelnl/prestashop/commit/f4444b3f0082ed32106c12729ae0b393c6705206))
* remove override ([04bc88a](https://github.com/myparcelnl/prestashop/commit/04bc88ab94925a9a84f4b1173eef0042167d0fc1))
* remove unused config ([34eb129](https://github.com/myparcelnl/prestashop/commit/34eb12999d6f2eb0dbcc3546177581caa2bd4da9))
* remove validation ([abe02ab](https://github.com/myparcelnl/prestashop/commit/abe02abf5cd8d896384ef96f6dbab61c3fb7de07))
* set ps carrier as myparcel carrier ([8c67199](https://github.com/myparcelnl/prestashop/commit/8c67199a0436aeee1f68e67555c8ad7d5e634b70))
* set to static ([f7bdcae](https://github.com/myparcelnl/prestashop/commit/f7bdcae89501381cc052e7b2cd38348a02a627ff))
* update carrierType ([6fceb82](https://github.com/myparcelnl/prestashop/commit/6fceb82341a0d0e849506b18effb6ddcd875c59e))
* Update readme.md ([deb635b](https://github.com/myparcelnl/prestashop/commit/deb635b83aaaa90f4b20af84ac229383c31118ae))
* use the track trace link which is already known in the order ([198b0b4](https://github.com/myparcelnl/prestashop/commit/198b0b4bfa7c5e4563c452e34eb2a435a85165fb))
* webhook not changing order status ([aae145f](https://github.com/myparcelnl/prestashop/commit/aae145f77755ee88c9b8a68616e00194cf8e7920))

## [1.1.0](https://github.com/myparcelnl/prestashop/compare/v1.0.7...v1.1.0) (2021-05-28)


### :sparkles: New Features

* add new carrier ([4685332](https://github.com/myparcelnl/prestashop/commit/4685332dbb125619cd6660bf999ee361ad6cc544))


### :bug: Bug Fixes

* remove search ([5b0bc01](https://github.com/myparcelnl/prestashop/commit/5b0bc01e2e9ec4f4448638087a2fb176cf8266a3))

### [1.0.7](https://github.com/myparcelnl/prestashop/compare/v1.0.6...v1.0.7) (2021-05-21)


### :bug: Bug Fixes

* add tax to prices, remove unused method calls ([aaeb3b0](https://github.com/myparcelnl/prestashop/commit/aaeb3b0002d097b99e1604de7fc547a178bcfc01))


### Performance Improvements

* remove comments ([bbb9333](https://github.com/myparcelnl/prestashop/commit/bbb9333bc257c4f877a5b78bbefbe3835bbcdf6e))
* remove variable, add method getShippingoption ([01a8557](https://github.com/myparcelnl/prestashop/commit/01a8557940b4305282584a94da7f314c45974186))

### [1.0.6](https://github.com/myparcelnl/prestashop/compare/v1.0.5...v1.0.6) (2021-05-14)


### :bug: Bug Fixes

* add pSQL in query ([fcf7a6c](https://github.com/myparcelnl/prestashop/commit/fcf7a6c391413413f3b216e6bb376a1fe68a4553))

### [1.0.5](https://github.com/myparcelnl/prestashop/compare/v1.0.4...v1.0.5) (2021-05-07)


### :sparkles: New Features

* upgrade to 4.0.0 ([18caea0](https://github.com/myparcelnl/prestashop/commit/18caea01efce310c15d113d55557ac1bb2e046fc))

### [1.0.4](https://github.com/myparcelnl/prestashop/compare/v1.0.3...v1.0.4) (2021-04-30)


### :bug: Bug Fixes

* LabelsColumn defaults ([9132bb5](https://github.com/myparcelnl/prestashop/commit/9132bb5fd76bfd40cc64952e54a11dcc61daa9c5))
* order label column for PS v1.7.7.3+ ([a321b94](https://github.com/myparcelnl/prestashop/commit/a321b94fff0cef5a36a31310fb8215cfabf2bc0c))

### [1.0.3](https://github.com/myparcelnl/prestashop/compare/v1.0.2...v1.0.3) (2021-04-08)


### :bug: Bug Fixes

* **build:** do rename on each job ([78b585f](https://github.com/myparcelnl/prestashop/commit/78b585fdf5f2a94b3ab8f13c0a814a4d193582b5))

### [1.0.2](https://github.com/myparcelnl/prestashop/compare/v1.0.1...v1.0.2) (2021-04-08)


### :sparkles: New Features

* **build:** only ci on tags ([14c7701](https://github.com/myparcelnl/prestashop/commit/14c77016a1d9d7f93aa1421406411637a4d5519f))
* gitlab ci ([6f9f9ee](https://github.com/myparcelnl/prestashop/commit/6f9f9eee151784cb5bfc1a0386e2be8fbcaa3139))


### :bug: Bug Fixes

* **build:** only run on tags ([b61c89d](https://github.com/myparcelnl/prestashop/commit/b61c89ded822115e9e5930a72985a8166bbf9125))
* **build:** php version and npm ([e08c5a6](https://github.com/myparcelnl/prestashop/commit/e08c5a6c0c711a32306e357628eeb6f23a8a5f2c))
* **build:** rename main module file ([a8d858e](https://github.com/myparcelnl/prestashop/commit/a8d858ed75ec26d4db33bc32b240a7d1d72d8eba))
* **build:** replace be to nl ([db5a1ac](https://github.com/myparcelnl/prestashop/commit/db5a1ac04e9edd24fcab610fa46023d622de3052))
* use ps generated url ([8515606](https://github.com/myparcelnl/prestashop/commit/85156063959ef0f48c063e5cd32d26677898957c))

### [1.0.1](https://github.com/myparcelnl/prestashop/compare/v1.0.0...v1.0.1) (2021-03-25)

## 1.0.0 (2021-01-28)
