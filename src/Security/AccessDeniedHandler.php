<?php

namespace App\Security;

use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment as Twig;


class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        return new Response(
            '<div class="center">
                        <h1>Yetkiniz Yok</h1>
                        <p>Bu sayfayı görüntülemek için yetkiniz yok.</p>
                    </div>',
            403
        );
    }

}