<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // receive idToken from client, then get user info from line login api, using oauth id to check if it in database, then update user data, if not then create new user
    public function login(Request $request)
    {
        $idToken = $request->input('idToken');

        if (!$idToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'idToken is required'
            ], 400);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://api.line.me/oauth2/v2.1/verify', [
                'form_params' => [
                    'id_token' => $idToken,
                    'client_id' => env('LINE_CLIENT_ID', false),
                ],
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            return response()->json([
                'error' => $responseBodyAsString,
                'status' => 'error',
                'message' => 'idToken is invalid'
            ], 400);
        }

        $body = $response->getBody();
        $body = json_decode($body);
        $user = User::where('user_oauth_id', $body->sub)->where('user_oauth_provider', 'line')->first();
        if ($user) {
            $user->user_name = $body->name;
            $user->user_avatar = isset($body->picture) ? $body->picture : null;
            $user->user_oauth_provider = 'line';
            $user->updated_by = $user->user_id;
            $user->save();
        } else {
            $user = new User();
            $user->user_name = $body->name;
            $user->user_avatar = isset($body->picture) ? $body->picture : null;
            $user->user_oauth_id = $body->sub;
            $user->user_oauth_provider = 'line';
            $user->created_by = -1;
            $user->updated_by = -1;
            $user->save();
        }

        Auth::login($user);

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function status(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'success' => true,
                'user' => Auth::user()
            ]);
        } else {
            return response()->json([
                'success' => false
            ], 401);
        }
    }
}
