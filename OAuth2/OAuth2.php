<?php

namespace home\tomek\php\FOSOAuthServerBundle\OAuth2;

use OAuth2\OAuth2 as BaseOAuth2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OAuth2\OAuth2\OAuth2ServerException;
use OAuth2\Model\IOAuth2Client;

class OAuth2 extends BaseOAuth2
{

    // Access token granting (Section 4).

  /**
   * Grant or deny a requested access token.
   * This would be called from the "/token" endpoint as defined in the spec.
   * Obviously, you can call your endpoint whatever you want.
   *
   * @param $inputData - The draft specifies that the parameters should be
   * retrieved from POST, but you can override to whatever method you like.
   * @throws OAuth2ServerException
   *
   * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4
   * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-10.6
   * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-4.1.3
   *
   * @ingroup oauth2_section_4
   */
  public function grantAccessToken(Request $request = NULL) {
    $filters = array(
      "grant_type" => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => self::GRANT_TYPE_REGEXP), "flags" => FILTER_REQUIRE_SCALAR),
      "scope" => array("flags" => FILTER_REQUIRE_SCALAR),
      "code" => array("flags" => FILTER_REQUIRE_SCALAR),
      "redirect_uri" => array("filter" => FILTER_SANITIZE_URL),
      "username" => array("flags" => FILTER_REQUIRE_SCALAR),
      "password" => array("flags" => FILTER_REQUIRE_SCALAR),
      "refresh_token" => array("flags" => FILTER_REQUIRE_SCALAR),
    );

    if ($request === NULL) {
      $request = Request::createFromGlobals();
    }

    // Input data by default can be either POST or GET
    if ($request->getMethod() === 'POST') {
      $inputData = $request->request->all();
    } else {
      $inputData = $request->query->all();
    }

    // Basic authorization header
    $authHeaders = $this->getAuthorizationHeader($request);

    // Filter input data
    $input = filter_var_array($inputData, $filters);

    // Grant Type must be specified.
    if (!$input["grant_type"]) {
      throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');
    }

    // Authorize the client
    $clientCreds = $this->getClientCredentials($inputData, $authHeaders);

    $client = $this->storage->getClient($clientCreds[0]);

    if (!$client) {
      throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
    }

    if ($this->storage->checkClientCredentials($client, $clientCreds[1]) === FALSE) {
      throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_CLIENT, 'The client credentials are invalid');
    }

    if (!$this->storage->checkRestrictedGrantType($client, $input["grant_type"])) {
      throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_UNAUTHORIZED_CLIENT, 'The grant type is unauthorized for this client_id');
    }

    // Do the granting
    switch ($input["grant_type"]) {
      case self::GRANT_TYPE_AUTH_CODE:
        $stored = $this->grantAccessTokenAuthCode($client, $input); // returns array('data' => data, 'scope' => scope)
        break;
      case self::GRANT_TYPE_USER_CREDENTIALS:
        $stored = $this->grantAccessTokenUserCredentials($client, $input); // returns: true || array('scope' => scope)
        break;
      case self::GRANT_TYPE_CLIENT_CREDENTIALS:
        $stored = $this->grantAccessTokenClientCredentials($client, $input, $clientCreds); // returns: true || array('scope' => scope)
        break;
      case self::GRANT_TYPE_REFRESH_TOKEN:
        $stored = $this->grantAccessTokenRefreshToken($client, $input); // returns array('data' => data, 'scope' => scope)
        break;
      default:
        if (filter_var($input["grant_type"], FILTER_VALIDATE_URL)) {
          $stored = $this->grantAccessTokenExtension($client, $inputData, $authHeaders); // returns: true || array('scope' => scope)
        } else {
          throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');
        }
    }

    if (!is_array($stored)) {
        $stored = array();
    }

    // if no scope provided to check against $input['scope'] then application defaults are set
    // if no data is provided than null is set
    $stored += array('scope' => $this->getVariable(self::CONFIG_SUPPORTED_SCOPES, 'api_user'), 'data' => null);

    // Check scope, if provided
    if ($input["scope"] && (!isset($stored["scope"]) || !$this->checkScope($input["scope"], $stored["scope"]))) {
      throw new OAuth2ServerException(self::HTTP_BAD_REQUEST, self::ERROR_INVALID_SCOPE, 'An unsupported scope was requested.');
    }

    // Check current access token for client is not expired
    if ($input["grant_type"] != self::GRANT_TYPE_CLIENT_CREDENTIALS || false === $token = $this->grantCurrentAccessToken($client, $stored['scope'])) {
        $token = $this->createAccessToken($client, $stored['data'], $stored['scope']);
    }

    return new Response(json_encode($token), 200, $this->getJsonHeaders());
  }

  protected function grantCurrentAccessToken(IOAuth2Client $client, $scope)
  {
      $currentToken = $this->storage->getAccessTokenByClient($client);
      
      if (null === $currentToken || true === $currentToken->hasExpired()) {
        return false;
      }

      $token = array(
        "access_token" => $currentToken->getToken(),
        "expires_in"   => $currentToken->getExpiresAt(),
        "token_type"   => $this->getVariable(self::CONFIG_TOKEN_TYPE),
        "scope"        => $scope,
      );

      return $token;
  }

}