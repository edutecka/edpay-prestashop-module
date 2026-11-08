<?php
/**
 * EDPay Payment Module for PrestaShop 1.7 & 8.x
 *
 * @author    EDPay Engineering <dev@edpay.co.mz>
 * @copyright 2026 EDPay
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Edpay extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'edpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'EDPay Engineering';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EDPay Multi-Gateway (M-Pesa, e-Mola, Cartões)');
        $this->description = $this->l('Aceite pagamentos em Meticais (MZN) através de múltiplos canais locais e internacionais.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && Configuration::updateValue('EDPAY_API_URL', 'https://api-pay.edutecka.com')
            && Configuration::updateValue('EDPAY_SANDBOX', 1);
    }

    public function uninstall(): bool
    {
        return Configuration::deleteByName('EDPAY_PUBLIC_KEY')
            && Configuration::deleteByName('EDPAY_PRIVATE_KEY')
            && Configuration::deleteByName('EDPAY_WEBHOOK_SECRET')
            && Configuration::deleteByName('EDPAY_SANDBOX')
            && Configuration::deleteByName('EDPAY_API_URL')
            && parent::uninstall();
    }

    public function hookPaymentOptions($params): array
    {
        if (!$this->active) {
            return [];
        }

        $newOption = new PaymentOption();
        $newOption->setCallToActionText($this->l('Pagar com EDPay (M-Pesa, e-Mola, Cartão, Banco)'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setAdditionalInformation($this->l('Pagamento seguro multi-canal em Meticais (MZN).'));

        return [$newOption];
    }

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submit_edpay')) {
            Configuration::updateValue('EDPAY_PUBLIC_KEY', Tools::getValue('EDPAY_PUBLIC_KEY'));
            Configuration::updateValue('EDPAY_PRIVATE_KEY', Tools::getValue('EDPAY_PRIVATE_KEY'));
            Configuration::updateValue('EDPAY_WEBHOOK_SECRET', Tools::getValue('EDPAY_WEBHOOK_SECRET'));
            Configuration::updateValue('EDPAY_SANDBOX', (int) Tools::getValue('EDPAY_SANDBOX'));
            $output .= $this->displayConfirmation($this->l('Configurações atualizadas com sucesso!'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm(): string
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configurações da Conta EDPay'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Chave Pública (Public Key)'),
                        'name' => 'EDPAY_PUBLIC_KEY',
                        'desc' => $this->l('pk_live_... ou pk_test_...'),
                        'required' => true,
                    ],
                    [
                        'type' => 'password',
                        'label' => $this->l('Chave Privada (Private Key)'),
                        'name' => 'EDPAY_PRIVATE_KEY',
                        'desc' => $this->l('sk_live_... ou sk_test_...'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Segredo do Webhook'),
                        'name' => 'EDPAY_WEBHOOK_SECRET',
                        'desc' => $this->l('whsec_... obtido no painel EDPay.'),
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Modo Sandbox'),
                        'name' => 'EDPAY_SANDBOX',
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Sim')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Não')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Salvar'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->fields_value['EDPAY_PUBLIC_KEY'] = Configuration::get('EDPAY_PUBLIC_KEY');
        $helper->fields_value['EDPAY_PRIVATE_KEY'] = Configuration::get('EDPAY_PRIVATE_KEY');
        $helper->fields_value['EDPAY_WEBHOOK_SECRET'] = Configuration::get('EDPAY_WEBHOOK_SECRET');
        $helper->fields_value['EDPAY_SANDBOX'] = Configuration::get('EDPAY_SANDBOX');

        return $helper->generateForm([$fieldsForm]);
    }
}
