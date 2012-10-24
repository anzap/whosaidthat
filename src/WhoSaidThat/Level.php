<?php

namespace WhoSaidThat;

class Level {

	private $totalAvailableTime; 
    private $bonusFactor; 

	public function __construct($level) {
		if($level) {
			switch($level) {
				case 'Novice':
				$totalAvailableTime = 15;
				$bonusFactor = 80;
				break;
				case 'Normal':
				$totalAvailableTime = 10;
				$bonusFactor = 100;
				break;
				case 'Expert':
				$totalAvailableTime = 5;
				$bonusFactor = 150;
				break;
				default:
				throw new LevelSelectionException("The selected level is not recognized from the app");
			}
		}
	}

	public function getBonusFactor() {
		return $this->bonusFactor;
	}

	public function getTotalAvailableTime() {
		return $this->totalAvailableTime;
	}
	
}