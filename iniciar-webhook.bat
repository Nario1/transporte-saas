echo "Iniciando LocalTunnel para Webhooks de Mercado Pago..."
echo "URL Fija: https://transjunin-mp-test.loca.lt/webhook/mercadopago"
echo "--------------------------------------------------------"
echo "Mantener esta ventana abierta mientras se realizan pruebas locales"
lt --port 8000 --subdomain transjunin-mp-test
pause
