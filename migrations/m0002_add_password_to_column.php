<?php

use App\Core\Application;

class m0002_add_password_to_column
{
    public function up()
    {
        $database = Application::$APPLICATION->database;
        $query = "ALTER TABLE users ADD COLUMN password VARCHAR(512) NOT NULL";
        $database->mysql->query($query);
    }

    public function down()
    {
        $database = Application::$APPLICATION->database;
        $query = "ALTER TABLE users ADD COLUMN password VARCHAR(512) NOT NULL";
        $database->mysql->query($query);
    }
}
