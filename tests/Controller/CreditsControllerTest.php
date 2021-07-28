<?php


namespace App\Tests\Entity;

use App\Entity\Config;
use PHPUnit\Framework\TestCase;

include 'D:/xampp_htdocs/bakis/src/Entity/Config.php';

class ConfigTest extends TestCase {
	public function testName() {
		$cfg = new Config();
		$cfg->setName('vardas');
		$this->assertEquals('vardas', $cfg->getName());
	}

	public function testValue() {
		$cfg = new Config();
		$cfg->setValue('54');
		$this->assertEquals('54', $cfg->getValue());
	}
}