# Dockerfile para Kubernetes
# Dockerfile optimizado para httpd:alpine
FROM httpd:alpine

# Copiar todo el contenido del repositorio (HTML, etc.) al directorio web de Apache
COPY . /usr/local/apache2/htdocs/

# El puerto 80 ya está expuesto por la imagen base, pero es buena práctica declararlo.
EXPOSE 80
