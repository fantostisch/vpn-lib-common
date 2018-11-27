<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2018, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace SURFnet\VPN\Common\Http;

use fkooman\SeCookie\SessionInterface;

class LogoutModule implements ServiceModuleInterface
{
    const MELLON_LOGOUT = 'saml/logout';

    /** @var \fkooman\SeCookie\SessionInterface */
    private $session;

    /** @var bool */
    private $isMellon;

    /**
     * @param \fkooman\SeCookie\SessionInterface $session
     * @param bool                               $isMellon
     */
    public function __construct(SessionInterface $session, $isMellon)
    {
        $this->session = $session;
        $this->isMellon = $isMellon;
    }

    /**
     * @return void
     */
    public function init(Service $service)
    {
        $service->post(
            '/_logout',
            /**
             * @return \SURFnet\VPN\Common\Http\Response
             */
            function (Request $request, array $hookData) {
                $this->session->destroy();
                $httpReferrer = $request->requireHeader('HTTP_REFERER');
                if ($this->isMellon) {
                    $mellonLogoutUrl = sprintf('%s/%s', $request->getAuthority(), self::MELLON_LOGOUT);

                    return new RedirectResponse(
                        sprintf(
                            '%s?%s',
                            $mellonLogoutUrl,
                            http_build_query(
                                [
                                    'ReturnTo' => $httpReferrer,
                                ]
                            )
                        )
                    );
                }

                return new RedirectResponse($httpReferrer);
            }
        );
    }
}