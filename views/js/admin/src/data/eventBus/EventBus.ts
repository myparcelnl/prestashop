import { DoRequest, doRequest } from '@/services/ajax/doRequest';
import mitt, { Emitter } from 'mitt';
import { isOfType } from '@/utils/type-guard/isOfType';

export enum EventName {
  BUSY = 'busy',
  ERROR = 'error',
  RESPONSE = 'response',
}

export interface EmitterRequestData<T> {
  parameters: RequestParameters;
  requestOptions: JQuery.AjaxSettings | null;
  response: T;
  url: string;
}

export type EventCallbackData<EN extends EventName> =
  EN extends EventName.BUSY ? boolean :
    EN extends EventName.ERROR ? ErrorResponse :
      EN extends EventName.RESPONSE ? SuccessResponse :
        unknown;

export type EventCallback<EN extends EventName> = (data: EmitterRequestData<EventCallbackData<EN>>) => void;

type EventBusRequest = (
  url: string,
  parameters?: RequestParameters,
  requestOptions?: JQuery.AjaxSettings,
) => Promise<RequestResponse>;

export class EventBus {
  private data: RequestData;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  private emitter: Emitter<Record<EventName, any>> = mitt();

  /**
   * Delete all stored data.
   */
  public clear(): void {
    this.data = null;
  }

  public get: EventBusRequest = async(url, parameters = {}, requestOptions = {}) => {
    return this.doRequest(url, parameters, { method: 'GET', ...requestOptions });
  };

  /**
   * Retrieve stored data.
   */
  public getData(): RequestData {
    return this.data;
  }

  public off<T extends EventName>(event: T, callback?: EventCallback<T>): void {
    this.emitter.off(event, callback);
  }

  public once<T extends EventName>(event: T, callback: EventCallback<T>): void {
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

  /**
   * Update stored data.
   */
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
      return;
    }

    this.clear();
    this.emit(EventName.RESPONSE, { response, ...data });
    return response;
  };

  protected emit(event: EventName, data: EmitterRequestData<SuccessResponse | ErrorResponse | boolean>): void {
    this.emitter.emit(event, data);
  }

  private getRequestData(data: JQuery.AjaxSettings['data']): JQuery.AjaxSettings['data'] {
    let newData = data;

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
