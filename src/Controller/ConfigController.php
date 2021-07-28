<?php
namespace App\Controller;

use App\Entity\Config;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
	/**
	 * @Route("/config", name="config")
	 * @return Response
	 */
	public function configsIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(Config::class);
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$configs = $em->findAll();
		$textFields = ['stdInfo'];
		return $this->render('configs.html.twig', ['configs' => $configs, 'textFields' => $textFields, 'cfg'=>$configs]);
	}

	/**
	 * @Route("/saveConfigs", name="saveConfigs", methods={"GET", "HEAD"})
	 * @return Response
	 */
	public function saveConfigs(Request $request) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$configRepo = $em->getRepository(Config::class);
		/**
		 * @var Config[] $configsAll
		 */
		$configsAll = $configRepo->findAll();
		$configs = [];
		foreach ($configsAll as $config) {
			$configs[$config->getName()] = $config;
		}
		$changedFields = $request->query->keys();

		foreach ($changedFields as $field) {
			$value = $request->query->get($field);
			$configs[$field]->setValue($value);
			$em->persist($configs[$field]);
		}

		$em->flush();
		return new RedirectResponse('/config');
	}
}