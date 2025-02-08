PASSWORD=$(grep MYSQL_ROOT_PASSWORD .env | awk -F '=' '{print $2}')
echo "
use camagru;
insert into users (username, email, password, is_verified, notification) values ('admin', 'ok', 'admin', 1, 0);
SET @admin_id = LAST_INSERT_ID();
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/example.png');
insert into posts (user_id, image_path) values (@admin_id, 'img/egervais.jpg');
" | docker exec -i camagru-mysql-1 mysql -u root -p$PASSWORD