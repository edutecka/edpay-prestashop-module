# Roadmap & Plano de Arquitetura - EDPay PrestaShop Module

## Fases de Desenvolvimento

### Fase 1: Estrutura do Módulo (Concluído)
- [x] Declaração da classe `EdPay` herdando de `PaymentModule`.
- [x] Registo dos hooks `paymentOptions` e `paymentReturn`.

### Fase 2: Controladores Front & Webhook (Em Desenvolvimento)
- [ ] Front controller para redirecionamento ao checkout hosted.
- [ ] Module front controller para validação de webhooks (`/module/edpay/validation`).

### Fase 3: Certificação PrestaShop Addons
- [ ] Validação de padrões de código do PrestaShop Validator.
- [ ] Publicação no PrestaShop Addons Marketplace.
