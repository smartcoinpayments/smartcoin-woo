var headTag = document.getElementsByTagName('head')[0];
var smartTag = document.createElement('script');
smartTag.type = 'text/javascript';
smartTag.async = false;
smartTag.src = 'https://js.smartcoin.com.br/v1/smartcoin.js';
headTag.appendChild(smartTag);