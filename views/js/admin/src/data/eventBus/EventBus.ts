import { DoRequest, doRequest } from '@/services/ajax/doRequest';
import mitt, { Emitter } from 'mitt';
import { isOfType } from '@/utils/type-guard/isOfType';

export enum EventName {
  BUSY = 'BUSY',
  ERROR = 'error',
  RESPONSE = 'response',
}

type EventCallback<T> =
  T extends EventName.BUSY ? (state: boolean) => void :
    T extends EventName.ERROR ? (errorResponse: ErrorResponse) => void :
      T extends EventName.RESPONSE ? (response: SuccessResponse) => void :
        (...args: unknown[]) => void;

type EventBusRequest = (
  url: string,
  parameters?: RequestParameters,
  requestOptions?: JQuery.AjaxSettings,
) => Promise<RequestResponse>;

export class EventBus {
  private data: RequestData;

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
    this.emit(EventName.BUSY, true);
    const response = await doRequest(url, parameters, requestOptions);
    this.emit(EventName.BUSY, false);

    if (isOfType<ErrorResponse>(response, 'errors')) {
      this.emit(EventName.ERROR, response);
    }

    if (isOfType<SuccessResponse>(response, 'data')) {
      this.emit(EventName.RESPONSE, response);
      return response;
    }
  };

  protected emit(event: EventName, data: SuccessResponse | ErrorResponse | string | boolean): void {
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
