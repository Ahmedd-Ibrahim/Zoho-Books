<?php

namespace Ahmedd\ZohoBooks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TokenManager
{
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $tokenPath;
    protected $region;

    protected $regions = array(
        'us' => array(
            'accounts' => 'https://accounts.zoho.com',
            'api'      => 'https://www.zohoapis.com/books/v3/'
        ),
        'eu' => array(
            'accounts' => 'https://accounts.zoho.eu',
            'api'      => 'https://www.zohoapis.eu/books/v3/'
        ),
        'in' => array(
            'accounts' => 'https://accounts.zoho.in',
            'api'      => 'https://www.zohoapis.in/books/v3/'
        ),
        'au' => array(
            'accounts' => 'https://accounts.zoho.com.au',
            'api'      => 'https://www.zohoapis.com.au/books/v3/'
        ),
        'jp' => array(
            'accounts' => 'https://accounts.zoho.jp',
            'api'      => 'https://www.zohoapis.jp/books/v3/'
        ),
        'ca' => array(
            'accounts' => 'https://accounts.zoho.ca',
            'api'      => 'https://www.zohoapis.ca/books/v3/'
        ),
        'cn' => array(
            'accounts' => 'https://accounts.zoho.com.cn',
            'api'      => 'https://www.zohoapis.com.cn/books/v3/'
        ),
        'sa' => array(
            'accounts' => 'https://accounts.zoho.sa',
            'api'      => 'https://www.zohoapis.sa/books/v3/'
        )
    );

    public function __construct($clientId, $clientSecret, $refreshToken, $tokenPath = null, $region = 'us')
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;

        $this->region = isset($this->regions[$region]) ? $region : 'us';

        if ($tokenPath === null && function_exists('storage_path')) {
            $appPath = storage_path('app');
            if (!file_exists($appPath)) {
                mkdir($appPath, 0775, true);
            }
            $this->tokenPath = $appPath . '/zoho_token.json';
        } else {
            $this->tokenPath = $tokenPath;
        }
    }

    /**
     * Get valid access token, refresh if expired.
     *
     * @return string
     * @throws \Exception
     */
    public function getAccessToken()
    {
        $tokenData = $this->readTokenFile();

        if (!isset($tokenData['access_token']) || $this->isTokenExpired($tokenData)) {
            return $this->refreshTokenAndStore();
        }

        return $tokenData['access_token'];
    }

    /**
     * Refresh token from Zoho OAuth server.
     *
     * @return string
     * @throws \Exception
     */
    public function refreshTokenAndStore()
    {
        $client = new Client();

        $url = $this->regions[$this->region]['accounts'] . '/oauth/v2/token';

        try {
            $response = $client->request('POST', $url, array(
                'form_params' => array(
                    'refresh_token' => $this->refreshToken,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type'    => 'refresh_token',
                ),
                'timeout' => 10,
            ));

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!isset($data['access_token'])) {
                throw new \Exception('Zoho token response missing access_token. Response: ' . $body);
            }

            $data['expires_at'] = time() + (isset($data['expires_in']) ? intval($data['expires_in']) : 3600) - 60;

            $dir = dirname($this->tokenPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            file_put_contents($this->tokenPath, json_encode($data));

            return $data['access_token'];

        } catch (RequestException $e) {
            throw new \Exception('Failed to refresh Zoho token: ' . $e->getMessage());
        }
    }

    /**
     * Get region-specific Zoho Books API base URL.
     *
     * @return string
     */
    public function getBaseApiUrl()
    {
        return $this->regions[$this->region]['api'];
    }

    /**
     * Read token from disk.
     *
     * @return array
     */
    protected function readTokenFile()
    {
        if (!file_exists($this->tokenPath)) {
            return array();
        }

        $json = file_get_contents($this->tokenPath);
        $data = json_decode($json, true);

        return is_array($data) ? $data : array();
    }

    /**
     * Check token expiry.
     *
     * @param array $data
     * @return bool
     */
    protected function isTokenExpired($data)
    {
        return !isset($data['expires_at']) || time() >= $data['expires_at'];
    }
}
