<?php

namespace Webkul\SocialLogin;

use Exception;
use Laravel\Socialite\One\TwitterProvider;
use Laravel\Socialite\SocialiteManager as BaseSocialiteManager;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\LinkedInOpenIdProvider;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

class SocialiteManager extends BaseSocialiteManager
{
    /**
     * Create an instance of the specified driver.
     *
     * @return AbstractProvider
     */
    protected function createGithubDriver()
    {
        return $this->buildProvider(
            GithubProvider::class, $this->getProviderConfig('github')
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return AbstractProvider
     */
    protected function createFacebookDriver()
    {
        return $this->buildProvider(
            FacebookProvider::class, $this->getProviderConfig('facebook')
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return AbstractProvider
     */
    protected function createGoogleDriver()
    {
        return $this->buildProvider(
            GoogleProvider::class, $this->getProviderConfig('google')
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return AbstractProvider
     */
    protected function createLinkedinOpenidDriver()
    {
        return $this->buildProvider(
            LinkedInOpenIdProvider::class, $this->getProviderConfig('linkedin-openid')
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider|AbstractProvider
     */
    protected function createTwitterDriver()
    {
        $config = $this->getProviderConfig('twitter');

        if (($config['oauth'] ?? null) === 2) {
            return $this->createTwitterOAuth2Driver();
        }

        return new TwitterProvider(
            $this->container->make('request'), new TwitterServer($this->formatConfig($config))
        );
    }

    /**
     * Build the provider configuration from the admin configuration
     * (Settings > Configuration > Customers > Social Login), falling
     * back to the environment variables (services config).
     *
     * @throws Exception
     */
    protected function getProviderConfig(string $provider): array
    {
        $key = $provider === 'linkedin-openid' ? 'linkedin' : $provider;

        $clientId = core()->getConfigData("customer.settings.social_login.{$key}_client_id");
        $clientSecret = core()->getConfigData("customer.settings.social_login.{$key}_client_secret");
        $callbackUrl = core()->getConfigData("customer.settings.social_login.{$key}_callback_url");

        $environment = $this->config->get("services.$provider") ?: [];

        $clientId = $clientId ?: ($environment['client_id'] ?? null);
        $clientSecret = $clientSecret ?: ($environment['client_secret'] ?? null);
        $callbackUrl = $callbackUrl ?: ($environment['redirect'] ?? null);

        if (! $clientId || ! $clientSecret) {
            throw new Exception(
                "Social login is not configured for \"{$provider}\". "
                .'Set the client ID and client secret in Admin > Settings > Configuration > Customers > Social Login, '
                ."or in the {$key}_CLIENT_ID and {$key}_CLIENT_SECRET environment variables."
            );
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => $callbackUrl ?: route('customer.social-login.callback', ['provider' => $provider]),
        ];
    }
}
