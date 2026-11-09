# EDPay PrestaShop Module

Official EDPay integration preview for PrestaShop. This repository provides source code for a checkout payment option, hosted payment-link redirect, and webhook controller. Treat this module as beta: review and test it in a non-production shop before considering any live use. It is not represented as production-ready.

## Requirements

- PrestaShop 1.7 or later (the module declares a minimum of 1.7.0.0)
- PHP with the `curl`, `json`, and `hash` extensions
- EDPay public and private API keys, plus a webhook signing secret
- HTTPS for the shop and webhook endpoint

## Install from source

Clone the repository into the PrestaShop `modules` directory using the module's technical name, `edpay`:

```bash
cd /path/to/prestashop/modules
git clone https://github.com/edutecka/edpay-prestashop-module.git edpay
```

In the PrestaShop back office, open **Modules > Module Manager**, find the EDPay module, install it, and open its configuration.

## Configure and test checkout

The module configuration accepts a public key (`pk_test_...` or `pk_live_...`), private key (`sk_test_...` or `sk_live_...`), webhook secret (`whsec_...`), and a Sandbox switch. Keep credentials private and use non-production credentials for testing.

**Important implementation limitation:** although the configuration stores a Sandbox switch, the current code does not use it to select an API environment. Payment-link requests use the configured `EDPAY_API_URL`, initialized to `https://api-pay.edutecka.com`; there is no API URL field in the module configuration form. Verify the correct endpoint and environment with EDPay before testing, and do not send live credentials or customer orders until this behavior has been confirmed and reviewed.

During checkout, the module creates a PrestaShop order in the Preparation state, then sends a payment-link request to `POST /v1/core/payment-links` with `X-EDPay-Public-Key`, `X-EDPay-Private-Key`, `Content-Type: application/json`, and an `Idempotency-Key`. The request includes the order amount (the module multiplies the PrestaShop total by 100), currency, order metadata, and success and cancel URLs. On a successful API response, it redirects to `data.hosted_url` or `data.checkout_url`.

The current failure path returns the customer to checkout after the order has already been created in Preparation. Review pending orders after failed requests. The idempotency key includes the current time, so separate attempts do not reuse a stable key; do not rely on it to deduplicate retries.

## Configure the webhook

Register the following HTTPS callback in the EDPay dashboard under **Developers > Webhooks**, replacing the host with the shop's public domain:

```text
https://store.example/module/edpay/webhook
```

The controller reads the raw request body and `X-EDPay-Signature`, verifies a hexadecimal HMAC-SHA256 signature using the configured webhook secret, and changes an order to the PrestaShop Payment accepted state for `payment.succeeded` events whose data contains the `prestashop_order_id` metadata. Other event types do not change the order.

**Security and reconciliation warnings:** the current controller skips signature verification if no webhook secret is configured, so configure a valid secret before enabling the callback. The implementation does not record processed event IDs or provide replay/deduplication protection; test duplicate notifications and reconcile orders against EDPay. Never treat the checkout return URL as proof of payment. Restrict access to the PrestaShop back office and configuration database because the module stores API credentials there.

## Official documentation

- [PrestaShop integration guide](https://docs-pay.edutecka.com/plugins/prestashop/)
- [Payments API](https://docs-pay.edutecka.com/api/payments/)
- [Webhooks](https://docs-pay.edutecka.com/api/webhooks/)
- [SDK installation and integration guide](https://docs-pay.edutecka.com/sdks/)

## License

MIT (as declared in the module source).

## Other official SDKs and plugins

### SDKs

- [JavaScript / TypeScript](https://github.com/edutecka/edpay-javascript-sdk)
- [Python](https://github.com/edutecka/edpay-python-sdk)
- [PHP](https://github.com/edutecka/edpay-php-sdk)
- [Java](https://github.com/edutecka/edpay-java-sdk)
- [.NET](https://github.com/edutecka/edpay-dot-net-sdk)

### E-commerce integrations

These are links to official integration repositories; some contain previews. Review each repository's status and implementation limitations before installing.

- [WooCommerce (WordPress)](https://github.com/edutecka/edpay-woocommerce-plugin)
- [Shopify](https://github.com/edutecka/edpay-shopify-app)
- [Magento / Adobe Commerce](https://github.com/edutecka/edpay-magento2-module)
- [OpenCart](https://github.com/edutecka/edpay-opencart-extension)
- [WHMCS](https://github.com/edutecka/edpay-whmcs-gateway)
