<?php
namespace App\Service;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConfigService
{
	private $entityManager;
	private $config;

	/**
	 * ConfigService constructor.
	 * @param EntityManagerInterface $entityManager
	 * @return array|int|string
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
		return $this->getConfig();
	}

	public function getConfig() {
		if (!empty($this->config)) {
			return $this->config;
		}
		/**
		 * @var ConfigRepository $cfgRepo
		 */
		$cfgRepo = $this->entityManager->getRepository(Config::class);
		$this->config = $cfgRepo->findAllAssoc();
		return $this->config;
	}
}