<?php
namespace App\Services;
use Illuminate\Support\Facades\Log;

class PeyflexService {
    protected $base;
    protected $token;

    public function __construct() {
        $this->base  = config('services.peyflex.base_url');
        $this->token = config('services.peyflex.api_key');
    }

    private function request($method, $endpoint, $data = []) {
        $url = "{$this->base}{$endpoint}";

        $options = [
            'http' => [
                'method'  => strtoupper($method),
                'header'  => "Authorization: Token {$this->token}\r\n"
                           . "Accept: application/json\r\n",
                'timeout' => 20,
                'ignore_errors' => true,
            ]
        ];

        if (strtoupper($method) === 'POST' && !empty($data)) {
            $jsonBody = json_encode($data);
            $options['http']['header'] .= "Content-Type: application/json\r\n"
                                        . "Content-Length: " . strlen($jsonBody) . "\r\n";
            $options['http']['content'] = $jsonBody;
        }

        $context = stream_context_create($options);

        try {
            $response = file_get_contents($url, false, $context);
        } catch (\Exception $e) {
            Log::error('Peyflex native request exception', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if ($response === false) {
            Log::error('Peyflex native request failed', ['url' => $url]);
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Peyflex invalid JSON', ['url' => $url, 'body' => $response]);
            return null;
        }

        return $decoded;
    }

    public function getDataNetworks() {
        return $this->request('get', '/api/data/networks/');
    }

    public function getAirtimeNetworks() {
        return $this->request('get', '/api/airtime/networks/');
    }

    public function getDataPlans($networkId) {
        return $this->request('get', "/api/data/plans/?network=$networkId");
    }

    public function buyData($data) {
        return $this->request('post', '/api/data/purchase/', $data);
    }

    public function buyAirtime($data) {
        return $this->request('post', '/api/airtime/topup/', $data);
    }
}
