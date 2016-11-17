<?php

namespace OpenOrchestra\Backoffice\Context;

use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;

/**
 * Class TestContextManager
 */
class TestContextManager extends ContextManager
{
    protected $siteId = '2';
    protected $defaultLanguage = 'fr';
    protected $siteName = 'Demo site';

    /**
     * @param SiteRepositoryInterface $siteRepository
     * @param string                  $defaultLocale
     */
    public function __construct(SiteRepositoryInterface $siteRepository, $defaultLocale = 'en')
    {
        $this->siteRepository = $siteRepository;
        $this->defaultLocal = $defaultLocale;
    }

    /**
     * @return string
     */
    public function getCurrentLocale()
    {
        return $this->defaultLocal;
    }

    /**
     * @param string $locale
     */
    public function setCurrentLocale($locale)
    {
        $this->defaultLocal = $locale;
    }

    /**
     * @param string $siteId
     * @param string $siteDomain
     * @param string $defaultLanguage
     * @param array  $languages
     */
    public function setCurrentSite($siteId, $siteDomain, $defaultLanguage, array $languages)
    {
        $this->siteId = $siteId;
        $this->siteDomain = $siteDomain;
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * @return string
     */
    public function getCurrentSiteId()
    {
        return $this->siteId;
    }

    /**
     * @return string
     */
    public function getCurrentSiteName()
    {
        return $this->siteName;
    }

    /**
     * @return string
     */
    public function getCurrentSiteDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * @return array
     */
    public function getAvailableSites()
    {
        return $this->siteRepository->findByDeleted(false);
    }
}
