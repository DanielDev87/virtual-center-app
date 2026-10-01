<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveStorageService
{
    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const UPLOAD_ENDPOINT = 'https://www.googleapis.com/upload/drive/v3/files';
    private const PERMISSION_ENDPOINT = 'https://www.googleapis.com/drive/v3/files';

    public function isConfigured(): bool
    {
        return (bool) config('services.google_drive.enabled')
            && !empty(config('services.google_drive.client_id'))
            && !empty(config('services.google_drive.client_secret'))
            && !empty(config('services.google_drive.refresh_token'));
    }

    public function uploadEvidence(UploadedFile $file): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $client = new Client($this->httpOptions(30));

        $metadata = [
            'name' => now()->format('Ymd_His') . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName()),
        ];

        $folderId = config('services.google_drive.folder_id');
        if (!empty($folderId)) {
            $metadata['parents'] = [$folderId];
        }

        try {
            $response = $client->post(self::UPLOAD_ENDPOINT, [
                'query' => [
                    'uploadType' => 'multipart',
                    'fields' => 'id,name,webViewLink,webContentLink,mimeType,size',
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'multipart' => [
                    [
                        'name' => 'metadata',
                        'contents' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                        'headers' => ['Content-Type' => 'application/json; charset=UTF-8'],
                    ],
                    [
                        'name' => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'headers' => ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream'],
                    ],
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);

            if (empty($payload['id'])) {
                return null;
            }

            if ((bool) config('services.google_drive.share_publicly', false)) {
                $this->makeFilePublic($payload['id'], $accessToken, $client);
            }

            return [
                'file_id' => $payload['id'],
                'name' => $payload['name'] ?? $file->getClientOriginalName(),
                'mime_type' => $payload['mimeType'] ?? $file->getMimeType(),
                'size' => isset($payload['size']) ? (int) $payload['size'] : $file->getSize(),
                'web_view_link' => $payload['webViewLink'] ?? null,
                'web_content_link' => $payload['webContentLink'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Google Drive upload failed, using local fallback.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getAccessToken(): ?string
    {
        try {
            $client = new Client($this->httpOptions(20));
            $response = $client->post(self::TOKEN_ENDPOINT, [
                'form_params' => [
                    'client_id' => config('services.google_drive.client_id'),
                    'client_secret' => config('services.google_drive.client_secret'),
                    'refresh_token' => config('services.google_drive.refresh_token'),
                    'grant_type' => 'refresh_token',
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);
            return $payload['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('Google Drive access token request failed.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function makeFilePublic(string $fileId, string $accessToken, Client $client): void
    {
        try {
            $client->post(self::PERMISSION_ENDPOINT . '/' . $fileId . '/permissions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => ['fields' => 'id'],
                'json' => [
                    'type' => 'anyone',
                    'role' => 'reader',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not make Google Drive file public.', [
                'file_id' => $fileId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function httpOptions(int $timeout): array
    {
        return [
            'timeout' => $timeout,
            'verify' => (bool) config('services.google_drive.verify_ssl', true),
        ];
    }
}
