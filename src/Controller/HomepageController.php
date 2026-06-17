<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SectionRepository;
use App\Repository\PageRepository;


class HomepageController extends AbstractController
{
    #[Route('/homepage', name: 'app_homepage')]
    public function index(SectionRepository $sectionsRepository, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findOneById(1);
        $nb = count($page->getSections()) + 1;

        $sections = $sectionsRepository->findBy(array('page' => 1), array('ordre' => 'asc'));
        $maxOrdre = $sectionsRepository->findMaxOrdreByPage(1);
        return $this->render('homepage/index.html.twig', [
            'sections' => $sections,
            'page' => $page,
            'ordreMax' => $nb,
            'maxOrdre' => $maxOrdre
        ]);
    }
}
