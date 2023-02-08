# composer
```sh
composer install
```
```sh
docker
```

# basic configuration
1.Launch up docker-compose
  ```sh
  docker-compose up -d
  ```
2.Install npm necessities
  ```sh
  docker-compose run node bash

  npm install

  npm run watch

  exit
  ```
3.Install php necessities
  ```sh
  docker-compose exec php bash

  php bin/console doctrine:database:create

  php bin/console doctrine:migrations:migrate

  sudo chown -R www-data:www-data /application/

  sudo chown -R g+rw /application/
  ```
# add users

```sh
php bin/console app:add-user <name> <surname> <email> <role> <password>
```
