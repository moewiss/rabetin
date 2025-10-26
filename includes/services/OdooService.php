<?php

use GuzzleHttp\Client;
use RuntimeException;

class OdooService
{
    private string $url;
    private string $db;
    private string $username;
    private string $password;
    private int $uid;
    private Client $http;

    public function __construct()
    {
        // Load from $_ENV or getenv()
        $this->url = rtrim($_ENV['ODOO_URL'] ?? getenv('ODOO_URL') ?: '', '/');
        $this->db = $_ENV['ODOO_DB'] ?? getenv('ODOO_DB') ?: '';
        $this->username = $_ENV['ODOO_EMAIL'] ?? getenv('ODOO_EMAIL') ?: '';
        $this->password = $_ENV['ODOO_API_KEY'] ?? getenv('ODOO_API_KEY') ?: '';

        if (!$this->url || !$this->db || !$this->username || !$this->password) {
            throw new RuntimeException('Missing Odoo credentials in environment variables');
        }

        $this->http = new Client([
            'base_uri' => $this->url,
            'timeout'  => 30,
            'headers'  => ['Content-Type' => 'application/json'],
        ]);

        // Authenticate via JSON-RPC
        $auth = $this->jsonRpc('common', 'authenticate', [$this->db, $this->username, $this->password, []]);
        if (!$auth || !is_int($auth)) {
            throw new RuntimeException('Odoo authentication failed. Check URL/DB/Email/API key.');
        }
        $this->uid = $auth;
    }

    private function jsonRpc(string $service, string $method, array $args)
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'params'  => [
                'service' => $service,
                'method'  => $method,
                'args'    => $args,
            ],
            'id' => uniqid(),
        ];

        $resp = $this->http->post('/jsonrpc', ['body' => json_encode($payload)]);
        $data = json_decode((string) $resp->getBody(), true);

        if (isset($data['error'])) {
            $msg = $data['error']['data']['message'] ?? json_encode($data['error']);
            throw new RuntimeException("Odoo RPC error: " . $msg);
        }

        return $data['result'] ?? null;
    }

    private function executeKW(string $model, string $method, array $args = [], array $kwargs = [])
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'params'  => [
                'service' => 'object',
                'method'  => 'execute_kw',
                'args'    => [
                    $this->db,
                    $this->uid,
                    $this->password,
                    $model,
                    $method,
                    $args,
                    $kwargs,
                ],
            ],
            'id' => uniqid(),
        ];

        $resp = $this->http->post('/jsonrpc', ['body' => json_encode($payload)]);
        $data = json_decode((string) $resp->getBody(), true);

        if (isset($data['error'])) {
            $msg = $data['error']['data']['message'] ?? json_encode($data['error']);
            throw new RuntimeException("Odoo execute_kw error: " . $msg);
        }

        return $data['result'] ?? null;
    }

    /** Get available subscription plans from Odoo */
    public function getPlans(): array
    {
        $fields = ['id', 'name'];

        try {
            // Try new Odoo version model
            return $this->executeKW(
                'sale.subscription.plan',
                'search_read',
                [[]],
                ['fields' => $fields]
            ) ?? [];
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), "doesn't exist")) {
                // Fallback to older model
                return $this->executeKW(
                    'sale.subscription.template',
                    'search_read',
                    [[]],
                    ['fields' => $fields]
                ) ?? [];
            }
            throw $e;
        }
    }

    /** Ensure partner exists, create if not */
    public function ensurePartner(array $user): int
    {
        $email = $user['email'] ?? null;
        if (!$email) {
            throw new RuntimeException('User email required for partner creation.');
        }

        $ids = $this->executeKW('res.partner', 'search', [[['email', '=', $email]]]);
        if (!empty($ids)) {
            return (int) $ids[0];
        }

        return (int) $this->executeKW('res.partner', 'create', [[
            'name'  => $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email' => $email,
        ]]);
    }

    /** Create a new subscription */
    public function createSubscription(int $partnerId, int $planId, string $displayName): int
    {
        return (int) $this->executeKW('sale.subscription', 'create', [[
            'partner_id'  => $partnerId,
            'template_id' => $planId,
            'name'        => $displayName,
        ]]);
    }

    /** Fetch subscriptions for a given partner */
    public function findSubscriptionsByPartner(int $partnerId): array
    {
        return $this->executeKW('sale.subscription', 'search_read', [[['partner_id', '=', $partnerId]]], [
            'fields' => ['id', 'name'],
        ]) ?? [];
    }
}

