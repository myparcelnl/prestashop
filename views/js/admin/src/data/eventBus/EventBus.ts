import { DoRequest, doRequest } from '@/services/ajax/doRequest';
import mitt, { Emitter } from 'mitt';
import { isOfType } from '@/utils/type-guard/isOfType';

export enum EventName {
  BUSY = 'BUSY',
  ERROR = 'error',
  RESPONSE = 'response',
}

export interface EmitterRequestData<T> {
  parameters: RequestParameters;
  requestOptions: JQuery.AjaxSettings | null;
  response: T;
  url: string;
}

type EventCallback<T> =
  T extends EventName.BUSY ? (data: EmitterRequestData<boolean>) => void :
    T extends EventName.ERROR ? (data: EmitterRequestData<ErrorResponse>) => void :
      T extends EventName.RESPONSE ? (data: EmitterRequestData<SuccessResponse>) => void :
        (...args: unknown[]) => void;

type EventBusRequest = (
  url: string,
  parameters?: RequestParameters,
  requestOptions?: JQuery.AjaxSettings,
) => Promise<RequestResponse>;

export class EventBus {
  private data: RequestData;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  private emitter: Emitter<Record<EventName, any>> = mitt();

  public get: EventBusRequest = async(url, parameters = {}, requestOptions = {}) => {
    return this.doRequest(url, parameters, { method: 'GET', ...requestOptions });
  };

  public getData(): RequestData {
    return this.data;
  }

  public off<T extends EventName>(event: T, callback?: EventCallback<T>): void {
    this.emitter.off(event, callback);
  }

  public once<T extends EventName>(event: T, callback: EventCallback<T>): void {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const newCallback: EventCallback<T> = (args) => {
      callback(args);
      this.off(event, newCallback);
    };

    this.on(event, newCallback);
  }

  public on<T extends EventName>(event: T, callback: EventCallback<T>): void {
    this.emitter.on(event, callback);
  }

  public post: EventBusRequest = async(url, parameters = {}, requestOptions = {}) => {
    return this.doRequest(url, parameters, {
      method: 'POST',
      ...requestOptions,
      data: this.getRequestData(requestOptions.data),
    });
  };

  public update(data: null | Record<string, unknown>): void {
    this.data = data;
  }

  protected doRequest: DoRequest<RequestResponse> = async(
    url,
    parameters = {},
    requestOptions = {},
  ): Promise<RequestResponse> => {
    const data = { requestOptions, url, parameters };

    this.emit(EventName.BUSY, { response: true, ...data });
    const response = await doRequest(url, parameters, requestOptions);
    this.emit(EventName.BUSY, { response: false, ...data });

    if (isOfType<ErrorResponse>(response, 'errors')) {
      this.emit(EventName.ERROR, { response, ...data });
    }

    if (isOfType<SuccessResponse>(response, 'data')) {
      this.emit(EventName.RESPONSE, { response, ...data });
      return response;
    }
  };

  protected emit(event: EventName, data: EmitterRequestData<SuccessResponse | ErrorResponse | boolean>): void {
    this.emitter.emit(event, data);
  }

  private getRequestData(data: JQuery.AjaxSettings['data']): JQuery.AjaxSettings['data'] {
    let newData = null;

    if (this.data) {
      newData = typeof data === 'string'
        ? data
        : {
          ...this.data,
          ...data,
        };
    }

    return newData;
  }
}
