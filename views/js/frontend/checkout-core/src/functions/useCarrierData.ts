import {type MyParcel} from '@myparcel/delivery-options';

interface CarrierData {
  carrier: MyParcel.CarrierIdentifier;
  row: JQuery;
}

const data: CarrierData[] = [];

export const useCarrierData = (): CarrierData[] => {
  if (data.length) {
    return data;
  }

  const dataFields = document.querySelectorAll('.myparcelnl-carrier-data');

  dataFields.forEach((dataField) => {
    const carrier = (dataField?.getAttribute('data-carrier') ?? '') as MyParcel.CarrierIdentifier;

    data.push({
      carrier,
      row: jQuery(dataField).next('.myparcelnl-delivery-options-wrapper') as JQuery,
    });

    dataField.remove();
  });

  return data;
};
