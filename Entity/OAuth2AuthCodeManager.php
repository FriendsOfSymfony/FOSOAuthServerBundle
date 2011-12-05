<?php

namespace Alb\OAuth2ServerBundle\Entity;

use Alb\OAuth2ServerBundle\Entity\OAuth2TokenManager;
use Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeManager as BaseOAuth2AuthCodeManager;
use Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeInterface;
use Doctrine\ORM\EntityManager;

class OAuth2AuthCodeManager extends BaseOAuth2AuthCodeManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param string $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $criteria
     */
    public function findAuthCodeBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @param OAuth2AuthCodeInterface $authCode
     * @param bool $andFlush
     */
    public function updateAuthCode(OAuth2AuthCodeInterface $authCode, $andFlush = true)
    {
        $this->em->persist($authCode);
        if ($andFlush) {
            $this->em->flush();
        }
    }
    
    /**
     * @param OAuth2AuthCodeInterface $authCode
     */
    public function deleteAuthCode(OAuth2AuthCodeInterface $authCode)
    {
        $this->em->remove($authCode);
        $this->em->flush();
    }

}

