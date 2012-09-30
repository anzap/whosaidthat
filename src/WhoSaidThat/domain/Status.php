<?php

namespace WhoSaidThat\domain;

class Status {
	private $id;
	private $message;
	private $user;

	public function __construct($id, $message, User $user) {
		$this->id = $id;
		$this->message = $message;
		$this->user = $user;
	}

	public function getId() {
		return $this->id;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getUser() {
		return $this->user;
	}
}