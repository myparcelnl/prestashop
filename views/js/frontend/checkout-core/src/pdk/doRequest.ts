export const doRequest = async (endpoint: any) => {
  const query = new URLSearchParams(endpoint.parameters).toString();

  const response = await window.fetch(`${endpoint.baseUrl}/${endpoint.path}?${query}`, {
    method: endpoint.method,
    body: endpoint.body,
  });

  if (response.ok) {
    return response;
  }

  throw new Error('Request failed');
};
