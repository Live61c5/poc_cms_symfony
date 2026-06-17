<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


// Repositories
use App\Repository\SectionRepository;
use App\Repository\LineRepository;
use App\Repository\PageRepository;
use App\Repository\BlockRepository;
use App\Repository\ContentRepository;

// Entities
use App\Entity\Section;
use App\Entity\line;
use App\Entity\Page;
use App\Entity\Block;
use App\Entity\Content;
use App\Form\BlockType;
use Doctrine\ORM\EntityManager;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
// Components
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


use Symfony\Component\HttpFoundation\Request;


class AdminController extends AbstractController
{

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/addSection', name: 'app_addSection')]
    public function addSection(EntityManagerInterface $entityManager, PageRepository $pageRepository, SectionRepository $sectionRepository, Request $request): Response
    {
        // Récupération de la page à partir de la requête POST
        $pageId = $request->request->get('page');
        $page = null;
        if ($pageId) {
            $page = $pageRepository->findOneById($pageId);
        }


        $section = new Section();
        $ordreAttribue = (int) $request->request->get('ordreSection');

        $section->setPage($page);
        $picture = ($request->files->get('pictureSection'));
        if ($picture) {
            $fileName = uniqid() . '.' . $picture->guessExtension();
            try {
                $picture->move($this->getParameter('pictures_directory'), $fileName);
            } catch (FileException $e) {
                // Gérer l'exception si quelque chose se passe mal pendant le téléchargement du fichier
                $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                return $this->redirect($request->headers->get('referer'));
            }
            $section->setPicture($fileName);
        } else {
            $section->setColor($request->request->get('couleurSection'));
        }
        // Vérifie s'il existe déjà une section avec cet ordre sur la même page
        $sectionsExistantes = $sectionRepository->findBy(['page' => $page], ['ordre' => 'ASC']);
        $section->setOrdre($ordreAttribue);
        $decalageNecessaire = false;
        foreach ($sectionsExistantes as $sectionExistante) {
            // Si l'ordre de la section existante est supérieur ou égal à celui de la nouvelle section et qu'un décalage est nécessaire
            if ($decalageNecessaire) {
                $sectionExistante->setOrdre($sectionExistante->getOrdre() + 1);
                $entityManager->persist($sectionExistante);
            } elseif ($sectionExistante->getOrdre() == $ordreAttribue) {
                // Si l'ordre de la section existante est égal à celui de la nouvelle section
                $decalageNecessaire = true; // Marquer pour commencer le décalage à partir de la prochaine section
                $sectionExistante->setOrdre($sectionExistante->getOrdre() + 1);
                $entityManager->persist($sectionExistante);
            }
        }


        $entityManager->persist($section);

        // Création et persistance de la première ligne dans la section
        $line = new Line();
        $line->setOrdre(1);
        $line->setSection($section);
        $entityManager->persist($line);

        $entityManager->flush();

        // Redirection
        $referer = $request->headers->get('referer') . '#section' . $section->getId();
        return $this->redirect($referer);
    }



    #[Route('/admin/deleteSection/{id}/{page}', name: 'app_deleteSection')]
    public function app_deleteSection(EntityManagerInterface $entityManager, BlockRepository $blockRepository, LineRepository $LineRepository, SectionRepository $SectionRepository, ContentRepository $contentRepository, PageRepository $pageRepository, Request $request, $id, $page): Response
    {

        $pageR = $pageRepository->findOneById($page);

        if (!empty($pageR)) {
            $page = $pageR->getRoute();
        }

        //SECTIONS, line, BLOCKS, CONTENTS
        $sections = $SectionRepository->findOneById($id);
        $oldSection = $sections->getId();

        $line = $LineRepository->findBy(array('section' => $sections));
        foreach ($line as $line) {
            $block = $blockRepository->findBy(array('line' => $line));

            foreach ($block as $res) {

                $content = $contentRepository->findBy(array('block' => $res));

                foreach ($content as $res2) {
                    if ($res2->getPicture()) {
                        $lien = '../public/uploads/' . $res2->getPicture();
                        unlink($lien);
                    }

                    $entityManager->remove($res2);
                }

                $entityManager->remove($res);
            }

            $entityManager->remove($line);
        }

        $entityManager->remove($sections);
        $entityManager->flush();

        //return $this->redirectToRoute($page, []);
        $referer = $request->headers->get('referer') . '#section' . $oldSection;
        return $this->redirect($referer);

        if (!empty($pageR)) {
            //return $this->redirectToRoute($page, []);
            $referer = $request->headers->get('referer') . '#section' . $oldSection;
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_homepage', []);
        }
    }

    #[Route('/admin/updateSection/{id}/{page}', name: 'app_updateSection')]
    public function app_updateSection(EntityManagerInterface $entityManager, BlockRepository $blockRepository, LineRepository $LineRepository, SectionRepository $sectionRepository, ContentRepository $contentRepository, PageRepository $pageRepository, Request $request, $id, $page): Response
    {

        $page = $pageRepository->findOneById($page);
        if (!empty($pageR)) {
            $page = $pageR->getRoute();
        }

        //SECTION, line, block, contentS
        $section = $sectionRepository->findOneById($id);

        $form = $this->createFormBuilder($section)

            ->add('picture', FileType::class, array('data_class' => null, 'required' => false, 'label' => false, 'empty_data' => 'ok'))
            ->add('color', ColorType::class, [
                'attr' => ['class' => 'attached-input'],

                'label' => false,
                'required' => false
            ])
            ->add('ordre', HiddenType::class, [
                'attr' => ['class' => 'attached-input'],

                'label' => false,
                'required' => false
            ])

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $color = $form->get('color')->getData();
            $section->setColor($color);
            $ordreAttribue = $form->get('ordre')->getData(); // Nouvel ordre souhaité
            $ordreActuel = $section->getOrdre(); // Ordre actuel de la section avant mise à jour

            // Pas besoin de mettre temporairement la section hors de conflit, ajustons directement les autres sections
            $sectionsExistantes = $sectionRepository->findBy(['page' => $page], ['ordre' => 'ASC']);
            $picture = ($request->files->get('pictureSection'));
            if ($picture) {
                $fileName = uniqid() . '.' . $picture->guessExtension();
                try {
                    $picture->move($this->getParameter('pictures_directory'), $fileName);
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe mal pendant le téléchargement du fichier
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->redirect($request->headers->get('referer'));
                }
                $section->setPicture($fileName);
            }

            foreach ($sectionsExistantes as $sectionExistante) {
                if ($ordreActuel > $ordreAttribue) {
                    // Déplacement vers le haut
                    if ($sectionExistante->getOrdre() >= $ordreAttribue && $sectionExistante->getOrdre() < $ordreActuel) {
                        // Pour chaque section entre l'ordre actuel et l'ordre attribué, augmenter leur ordre de 1
                        $sectionExistante->setOrdre($sectionExistante->getOrdre() + 1);
                    }
                } else if ($ordreActuel < $ordreAttribue) {
                    // Déplacement vers le bas
                    if ($sectionExistante->getOrdre() > $ordreActuel && $sectionExistante->getOrdre() <= $ordreAttribue) {
                        // Pour chaque section entre l'ordre actuel et l'ordre attribué, diminuer leur ordre de 1
                        $sectionExistante->setOrdre($sectionExistante->getOrdre() - 1);
                    }
                }
                $entityManager->persist($sectionExistante);
            }

            // Maintenant, ajuster les ordres des sections affectées
            $entityManager->flush();

            // Finalement, attribuer le nouvel ordre à la section ciblée et persister le changement
            $section->setOrdre($ordreAttribue);
            $entityManager->persist($section);
            $entityManager->flush();

            if (!empty($pageR)) {
                $url = $this->generateUrl($page);
                $referer = $url . '#section' . $section->getId();

                $referer = $request->headers->get('referer') . '#section' . $section->getId();
                return $this->redirect($referer);
            } else {
                return $this->redirectToRoute('app_homepage', []);
            }
        }

        return $this->render('admin/updateSection.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'page' => $page
        ]);
    }

    #[Route('/admin/updateOrdreSectionUp/{id}/{page}', name: 'app_updateOrdreSectionUp')]
    public function updateOrdreSectionUp(EntityManagerInterface $entityManager, SectionRepository $sectionRepository, PageRepository $pageRepository, $id, $page): Response
    {
        // Récupération de la section à déplacer vers le haut et de la page correspondante
        $page = $pageRepository->findOneById($page);
        $sectionToMoveUp = $sectionRepository->findOneById($id);

        if ($sectionToMoveUp && $page) {
            $ordreActuel = $sectionToMoveUp->getOrdre();

            // Vérifier si l'ordre actuel n'est pas déjà le premier
            if ($ordreActuel > 1) {
                // Trouver la section qui doit descendre (l'ordre actuel - 1)
                $sectionToMoveDown = $sectionRepository->findOneBy(['page' => $page, 'ordre' => $ordreActuel - 1]);

                if ($sectionToMoveDown) {
                    // Échanger les ordres des deux sections
                    $sectionToMoveDown->setOrdre($ordreActuel);
                    $sectionToMoveUp->setOrdre($ordreActuel - 1);

                    // Sauvegarder les changements
                    $entityManager->persist($sectionToMoveDown);
                    $entityManager->persist($sectionToMoveUp);
                    $entityManager->flush();
                }
            }

            // Rediriger l'utilisateur vers une page appropriée après l'opération
            return $this->redirectToRoute('app_homepage', ['page' => $page->getId()]);
        } else {
            // Gérer le cas où la section ou la page n'existe pas
            return $this->redirectToRoute('app_errorPage', ['error' => 'Section or Page not found']);
        }
    }

    #[Route('/admin/updateOrdreSectionDown/{id}/{page}', name: 'app_updateOrdreSectionDown')]
    public function updateOrdreSectionDown(EntityManagerInterface $entityManager, SectionRepository $sectionRepository, PageRepository $pageRepository, $id, $page): Response
    {
        // Récupération de la section à déplacer vers le bas et de la page correspondante
        $page = $pageRepository->findOneById($page);
        $sectionToMoveDown = $sectionRepository->findOneById($id);

        if ($sectionToMoveDown && $page) {
            $ordreActuel = $sectionToMoveDown->getOrdre();

            // Déterminer l'ordre maximum pour éviter de déplacer la dernière section plus bas
            $maxOrdre = $sectionRepository->findOneBy(['page' => $page], ['ordre' => 'DESC'])->getOrdre();

            // Vérifier si l'ordre actuel n'est pas déjà le dernier
            if ($ordreActuel < $maxOrdre) {
                // Trouver la section qui doit monter (l'ordre actuel + 1)
                $sectionToMoveUp = $sectionRepository->findOneBy(['page' => $page, 'ordre' => $ordreActuel + 1]);

                if ($sectionToMoveUp) {
                    // Échanger les ordres des deux sections
                    $sectionToMoveUp->setOrdre($ordreActuel);
                    $sectionToMoveDown->setOrdre($ordreActuel + 1);

                    // Sauvegarder les changements
                    $entityManager->persist($sectionToMoveUp);
                    $entityManager->persist($sectionToMoveDown);
                    $entityManager->flush();
                }
            }

            // Rediriger l'utilisateur vers une page appropriée après l'opération
            return $this->redirectToRoute('app_homepage', ['page' => $page->getId()]);
        } else {
            // Gérer le cas où la section ou la page n'existe pas
            return $this->redirectToRoute('app_errorPage', ['error' => 'Section or Page not found']);
        }
    }

    #[Route('/admin/addBlock/{line}/{type}', name: 'app_addBlock')]
    public function addBlock(EntityManagerInterface $entityManager, LineRepository $LineRepository, Request $request, $line, $type): Response
    {
        $lineT = $LineRepository->findOneById($line);
        if (!$lineT) {
            // Gérer l'erreur si $lineT n'est pas trouvé.
            // Par exemple, rediriger vers une route par défaut ou afficher une erreur.
            return $this->redirectToRoute('app_homepage');
        }

        $line = new Line();
        $line->setSection($lineT->getSection());
        $line->setOrdre(1);
        $entityManager->persist($line);
        $entityManager->flush();

        // Col-12
        if ($type == 12) {
            $block = new Block();
            $block->setLine($line);
            $block->setType($type);
            $block->setOrdre(1);
            $entityManager->persist($block);
            $entityManager->flush();
        }

        // Col-6 Col-6
        elseif ($type == 66) {
            for ($i = 0; $i < 2; $i++) {
                $block = new Block();
                $block->setLine($line);
                $block->setType(6);
                $block->setOrdre($i + 1);
                $entityManager->persist($block);
            }
            $entityManager->flush();
        }

        // Col-4 Col-4 Col-4
        elseif ($type == 444) {
            for ($i = 0; $i < 3; $i++) {
                $block = new Block();
                $block->setLine($line);
                $block->setType(4);
                $block->setOrdre($i + 1);
                $entityManager->persist($block);
            }
            $entityManager->flush();
        }

        // Col-8 Col-4
        elseif ($type == 84) {
            $block = new Block();
            $block->setLine($line);
            $block->setType(8);
            $block->setOrdre(1);
            $entityManager->persist($block);

            $block = new Block();
            $block->setLine($line);
            $block->setType(4);
            $block->setOrdre(2);
            $entityManager->persist($block);
            $entityManager->flush();
        }

        // Col-4 Col-8
        elseif ($type == 48) {
            $block = new Block();
            $block->setLine($line);
            $block->setType(4);
            $block->setOrdre(1);
            $entityManager->persist($block);

            $block = new Block();
            $block->setLine($line);
            $block->setType(8);
            $block->setOrdre(2);
            $entityManager->persist($block);
            $entityManager->flush();
        }

        // Col-3 Col-3 Col-3 Col-3
        elseif ($type == 3333) {
            for ($i = 0; $i < 4; $i++) {
                $block = new Block();
                $block->setLine($line);
                $block->setType(3);
                $block->setOrdre($i + 1);
                $entityManager->persist($block);
            }
            $entityManager->flush();
        }

        // Rediriger vers la page précédente avec l'ancre au dernier bloc ajouté.
        $referer = $request->headers->get('referer') . '#block' . $block->getId();
        return $this->redirect($referer);
    }

    #[Route('/admin/addContent/{block}/{type}', name: 'app_addContent')]
    public function app_addContent(EntityManagerInterface $entityManager, BlockRepository $blockRepository, Request $request, $block, $type): Response
    {
        $blockEntity = $blockRepository->findOneById($block);
        if (!$blockEntity) {
            $this->addFlash('error', 'Bloc introuvable.');
            return $this->redirectToRoute('error_route_name');
        }

        $referer = $request->headers->get('referer');
        $content = new Content();
        $formBuilder = $this->createFormBuilder($content)
            ->add('titre', CKEditorType::class, [
                'required' => false,
                'label' => false,
            ]);

        if ($type === 'text') {
            $content->setType($type);
            $formBuilder->add('text', CKEditorType::class, [
                'required' => false,
                'label' => false,
            ]);
        } elseif ($type === 'picture') {
            $content->setType($type);
            $formBuilder->add('picture', FileType::class, [
                'required' => true,
                'label' => false,
                'mapped' => false, // Important si le champ n'est pas directement lié à la base de données
            ]);
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($type === 'picture' && $pictureFile = $form->get('picture')->getData()) {
                $fileName = uniqid() . '.' . $pictureFile->guessExtension();
                try {
                    $pictureFile->move($this->getParameter('pictures_directory'), $fileName);
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe mal pendant le téléchargement du fichier
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->redirect($referer);
                }
                $content->setPicture($fileName);
            }

            $content->setBlock($blockEntity);
            $entityManager->persist($content);
            $entityManager->flush();

            $urlReferer = $request->request->get('redirection', $referer) . '#block' . $blockEntity->getId();
            return $this->redirect($urlReferer ?: $this->generateUrl('app_homepage'));
        }

        return $this->render('admin/addcontent.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'contents' => $content,
            'referer' => $referer
        ]);
    }

    #[Route('/admin/editContent/{id}/{type}', name: 'app_editContent')]
    public function app_editContent(EntityManagerInterface $entityManager, BlockRepository $blockRepository, LineRepository $LineRepository, SectionRepository $SectionRepository, ContentRepository $contentRepository, PageRepository $pageRepository, Request $request, $id, $type): Response
    {


        $content = $contentRepository->findOneById($id);
        $block = $content->getBlock();
        $page = $content->getBlock()->getline()->getSection()->getPage()->getRoute();
        $referer = $request->headers->get('referer');

        if ($type == 'text') {
            $content->getType($type);

            $form = $this->createFormBuilder($content)

                ->add('titre', CKEditorType::class, [
                    'attr' => ['class' => 'attached-input'],
                    'label' => false,
                    'required' => false
                ])
                ->add('text', CKEditorType::class, [
                    'attr' => ['class' => 'attached-input'],
                    'label' => false,
                    'required' => false
                ])
                ->add('ordre', NumberType::class, [
                    'attr' => ['class' => 'attached-input'],
                    'mapped' => false,
                    'label' => false,
                    'required' => false
                ])

                ->getForm();
        }


        if ($type == 'picture') {
            $content->getType($type);
            $form = $this->createFormBuilder($content)
                ->add('titre', CKEditorType::class, [
                    'attr' => ['class' => 'attached-input'],
                    'label' => false,
                    'required' => false
                ])
                ->add('picture', FileType::class, array('data_class' => null, 'required' => false, 'label' => false))
                ->add('ordre', NumberType::class, [
                    'attr' => ['class' => 'attached-input'],
                    'mapped' => false,
                    'label' => false,
                    'required' => false
                ])
                ->getForm();
        }
        $oldPicture = $content->getPicture();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ordreAttribue = $form->get('ordre')->getData();
            $blockActuel = $content->getBlock();
            $blockConflit = $blockRepository->findOneBy(['ordre' => $ordreAttribue]);

            // Vérifier si le conflit est causé par le même bloc
            if ($blockConflit && $blockActuel->getId() === $blockConflit->getId()) {
                // Le conflit est causé par le même bloc essayant d'être assigné à son propre ordre.
                // Ne rien faire car cela signifie que l'utilisateur n'a pas modifié l'ordre.
            } elseif ($blockConflit) {
                // Échanger les ordres si un autre bloc a l'ordre attribué
                $ordreTemp = $blockActuel->getOrdre();

                $blockActuel->setOrdre($ordreAttribue);
                $blockConflit->setOrdre($ordreTemp);

                $entityManager->persist($blockConflit);
                $entityManager->persist($blockActuel);
            } else {
                // Si aucun bloc en conflit, simplement définir le nouvel ordre
                $blockActuel->setOrdre($ordreAttribue);
                $entityManager->persist($blockActuel);
            }

            $entityManager->flush();


            $picture = $form->get('picture')->getData();
            $titre = $form->get('titre')->getData();

            $picture = $form->get('picture')->getData();

            if ($picture !== null) {
                $fileName = uniqid() . '.' . $picture->guessExtension();
                try {
                    $picture->move($this->getParameter('pictures_directory'), $fileName);
                    $content->setPicture($fileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->redirect($request->headers->get('referer'));
                }
            } else {
                $content->setPicture($oldPicture);
            }

            $content->setTitre($titre);
            $entityManager->persist($content);
            $entityManager->flush();


            $urlReferer = $_POST['redirection'] . '#block' . $content->getBlock()->getId();
            return $this->redirect($urlReferer);
        }


        return $this->render('admin/editContent.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'content' => $content,
            'referer' => $referer,
            'block' => $block
        ]);
    }

    #[Route('/admin/deleteContent/{id}', name: 'app_deleteContent')]
    public function app_deleteContent(EntityManagerInterface $entityManager, ContentRepository $contentRepository, $id): Response
    {
        $content = $contentRepository->findOneById($id);

        if (!$content) {
            $this->addFlash('error', 'Contenu introuvable.');
            return $this->redirectToRoute('app_homepage');
        }

        // Suppression de l'image associée, si elle existe
        if (!empty($content->getPicture())) {
            $fullPath = $this->getParameter('pictures_directory') . '/' . $content->getPicture();
            if (file_exists($fullPath) && is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        $entityManager->remove($content);
        $entityManager->flush();

        $this->addFlash('success', 'Le contenu a été supprimé avec succès.');

        // Redirection simple vers la page d'accueil ou une autre route
        return $this->redirectToRoute('app_homepage');
    }

    #[Route('/admin/app_deleteline/{id}/{page}', name: 'app_deleteLine')]
    public function app_deleteline(EntityManager $entityManager, BlockRepository $blockRepository, LineRepository $LineRepository, SectionRepository $SectionRepository, ContentRepository $contentRepository, PageRepository $pageRepository, Request $request, $id, $page): Response
    {

        $pageR = $pageRepository->findOneById($page);
        if (!empty($pageR)) {
            $page = $pageR->getRoute();
        }

        $sections = $LineRepository->findOneById($id)->getSection();

        $line = $LineRepository->findOneById($id);

        $blocks = $blockRepository->findBy(['line' => $line]);
        foreach ($blocks as $block) {
            $section = $block->getline()->getSection()->getId();
            $content = $contentRepository->findBy(['Block' => $block]);
            $idsContent = array();

            foreach ($content as $res) {
                $entityManager->remove($res);
            }

            $entityManager->remove($block);
        }

        $entityManager->remove($line);

        if (!empty($pageR)) {
            $url = $this->generateUrl($page);
            $referer = $url . '#section' . $sections->getId();
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('app_homepage', []);
        }
    }

    #[Route('/admin/app_deleteBlock/{id}/{page}', name: 'app_deleteBlock')]
    public function app_deleteBlock(EntityManagerInterface $entityManager, BlockRepository $blockRepository, ContentRepository $contentRepository, PageRepository $pageRepository, Request $request, $id, $page): Response
    {

        $pageR = $pageRepository->findOneById($page);
        $page = $pageR->getRoute();

        $block = $blockRepository->findOneBy(['id' => $id]);
        $section = $block->getline()->getSection()->getId();

        $content = $contentRepository->findBy(['Block' => $block]);

        $idsContent = array();
        foreach ($content as $res) {
            array_push($idsContent, $res->getId());
            if ($res->getPicture()) {
                $lien = '../public/uploads/' . $res->getPicture();
                unlink($lien);
            }

            $entityManager->remove($res);
        }


        $entityManager->remove($block);

        // return $this->redirectToRoute($page, []);
        $referer = $request->headers->get('referer') . '#section' . $section;
        return $this->redirect($referer);
    }
}
