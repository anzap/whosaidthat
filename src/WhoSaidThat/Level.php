<?php

namespace WhoSaidThat;

class Level {

	private $totalAvailableTime; 
    private $bonusFactor; 

	public function __construct($level) {
		switch($level) {
			case 'Novice':
				$this->totalAvailableTime = 15;
				$this->bonusFactor = 80;
				break;

			case 'Normal':
				$this->totalAvailableTime = 10;
				$this->bonusFactor = 100;
				break;

			case 'Expert':
				$this->totalAvailableTime = 5;
				$this->bonusFactor = 150;
				break;

			default:
				throw new LevelSelectionException("The selected level is not recognized from the app");
		}
	}

	public function getBonusFactor() {
		return $this->bonusFactor;
	}

	public function getTotalAvailableTime() {
		return $this->totalAvailableTime;
	}

	public function __toString() {
		return $this->totalAvailableTime.' '.$this->bonusFactor;
	}
	
}