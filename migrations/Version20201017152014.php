<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201017152014 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
        	'CREATE TABLE Ticket(
				id VARCHAR(255) NOT NULL,
				status VARCHAR(32)NOT NULL ,
				assignedTo VARCHAR(255) DEFAULT NULL,
				createdOn DATETIME NOT NULL,
				updatedOn DATETIME NOT NULL,
				PRIMARY KEY(id)
			)
			DEFAULT CHARACTER SET utf8mb4
			COLLATE `utf8mb4_unicode_ci`
			ENGINE = InnoDB'
        );

	    $this->addSql(
		    'INSERT INTO Ticket (id, status, assignedTo, createdOn, updatedOn)
			 VALUES ("70d48ec5-ceca-49b8-95e9-4f7ceae20451", "Nuovo", NULL, "2020-10-20 06:37:41", "2020-10-20 06:37:41")'
	    );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE Ticket');
    }
}
