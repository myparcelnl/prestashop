/** @vitest-environment happy-dom */
import {afterEach, describe, expect, it, vi} from 'vitest';
import {doRequest} from './doRequest';

const endpoint = {
  baseUrl: '/module/myparcelnl',
  path: '',
  method: 'GET',
  body: '',
  headers: {},
  parameters: {action: 'fetch_checkout_context'},
  property: 'context',
};
afterEach(() => vi.restoreAllMocks());

describe('doRequest', () => {
  it('returns parsed endpoint data rather than a Response object', async () => {
    const payload = {
      data: {
        context: [{checkout: {settings: {}, config: {physicalProperties: null}}}],
      },
    };
    vi.spyOn(window, 'fetch').mockResolvedValueOnce(new Response(JSON.stringify(payload)));
    await expect(doRequest(endpoint)).resolves.toEqual(payload);
  });

  it('rejects an unsuccessful HTTP response', async () => {
    vi.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('Unavailable', {status: 503}));
    await expect(doRequest(endpoint)).rejects.toThrow('Request failed');
  });

  it('rejects malformed JSON instead of returning an apparently successful response', async () => {
    vi.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('<html>Unexpected page</html>'));
    await expect(doRequest(endpoint)).rejects.toThrow();
  });
});
