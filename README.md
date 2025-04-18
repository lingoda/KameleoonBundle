# KameleoonBundle
Kameleoon API integration for Symfony apps

## Installation
```
composer req lingoda/kameleoon-bundle
```

## Bundle configuration

```
# config/packages/lingoda_kameleoon.yaml

lingoda_kameleoon:
  client_id: '%env(KAMELEOON_CLIENT_ID)%'
  client_secret: '%env(KAMELEOON_CLIENT_SECRET)%'
  site_code: '%env(KAMELEOON_SITE_CODE)%'
  cookie_options:
    domain: '%env(KAMELEOON_COOKIE_DOMAIN)%'
    secure: false
    http_only: false
    same_site: Lax
  work_dir: /tmp/app/cache/dev/kameleoon
  environment: '%kernel.environment%'
  debug_mode: '%kernel.debug%'
  refresh_interval_minute: 60
  default_timeout_millisecond: 10000
```
