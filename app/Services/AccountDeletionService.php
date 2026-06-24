<?php

namespace App\Services;

use App\Models\CustomImage;
use App\Models\CustomVideoRequest;
use App\Models\Purchase;
use App\Models\TokenBalance;
use App\Models\TokenTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AccountDeletionService
{
    /**
     * Kullanıcıya ait tüm verileri ve dosyaları siler.
     * Hiçbir parametre user_id almaz; her zaman dışarıdan
     * doğrulanmış $user (Auth::user()) ile çağrılmalıdır.
     */
    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->deleteCustomVideoRequests($user);
            $this->deleteCustomImages($user);
            $this->deleteGenerationRequests($user);

            TokenTransaction::where('user_id', $user->id)->delete();

            TokenBalance::where('user_id', $user->id)->delete();

            Purchase::where('user_id', $user->id)->delete();

            // Sanctum personal access token'ları
            $user->tokens()->delete();

            $user->delete();
        });

        Log::info('Account deleted', ['user_id' => $user->id]);
    }

    private function deleteCustomVideoRequests(User $user): void
    {
        $requests = CustomVideoRequest::byUser($user->id)->with(['segments.editRequests', 'referenceImages'])->get();

        foreach ($requests as $request) {
            foreach ($request->segments as $segment) {
                // Segment'e bağlı edit talepleri (dosya tutmuyor)
                $segment->editRequests()->delete();

                // video_url formatı garanti olmadığı için dosya silme denemesi yapılmıyor
                $segment->delete();
            }

            foreach ($request->referenceImages as $referenceImage) {
                $this->deleteFileIfLocal($referenceImage->image_path);
                $referenceImage->delete();
            }

            $this->deleteFileIfLocal($request->input_image_path);

            $request->delete();
        }
    }

    private function deleteCustomImages(User $user): void
    {
        $images = CustomImage::byUser($user->id)->with('referenceImages')->get();

        foreach ($images as $image) {
            foreach ($image->referenceImages as $referenceImage) {
                $this->deleteFileIfLocal($referenceImage->image_path);
                $referenceImage->delete();
            }

            $this->deleteFileIfLocal($image->input_image_path);

            // admin_image_url formatı garanti olmadığı için dosya silme denemesi yapılmıyor
            $image->delete();
        }
    }

    private function deleteGenerationRequests(User $user): void
    {
        $requests = \App\Models\GenerationRequest::byUser($user->id)->get();

        foreach ($requests as $request) {
            $this->deleteFileIfLocal($request->input_image_path);

            // output_url formatı garanti olmadığı için dosya silme denemesi yapılmıyor
            $request->delete();
        }
    }

    /**
     * Sadece "public" disk içinde gerçekten var olan, local olarak
     * yazıldığını bildiğimiz path'leri siler. Boş, null veya tam URL
     * (http://, https://) olan değerlere dokunmaz.
     */
    private function deleteFileIfLocal(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
