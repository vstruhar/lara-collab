<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class UserService
{
    /**
     * Store avatar if uploaded by user
     * otherwise attempt to fetch it via unavatar.io service
     */
    public static function storeOrFetchAvatar(User $user, ?UploadedFile $avatar): ?string
    {
        if ($avatar) {
            $filename = $user->id.'.'.$avatar->clientExtension();
            $avatar->storePubliclyAs('public/avatars', $filename);

            return "/storage/avatars/{$user->id}.jpg";
        } else {
            $filepath = storage_path("app/public/avatars/{$user->id}.jpg");

            try {
                $response = Http::timeout(20)
                    ->sink($filepath)
                    ->get("https://unavatar.io/{$user->email}?fallback=false");

                if ($response->successful()) {
                    return "/storage/avatars/{$user->id}.jpg";
                } else {
                    File::delete($filepath);

                    return null;
                }
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}
