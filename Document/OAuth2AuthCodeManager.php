<?php

namespace Alb\OAuth2ServerBundle\Document;

use Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeManager as BaseOAuth2AuthCodeManager;
use Alb\OAuth2ServerBundle\Model\OAuth2AuthCodeInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class OAuth2AuthCodeManager extends BaseOAuth2AuthCodeManager
{
    protected $dm;

    protected $repository;

    protected $class;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);
        $this->class = $class;
    }

    /**
     * @return string
     */
    function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $criteria
     */
    function findAuthCodeBy(array $criteria)
    {
        return $this->dm->findOneBy($criteria);
    }

    /**
     * @param OAuth2AuthCodeInterface $authCode
     * @param bool $andFlush
     */
    function updateAuthCode(OAuth2AuthCodeInterface $authCode, $andFlush = true)
    {
        $this->dm->persist($authCode);

        if ($andFlush) {
            $this->dm->flush();
        }
    }

    function deleteAuthCode(OAuth2AuthCodeInterface $authCode)
    {
        $this->dm->remove($authCode);
        $this->dm->flush();
    }
}

