APP_NAME="{{ $restaurant?->name }}"
APP_ENV=production
APP_KEY=base64:FBQ0+qwgIiBuy0CfgzY7HiQlIivQAcsJqywCut1jqPA=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL={{ $restaurant?->domain }}
#ASSET_URL="${APP_URL}/public"

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION={{ $restaurant?->db?->first()?->connection ?? "mysql" }}
DB_HOST={{ $restaurant?->db?->first()?->host ?? "127.0.0.1" }}
DB_PORT={{ $restaurant?->db?->first()?->port ?? 3306 }}
DB_DATABASE={{ $restaurant?->db?->first()?->database ?? 3306 }}
DB_USERNAME={{ $restaurant?->db?->first()?->username ?? 3306 }}
DB_PASSWORD={{ $restaurant?->db?->first()?->password ?? 3306 }}

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# CUSTOM CREDENTIALS
API_URL=https://menuempire.com
RESTAURANT_URL=${APP_URL}