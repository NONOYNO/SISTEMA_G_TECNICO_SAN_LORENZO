# Configuración XAMPP

1. Abrir XAMPP Control Panel
2. Start **Apache** y **MySQL**
3. Confirmar `http://localhost/` responde
4. Proyecto en `C:\xampp\htdocs\PortalInfor`
5. En `httpd.conf` / vhost: `AllowOverride All` para `htdocs`

Si las rutas amigables fallan, verificar `app/public/.htaccess` y `mod_rewrite`.
