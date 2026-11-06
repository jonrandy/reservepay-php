<?php

declare(strict_types=1);

namespace Reservepay\SDK;

use WebFunction\Page;
use WebFunction\Package;
use WebFunction\Pipeline;
use WebFunction\BadRequestError;
use WebFunction\UnexpectedStatusCodeError;
use WebFunction\JsonParseError;

// IMPORTANT - composer require webfunction-protocol/webfunction

/**
 * Generated client for the Reservepay SDK API webfunction package.
 * Source: https://api.reservepay.com/sdk
 *
 * @phpstan-type InstallmentPlanAttributes = array{installment_plan_id: string, name: string, months: int|float, interest_rate: string, interest_bearer: 'customer'|'merchant', monthly_amount: int|float, total_amount: int|float, monthly_interest_amount: int|float, total_interest_amount: int|float, currency: string, bank_name: string, bank_display_name: string}
 * @psalm-type InstallmentPlanAttributes = array{installment_plan_id: string, name: string, months: int|float, interest_rate: string, interest_bearer: 'customer'|'merchant', monthly_amount: int|float, total_amount: int|float, monthly_interest_amount: int|float, total_interest_amount: int|float, currency: string, bank_name: string, bank_display_name: string}
 * @phpstan-type BankAttributes = array{name: string, display_name: string}
 * @psalm-type BankAttributes = array{name: string, display_name: string}
 * @phpstan-type PaymentMethodAttributes = array{name: string, display_name: string, flow: string, category: string, requires_address: bool, required_fields: list<string>, banks: list<BankAttributes>}
 * @psalm-type PaymentMethodAttributes = array{name: string, display_name: string, flow: string, category: string, requires_address: bool, required_fields: list<string>, banks: list<BankAttributes>}
 */
final class Client
{
    private \WebFunction\Client $client;

    public function __construct(?string $bearerAuth = null, ?string $version = null)
    {
        $this->client = \WebFunction\Client::fromPackageEndpoint('https://api.reservepay.com/sdk', bearerAuth: $bearerAuth, version: $version);
    }

    /**
     * Escape hatch for calling an endpoint this client doesn't have a typed method for.
     */
    public function call(string $name, array $args = []): mixed
    {
        return $this->client->call($name, $args);
    }

    public function getPackage(): ?Package
    {
        return $this->client->getPackage();
    }

    public function setBearerAuth(?string $bearerAuth): void
    {
        $this->client->setBearerAuth($bearerAuth);
    }

    public function setVersion(?string $version): void
    {
        $this->client->setVersion($version);
    }

    public function setPipeline(?Pipeline $pipeline): void
    {
        $this->client->setPipeline($pipeline);
    }

    /**
     * ## Check the status of the payment session
     *
     * This endpoint allows you to check the current status of a payment session. The payment session
     * goes through several states during its lifecycle:
     *
     * - `PENDING`: The customer has requested to start the payment flow but hasn't begun yet
     * - `INITIATED`: You (the merchant) have initiated the payment flow and it's ready for the customer
     * - `SUCCESSFUL`: Payment completed successfully
     * - `FAILED`: Payment failed, was declined or was cancelled
     * - `EXPIRED`: Payment expired before completion
     *
     * You should poll this endpoint periodically to track the payment progress. We recommend polling
     * not faster than every 1 second.
     * @param array{merchant_id: string, installation_id: string, payment_session_id: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or payment session with the IDs provided. Double check that you are using the correct IDs.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function checkStatus(array $args): string
    {
        return $this->client->call('check-status', $args);
    }

    /**
     * ## Discover the next action
     *
     * This endpoint guide you through the payment flow by telling you what action to take next.
     * You should poll this endpoint from your frontend to track the payment progress. We recommend polling
     * every 1-2 seconds.
     *
     * The possible responses are:
     *
     * - `WAIT`: The payment is not ready yet, continue polling
     * - `DISPLAY_QR`: Display a QR code to the customer
     * - `REDIRECT`: Redirect the customer to complete the payment
     * - `CHECK_STATUS`: The payment flow is complete, check the final status
     *
     * General implementation flow:
     *
     * 1. Start polling this endpoint after selecting a payment method
     * 2. When you receive an action (`DISPLAY_QR` or `REDIRECT`) take the appropriate action for your customer and continue polling in the background.
     * 3. When you receive `CHECK_STATUS` stop polling this endpoint and call `check-status` to see if payment succeeded.
     *
     * Implementation tips:
     *
     * - Use `setInterval()` or `setTimeout()` for polling from your frontend
     * - Add exponential backoff if you get multiple `WAIT` responses
     * - Handle network errors gracefully and retry failed requests
     * - Stop polling once you receive `CHECK_STATUS`
     * @param array{merchant_id: string, installation_id: string, payment_session_id: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or payment session with the IDs provided. Double check that you are using the correct IDs.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function discoverNextAction(array $args): string
    {
        return $this->client->call('discover-next-action', $args);
    }

    /**
     * ## Get the redirect URL
     *
     * This endpoint returns the URL where you should redirect your customer to complete their payment.
     * The URL is specific to the payment method that was selected.
     *
     * You should only call this endpoint after `discover-next-action` returns `REDIRECT`. The URL may not
     * be ready immediately after creating the payment session - in this case you'll receive a `NOT_READY`
     * error and should try again in a few seconds.
     *
     * After redirecting the customer, you should continue polling `discover-next-action` in the background
     * to know when they've completed the payment flow.
     *
     * Note: Not all payment methods require redirection. For methods that don't support redirection,
     * you'll receive a `NOT_SUPPORTED` error.
     * @param array{merchant_id: string, installation_id: string, payment_session_id: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or payment session with the IDs provided. Double check that you are using the correct IDs.
     * @throws BadRequestError NOT_READY - This error occurs when the redirect URL is not yet ready. Wait a few seconds and try again.
     * @throws BadRequestError NOT_SUPPORTED - This error occurs when trying to get a redirect URL for a payment method that doesn't support redirection (like QR codes).
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function getRedirectUrl(array $args): string
    {
        return $this->client->call('get-redirect-url', $args);
    }

    /**
     * ## Get the return URL
     *
     * This endpoint returns the URL where you should redirect your customer after payment completed.
     * @param array{merchant_id: string, installation_id: string, payment_session_id: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or payment session with the IDs provided. Double check that you are using the correct IDs.
     * @throws BadRequestError NOT_READY - This error occurs when the redirect URL is not yet ready. Wait a few seconds and try again.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function getReturnUrl(array $args): string
    {
        return $this->client->call('get-return-url', $args);
    }

    /**
     * ## List installment plans
     *
     * This endpoint returns the card installment plans available for a given amount and card.
     * Identify the card with either a `bin` (the leading digits of the card number) or a `token`
     * from `tokenize-card`.
     *
     * Eligibility depends on the card's BIN (issuing bank), the requested amount, the merchant's
     * installment configuration, and per-plan constraints (minimum amount, maximum amount, minimum
     * monthly amount). If no plans are eligible the endpoint returns an empty array; the client
     * should hide the installment step and proceed to `select-payment-method` without an
     * `installment_plan_id`.
     *
     * The amounts returned (`monthly_amount`, `total_amount`, `interest_amount`) are computed against
     * the amount you pass to this endpoint. Pass the same amount to `select-payment-method` to keep
     * the customer's quote consistent.
     * @param array{merchant_id: string, installation_id: string, bin?: string, token?: string, amount: string, currency: string, interest_bearer?: 'customer'|'merchant'} $args
     * @return list<InstallmentPlanAttributes>
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or card token with the IDs provided. Double check that you are using the correct IDs.
     * @throws BadRequestError TOKEN_ALREADY_USED - This token has already been used to create a payment session. Each token can only be used once. Request a new token from `tokenize-card`.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function listInstallmentPlans(array $args): array
    {
        return $this->client->call('list-installment-plans', $args);
    }

    /**
     * ## Rehydrate a card security code
     *
     * This endpoint attaches a newly collected security code to a saved card's `token`, so the
     * token can be used for a customer-initiated payment (CIT). It returns the same `token`.
     *
     * The security code is never saved with the card. It covers only the payment made right
     * after it's entered, so every customer-initiated payment on a saved card needs a newly
     * entered security code:
     *
     * 1. Collect the security code from the customer.
     * 2. Call `rehydrate-card` with the saved card's `token` and the security code.
     * 3. Right after, call `select-payment-method` with the same `token` and start the payment.
     *
     * If the payment fails, collect the security code and call this endpoint again before
     * retrying.
     *
     * The browser SDK calls this endpoint for you in saved-card mode. Merchant-initiated payments
     * (MIT) through the merchant `request-payment` endpoint don't need it.
     * @param array{merchant_id: string, security_code: string, token: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account with the IDs provided. Double check that you are using the correct IDs.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function rehydrateCard(array $args): string
    {
        return $this->client->call('rehydrate-card', $args);
    }

    /**
     * ## Retrieve Checkout
     *
     * Retrieves a specific checkout by its ID for a given installation.
     * @param array{merchant_id: string, installation_id: string, checkout_id: string} $args
     * @return array{return_url?: string|null}
     * @throws BadRequestError NOT_FOUND - The merchant, installation, or checkout could not be found with the provided IDs.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function retrieveCheckout(array $args): array
    {
        return $this->client->call('retrieve-checkout', $args);
    }

    /**
     * ## Retrieve the installation settings
     *
     * This endpoint allows you to retrieve the settings and configuration for a specific installation
     * (e.g. your iOS app, Android app, website, or terminal). The settings contain important information like
     * the supported payment methods and UI customization options.
     * @param array{merchant_id: string, installation_id: string, interest_bearer?: 'customer'|'merchant'} $args
     * @return array{installation_id: string, name: string, platform: 'WEB'|'IOS'|'ANDROID'|'TERMINAL', identifier: string, payment_methods: list<PaymentMethodAttributes>, email_capture: 'disabled'|'optional'|'required', mobile_capture: 'disabled'|'optional'|'required', address_capture: 'disabled'|'optional'|'required', merchant_name: string, interest_bearer?: 'merchant'|'customer'|null, primary_color: string, secondary_color: string, sidebar_color: string, sidebar_background_type: 'color'|'image', tile_sidebar_image: bool, hide_logo: bool, heading_font_family: string, heading_font_variant: 'Thin'|'ExtraLight'|'Light'|'Regular'|'Medium'|'SemiBold'|'Bold'|'ExtraBold'|'Black', body_font_family: string, body_font_variant: 'Thin'|'ExtraLight'|'Light'|'Regular'|'Medium'|'SemiBold'|'Bold'|'ExtraBold'|'Black', use_iso_code: bool, hide_non_significant_zeros: bool, show_amount_in_button: bool, symbol_placement: 'use_default'|'before_amount'|'after_amount', default_button_text: string, button_shape: 'rounded_none'|'rounded_full'|'rounded_xs'|'rounded_sm'|'rounded_md'|'rounded_lg'|'rounded_xl', light_logo_url?: string|null, dark_logo_url?: string|null, sidebar_image_url?: string|null}
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account or installation with the IDs provided. Double check that you are using the correct IDs.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function retrieveInstallationSettings(array $args): array
    {
        return $this->client->call('retrieve-installation-settings', $args);
    }

    /**
     * ## Retrieve payment link
     *
     * Retrieves a specific payment link by its ID for a given installation.
     * @param array{merchant_id: string, installation_id: string, payment_link_id: string} $args
     * @return array{description: string, expires_at?: int|float|null}
     * @throws BadRequestError NOT_FOUND - The merchant, installation, or payment link could not be found with the provided IDs.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function retrievePaymentLink(array $args): array
    {
        return $this->client->call('retrieve-payment-link', $args);
    }

    /**
     * ## Retrieve payment session
     *
     * Retrieves a payment session by its ID. Returns the payment session details including payment status, amount, currency, and reference numbers.
     * @param array{payment_session_id: string} $args
     * @return array{amount: int|float, currency: string, payment_method: string, payment_method_display_name: string, card_last_digits?: string|null, card_network?: string|null, installment_term?: int|float|null, interest_bearer?: 'customer'|'merchant'|null, installment_interest_rate?: string|null, installment_monthly_amount?: int|float|null, installment_total_amount?: int|float|null, installment_total_interest_amount?: int|float|null, installment_bank_name?: string|null, installment_bank_display_name?: string|null, payment_authorized_at?: string|null, payment_captured_at?: string|null, payment_voided_at?: string|null, status: 'PENDING'|'INITIATED'|'SUCCESSFUL'|'FAILED'|'EXPIRED', payment_ref1?: string|null, payment_ref2?: string|null, payment_ref3?: string|null, terminal_id?: string|null, expires_at?: int|float|null}
     * @throws BadRequestError NOT_FOUND - The payment session could not be found with the provided ID.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function retrievePaymentSession(array $args): array
    {
        return $this->client->call('retrieve-payment-session', $args);
    }

    /**
     * ## Retrieve the data to display a QR code
     *
     * This endpoint returns the data needed to display a QR code for payment methods that support QR-based payments (like PromptPay).
     *
     * You should only call this endpoint after `discover-next-action` returns `DISPLAY_QR`. The QR data may not be ready immediately
     * after creating the payment session - in this case you'll receive a `NOT_READY` error and should try again in a few seconds.
     *
     * After displaying the QR code to your customer, you should continue polling `check-status` in the background to know when
     * they've completed the payment by scanning the code.
     *
     * Note: Not all payment methods use QR codes. For methods that don't support QR codes, you'll receive a `NOT_SUPPORTED` error.
     * @param array{merchant_id: string, installation_id: string, payment_session_id: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or payment session with the IDs provided. Double check that you are using the correct IDs.
     * @throws BadRequestError NOT_READY - This error occurs when the QR data is not yet ready. Wait a few seconds and try again.
     * @throws BadRequestError NOT_SUPPORTED - This error occurs when trying to get QR data for a payment method that doesn't support QR codes.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function retrieveQrData(array $args): string
    {
        return $this->client->call('retrieve-qr-data', $args);
    }

    /**
     * ## Select payment method
     *
     * This endpoint creates a new payment session with the customer's chosen payment method.
     * It returns a payment session ID that you'll use in subsequent API calls to complete the payment flow.
     *
     * The flow after calling this endpoint depends on the selected payment method:
     *
     * - For card payments: You must provide a card token obtained from `tokenize-card`, or a
     *   saved card's token right after `rehydrate-card`
     * - For QR code payments: Call `retrieve-qr-data` to get the QR code data to display
     * - For redirect-based payments: Call `get-redirect-url` to get the URL to redirect the customer to
     *
     * Card installments:
     *
     * To charge a card in monthly installments instead of a single payment, pass an
     * `installment_plan_id` obtained from `list-installment-plans`. The `amount` and `currency`
     * must match the values you passed to `list-installment-plans`, and the card you tokenize
     * must be the card whose BIN (or token) you used there, so the chosen plan stays eligible
     * and the customer's quote remains consistent. Omit `installment_plan_id` to charge the card
     * as a one-time payment.
     *
     * Billing-address fields are routed according to the setting that requires them:
     *
     * | Configuration | Address destination | Required fields |
     * | --- | --- | --- |
     * | Card payment method `requires_address: true` | `tokenize-card` | The card payment method's `required_fields` |
     * | Installation `address_capture: required` | `select-payment-method` | The selected payment method's `required_fields` |
     *
     * Address fields required by the selected payment method must be submitted to this endpoint.
     * An empty `required_fields` configuration uses the default full address consisting of
     * `street_address_1`, `postal_code`, `city`, `state`, and `country`; `street_address_2` is
     * always optional.
     *
     * After starting the payment flow, you should poll `discover-next-action` to track the payment status
     * and determine the next steps.
     * @param array{merchant_id: string, installation_id: string, installment_plan_id?: string, amount: string, currency: string, payment_method: string, token?: string, customer_email?: string, customer_mobile?: string, customer_name?: string, customer_language?: 'en'|'th', interest_bearer?: 'customer'|'merchant', street_address_1?: string, street_address_2?: string, postal_code?: string, city?: string, state?: string, country?: string} $args
     * @return string
     * @throws BadRequestError INVALID_ARGUMENTS - This error occurs when the request contains invalid or missing parameters. Common cases include:
     * 
     * - A missing `merchant_id`, `installation_id`, `amount`, `currency`, or `payment_method`
     * - An unsupported `currency` (must match the funding currency configured for your provider)
     * - An installation that could not be found or does not have the requested payment method enabled
     * - Missing customer email or mobile when required by the installation
     * - Missing address fields when required by the installation and payment method
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account, installation, or card token with the IDs provided. Double check that you are using the correct IDs.
     * @throws BadRequestError TOKEN_ALREADY_USED - This single-use token has already been used to create a payment session. Each single-use token can only be used once. Request a new token from `tokenize-card`.
     * @throws BadRequestError NOT_ENABLED - This installation is currently disabled.
     * @throws BadRequestError INSTALLMENT_NOT_ELIGIBLE - The installment plan you provided is not eligible for this payment, for example because the card, amount, or interest bearer does not match the plan, or the plan is no longer offered.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function selectPaymentMethod(array $args): string
    {
        return $this->client->call('select-payment-method', $args);
    }

    /**
     * ## Tokenize a card number
     *
     * This endpoint securely converts sensitive card details into a token that can be safely
     * stored and used for future payments. The token can be used with the
     * `select-payment-method` endpoint to process card payments.
     *
     * The tokenization process helps you maintain PCI compliance by avoiding the need to
     * handle raw card data directly in your systems. The token can only be used with your
     * merchant account and cannot be used to process payments elsewhere.
     *
     * For security reasons, tokens are single-use by default. To save the card to a contact for
     * later payments, set usage to `MULTIPLE` when creating the token.
     *
     * The security code is never saved with the card. It covers only the payment made right
     * after tokenization, so use the token right away. For a later customer-initiated payment on
     * a saved card, collect the security code again and call `rehydrate-card` right before
     * `select-payment-method`. To charge a saved card from your backend without the security
     * code (a merchant-initiated payment), use the merchant `request-payment` endpoint instead.
     *
     * Billing-address fields are routed according to the setting that requires them:
     *
     * | Configuration | Address destination | Required fields |
     * | --- | --- | --- |
     * | Card payment method `requires_address: true` | `tokenize-card` | The card payment method's `required_fields` |
     * | Installation `address_capture: required` | `select-payment-method` | The selected payment method's `required_fields` |
     *
     * This endpoint validates only the card payment method's address requirement. Installation
     * address capture is validated by `select-payment-method`. `street_address_2` is always
     * optional.
     * @param array{merchant_id: string, installation_id: string, number: string, name: string, expiration_date: string, security_code: string, usage?: 'SINGLE'|'MULTIPLE', payment_method?: 'CARD'|'INSTALLMENT'|'CARD_PRESENT', street_address_1?: string, street_address_2?: string, postal_code?: string, city?: string, state?: string, country?: string} $args
     * @return string
     * @throws BadRequestError NOT_FOUND - This error occurs when we cannot find the merchant account or installation with the IDs provided. Double check that you are using the correct IDs.
     * @throws BadRequestError NOT_ENABLED - This installation is currently disabled.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function tokenizeCard(array $args): string
    {
        return $this->client->call('tokenize-card', $args);
    }

}

