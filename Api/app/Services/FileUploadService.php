<?php

namespace App\Services;

use App\Models\Media;

use App\Models\MediaThumbnail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    public $media_ids = array();

    /**
     * File store
     * @param $files
     * @return array
     */
    public function store($files)
    {
        $this->media_ids = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->uploadStructure($file);
            }
            return $this->media_ids;
        } else {
            $this->uploadStructure($files);
            if (isset($this->media_ids[0])) {
                return $this->media_ids[0];
            }
            return null;
        }
    }

    public function base64ImageUpload($image)
    {
        $storePath = public_path('uploads/');
        $image = str_replace('data:application/pdf;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $file_name = rand(1, 1000) . time() . 'web-cam.pdf';
        File::put(public_path() . '/uploads/' . $file_name, base64_decode($image));

        $media = Media::create([
            'real_name' => 'webcam_image.pdf',
            'url' => asset('uploads/' . $file_name),
            'file_name' => $file_name,
            'type' => 'application/pdf',
            'extension' => 'pdf',
            'path' => $storePath,
            'size' => null,
            'is_active' => 1,
        ]);
        return $media->media_id;

    }

    public function uploadStructure($file): void
    {
        // Define upload path
        $storePath = public_path('uploads/');
        $min_types = $file->getClientMimeType();
        $min_type = explode('/', $min_types);
        $file_name = time() . $file->getClientOriginalName();
        $file_name = str_replace(' ', '-', $file_name);

        if ($min_type[0] == 'image') {
            $this->uploadImage($file, $file_name, $storePath);
        } elseif ($min_type[0] == 'video') {
            $this->uploadVideo($file, $file_name, $storePath);
        } else {
            $file->move($storePath, $file_name);
            $media_id = $this->saveFiles($file, $file_name, $storePath);
            $this->media_ids[] = $media_id;
        }
    }

    /**
     * Upload Images
     * @param $originalImage
     * @param string $file_name
     * @param string $storePath
     */
    private function uploadImage($originalImage, string $file_name, string $storePath): void
    {
        $thumbnailImage = Image::make($originalImage);

        // image orientate
        $thumbnailImage->orientate();
        // save file
        $thumbnailImage->save($storePath . $file_name);
        $media_id = $this->saveFiles($originalImage, $file_name, $storePath);
        $this->media_ids[] = $media_id;

        // Resize and save image
        $sizes = [200, 1200];
        $thumbnailImage->backup();
        foreach ($sizes as $size) {
            $thumbnailImage->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbnailImage->save($storePath . $size . '-' . $file_name);
            $thumbnailImage->reset();

            $this->saveThumbnail($media_id, $size, $file_name);
        }
    }

    /**
     * Upload Video
     * @param $file
     * @param string $file_name
     * @param string $storePath
     */
    private function uploadVideo($file, string $file_name, string $storePath): void
    {
        $file->move($storePath, $file_name);
        $media_id = $this->saveFiles($file, $file_name, $storePath);
        $this->media_ids[] = $media_id;

//        $ffmpeg = config('app.ffmpeg_path');
//        $image_name = 'copy_' . $file_name . '.jpg';
////        $image_name = $file_name . '.jpg';
//        copy(public_path('uploads/') . $file_name, $storePath . $image_name);
//        $interval = 2;
//        $size = '200x200';
//        $video_path = $storePath . $file_name;
//        $cmd = "$ffmpeg -i $video_path -deinterlace -an -ss $interval -f mjpeg -t 1 -r 1 -y -s $size $image_name 2>&1";
//        exec($cmd);
//        rename(public_path('uploads/') . $image_name, $storePath . '200-' . $image_name);
////        rename(public_path('uploads\\') . $file_name, $storePath . '200-' . $image_name);
//        $this->saveThumbnail($media_id, 200, $image_name);
    }

    /**
     * Save File
     * @param $file
     * @param $file_name
     * @param $storePath
     * @return int
     */
    public function saveFiles($file, $file_name, $storePath): int
    {
        $media = Media::create([
            'real_name' => $file->getClientOriginalName(),
            'url' => asset('uploads/' . $file_name),
            'file_name' => $file_name,
            'type' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'path' => $storePath,
            'size' => File::size($storePath . $file_name),
            'is_active' => 1,
        ]);
        return $media->id;
    }

    /**
     * Save Thumbnail
     * @param $media_id
     * @param $size
     * @param $file_name
     * @return int
     */
    public function saveThumbnail($media_id, $size, $file_name): int
    {
        return (new MediaThumbnail())->createThumbnail([
            'media_id' => $media_id,
            'size' => $size,
            'thumb_path' => 'uploads/',
            'thumb_url' => asset('uploads/' . $size . '-' . $file_name)
        ]);
    }

    public function removeFormMedia($table, $records): void
    {
        if (empty($records)) {
            return;
        }
        foreach ($records as $record) {
            DB::table($table)
                ->where([
                    'media_id' => $record
                ])->delete();
        }
    }

    public function saveMediaToTable(string $table, ?array $data): int
    {
        return DB::table($table)->insertGetId($data);
    }

    public function getMediaByTable(string $table, $where1, $where2 ): \Illuminate\Support\Collection
    {
        return DB::table($table)
            ->select('media.id', 'media.media_ref', 'media.file_name', 'media.real_name', 'media.extension', 'media.type',
                'media.path', 'media.url', 'media.size')
            ->leftJoin('media', 'media.id', '=', $table . '.media_id')
            ->where($where1)
            ->where($where2)
            ->get();
    }

    public function getMediaByThumbnailsTable($where1, $where2 ): \Illuminate\Support\Collection
    {
        return DB::table('media_thumbnails')
            ->select('media_thumbnails.id', 'media_thumbnails.size', 'media_thumbnails.thumb_path', 'media_thumbnails.thumb_url')
            ->where($where1)
            ->where($where2)
            ->get();
    }

//    public function getMediaApproveByTable(string $table, $where1, $where2 ): \Illuminate\Support\Collection
//    {
//        return DB::table($table)
//            ->select('media.id', 'media.media_ref', 'media.file_name', 'media.real_name', 'media.extension', 'media.type',
//                'media.path', 'media.url', 'media.size')
//            ->leftJoin('media', 'media.id', '=', $table . '.media_id')
//            ->where($where1)
//            ->where($where2)
//            ->get();
//    }
    public function removeMediaByTable(string $string, array $array)
    {
        DB::table($string)
            ->where($array)
            ->delete();
    }
}
