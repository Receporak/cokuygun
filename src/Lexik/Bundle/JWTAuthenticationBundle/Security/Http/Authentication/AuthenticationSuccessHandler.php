<?php

namespace App\Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler AS LexikAuthenticationSuccessHandler;

/**
 * AuthenticationSuccessHandler.
 *
 * @author Dev Lexik <dev@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @final
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /** @var iterable|JWTCookieProvider[] */
    private $cookieProviders;
    /** @var JWTTokenManagerInterface */
    protected $jwtManager;
    /** @var EventDispatcherInterface */
    protected $dispatcher;
    /** @var bool */
    protected $removeTokenFromBodyWhenCookiesUsed;

    /**
     * @param iterable|JWTCookieProvider[] $cookieProviders
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, EventDispatcherInterface $dispatcher, $cookieProviders = [], bool $removeTokenFromBodyWhenCookiesUsed = true)
    {
        $this->jwtManager = $jwtManager;
        $this->dispatcher = $dispatcher;
        $this->cookieProviders = $cookieProviders;
        $this->removeTokenFromBodyWhenCookiesUsed = $removeTokenFromBodyWhenCookiesUsed;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var UserInterface $user */
        $user = $token->getUser();
        /** @var JWTAuthenticationSuccessResponse $JWTAuthenticationSuccessResponse */
        $JWTAuthenticationSuccessResponse = (new LexikAuthenticationSuccessHandler($this->jwtManager, $this->dispatcher, $this->cookieProviders, $this->removeTokenFromBodyWhenCookiesUsed))->handleAuthenticationSuccess($user);
        $payload = json_decode((string)$JWTAuthenticationSuccessResponse->getContent() ?? "",true) ?? [];
        /** @var User $user */
        $payload = array_merge($payload,[
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'refresh_token_iat' => (new \DateTime())->getTimestamp(),
            'refresh_token_exp' => ((new \DateTime())->modify("+30 day"))->getTimestamp()
        ]);

        $JWTAuthenticationSuccessResponse->setData($payload);

        return $JWTAuthenticationSuccessResponse;
    }
}
