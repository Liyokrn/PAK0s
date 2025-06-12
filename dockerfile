# Dockerfile para Kubernetes
FROM php:8.2-apache

# Habilitar módulos de Apache necesarios para Kubernetes
RUN a2enmod rewrite headers remoteip

# Copiar archivos PHP al directorio web
COPY index.php /var/www/html/
COPY apache-k8s.conf /etc/apache2/conf-available/kubernetes.conf

# Habilitar configuración de Kubernetes
RUN a2enconf kubernetes

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80