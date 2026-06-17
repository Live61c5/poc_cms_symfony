<?php

namespace App\Controller;

use App\Entity\Line;
use App\Form\LineType;
use App\Repository\LineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/line')]
class LineController extends AbstractController
{
    #[Route('/', name: 'app_line_index', methods: ['GET'])]
    public function index(LineRepository $LineRepository): Response
    {
        return $this->render('line/index.html.twig', [
            'lines' => $LineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_line_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $Line = new Line();
        $form = $this->createForm(LineType::class, $Line);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($Line);
            $entityManager->flush();

            return $this->redirectToRoute('app_line_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('line/new.html.twig', [
            'line' => $Line,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_line_show', methods: ['GET'])]
    public function show(Line $Line): Response
    {
        return $this->render('line/show.html.twig', [
            'line' => $Line,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_line_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Line $Line, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LineType::class, $Line);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_Line_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('line/edit.html.twig', [
            'line' => $Line,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_line_delete', methods: ['POST'])]
    public function delete(Request $request, Line $Line, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $Line->getId(), $request->request->get('_token'))) {
            $entityManager->remove($Line);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_Line_index', [], Response::HTTP_SEE_OTHER);
    }
}
