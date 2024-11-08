#!/bin/bash

	# Detener todos los contenedores en ejecución
	echo "Deteniendo todos los contenedores Docker..."
	docker-compose down

	# Limpiar todos los contenedores, imágenes y volúmenes no utilizados
	echo "Aplicando limpieza completa con docker system prune..."
	docker system prune -a -f

	# Levantar el contenedor nginx
	echo "Levantando el contenedor nginx..."
	docker-compose up -d nginx

	# Levantar el contenedor crontab
	echo "Levantando el contenedor crontab..."
	docker-compose up -d cron

	# Levantar el contenedor supervisor
	echo "Levantando el contenedor supervisor..."
	docker-compose up -d supervisor

	echo "Operación completada."
