<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
class PeyflexService {
    protected $base;
    public function __construct() { $this->base = config('services.peyflex.base_url'); }
    public function getNetworks() {
        return Http::withToken(config('services.peyflex.api_key'))
            ->get("{$this->base}/api/data/networks/")->json();
    }
    public function getPlans($networkId) {
        return Http::withToken(config('services.peyflex.api_key'))
            ->get("{$this->base}/api/data/plans/?network=$networkId")->json();
    }
    public function buyData($data) {
        return Http::withToken(config('services.peyflex.api_key'))
            ->post("{$this->base}/api/data/purchase/", $data)->json();
    }
    public function buyAirtime($data) {
        return Http::withToken(config('services.peyflex.api_key'))
            ->post("{$this->base}/api/airtime/topup/", $data)->json();
    }
}
