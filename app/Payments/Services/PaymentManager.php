<?php

namespace App\Payments\Services;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Models\PaymentMethod;

class PaymentManager
{
    protected array $gateways = [];

    /**
     * Register a payment gateway.
     */
    public function registerGateway(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getId()] = $gateway;
    }

    /**
     * Get all registered gateway instances.
     */
    public function getRegisteredGateways(): array
    {
        return $this->gateways;
    }

    /**
     * Get a specific gateway instance by ID.
     */
    public function getGateway(string $id): ?PaymentGatewayInterface
    {
        return $this->gateways[$id] ?? null;
    }

    /**
     * Get all gateways that are installed in the DB.
     */
    public function getInstalledGateways(): array
    {
        try {
            $installedIds = PaymentMethod::where('is_installed', true)->pluck('id')->toArray();
            return array_filter($this->gateways, function ($gateway) use ($installedIds) {
                return in_array($gateway->getId(), $installedIds);
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all gateways that are active (installed and enabled) in the DB.
     */
    public function getActiveGateways(): array
    {
        try {
            $activeIds = PaymentMethod::where('is_installed', true)
                ->where('is_enabled', true)
                ->pluck('id')
                ->toArray();
                
            return array_filter($this->gateways, function ($gateway) use ($activeIds) {
                return in_array($gateway->getId(), $activeIds);
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all active gateways (compatibility alias).
     */
    public function getEnabledGateways(): array
    {
        return $this->getActiveGateways();
    }
    
    /**
     * Check if a gateway is enabled.
     */
    public function isEnabled(string $id): bool
    {
        try {
            $method = PaymentMethod::find($id);
            return $method && $method->is_installed && $method->is_enabled;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get database config for a gateway.
     */
    public function getGatewayConfig(string $id): array
    {
        try {
            $method = PaymentMethod::find($id);
            return $method ? ($method->config ?? []) : [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
