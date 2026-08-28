// <?php

// namespace App\Application\Controller;

// use Psr\Http\Message\ResponseInterface;
// use Psr\Http\Message\ServerRequestInterface;

// class CookieController
// {
//     public function acceptCookies(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
//     {
//         $cookie = sprintf(
//             'cookiesAccepted=true; Path=/; Max-Age=%d; SameSite=Lax',
//             60 * 60 * 24 * 365
//         );

//         return $response->withHeader('Set-Cookie', $cookie);
//     }
// }
