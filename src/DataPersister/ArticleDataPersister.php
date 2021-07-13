<?php

// src/DataPersister

namespace App\DataPersister;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

class ArticleDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     *
     * @param SluggerInterface
     */
    private $slugger;

    /**
     *
     * @param Request
     */
    private $request;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        RequestStack $request
    ) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->request = $request->getCurrentRequest();
    }
    
    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Article;
    }

    /**
     * @param Article $data
     */
    public function persist($data, array $context = [])
    {
        // Update the slug only if the article isn't published
        if (!$data->getIsPublished()) {
            $data->setSlug(
                $this
                    ->slugger
                    ->slug(strtolower($data->getTitle())). '-' .uniqid()
            );
        }

        // Set the updatedAt value if it's not a POST request
        if ($this->request->getMethod() !== 'POST') {
            $data->setUpdatedAt(new \DateTime());
        }

        $this->em->persist($data);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data, array $context = [])
    {
        $this->em->remove($data);
        $this->em->flush();
    }
}