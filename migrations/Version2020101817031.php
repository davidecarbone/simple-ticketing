<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020101817031 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
        	'CREATE TABLE User(
				id VARCHAR(255) NOT NULL,
				username VARCHAR(32) NOT NULL,
				password VARCHAR(255) NOT NULL,
				fullName VARCHAR(64) DEFAULT NULL,
				type ENUM("ADMIN", "CUSTOMER") DEFAULT "CUSTOMER",
				PRIMARY KEY(id)
			)
			DEFAULT CHARACTER SET utf8mb4
			COLLATE `utf8mb4_unicode_ci`
			ENGINE = InnoDB'
        );

	    $this->addSql(
		    'INSERT INTO User (id, username, password, fullName, type)
			VALUES 
				("4d8f38dc-05d4-42a6-93fe-69a72fc533b1", "admin", "$2y$12$S3RahWt0Uh7DsjOXaiOhceqwy2Ryi.rc/ptYpUCKgK4Fsm1hX9jMS", "Administrator", "ADMIN"),
				("721d5112-e477-419c-9928-acfbb965c761", "user", "$2y$12$9QIS/MiyyIap1d0ueI6iWuMO5Kmx485kLDmBMOTH.Y.sHoBWjRWJu", "Mario Rossi", "CUSTOMER")'
	    );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE User');
    }
}
