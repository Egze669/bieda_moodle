# bieda_moodle
composer install

docker

//basic nofiguration
docker-compose up -d
docker-compose run node bash
npm install
npm run watch
exit

docker-compose exec php bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
sudo chown -R www-data:www-data /application/
sudo chown -R g+rw /application/

//dodawanie 
php bin/console app:add-user <name> <surname> <email> <role> <password>

