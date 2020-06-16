<?php

namespace UsabilityDynamics\MijirehClient {

  class Exception extends \Exception
  {
  }

  class ClientError extends Exception
  {
  }         /* Status: 400-499 */

  class BadRequest extends ClientError
  {
  }        /* Status: 400 */

  class Unauthorized extends ClientError
  {
  }      /* Status: 401 */

  class NotFound extends ClientError
  {
  }          /* Status: 404 */

  class ServerError extends Exception
  {
  }         /* Status: 500-599 */

  class InternalError extends ServerError
  {
  }     /* Status: 500 */

  class Mijireh
  {

    /* Live server urls */
    public static $base_url = 'https://secure.mijireh.com/';
    public static $url = 'https://secure.mijireh.com/api/1/';

    public static $access_key;

    /**
     * Return the job id of the slurp
     */
    public static function slurp($url)
    {
      $url_format = '/^(https?):\/\/' .                           // protocol
          '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+' .         // username
          '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?' .      // password
          '@)?(?#' .                                                  // auth requires @
          ')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*' .                      // domain segments AND
          '[a-z][a-z0-9-]*[a-z0-9]' .                                 // top level domain  OR
          '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}' .
          '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])' .                 // IP address
          ')(:\d+)?' .                                                // port
          ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*' . // path
          '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)' .      // query string
          '?)?)?' .                                                   // path and query string optional
          '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?' .      // fragment
          '$/i';

      if (!preg_match($url_format, $url)) {
        throw new NotFound('Unable to slurp invalid URL: $url');
      }

      try {
        $rest = new Rest($url);
        $html = $rest->get('');
        $data = array(
            'url' => $url,
            'html' => $html,
        );
        $rest = new RestJSON(self::$url);
        $rest->setupAuth(self::$access_key, '');
        $result = $rest->post('slurps', $data);
        return $result['job_id'];
      } catch (Rest_Unauthorized $e) {
        throw new Unauthorized("Unauthorized. Please check your api access key");
      } catch (Rest_NotFound $e) {
        throw new NotFound("Mijireh resource not found: " . $rest->last_request['url']);
      } catch (Rest_ClientError $e) {
        throw new ClientError($e->getMessage());
      } catch (Rest_ServerError $e) {
        throw new ServerError($e->getMessage());
      } catch (Rest_UnknownResponse $e) {
        throw new Exception('Unable to slurp the URL: $url');
      }
    }

    /**
     * Return an array of store information
     */
    public static function get_store_info()
    {
      $rest = new RestJSON(self::$url);
      $rest->setupAuth(self::$access_key, '');
      try {
        $result = $rest->get('store');
        return $result;
      } catch (Rest_BadRequest $e) {
        throw new BadRequest($e->getMessage());
      } catch (Rest_Unauthorized $e) {
        throw new Unauthorized("Unauthorized. Please check your api access key");
      } catch (Rest_NotFound $e) {
        throw new NotFound("Mijireh resource not found: " . $rest->last_request['url']);
      } catch (Rest_ClientError $e) {
        throw new ClientError($e->getMessage());
      } catch (Rest_ServerError $e) {
        throw new ServerError($e->getMessage());
      }
    }

    public static function preview_checkout_link()
    {
      if (empty(Mijireh::$access_key)) {
        throw new Exception('Access key required to view checkout preview');
      }

      return self::$base_url . 'checkout/' . self::$access_key;
    }

  }
}
