<?php

namespace App\Http\Controllers;

use App\Models\HakaruAiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HakaruAiTokenController extends Controller
{
  // ok
  public function accessToken()
  {
    // トークン取得のロジック
    $response = Http::withHeaders([
      'Content-Type' => 'application/json',
    ])->post(
      'https://public-api.hakaru.ai/v2/oauth2/access_token',
      [
        'username' => env('HAKARU_AI_USERNAME'),
        'password' => env('HAKARU_AI_PASSWORD'),
      ]
    );

    if ($response->successful()) {
      $data = $response->json();
      $accessToken = $data['access_token'];
      $refreshToken = $data['refresh_token'];

      // トークンをデータベースに保存
      $token = new HakaruAiToken();
      $token->access_token = encrypt($accessToken);
      $token->refresh_token = encrypt($refreshToken);
      $token->expires_at = now()->addSeconds(3000);
      $token->save();

      return $accessToken;
    }

    return response()->json(['message' => 'トークンの取得に失敗しました。'], 500);
  }

  public function refreshToken()
  {
    // トークン更新のロジック
    $token = HakaruAiToken::latest()->first();
    $refreshToken = $token->refresh_token;
    $response = Http::withHeaders([
      'Content-Type' => 'application/json',
    ])->post(
      'https://public-api.hakaru.ai/v2/oauth2/refresh_token',
      [
        'refresh_token' => $refreshToken
      ]
    );

    if ($response->successful()) {
      $data = $response->json();
      $newAccessToken = $data['access_token'];

      // トークン情報を更新
      $token->access_token = $newAccessToken;
      $token->expires_at = now()->addSeconds(3000);
      $token->save();

      return $newAccessToken;
    } else {
      // 新しくトークンを取得する
      $accessToken = $this->accessToken();
      return $accessToken;
    }

    return response()->json(['message' => 'アクセストークンの更新に失敗しました。'], 500);
  }

  public function uploadImage(Request $request)
  {
    $token = HakaruAiToken::latest()->first();
    $accessToken = $token->access_token;
    // トークンの有効期限確認
    if (now()->greaterThanOrEqualTo($token->expires_at)) {
      $accessToken = $this->refreshToken($token->refresh_token);
    }
    // 画像アップロードとAPIリクエストのロジック
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
      $encryptedAccessToken = HakaruAiToken::latest()->first()->access_token;
      $accessToken = decrypt($encryptedAccessToken);
      $image = base64_encode(file_get_contents($request->file('image')));
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
        'Content-Type' => 'application/json',
      ])->post('https://public-api.hakaru.ai/v1/resources/images/meter_type/MET0005', [
        'image' => $image
      ]);

      if ($response->successful()) {
        $data = $response->json();

        return response()->json(['data' => $data]);
      }

      return response()->json(['message' => '画像の処理に失敗しました。'], 500);
    }

    return response()->json(['message' => '無効な画像ファイルです。'], 400);
  }
}
