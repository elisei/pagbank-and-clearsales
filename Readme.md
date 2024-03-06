# O2TI - PagBank e Clear Sales
Automatiza o fluxo de captura e negação de pedidos na PagBank de a acordo com o status do pedido retornado pela clear sales.

Nesse processo, após a decisão de análise de risco da Clear Sale é executada a ação de Accept ou Deny do pagamento na Magento/Adobe Commerce.

## Configuração

> Lojas -> Situação do pedido

![Captura de tela de 2023-09-14 10-41-02](https://github.com/elisei/pagbank-and-clearsales/assets/1786389/74feaa21-bb1f-4548-9869-67338ab1c7a7)

Atribuir os status criados pelo módulo da Clear Sale com o state payment_review, conforme print:


> Lojas -> Configuração -> Aba O2TI -> PagBank and Clear Sale

![Captura de tela de 2023-09-14 10-43-06](https://github.com/elisei/pagbank-and-clearsales/assets/1786389/f5bcf721-19ae-455e-b1c3-07cd2d018d74)


## Instalação
```bash
composer require o2ti/pagbank-and-clearsale
bin/magento s:u
bin/magento s:d:c
```
