<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SocialLogin
{
    public const PROVIDERS = ['google', 'line'];

    public function isSupported(string $provider): bool
    {
        return in_array($provider, self::PROVIDERS, true);
    }

    public function isConfigured(string $provider): bool
    {
        if (! $this->isSupported($provider)) {
            return false;
        }

        $config = config("social_login.{$provider}", []);

        return (bool) ($config['enabled'] ?? false)
            && filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null)
            && filled($config['redirect_uri'] ?? null);
    }

    public function configuredProviders(): array
    {
        return collect(self::PROVIDERS)
            ->filter(fn (string $provider): bool => $this->isConfigured($provider))
            ->mapWithKeys(fn (string $provider): array => [
                $provider => (string) config("social_login.{$provider}.label", ucfirst($provider)),
            ])
            ->all();
    }

    public function authorizationUrl(string $provider, string $state, string $nonce): string
    {
        $this->ensureConfigured($provider);
        $config = config("social_login.{$provider}");
        $parameters = [
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => $config['scopes'],
            'state' => $state,
            'nonce' => $nonce,
        ];

        if ($provider === 'google') {
            $parameters['prompt'] = 'select_account';
        }

        return $config['authorize_url'].'?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @return array{provider:string, provider_user_id:string, email:?string, name:?string, avatar_url:?string}
     */
    public function fetchIdentity(string $provider, string $code, string $nonce): array
    {
        $this->ensureConfigured($provider);

        return match ($provider) {
            'google' => $this->fetchGoogleIdentity($code, $nonce),
            'line' => $this->fetchLineIdentity($code, $nonce),
        };
    }

    private function fetchGoogleIdentity(string $code, string $nonce): array
    {
        $config = config('social_login.google');
        $token = Http::asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(12)
            ->post($config['token_url'], [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
            ]);

        if (! $token->successful() || blank($token->json('id_token'))) {
            throw new RuntimeException('Google token exchange failed.');
        }

        $payload = (new GoogleClient(['client_id' => $config['client_id']]))
            ->verifyIdToken((string) $token->json('id_token'));

        if (! is_array($payload)
            || blank($payload['sub'] ?? null)
            || ! filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || ! hash_equals($nonce, (string) ($payload['nonce'] ?? ''))) {
            throw new RuntimeException('Google identity verification failed.');
        }

        return $this->identity('google', $payload);
    }

    private function fetchLineIdentity(string $code, string $nonce): array
    {
        $config = config('social_login.line');
        $token = Http::asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(12)
            ->post($config['token_url'], [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
            ]);

        if (! $token->successful() || blank($token->json('id_token'))) {
            throw new RuntimeException('LINE token exchange failed.');
        }

        $verified = Http::asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(12)
            ->post($config['verify_url'], [
                'id_token' => $token->json('id_token'),
                'client_id' => $config['client_id'],
                'nonce' => $nonce,
            ]);

        if (! $verified->successful()
            || blank($verified->json('sub'))
            || ! hash_equals($nonce, (string) $verified->json('nonce', ''))) {
            throw new RuntimeException('LINE identity verification failed.');
        }

        return $this->identity('line', $verified->json());
    }

    private function identity(string $provider, array $payload): array
    {
        return [
            'provider' => $provider,
            'provider_user_id' => (string) $payload['sub'],
            'email' => filled($payload['email'] ?? null) ? mb_strtolower(trim((string) $payload['email'])) : null,
            'name' => filled($payload['name'] ?? null) ? trim((string) $payload['name']) : null,
            'avatar_url' => filled($payload['picture'] ?? null) ? (string) $payload['picture'] : null,
        ];
    }

    private function ensureConfigured(string $provider): void
    {
        if (! $this->isConfigured($provider)) {
            throw new RuntimeException('Social login provider is not configured.');
        }
    }
}
