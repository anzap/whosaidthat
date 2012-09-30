<?php

namespace WhoSaidThat\domain;

class Friend {
	private $friend;
	private $user;

	public function __construct(User $friend, User $user) {
		$this->friend = $friend;
		$this->user = $user;
	}

	public function getFriend() {
		return $this->friend;
	}

	public function getUser() {
		return $this->user;
	}
}