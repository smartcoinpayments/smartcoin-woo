#Vamos começar

O plugin para [WooCommerce](http://www.woothemes.com/woocommerce/) agiliza a integração da [Smartcoin](https://smartcoin.com.br/) com o WooCommerce para [Wordpress](https://wordpress.org/).

**1)** Baixe o plugin [aqui](https://github.com/smartcoinpayments/smartcoin-woo/archive/master.zip);

**2)** Instale o plugin no WordPress que já tenha o plugin do WooCommerce;

**3)** Configure as chaves de acesso a API (test/live). Se ainda não tem uma conta na Smartcoin, cadastre-se [aqui].(https://manage.smartcoin.com.br/#/signup)

Pronto! Vocês já pode usar a Smartcoin no ambiente de teste. Não se esquece te ativar sua conta da [Smartcoin](https://smartcoin.com.br/) para poder começar a receber pagamentos. Saiba mais como [aqui](https://github.com/smartcoinpayments/Documentation/wiki/Ativa%C3%A7%C3%A3o-da-Conta).

=== Smartcoin ===
Contributors: smartcoin
Donate link: https://smartcoin.com.br
Tags: pagamento, cartão de crédito, boleto bancário
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrate WooCommerce with Smartcoin to receive payments in Brasil

== Description ==

The Smartcoin plugin for WooCommerce allow the e-commerce charge via credit card or bank slip in R$ (Reais).

*   "Contributors" agranado
*   "Tags" payment, credit card, bank slip, pagamento, cartão de crédito, boleto

== Installation ==

This section describes how to install the plugin and get it working.

1. Install the plugin;
2. Create account in Smartcoin (https://manage.smartcoin.com.br);
3. Copy the Access Key to Smartcoin pulgin settings;

== Frequently Asked Questions ==

= How to create a Smartcoin Account =

Go to the website https://smartcoin.com.br and sign up to Smartcoin.

= Can I use the plugin without Smartcoin account =

No. You should create a Smartcoin account in https://smartcoin.com.br


== Changelog ==

= 0.3.1
* Integrate webhook to update the bank slip charge status
* BugFix in Credit Card form
* Improve the Thank You page to avoid crash previous layout
* Improve the Email instructions layout

= 0.2.0
* Allow bank slip charge
* Update Smartcoin lib for 0.3.5 version
* Include more informartion in Order notes about Smartcoin charge

= 0.1.0 =
* Can create credit card charges
