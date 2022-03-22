type RequestParameters<T = Record<string, undefined | null | string | number | (string | number)[]>> = T;
type RequestData = Record<string, string | RequestData>;

interface ResponseMessage {
  message: string;
}

interface SuccessResponseWithMessages {
  messages: ResponseMessage[];
}

type ActionSuccessResponse<
  Action extends string,
  Data = SuccessResponse,
> = Data & {
  action: Action;
};

interface ErrorResponse {
  errors: ResponseMessage[];
}

type SuccessResponse<D = unknown> = D & Partial<SuccessResponseWithMessages>;

type RequestResponse<D = unknown> = SuccessResponse<D> | void;
