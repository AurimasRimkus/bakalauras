<?php

namespace App\Controller;

use App\Entity\Pricelist;
use App\Form\PricelistType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PricelistController extends AbstractController
{
	/**
	 * @Route("/pricelists", name="pricelists")
	 */
	public function pricelistsIndex() {
		$em = $this->getDoctrine()->getManager()->getRepository(Pricelist::class);
		if($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
			$pricelists = $em->findAll();
			return $this->render('pricelists.html.twig', ['pricelists' => $pricelists]);
		}
		return new RedirectResponse('/admin');
	}

	/**
	 * @Route("/pricelist/{id}", name="submitPricelist", methods={"POST"})
	 */
	public function submitEditPricelist(Request $request, int $id)
	{
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$pricelist = new Pricelist();

		$form = $this->createForm(\App\Form\PricelistType::class, $pricelist);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$pricelist` variable has also been updated
			$pricelist = $form->getData();

			$entityManager = $this->getDoctrine()->getManager();
			$repo = $entityManager->getRepository(Pricelist::class);
			/**
			 * @var Pricelist $changingPricelist
			 */
			$changingPricelist = $repo->find($id);
			if (empty($changingPricelist)) {
				return new RedirectResponse('/pricelists');
			}
			$changingPricelist->setCreditFrom($pricelist->getCreditFrom());
			$changingPricelist->setCreditTo($pricelist->getCreditTo());
			$changingPricelist->setInterest($pricelist->getInterest());
			$changingPricelist->setLengthTo($pricelist->getLengthTo());
			$changingPricelist->setLengthFrom($pricelist->getLengthFrom());
			$entityManager->persist($changingPricelist);
			$entityManager->flush();

			return $this->redirectToRoute('pricelists');
		}

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Edit pricelist',
		]);
	}

	/**
	 * @Route("/pricelist/{id}", name="pricelist", methods={"GET", "HEAD"})
	 */
	public function ViewPricelist(Request $request, int $id) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager()->getRepository(Pricelist::class);
		$pricelist = $em->find($id);
		if (empty($pricelist)) {
			return new RedirectResponse('/pricelists');
		}
		$form = $this->createForm(\App\Form\PricelistType::class, $pricelist);
		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Edit pricelist',
		]);
	}

	/**
	 * @Route("/addPricelist", name="addPricelist", methods={"GET", "HEAD"})
	 */
	public function AddPricelist(Request $request) {
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$pricelist = new Pricelist();
		$form = $this->createForm(\App\Form\PricelistType::class, $pricelist);
		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Create a new pricelist',
		]);
	}

	/**
	 * @Route("/addPricelist", name="submitAddPricelist", methods={"POST"})
	 */
	public function submitAddPricelist(Request $request)
	{
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$pricelist = new Pricelist();

		$form = $this->createForm(\App\Form\PricelistType::class, $pricelist);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$pricelist` variable has also been updated
			$pricelist = $form->getData();
			if (empty($pricelist)) {
				return new RedirectResponse('/pricelists');
			}
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($pricelist);
			$entityManager->flush();

			return $this->redirectToRoute('pricelists');
		}

		return $this->render('formEdit.html.twig', [
			'form' => $form->createView(),
			'form_name' => 'Create a new pricelist',
		]);
	}

	/**
	 * @Route("/deletePricelist/{id}", name="deletePricelist")
	 */
	public function deletePricelist(Request $request, int $id)
	{
		if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPER_ADMIN')) {
			return new RedirectResponse('/admin');
		}
		$em = $this->getDoctrine()->getManager();
		$pricelistRepo = $em->getRepository(Pricelist::class);
		/**
		 * @var Pricelist $pricelist
		 */
		$pricelist = $pricelistRepo->find($id);
		if (empty($pricelist)) {
			return new RedirectResponse('/pricelists');
		}
		$em->remove($pricelist);
		$em->flush();
		return new RedirectResponse("/pricelists");
	}
}