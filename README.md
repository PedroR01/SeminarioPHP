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

## Actualizar autoload composer.json

```bash
docker compose run --rm composer dump-autoload
```

## Validaciones a tener en cuenta

<p>Al introducir el documento del Inquilino, respetar la validación de expresion regular con el siguiente formato: <br>xx.xxx.xxx o x.xxx.xxx</p>

<p>Al introducir el domicilio de la Propiedad, respetar la validación de expresion regular con el siguiente formato (sin usar las ""): <br>Calle "numeroDeCalle" "numeroDeDomicilio"</p>
