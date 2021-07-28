<?php
namespace App\Tests\Service;

use App\Service\ConfigService;
use PHPUnit\Framework\TestCase;

class ConfigServiceTest extends TestCase {
	public function testConstruct() {
		// First, mock the object to be used in the test
		$config = $this->createMock('\App\Entity\Config');
		$config->expects($this->once())
			->method('getName')
			->will($this->returnValue('systemName'));
		$config->expects($this->once())
			->method('getValue')
			->will($this->returnValue('BestCredits'));
		$configs = [$config];

		// Now, mock the repository so it returns the mock of the employee
		$configRepo = $this->getMockBuilder('\App\Repository\ConfigRepository')
			->disableOriginalConstructor()
			->getMock();
		$configRepo->expects($this->once())
			->method('findAllAssoc')
			->will($this->returnValue($configs));

		// Last, mock the EntityManager to return the mock of the repository
		$entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
			->disableOriginalConstructor()
			->getMock();
		$entityManager->expects($this->once())
			->method('getRepository')
			->will($this->returnValue($configRepo));

		$configService = new ConfigService($entityManager);
		$this->assertEquals('systemName', $configService->getConfig()[0]->getName());
		$this->assertEquals('BestCredits', $configService->getConfig()[0]->getValue());
	}
}