<?php
/**
 * EDPay PrestaShop Webhook Controller
 */

class EdpayWebhookModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_EDPAY_SIGNATURE'] ?? '';
        $secret = Configuration::get('EDPAY_WEBHOOK_SECRET');

        if ($secret) {
            $expected = hash_hmac('sha256', $payload, $secret);
            if (!hash_equals($expected, (string) $signature)) {
                header('HTTP/1.1 401 Unauthorized');
                die(json_encode(['error' => 'Assinatura inválida']));
            }
        }

        $event = json_decode($payload, true);
        if (!$event || empty($event['type'])) {
            header('HTTP/1.1 400 Bad Request');
            die(json_encode(['error' => 'Payload inválido']));
        }

        if ($event['type'] === 'payment.succeeded') {
            $data = $event['data'] ?? [];
            $orderId = (int) ($data['metadata']['prestashop_order_id'] ?? 0);
            if ($orderId > 0) {
                $order = new Order($orderId);
                if (Validate::isLoadedObject($order)) {
                    $history = new OrderHistory();
                    $history->id_order = (int) $order->id;
                    $history->changeIdOrderState((int) Configuration::get('PS_OS_PAYMENT'), (int) $order->id);
                    $history->addWithemail(true);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}
