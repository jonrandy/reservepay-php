<?php

declare(strict_types=1);

namespace Reservepay\Terminal;

use WebFunction\Page;
use WebFunction\Package;
use WebFunction\Pipeline;
use WebFunction\BadRequestError;
use WebFunction\UnexpectedStatusCodeError;
use WebFunction\JsonParseError;

// IMPORTANT - composer require webfunction-protocol/webfunction

/**
 * Generated client for the Reservepay Terminal API webfunction package.
 * Source: https://api.reservepay.com/terminals
 *
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
        $this->client = \WebFunction\Client::fromPackageEndpoint('https://api.reservepay.com/terminals', bearerAuth: $bearerAuth, version: $version);
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
     * ## Connect a device to a terminal
     *
     * Connect a device to a terminal instance. If the terminal has no device bound,
     * this will bind the provided `device_id` and generate a fresh API key token
     * (prefixed with `reservepay_`). If the terminal is already bound to the same
     * `device_id`, the previous key is revoked and a new one is issued.
     *
     * If the terminal is bound to a different device, an `ALREADY_CONNECTED` error
     * is returned.
     *
     * If the terminal is disabled, `NOT_ENABLED` is returned.
     *
     * If the terminal has no installation linked, `NOT_CONFIGURED` is returned.
     * @param array{merchant_id: string, terminal_id: string, device_id: string, challenge?: string} $args
     * @return string
     * @throws BadRequestError ALREADY_CONNECTED - The terminal is connected to another device.
     * @throws BadRequestError INVALID_CHALLENGE - The challenge is missing or does not match the terminal's current challenge.
     * @throws BadRequestError NOT_FOUND - Merchant or terminal could not be found.
     * @throws BadRequestError NOT_ENABLED - The terminal exists but is disabled; enable it before connecting a device.
     * @throws BadRequestError NOT_CONFIGURED - The terminal has no installation linked; assign an installation first.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function connectDevice(array $args): string
    {
        return $this->client->call('connect-device', $args);
    }

    /**
     * ## Connect a device with a temporary challenge
     *
     * Connect a device to the terminal that owns the given 8-digit temporary challenge.
     * The code must be unexpired. On success the device is bound, a fresh API key token
     * (prefixed with `reservepay_`) is returned, and the temporary challenge is cleared.
     *
     * If the terminal is already bound to a different device, `ALREADY_CONNECTED` is returned.
     * If it is bound to the same device, the previous key is revoked and a new one is issued.
     *
     * If the terminal is disabled, `NOT_ENABLED` is returned.
     *
     * If the terminal has no installation linked, `NOT_CONFIGURED` is returned.
     * @param array{code: string, device_id: string} $args
     * @return string
     * @throws BadRequestError ALREADY_CONNECTED - The terminal is connected to another device.
     * @throws BadRequestError INVALID_CHALLENGE - The code is unknown or has expired.
     * @throws BadRequestError NOT_ENABLED - The terminal exists but is disabled; enable it before connecting a device.
     * @throws BadRequestError NOT_CONFIGURED - The terminal has no installation linked; assign an installation first.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function connectDeviceByCode(array $args): string
    {
        return $this->client->call('connect-device-by-code', $args);
    }

    /**
     * ## Retrieve the installation settings
     *
     * Returns settings and configuration for the installation linked to the terminal
     * (payment methods, UI options, etc.), after verifying the bound device.
     *
     * The terminal must be connected via `connect-device` and must have an installation
     * configured on the merchant terminal record.
     * @param array{merchant_id: string, terminal_id: string, device_id: string} $args
     * @return array{installation_id: string, name: string, platform: 'WEB'|'IOS'|'ANDROID'|'TERMINAL', identifier: string, payment_methods: list<PaymentMethodAttributes>, email_capture: 'disabled'|'optional'|'required', mobile_capture: 'disabled'|'optional'|'required', address_capture: 'disabled'|'optional'|'required', merchant_name: string, interest_bearer?: 'merchant'|'customer'|null, primary_color: string, secondary_color: string, sidebar_color: string, sidebar_background_type: 'color'|'image', tile_sidebar_image: bool, hide_logo: bool, heading_font_family: string, heading_font_variant: 'Thin'|'ExtraLight'|'Light'|'Regular'|'Medium'|'SemiBold'|'Bold'|'ExtraBold'|'Black', body_font_family: string, body_font_variant: 'Thin'|'ExtraLight'|'Light'|'Regular'|'Medium'|'SemiBold'|'Bold'|'ExtraBold'|'Black', use_iso_code: bool, hide_non_significant_zeros: bool, show_amount_in_button: bool, symbol_placement: 'use_default'|'before_amount'|'after_amount', default_button_text: string, button_shape: 'rounded_none'|'rounded_full'|'rounded_xs'|'rounded_sm'|'rounded_md'|'rounded_lg'|'rounded_xl', light_logo_url?: string|null, dark_logo_url?: string|null, sidebar_image_url?: string|null, terminal_name: string, terminal_branch_name: string, terminal_kiosk_mode: bool}
     * @throws BadRequestError NOT_FOUND - The merchant or terminal could not be found, or no device is bound to the terminal.
     * @throws BadRequestError UNRECOGNIZED_DEVICE - The device_id does not match the one bound to the terminal.
     * @throws BadRequestError NOT_ENABLED - The terminal exists but is disabled.
     * @throws BadRequestError NOT_CONFIGURED - The terminal has no installation linked; assign an installation to the terminal first.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function retrieveInstallationSettings(array $args): array
    {
        return $this->client->call('retrieve-installation-settings', $args);
    }

    /**
     * ## Verify connection
     *
     * Verifies the connection between a physical device and a terminal.
     *
     * Returns one of:
     * - `CONNECTED` when the provided device is bound to the terminal
     * - `RESET_CONNECTION` when no device is bound (erase keys/PIN and call `connect-device`)
     *
     * If no device is bound, you should call the [connect-device](/api/terminals/connect-device) endpoint.
     *
     * Errors:
     * - `UNRECOGNIZED_DEVICE` if another device is currently bound
     * - `NOT_FOUND` if the merchant or terminal cannot be found
     * - `NOT_ENABLED` if the terminal is disabled
     * - `NOT_CONFIGURED` if the terminal has no installation linked
     * @param array{merchant_id: string, terminal_id: string, device_id: string} $args
     * @return string
     * @throws BadRequestError UNRECOGNIZED_DEVICE - The device_id does not match the one bound to the terminal.
     * @throws BadRequestError NOT_FOUND - Merchant or terminal could not be found.
     * @throws BadRequestError NOT_ENABLED - The terminal exists but is disabled.
     * @throws BadRequestError NOT_CONFIGURED - The terminal has no installation linked; assign an installation first.
     * @throws UnexpectedStatusCodeError Non-200, non-400 response.
     * @throws JsonParseError Response body wasn't valid JSON.
     */
    public function verifyConnection(array $args): string
    {
        return $this->client->call('verify-connection', $args);
    }

}

