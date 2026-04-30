# Guía de Despliegue - VoltGym en Servidor Remoto

Esta guía te permite levantar el proyecto completo en un servidor Ubuntu/Debian remoto.

---

## Requisitos del Servidor

- **Ubuntu 20.04+ / Debian 11+**
- **Docker** instalado
- **Docker Compose** instalado
- **Puerto 80, 443, 22 (SSH) disponibles**
- **2GB RAM mínimo** (recomendado 4GB+)
- **20GB disco mínimo**

---

## Paso 1: Conectar al Servidor por SSH

```bash
ssh usuario@tu-servidor-ip
```

Ejemplo:
```bash
ssh root@192.168.1.100
```

---

## Paso 2: Instalar Docker (si no está instalado)

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias
sudo apt install -y ca-certificates curl gnupg

# Añadir repositorio Docker
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Añadir usuario al grupo docker
sudo usermod -aG docker $USER
newgrp docker
```

---

## Paso 3: Clonar el Repositorio

```bash
# Crear directorio para el proyecto
mkdir -p ~/voltgym
cd ~/voltgym

# Clonar repositorio
git clone https://github.com/ieszaidinvergeles/fitapp.git .

# Entrar a la carpeta de infraestructura
cd voltgym-infra
```

---

## Paso 4: Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar el archivo .env
nano .env
```

**Configuración mínima para producción:**

```env
APP_ENV=production
APP_KEY=                    # Se generará después
APP_URL=https://tudominio.com

# MySQL
DB_ROOT_PASSWORD=contraseña_root_segura
DB_DATABASE=voltgym
DB_USERNAME=voltgym_user
DB_PASSWORD=contraseña_db_segura

# WordPress
WP_DB_NAME=voltgym_wordpress
WP_ADMIN_USER=admin
WP_ADMIN_PASSWORD=contraseña_wp_segura
WP_ADMIN_EMAIL=admin@tudominio.com

# Repositorio
REPO_URL=https://github.com/ieszaidinvergeles/fitapp.git
REPO_BRANCH=main
```

**Guardar y salir:** `Ctrl+O`, luego `Ctrl+X`

---

## Paso 5: Configurar SSL (HTTPS)

### Opción A: Certificados auto-firmados (desarrollo)

```bash
# Los certificados ya están incluidos en ssl_certs/
# Se generarán automáticamente en el primer inicio
```

### Opción B: Let's Encrypt (producción)

```bash
# Instalar Certbot
sudo apt install -y certbot

# Obtener certificado (reemplaza tudominio.com)
sudo certbot certonly --standalone -d tudominio.com -d www.tudominio.com

# Copiar certificados a ssl_certs/
sudo cp /etc/letsencrypt/live/tudominio.com/fullchain.pem voltgym-infra/ssl_certs/certificate.crt
sudo cp /etc/letsencrypt/live/tudominio.com/privkey.pem voltgym-infra/ssl_certs/private.key
```

---

## Paso 6: Iniciar los Contenedores

```bash
# Construir e iniciar todos los servicios
docker compose up -d --build

# Ver estado de los contenedores
docker compose ps
```

**Servicios que se iniciarán:**
| Contenedor | Puerto | Descripción |
|------------|--------|-------------|
| nginx | 80, 443 | Proxy inverso |
| laravel | 9000 | API REST |
| wordpress | 9000 | Frontend |
| mysql | 3306 | Base de datos |
| redis | 6379 | Cache/Colas |
| adminer | 8080 | UI base de datos |

---

## Paso 7: Esperar a MySQL y Bootstrap Laravel

```bash
# Ver logs de MySQL
docker compose logs -f mysql

# Cuando MySQL esté listo (20-30 segundos), ejecutar:
docker compose exec laravel bash -c "
    composer install --no-dev --optimize-autoloader &&
    php artisan key:generate &&
    php artisan migrate --force &&
    php artisan db:seed --force &&
    php artisan storage:link &&
    chmod -R 775 storage bootstrap/cache
"
```

---

## Paso 8: Verificar Servicios

```bash
# Ver todos los servicios
docker compose ps

# Probar API
curl http://localhost/api/v1/activities

# Probar WordPress
curl http://localhost:8000

# Acceder a Adminer (UI de base de datos)
# http://localhost:8080
# Servidor: mysql
# Usuario: voltgym_user
# Contraseña: (la que pusiste en .env)
```

---

## Comandos Útiles

### Ver logs
```bash
# Todos los servicios
docker compose logs -f

# Servicio específico
docker compose logs -f laravel
docker compose logs -f nginx
docker compose logs -f mysql
```

### Reiniciar servicios
```bash
docker compose restart
```

### Parar servicios
```bash
docker compose down
```

### Actualizar código (después de git pull)
```bash
docker compose down
git pull origin main
docker compose up -d --build
docker compose exec laravel bash -c "php artisan migrate --force"
```

### Backup de base de datos
```bash
docker compose exec mysqldump -u voltgym_user -p voltgym > backup_$(date +%Y%m%d).sql
```

### Ver uso de recursos
```bash
docker stats
```

---

## Credenciales de Demo

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@fitapp.com | Admin1234! |
| Manager | manager@fitapp.com | Manager1234! |
| Assistant | assistant@fitapp.com | Assistant1234! |
| Staff | staff@fitapp.com | Staff1234! |
| Client | client@fitapp.com | Client1234! |

---

## Puertos y URLs Finales

| Servicio | URL | Puerto |
|----------|-----|--------|
| API Laravel | http://tudominio.com/api/ | 80 |
| WordPress | http://tudominio.com:8000 | 8000 |
| Adminer | http://tudominio.com:8080 | 8080 |

---

## Solución de Problemas

### MySQL no inicia
```bash
# Ver logs detallados
docker compose logs mysql

# Eliminar volumen y recrear
docker compose down -v
docker compose up -d
```

### Permisos Laravel
```bash
docker compose exec laravel chmod -R 775 storage bootstrap/cache
```

### Reiniciar todo desde cero
```bash
docker compose down -v
docker compose up -d --build
```

### Ver uso de memoria
```bash
docker stats --no-stream
```

---

## Notas Adicionales

1. **Firewall:** Si tienes ufw instalado, permite los puertos:
   ```bash
   sudo ufw allow 22    # SSH
   sudo ufw allow 80    # HTTP
   sudo ufw allow 443   # HTTPS
   sudo ufw allow 8080 # Adminer
   sudo ufw allow 8000  # WordPress
   ```

2. **Dominio:** Configura tu DNS para que apunte a la IP del servidor.

3. **SSL:** Para producción, usa Let's Encrypt como se indicó en el Paso 5.

4. **Backups:** Programa backups automáticos del volumen de MySQL.