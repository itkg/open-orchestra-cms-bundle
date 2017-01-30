<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\HttpException\AreaTransformerHttpException;
use OpenOrchestra\BaseApi\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\ModelInterface\Model\AreaInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AreaTransformer
 */
class AreaTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $areaClass;

    /**
     * @param string                        $facadeClass
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $areaClass
     */
    public function __construct(
        $facadeClass,
        AuthorizationCheckerInterface $authorizationChecker,
        $areaClass
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->areaClass = $areaClass;
    }

    /**
     * @param AreaInterface $area
     *
     * @return FacadeInterface
     *
     * @throws TransformerParameterTypeException
     * @throws AreaTransformerHttpException
     */
    public function transform($area)
    {
        $facade = $this->newFacade();

        if (!$area instanceof AreaInterface) {
            throw new TransformerParameterTypeException();
        }

        foreach ($area->getBlocks() as $block) {
            $facade->addBlock($this->getTransformer('block')->transform($block));
        }

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param null $source
     *
     * @return AreaInterface
     */
    public function reverseTransform(FacadeInterface $facade, $source = null)
    {
        /** @var AreaInterface $area */
        $area = new $this->areaClass();
        foreach ($facade->getBlocks() as $block) {
            $area->addBlock($this->getTransformer('block')->reverseTransform($block));
        }

        return $area;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'area';
    }
}
