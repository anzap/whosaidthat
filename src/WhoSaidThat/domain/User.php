<?php

namespace WhoSaidThat\domain;

class User {
	private $id;
	private $name;
	private $points;

	public function __construct($id, $name = '', $points = 0) {
		$this->id = $id;
		$this->name = $name;
		$this->points = $points;
	}

	public function getId() {
		return $this->id;
	}
	public function getName() {
		return $this->name;
	}
	public function getPoints() {
		return $this->points;
	}
	public function setPoints($points = 0) {
		$this->points = $points;
	}
	public function __toString() {
		return $this->id.' '.$this->name.' '.$this->points;
	}
}