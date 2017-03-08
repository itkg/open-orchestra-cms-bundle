<?php

namespace OpenOrchestra\UserAdminBundle\OAuth2\Strategy;

use JMS\Serializer\Serializer;
use OpenOrchestra\BaseApi\Exceptions\HttpException\BadUserCredentialsHttpException;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Facade\OAuth2\AccessTokenFacade;
use OpenOrchestra\BaseApi\Manager\AccessTokenManager;
use OpenOrchestra\BaseApi\OAuth2\Strategy\AbstractOAuth2Strategy;
use OpenOrchestra\BaseApi\Repository\AccessTokenRepositoryInterface;
use OpenOrchestra\BaseApi\Repository\ApiClientRepositoryInterface;
use OpenOrchestra\UserBundle\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ResourceOwnerPasswordGrantStrategy
 */
class ResourceOwnerPasswordGrantStrategy extends AbstractOAuth2Strategy
{
    protected $encoderFactory;
    protected $userRepository;

    /**
     * @param ApiClientRepositoryInterface   $apiClientRepository
     * @param UserRepositoryInterface        $userRepository
     * @param EncoderFactory                 $encoderFactory
     * @param Serializer                     $serializer
     * @param ValidatorInterface             $validator
     * @param AccessTokenManager             $accessTokenManager
     * @param AccessTokenRepositoryInterface $accessTokenRepository
     */
    public function __construct(
        ApiClientRepositoryInterface $apiClientRepository,
        UserRepositoryInterface $userRepository,
        EncoderFactory $encoderFactory,
        Serializer $serializer,
        ValidatorInterface $validator,
        AccessTokenManager $accessTokenManager,
        AccessTokenRepositoryInterface $accessTokenRepository
        )
    {
        parent::__construct($apiClientRepository, $serializer, $validator, $accessTokenManager, $accessTokenRepository);
        $this->encoderFactory = $encoderFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     *
     * @return boolean
     */
    public function supportRequestToken(Request $request)
    {
        $clientExist = $request->getUser() && $request->getPassword();
        $oauthParams = $request->get('grant_type') === 'password' && $request->headers->get('username') && $request->headers->get('password');

        return $oauthParams && $clientExist;
    }

    /**
     * @param Request $request
     *
     * @return ConstraintViolationListInterface|FacadeInterface
     */
    public function requestToken(Request $request)
    {
        $client = $this->getClient($request);
        $user   = $this->getUser($request);

        $accessToken = $this->accessTokenManager->createWithExpirationDate($client, $user);
        if (!$accessToken->isValid($this->validator)) {
            return $accessToken->getViolations();
        }
        $this->accessTokenManager->save($accessToken);

        $tokenFacade = new AccessTokenFacade();
        $tokenFacade->accessToken  = $accessToken->getCode();
        $tokenFacade->expiresAt    = $accessToken->getExpiredAt();
        $tokenFacade->refreshToken = $accessToken->getRefreshCode();

        return $tokenFacade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'resource_owner_password_grant';
    }

    /**
     * @param Request $request
     *
     * @return UserInterface
     * @throws BadUserCredentialsHttpException
     */
    protected function getUser(Request $request)
    {
        // find the user
        $user = $this->userRepository->findOneByUsername($request->headers->get('username'));
        if (!$user) {
            throw new BadUserCredentialsHttpException();
        }

        // Check the validity of the password
        $encoder = $this->encoderFactory->getEncoder($user);
        if (!$encoder->isPasswordValid($user->getPassword(), $request->headers->get('password'), $user->getSalt())) {
            throw new BadUserCredentialsHttpException();
        }

        return $user;
    }
}
