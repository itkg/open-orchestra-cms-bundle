<?php

namespace PHPOrchestra\ApiBundle\Transformer;
use PHPOrchestra\ApiBundle\Facade\FacadeInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class AbstractTransformer
 */
abstract class AbstractTransformer implements TransformerInterface
{
    protected $context;

    /**
     * @param TransformerManager $manager
     */
    public function setContext(TransformerManager $manager)
    {
        $this->context = $manager;
    }

    /**
     * @param string $name
     *
     * @return TransformerInterface
     */
    protected function getTransformer($name)
    {
        return $this->context->get($name);
    }

    /**
     * @return UrlGenerator
     */
    protected function getRouter()
    {
        return $this->context->getRouter();
    }

    /**
     * @param mixed $mixed
     *
     * @return FacadeInterface
     */
    public function transform($mixed)
    {
        // TODO: Implement transform() method.
    }

    /**
     * @param FacadeInterface $facade
     * @param mixed|null      $source
     *
     * @return mixed
     */
    public function reverseTransform(FacadeInterface $facade, $source = null)
    {
        // TODO: Implement reverseTransform() method.
    }
}
