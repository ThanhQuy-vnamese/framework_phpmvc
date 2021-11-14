<?php

use App\Core\Application;

class m0001_initial
{
    public function up()
    {
        $database = Application::$APPLICATION->database;
        $query = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                firstname VARCHAR(255) NOT NULL,
                lastname VARCHAR(255) NOT NULL,
                status TINYINT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )  ENGINE=INNODB;";
        $database->mysql->query($query);
    }

    public function down()
    {
        $database = Application::$APPLICATION->database;
        $query = "DROP TABLE users;";
        $database->mysql->query($query);
    }
}
