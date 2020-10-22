<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201021162239 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
        	'CREATE TABLE TicketMessage(
				id INT(11) NOT NULL AUTO_INCREMENT,
				ticketId VARCHAR(255) NOT NULL,
				authorId VARCHAR(255) NOT NULL,
				message TEXT NOT NULL,
				PRIMARY KEY(id)
			)
			DEFAULT CHARACTER SET utf8mb4
			COLLATE `utf8mb4_unicode_ci`
			ENGINE = InnoDB'
        );

	    $this->addSql(
		    'INSERT INTO TicketMessage (ticketId, authorId, message)
			 VALUES
			 	("70d48ec5-ceca-49b8-95e9-4f7ceae20451", "dfd32cbf-de2f-4616-90b6-27fc90632983", "first test message"),
			 	("70d48ec5-ceca-49b8-95e9-4f7ceae20451", "dfd32cbf-de2f-4616-90b6-27fc90632983", "second test message")'
	    );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE TicketMessage');
    }
}
