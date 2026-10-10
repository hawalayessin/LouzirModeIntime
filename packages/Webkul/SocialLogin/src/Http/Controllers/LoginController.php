<?php

namespace Webkul\SocialLogin\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Webkul\SocialLogin\Repositories\CustomerSocialAccountRepository;

class LoginController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Supported social providers.
     *
     * @var array
     */
    protected $providers = ['facebook', 'twitter', 'google', 'linkedin-openid', 'github'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CustomerSocialAccountRepository $customerSocialAccountRepository) {}

    /**
     * Redirects to the social provider
     *
     * @param  string  $provider
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        if (! in_array($provider, $this->providers)) {
            session()->flash('error', 'Social login provider "'.$provider.'" is not supported.');

            return redirect()->route('shop.customer.session.index');
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->route('shop.customer.session.index');
        }
    }

    /**
     * Handles callback
     *
     * @param  string  $provider
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        if (! in_array($provider, $this->providers)) {
            session()->flash('error', 'Social login provider "'.$provider.'" is not supported.');

            return redirect()->route('shop.customer.session.index');
        }

        try {
            $user = Socialite::driver($provider)->user();

            if (! $user->getEmail()) {
                throw new \Exception(
                    'The '.$provider.' account does not expose an email address. '
                    .'Please make sure it is verified on '.$provider.' or sign in with your email and password.'
                );
            }

            $customer = $this->customerSocialAccountRepository->findOrCreateCustomer($user, $provider);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->route('shop.customer.session.index');
        }

        auth()->guard('customer')->login($customer, true);

        Event::dispatch('customer.after.login', $customer);

        return redirect()->intended(route('shop.customers.account.profile.index'));
    }
}
