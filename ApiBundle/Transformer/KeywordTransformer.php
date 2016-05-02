<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use OpenOrchestra\BaseApi\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\Backoffice\NavigationPanel\Strategies\AdministrationPanelStrategy;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\ModelInterface\Model\KeywordInterface;

/**
 * Class KeywordTransformer
 */
class KeywordTransformer extends AbstractSecurityCheckerAwareTransformer
{
    /**
     * @param KeywordInterface $keyword
     *
     * @return FacadeInterface
     *
     * @throws TransformerParameterTypeException
     */
    public function transform($keyword)
    {
        if (!$keyword instanceof KeywordInterface) {
            throw new TransformerParameterTypeException();
        }

        $facade = $this->newFacade();

        $facade->id = $keyword->getId();
        $facade->label = $keyword->getLabel();

        $facade->addLink('_self', $this->generateRoute(
            'open_orchestra_api_keyword_show',
            array('keywordId' => $keyword->getId())
        ));

        if ($this->authorizationChecker->isGranted(AdministrationPanelStrategy::ROLE_ACCESS_DELETE_KEYWORD)) {
            $facade->addLink('_self_delete', $this->generateRoute(
                'open_orchestra_api_keyword_delete',
                array('keywordId' => $keyword->getId())
            ));
        }

        if ($this->authorizationChecker->isGranted(AdministrationPanelStrategy::ROLE_ACCESS_UPDATE_KEYWORD)) {
            $facade->addLink('_self_form', $this->generateRoute(
                'open_orchestra_backoffice_keyword_form',
                array('keywordId' => $keyword->getId())
            ));
        }

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'keyword';
    }
}
