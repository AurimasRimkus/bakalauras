<?php


namespace App\Tests\Entity;

use App\Entity\Deposit;
use App\Entity\DepositType;
use PHPUnit\Framework\TestCase;

include 'D:/xampp_htdocs/bakis/src/Entity/Deposit.php';

class DepositTest extends TestCase {
	public function testValue() {
		$deposit = new Deposit();
		$deposit->setValue(50);
		$this->assertEquals(50, $deposit->getValue());
	}

	public function testName() {
		$deposit = new Deposit();
		$deposit->setName("Namas");
		$this->assertEquals("Namas", $deposit->getName());
		$this->assertNotEquals("namas", $deposit->getName());
	}

	public function testQuantity() {
		$deposit = new Deposit();
		$deposit->setQuantity(1.7);
		$this->assertEquals(1.7, $deposit->getQuantity());
		$this->assertLessThan(2, $deposit->getQuantity());
		$this->assertGreaterThan(1, $deposit->getQuantity());
	}

	public function testAddress() {
		$deposit = new Deposit();
		$deposit->setAddress('adresiukas');
		$this->assertEquals('adresiukas', $deposit->getAddress());
	}

	public function testType() {
		$deposit = new Deposit();
		$depositType = new DepositType();
		$depositType->setName('NT');
		$this->assertEquals('NT', $depositType->getName());
		$deposit->setType($depositType);
		$this->assertEquals($depositType, $deposit->getType());
		$this->assertNull($depositType->getId());
	}

	public function testId() {
		//id nera naujame obj
		$deposit = new Deposit();
		$this->assertNull($deposit->getId());
	}

}