<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Concern;

use Symfony\Component\HttpFoundation\Response;

trait SendsResponse
{
    use HasErrors;

    /**
     * @param  string|array $data
     */
    protected function sendResponse($data = null): Response
    {
        if ($data) {
            $this->setResponse($data);
        }

        $response = $this->response;

        if (is_array($this->response)) {
            $response = $this->encodePdfData($this->response);
        }

        return $this->sendJsonResponse($response ?? []);
    }

    /**
     * @param  string|array $response
     *
     * @return self
     */
    protected function setResponse($response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param  array $data
     *
     * @return array
     */
    private function encodePdfData(array $data): array
    {
        if (is_string($data['pdf'] ?? null) && preg_match('/^%PDF-/', $data['pdf'])) {
            $data['pdf'] = base64_encode($data['pdf']);
        }
        return $data;
    }

    /**
     * @param  array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function sendJsonResponse(array $data): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $json = ['data' => $data];

        if ($this->hasErrors()) {
            $json = ['errors' => $this->getErrors()];
            $response->setStatusCode(400);
        }

        return $response->setContent(json_encode($json));
    }
}
