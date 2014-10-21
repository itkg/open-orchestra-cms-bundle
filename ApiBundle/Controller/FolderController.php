<?php

namespace PHPOrchestra\ApiBundle\Controller;

use PHPOrchestra\ApiBundle\Facade\FacadeInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PHPOrchestra\ApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FolderController
 *
 * @Config\Route("folder")
 */
class FolderController extends Controller
{
    /**
     * @param string $folder_id
     *
     * @Config\Route("/{folder_id}/delete", name="php_orchestra_api_folder_delete")
     * @Config\Method({"DELETE"})
     *
     * @return Response
     */
    public function deleteAction($folder_id)
    {
        $folder = $this->get('php_orchestra_model.repository.media_folder')->findOneById($folder_id);
        $this->get('php_orchestra_backoffice.manager.media_folder')->deleteTree($folder);
        $this->get('doctrine.odm.mongodb.document_manager')->flush();

        return new Response('', 200);
    }
}
