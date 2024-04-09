# Seminario de PHP, React, y API Rest

## Configuración inicial

1. Crear archivo `.env` a partir de `.env.dist`

```bash
cp .env.dist .env
```

2. Crear volumen para la base de datos

```bash
docker volume create seminariophp
```

donde _seminariophp_ es el valor de la variable `DB_VOLUME`

## Iniciar servicios

```bash
docker compose up -d
```

(En el caso de presentar algun tipo de error de comunicación con la base de datos o el servidor, como el error 2002)

```bash
docker compose up
```

## Terminar servicios

```bash
docker compose down -v
```

## Eliminar base de datos

```bash
docker volume rm seminariophp
```
