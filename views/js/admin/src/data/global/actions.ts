/* eslint-disable max-len,vue/max-len */

export type AdminAction = LabelAction | OrderAction;

export enum LabelAction {
  DELETE = 'delete',
  PRINT = 'print',
  REFRESH = 'refresh',
}

export enum OrderAction {
  EXPORT = 'export',
  EXPORT_PRINT = 'exportPrint',
  GET_SHIPMENT_OPTIONS_CONTEXT = 'getShipmentOptionsContext',
  PRINT = 'print',
  REFRESH_LABELS = 'refreshLabels',
  SAVE_DELIVERY_OPTIONS = 'saveDeliveryOptions',
  CREATE_RETURN_LABEL = 'createReturnLabel',
}

interface LabelIdsData {
  labelIds: string[];
}

export interface ShipmentLabelsData {
  shipmentLabels: ShipmentLabel[];
}

type PrintData = LabelIdsData & {

  /** PDF link or pdf content as base64 encoded string. */
  pdf: string;
};

export type ActionResponse<CK = AdminAction> =
  CK extends LabelAction.DELETE ? ActionSuccessResponse<CK, LabelIdsData> :
    CK extends OrderAction.EXPORT ? ActionSuccessResponse<CK, ShipmentLabelsData> :
      CK extends OrderAction.EXPORT_PRINT ? ActionSuccessResponse<CK, ShipmentLabelsData & PrintData> :
        CK extends LabelAction.PRINT ? ActionSuccessResponse<CK, PrintData> :
          CK extends OrderAction.PRINT ? ActionSuccessResponse<CK, PrintData> :
            CK extends LabelAction.REFRESH ? ActionSuccessResponse<CK, ShipmentLabelsData> :
              CK extends OrderAction.REFRESH_LABELS ? ActionSuccessResponse<CK, ShipmentLabelsData> :
                CK extends OrderAction.CREATE_RETURN_LABEL ? ActionSuccessResponse<CK, ShipmentLabelsData> :
                  never;

export const printActions = [
  LabelAction.PRINT,
  OrderAction.EXPORT_PRINT,
  OrderAction.PRINT,
] as const;

export const modifyLabelActions = [
  LabelAction.DELETE,
  LabelAction.REFRESH,
  OrderAction.EXPORT,
  OrderAction.EXPORT_PRINT,
  OrderAction.REFRESH_LABELS,
  OrderAction.CREATE_RETURN_LABEL,
] as const;
