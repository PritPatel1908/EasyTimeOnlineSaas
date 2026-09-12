<?php

namespace App\Dms;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class DmsRequest
{
    protected string $dms_url = '';

    protected string $dms_username = '';

    protected string $dms_password = '';

    public static string $area_uri = '/personnel/api/areas/';

    public static string $terminal_uri = '/iclock/api/terminals/';

    public static string $employee_uri = '/personnel/api/employees/';

    public static string $visitor_uri = '/personnel/api/employees/';

    public static string $adjust_area = '/personnel/api/employees/adjust_area/';

    public static string $put = 'PUT';

    public static string $get = 'GET';

    public static string $post = 'POST';

    public static string $patch = 'PATCH';

    public function __construct()
    {
        // call api to get new machines
        $this->dms_url = config('dms_url', 'http://localhost:8088');
        $this->dms_username = config('dms_username', 'admin');
        $this->dms_password = config('dms_password', 'admin');
    }

    /**
     * Sends a request to the specified URL.
     *
     * @param  string  $url  The URL to send the request to.
     * @param  string  $uri  The URI of the request.
     * @param  string  $args  Additional arguments for the request.
     * @param  array  $params  An array of parameters for the request.
     * @param  string  $type  The type of the request (GET, POST, etc.).
     * @param  mixed  $data  The data to send with the request.
     * @return void
     */
    public function sendRequest($url = '', $uri = '', $args = '', $params = [], $type = 'GET', $data = null)
    {
        if ($url == '' && $uri == '') {
            return [
                'error' => true,
                'message' => 'URL and URI cannot be null',
            ];
        }
        if ($url == '') {
            $url = $this->dms_url;
        }
        // dd($url . $uri . $args);
        $http = Http::withBasicAuth(
            $this->dms_username,
            $this->dms_password
        );
        if (count($params) > 0) {
            $http->withQueryParameters($params);
        }
        try {
            if ($type == 'GET') {
                $response = $http
                    ->get($url.$uri.$args)->throw()->json();
            } elseif ($type == 'POST') {
                $response = $http
                    ->post($url.$uri.$args, $data)->throw()->json();
            } elseif ($type == 'PUT') {
                $response = $http
                    ->put($url.$uri.$args, $data)->throw()->json();
            } elseif ($type == 'PATCH') {
                $response = $http
                    ->patch($url.$uri.$args, $data)->throw()->json();
            }
        } catch (RequestException $e) {
            if ($e->getCode() == 404) {
                if (array_key_exists('detail', $e->response->json()) && $e->response->json()['detail'] == 'Not found.') {
                    return [
                        'error' => true,
                        'message' => $e->response->json()['detail'],
                    ];
                }
            }

            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        } catch (ConnectionException $e) {
            return [
                'error' => true,
                'message' => 'Dms Connection Error. Check Dms Connection Setting.',
            ];
        }

        if (array_key_exists('detail', $response)) {
            if ($response['detail'] == 'Success.') {
                return [
                    'error' => false,
                    'message' => $response,
                ];
            }

            return [
                'error' => true,
                'message' => $response['detail'],
            ];
        }
        if (array_key_exists('error', $response)) {
            return [
                'error' => true,
                'message' => $response['error'],
            ];
        }

        if (array_key_exists('data', $response) || array_key_exists('id', $response) || array_key_exists('emp_code', $response)) {
            return [
                'error' => false,
                'message' => $response,
            ];
        }

        return [
            'error' => true,
            'message' => json_encode($response),
        ];
    }
}
