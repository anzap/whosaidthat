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

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
		$this->userStm = $this->pdo->prepare("INSERT INTO users (id, name) VALUES (:id, :name)");
		$this->friendStm = $this->pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (:user_id, :friend_id)");
		$this->statusStm = $this->pdo->prepare("INSERT INTO statuses (id, message, user_id) VALUES (:id, :message, :user_id)");
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
	
}