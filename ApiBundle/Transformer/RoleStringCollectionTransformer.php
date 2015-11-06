<?php

namespace OpenOrchestra\ApiBundle\Transformer;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\ApiBundle\Facade\RoleCollectionFacade;

/**
 * Class RoleStringCollectionTransformer
 */
class RoleStringCollectionTransformer extends AbstractTransformer
{
    /**
     * @param array  $roleCollection
     * @param string $type
     *
     * @return FacadeInterface
     */
    public function transform($roleCollection, $type = null)
    {
        $facade = new RoleCollectionFacade();

        foreach ($roleCollection as $role => $translation) {
            $facade->addRole($this->getTransformer('role_string')->transform($role, $translation));
        }

        $facade->addLink('_self', $this->generateRoute(
            'open_orchestra_api_role_list_by_type',
            array('type' => $type)
        ));

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'role_string_collection';
    }
}
