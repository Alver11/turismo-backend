# Actualizacion a Laravel 13

## Estado

El proyecto fue actualizado de Laravel 10.32.1 a Laravel 13.29.0. Conserva la
estructura clasica (`Http/Kernel.php`, providers y `Exceptions/Handler.php`),
que Laravel sigue soportando para aplicaciones actualizadas. El requisito
minimo paso de PHP 8.1 a PHP 8.3.

Dependencias principales actualizadas:

- Sanctum 3 -> 4.3
- Tinker 2 -> 3
- Spatie Permission 6 -> 8.3
- Yajra DataTables Oracle 10 -> 13.3
- PHPUnit 10 -> 12.5
- Carbon 2 -> 3

Se retiro el metapaquete `yajra/laravel-datatables`, porque la aplicacion solo
usa `yajra/laravel-datatables-oracle`; los modulos de botones, Excel y export no
aparecen en el codigo. Tambien se retiro `laravel-lang/common`: las traducciones
publicadas permanecen en `lang/` y el paquete no se usa en runtime.

## Inventario funcional

La aplicacion registra 64 rutas bajo `api/v1`: 53 protegidas por Sanctum y 11
publicas. Ademas existe la pagina web `/` y rutas internas de Sanctum/Ignition.

| Area | Funciones | Acceso |
| --- | --- | --- |
| Autenticacion | login, usuario actual, logout, CSRF, inicio/callback Google | Mixto |
| Roles y usuarios | CRUD, roles, permisos | Sanctum |
| Categorias y atributos | CRUD, listados auxiliares, panel de graficos | Mixto |
| Lugares turisticos | CRUD y listado publico | Mixto |
| Eventos y categorias de eventos | CRUD y listados publicos | Mixto |
| Tipos de servicio y servicios | CRUD y listado publico | Mixto |
| Departamentos y distritos | listados auxiliares | Sanctum |
| IA | consulta al modelo ajustado | Publico |

Los nueve recursos CRUD son `roles`, `users`, `categories`, `attributes`,
`typeServices`, `services`, `tourists`, `events` y `event-categories`.

## Compatibilidad revisada

- No hay implementaciones personalizadas de contratos de cache, cola,
  autenticacion o respuesta afectadas por Laravel 11-13.
- No hay migraciones con `change()`, tipos espaciales, `upsert()` ni floats con
  precision antigua. Los `double` existentes no pasan precision y son validos.
- Los modelos no crean nuevas instancias durante `boot()`; sus hooks de borrado
  siguen siendo compatibles.
- Los prefijos de cache y la cookie de sesion estan definidos en configuracion,
  por lo que conservan el formato anterior.
- Se adapto la constante SSL de PDO para PHP 8.5 sin perder compatibilidad con
  PHP 8.3/8.4.

## Riesgos corregidos

- `POST /api/v1/ask` ahora requiere Sanctum, permiso `ai ask`, validacion de
  entrada y un limite de 10 solicitudes por minuto por usuario.
- Las credenciales y modelos OpenAI se leen desde `config/services.php`; el job
  usa el endpoint vigente `POST /v1/fine_tuning/jobs`.
- Google OAuth crea o vincula la cuenta y devuelve un token Sanctum con la misma
  expiracion que el login tradicional.
- Los CRUD y listados administrativos exigen permisos de modulo. El rol
  `Super-Admin` tiene bypass explicito mediante Gate.
- Las validaciones de atributos y tipos de servicio usan sus tablas correctas.
- Los API resources dejaron de usar `env()` y son compatibles con
  `config:cache`.
- El borrado en cascada delega la eliminacion fisica al modelo `Image`, evitando
  el doble borrado desde los modelos padre.

## Trabajo pendiente recomendado

- Agregar pruebas de integracion de CRUD, permisos, uploads y PostgreSQL sobre
  una base temporal. Las pruebas actuales cubren arranque y controles de rutas.
- Definir `OPENAI_CHAT_MODEL` después de crear el modelo ajustado y probar el
  contrato real con OpenAI en un entorno de desarrollo con credito limitado.
- Confirmar con el frontend si Google OAuth debe recibir el token como JSON o
  mediante un redirect de un solo uso.

## Despliegue

Requisitos del servidor: PHP >= 8.3, `ext-curl`, extensiones PDO del motor usado
(actualmente PostgreSQL), mbstring, OpenSSL, tokenizer, ctype, fileinfo, DOM/XML
y Composer 2 actualizado.

Secuencia sugerida:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Antes del despliegue final se debe probar una copia de la base PostgreSQL y
ejecutar pruebas de contrato desde el frontend sobre los 64 endpoints API.
