<?php

require_once('Database/DatabaseAccess.php');

class ReviewsRepository {
    private $dbConnection;

    public function __construct() {
        $this->dbConnection = new DatabaseAccess();
    }

    public function __destruct() {
        unset($this->dbConnection);
    }

    public function postReview($object, $content, $user) {
        $statement = $this->dbConnection->prepareQuery('INSERT INTO Recensioni (ID, Oggetto, Contenuto, DataUltimaModifica, Utente) VALUES (NULL, ?, ?, NOW(), ?);');
        $statement->bind_param('sss', $object, $content, $user);
        return $this->dbConnection->executeNotSelectStatement($statement);
    }

    public function getReviews($offset) {
        $statement = $this->dbConnection->prepareQuery('SELECT * FROM Recensioni ORDER BY DataUltimaModifica DESC LIMIT 5 OFFSET ?;');
        $statement->bind_param('i', $offset);
        return $this->dbConnection->executeSelectStatement($statement);
    }
	
	public function getReviewsCount() {
        $statement = $this->dbConnection->prepareQuery('SELECT COUNT(*) AS Totale FROM Recensioni;');
        return $this->dbConnection->executeSelectStatement($statement);
    }

    public function getUserReviewsCount($user) {
        $statement = $this->dbConnection->prepareQuery('SELECT COUNT(*) AS Totale FROM Recensioni WHERE Utente=?;');
        $statement->bind_param('s', $user);
        return $this->dbConnection->executeSelectStatement($statement);
    }

    public function getUserReviews($user, $offset) {
        $statement = $this->dbConnection->prepareQuery('SELECT * FROM Recensioni WHERE Utente=? ORDER BY DataUltimaModifica DESC LIMIT 5 OFFSET ?;');
        $statement->bind_param('si', $user, $offset);
        return $this->dbConnection->executeSelectStatement($statement);
    }

    public function getReview($id) {
        $statement = $this->dbConnection->prepareQuery('SELECT * FROM Recensioni WHERE ID=?;');
        $statement->bind_param('i', $id);
        return $this->dbConnection->executeSelectStatement($statement);
    }

    public function updateReview($id, $object, $description, $user) {
        $statement = $this->dbConnection->prepareQuery('UPDATE Recensioni SET Oggetto=?, Contenuto=?, DataUltimaModifica=NOW() WHERE ID=? AND Utente=?;');
        $statement->bind_param('ssis', $object, $description, $id, $user);
        return $this->dbConnection->executeNotSelectStatement($statement);
    }

    public function deleteReview($id) {
        $statement = $this->dbConnection->prepareQuery('DELETE FROM Recensioni WHERE ID=?;');
        $statement->bind_param('i', $id);
        return $this->dbConnection->executeNotSelectStatement($statement);
    }
}

?>
