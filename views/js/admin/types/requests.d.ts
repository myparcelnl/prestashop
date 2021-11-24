type RequestParameters<T = Record<string, undefined | null | string | number | (string | number)[]>> = T;
type RequestData = Record<string, string | RequestData>;

interface ErrorResponse {
  errors: { message: string }[];
}

type SuccessResponse<Data = AjaxSuccessResponse['data']> = AjaxSuccessResponse<Data>;

interface ActionSuccessResponse<Action extends string, Data = AjaxSuccessResponse['data']> extends AjaxSuccessResponse<Data> {
  action: Action;
}

type RequestResponse = SuccessResponse<any> | void;

interface AjaxSuccessResponse<Data = any> {
  data: Data;
}

interface AjaxErrorResponse {
  errors: string[];
}

type AjaxResponse = AjaxSuccessResponse | AjaxErrorResponse;
