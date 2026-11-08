# Módulo de Pagamentos EDPay para PrestaShop (1.7 & 8.x)

[![Licença: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Versão PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%20|%208.x-orange.svg)](https://prestashop.com)
[![Multi-Gateway](https://img.shields.io/badge/EDPay-Multi--Gateway-0051FA.svg)](https://edpay.co.mz)

Módulo oficial para aceitar pagamentos com a **EDPay** em lojas virtuais **PrestaShop**, suportando **M-Pesa**, **e-Mola**, **Cartões Visa / Mastercard** e **Transferência Bancária** em Meticais (MZN).

---

## 🚀 Recursos Principais

- Integração nativa com o fluxo de checkout standard e one-page checkout do PrestaShop.
- Suporte a múltiplos canais de pagamento sem fricção.
- Notificações de confirmação de pagamento instantâneas via Webhooks (HMAC-SHA256).
- Atualização automática do estado da encomenda para `Pagamento Aceite` (`PS_OS_PAYMENT`).
- Suporte integral ao ambiente Sandbox e Produção (Live).

---

## 📦 Instalação

1. Baixe o repositório como ficheiro `.zip` ou clone na pasta `/modules/edpay/` da sua loja PrestaShop:
   ```bash
   cd /var/www/prestashop/modules
   git clone https://github.com/edutecka/edpay-prestashop-module.git edpay
   ```
2. No painel administrativo do PrestaShop, navegue até **Módulos > Gerenciador de Módulos**.
3. Procure por **EDPay Multi-Gateway** e clique em **Instalar**.
4. Clique em **Configurar**.

---

## ⚙️ Configuração

Insira as chaves de API obtidas no seu painel [EDPay](https://edpay.co.mz):
- **Chave Pública**: `pk_live_...` (ou `pk_test_...` para testes)
- **Chave Privada**: `sk_live_...`
- **Segredo do Webhook**: `whsec_...`
- **Modo Sandbox**: Ative para testes.

### URL de Webhook no Painel EDPay:
```
https://sua-loja.co.mz/module/edpay/webhook
```

---

## 📄 Licença

Distribuído sob licença MIT. Consulte `LICENSE` para detalhes adicionais.
