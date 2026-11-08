<?php
/**
 * EDPay PrestaShop Validation Controller
 */

class EdpayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        // Pre-create order in Pending status
        $this->module->validateOrder(
            $cart->id,
            Configuration::get('PS_OS_PREPARATION'),
            $total,
            $this->module->displayName,
            null,
            [],
            (int) $currency->id,
            false,
            $customer->secure_key
        );

        $orderId = $this->module->currentOrder;

        // Create Checkout Link via EDPay API
        $publicKey = Configuration::get('EDPAY_PUBLIC_KEY');
        $privateKey = Configuration::get('EDPAY_PRIVATE_KEY');
        $apiUrl = rtrim(Configuration::get('EDPAY_API_URL') ?: 'https://api-pay.edutecka.com', '/');

        $payload = [
            'title' => "Encomenda #{$orderId}",
            'description' => "Pagamento da Encomenda PrestaShop #{$orderId}",
            'amount' => (int) round($total * 100),
            'currency' => $currency->iso_code ?: 'MZN',
            'metadata' => [
                'prestashop_order_id' => (string) $orderId,
                'cart_id' => (string) $cart->id,
            ],
            'success_url' => $this->context->link->getPageLink('order-confirmation', true, null, [
                'id_cart' => (int) $cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => $this->module->currentOrder,
                'key' => $customer->secure_key,
            ]),
            'cancel_url' => $this->context->link->getPageLink('order', true, null, ['step' => 3]),
        ];

        $ch = curl_init("{$apiUrl}/v1/core/payment-links");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "X-EDPay-Public-Key: {$publicKey}",
                "X-EDPay-Private-Key: {$privateKey}",
                "Idempotency-Key: ps-order-{$orderId}-" . time(),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode((string) $response, true);
            $checkoutUrl = $data['data']['hosted_url'] ?? $data['data']['checkout_url'] ?? null;
            if ($checkoutUrl) {
                Tools::redirect($checkoutUrl);
                return;
            }
        }

        // If API fails, redirect to error page
        $this->errors[] = $this->module->l('Não foi possível gerar a ligação de pagamento EDPay. Tente novamente.');
        $this->redirectWithNotifications('index.php?controller=order&step=3');
    }
}
