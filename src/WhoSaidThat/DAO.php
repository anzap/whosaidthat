<?php

namespace WhoSaidThat;

use WhoSaidThat\domain\User;
use WhoSaidThat\domain\Friend;
use WhoSaidThat\domain\Status;
use \PDO;

class DAO {
	private $pdo;
	private $userStm;
	private $updateUserPointsStm;
	private $friendStm;
	private $statusStm;
	private $questionStm;
	private $alternativesStm;
	private $answerStm;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
		$this->findUserStm = $this->pdo->prepare("SELECT * FROM users where id = :id");
		$this->userStm = $this->pdo->prepare("INSERT INTO users (id, name) VALUES (:id, :name)");
		$this->updateUserPointsStm = $this->pdo->prepare("UPDATE users SET points = :points WHERE id = :id");
		$this->friendStm = $this->pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (:user_id, :friend_id)");
		$this->statusStm = $this->pdo->prepare("INSERT INTO statuses (id, message, user_id) VALUES (:id, :message, :user_id)");
		$this->questionStm = $this->pdo->prepare("select * from statuses st
			inner join users us on (st.user_id=us.id)
			where st.id NOT IN (
				select status_id from answers where user_id = :user_id
			)
			and st.user_id in (
				select friend_id from friends where user_id = :user_id
			) 
			offset random() * (select count(*)/3 from statuses) limit 1");
		$this->alternativesStm = $this->pdo->prepare("select * from users 
			where id in (select friend_id from friends where user_id = :user_id) and id != :right_user_id");
		$this->answerStm = $this->pdo->prepare("INSERT INTO answers (user_id, status_id) VALUES (:user_id, :status_id)");
	}

	public function findUser($id) {
		$this->findUserStm->bindParam(':id', $id);
		$this->findUserStm->execute();
		$result = $this->findUserStm->fetch();
		return new User($result['id'], $result['name'], $result['points']);
	}	

	public function createUser(User $user) {
		$id = $user->getId();
		$this->userStm->bindParam(':id', $id);
		$name = $user->getName();
		$this->userStm->bindParam(':name', $name);
		$this->userStm->execute();
	}

	public function updateUserPoints(User $user) {
		$id = $user->getId();
		$points = $user->getPoints();
		$this->updateUserPointsStm->bindParam(':id', $id);
		$this->updateUserPointsStm->bindParam(':points', $points);
		$this->updateUserPointsStm->execute();
	}

	public function createFriend(Friend $friend) {
		$user_id = $friend->getUser()->getId();
		$this->friendStm->bindParam(':user_id', $user_id);
		$friend_id = $friend->getFriend()->getId();
		$this->friendStm->bindParam(':friend_id', $friend_id);
		$this->friendStm->execute();
	}

	public function createStatus(Status $status) {
		$id = $status->getId();
		$this->statusStm->bindParam(':id', $id);
		$user_id = $status->getUser()->getId();
		$this->statusStm->bindParam(':user_id', $user_id);
		$message = $status->getMessage();
		$this->statusStm->bindParam(':message', $message);
		$this->statusStm->execute();
	}

	public function getNextQuestion($user_id) {
		$this->questionStm->bindParam(':user_id', $user_id);
		$this->questionStm->execute();
		return $this->questionStm->fetchAll();
	}

	public function getAlternatives($user_id, $right_user_id) {
		$this->alternativesStm->bindParam(':user_id', $user_id);
		$this->alternativesStm->bindParam(':right_user_id', $right_user_id);
		$this->alternativesStm->execute();
		return $this->alternativesStm->fetchAll();
	}

	public function saveAnswer($user_id, $status_id) {
		$this->answerStm->bindParam(':user_id', $user_id);
		$this->answerStm->bindParam(':status_id', $status_id);
		$this->answerStm->execute();
	}
	
}