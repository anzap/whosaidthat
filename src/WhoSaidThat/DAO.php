<?php

namespace WhoSaidThat;

use WhoSaidThat\domain\User;
use WhoSaidThat\domain\Friend;
use WhoSaidThat\domain\Status;
use \PDO;

class DAO {
	private $pdo;
	private $userStm;
	private $friendStm;
	private $statusStm;
	private $questionStm;
	private $alternativesStm;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
		$this->userStm = $this->pdo->prepare("INSERT INTO users (id, name) VALUES (:id, :name)");
		$this->friendStm = $this->pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (:user_id, :friend_id)");
		$this->statusStm = $this->pdo->prepare("INSERT INTO statuses (id, message, user_id) VALUES (:id, :message, :user_id)");
		$this->questionStm = $this->pdo->prepare("select * from statuses st
			inner join users us on (st.user_id=us.id)
			where st.id NOT IN (
				select status_id from answers where user_id = :user_id
			) 
			offset random() * (select count(*) from statuses) limit 1");
		$this->alternativesStm = $this->pdo->prepare("select * from users where id != :user_id and id != :right_user_id offset random() limit 3");
	}

	public function createUser(User $user) {
		$id = $user->getId();
		$this->userStm->bindParam(':id', $id);
		$name = $user->getName();
		$this->userStm->bindParam(':name', $name);
		$this->userStm->execute();
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
	
}