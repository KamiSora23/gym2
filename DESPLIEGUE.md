# Despliegue con Docker y Cloudflare

La aplicación corre en dos contenedores:

| Contenedor | Qué es | Red |
|---|---|---|
| `gym-app` | PHP 8.3 + Apache con Laravel ya compilado (assets incluidos) | `interna` y `cloudflare_red` |
| `gym-db` | MySQL 8.4 (sin puerto público) | `interna` |

Cloudflare entra por la red `cloudflare_red`, donde ya debe estar tu contenedor `cloudflared`.

---

## 1. Primera instalación

```bash
git clone https://github.com/KamiSora23/gym2.git gym
cd gym

# La red del túnel (si ya existe, este paso da un aviso y no pasa nada)
docker network create cloudflare_red

cp .env.docker.example .env
nano .env            # completa todos los valores con <...>
```

Genera la clave de la aplicación y pégala en `APP_KEY` del `.env`:

```bash
docker compose build
docker compose run --rm --no-deps --entrypoint php app artisan key:generate --show
```

Arranca:

```bash
docker compose up -d
docker compose logs -f app     # verás las migraciones y "Base de datos nueva: creando roles…"
```

En el primer arranque se crean automáticamente:

- los roles y permisos,
- el **super administrador** (`SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD`),
- la empresa principal con su **administrador** (`ADMIN_EMAIL` / `ADMIN_PASSWORD`),
- y, si pusiste `GYM_DATOS_DEMO=true`, datos de ejemplo.

> Las contraseñas del `.env` solo se usan esa primera vez. Después cámbialas desde **Mi perfil**.
> Evita el carácter `$` en las contraseñas del `.env` (docker compose lo interpreta como variable).

## 2. Cloudflare Tunnel

En **Cloudflare Zero Trust → Networks → Tunnels → tu túnel → Public Hostname → Add**:

| Campo | Valor |
|---|---|
| Subdomain / Domain | el dominio que pusiste en `APP_URL` |
| Service type | `HTTP` |
| URL | `gym-app:80` |

En el panel de tu dominio en Cloudflare:

- **SSL/TLS → Overview**: modo *Full* (o *Flexible* si solo usas el túnel; el túnel ya cifra el tramo hasta tu servidor).
- **SSL/TLS → Edge Certificates**: activa *Always Use HTTPS*.

La app ya está preparada para esto:

- confía en el proxy (`X-Forwarded-Proto`) y genera todos los enlaces en `https`,
- registra la IP real del visitante (`CF-Connecting-IP`) en la auditoría y en el límite de intentos de login,
- las cookies de sesión van marcadas como seguras (`SESSION_SECURE_COOKIE=true`).

La cámara del **check-in con QR** necesita `https`: con el dominio de Cloudflare funciona en el celular o la tablet de recepción.

## 3. Actualizar a una nueva versión

```bash
cd gym
git pull
docker compose build app
docker compose up -d app
```

Al arrancar, el contenedor aplica las migraciones nuevas, actualiza el catálogo de permisos y vuelve a generar las cachés. Los datos (MySQL) y los archivos subidos (logos y fotos de productos) están en volúmenes y no se pierden.

## 4. Copias de seguridad

Base de datos:

```bash
docker compose exec db sh -c 'mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction "$MYSQL_DATABASE"' > respaldo_$(date +%F).sql
```

Restaurar:

```bash
docker compose exec -T db sh -c 'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < respaldo_2026-11-03.sql
```

Archivos subidos (logos, fotos):

```bash
docker run --rm -v gym_gym_storage:/datos -v "$PWD":/respaldo alpine tar czf /respaldo/archivos_$(date +%F).tgz -C /datos app/public
```

> El nombre real del volumen es `<carpeta>_gym_storage`; revísalo con `docker volume ls`.

## 5. Comandos útiles

```bash
docker compose ps                                   # estado (ambos deben salir "healthy")
docker compose logs -f app                          # registros de la app
docker compose exec app php artisan gym:superadmin  # crear o recuperar un super administrador
docker compose exec app tail -n 100 storage/logs/laravel.log
docker compose restart app
```

## 6. Problemas frecuentes

| Síntoma | Causa y solución |
|---|---|
| `ERROR: falta APP_KEY` | Genera la clave (paso 1) y pégala en el `.env`. |
| `network cloudflare_red declared as external, but could not be found` | `docker network create cloudflare_red` y conecta ahí tu `cloudflared`. |
| Error 502 en Cloudflare | En el túnel la URL debe ser `gym-app:80` y `cloudflared` debe estar en la red `cloudflare_red`. |
| La página carga sin estilos o con enlaces `http://` | `APP_URL` debe empezar con `https://` y ser el dominio real. Luego `docker compose up -d app`. |
| Cambié la contraseña de la base en el `.env` y ya no conecta | MySQL solo toma esas variables al crear el volumen. Cámbiala dentro de MySQL o, si la base es nueva y vacía, borra el volumen `gym_mysql`. |
| Las horas de las asistencias no coinciden | Revisa `APP_TIMEZONE` (por defecto `America/Lima`). |
