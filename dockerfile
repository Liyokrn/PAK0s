# Usamos una imagen base de nginx
FROM nginx:alpine

# Copiamos nuestro archivo HTML al directorio de nginx
COPY index.html /usr/share/nginx/html/index.html

# Exponemos el puerto 80
EXPOSE 80

# Comando para iniciar nginx
CMD ["nginx", "-g", "daemon off;"]