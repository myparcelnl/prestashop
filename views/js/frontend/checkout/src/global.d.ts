import {type MyParcelDeliveryOptions} from '@myparcel-dev/delivery-options';

declare global {
  type PsCallbackParameters = {deliveryOption: JQuery; event: Event};

  interface Window {
    MyParcelConfig: MyParcelDeliveryOptions.Configuration;
    // eslint-disable-next-line @typescript-eslint/naming-convention
    myparcel_delivery_options_url: string;
    prestashop: {
      on(name: string, callback: (...args: any[]) => void): void;
      once(name: string, callback: (...args: any[]) => void): void;
      themeSelectors: {
        arrowDown: string;
        arrowUp: string;
        cart: {
          actions: string;
          discountCode: string;
          discountName: string;
          displayPromo: string;
          productLineQty: string;
          promoCode: string;
          promoCodeButton: string;
          quickview: string;
          touchspin: string;
        };
        checkout: {
          btn: string;
          carrierExtraContent: string;
          giftCheckbox: string;
          imagesLink: string;
          termsLink: string;
        };
        clear: string;
        contentWrapper: string;
        fileInput: string;
        footer: string;
        listing: {
          activeSearchFilters: string;
          list: string;
          listBottom: string;
          listHeader: string;
          listTop: string;
          product: string;
          searchFilterControls: string;
          searchFilterToggler: string;
          searchFilters: string;
          searchFiltersClearAll: string;
          searchFiltersWrapper: string;
          searchLink: string;
        };
        modal: string;
        modalContent: string;
        notifications: {
          container: string;
          dangerAlert: string;
        };
        order: {
          returnForm: string;
        };
        passwordPolicy: {
          container: string;
          hint: string;
          inputColumn: string;
          progressBar: string;
          requirementLength: string;
          requirementLengthIcon: string;
          requirementScore: string;
          requirementScoreIcon: string;
          strengthText: string;
          template: string;
        };
        product: {
          activeNavClass: string;
          activeTabClass: string;
          activeTabs: string;
          arrows: string;
          cover: string;
          customizationModal: string;
          imagesModal: string;
          modalProductCover: string;
          selected: string;
          tabs: string;
          thumb: string;
          thumbContainer: string;
        };
        touchspin: string;
      };
    };
  }
}
