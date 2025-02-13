<?php

namespace App\Models;

use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Collection\Collection;

/**
 * Class Teacher
 *
 * @property int $id
 * @property string $teacher_ref
 * @property int $gender_id
 * @property int|null $dp_media_id
 * @property int|null $cover_media_id
 * @property string $phone_number
 * @property string $email_address
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */

class Teacher extends Model
{
    protected $table = 'teachers';
    public $timestamps = true;

    protected $casts = [
        'gender_id' => 'int',
        'dp_media_id' => 'int',
        'cover_media_id' => 'int',
        'is_active' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int',
        'user_id' => 'int',
    ];

    protected $fillable = [
        'teacher_ref',
        'gender_id',
        'phone_number',
        'email_address',
        'is_active',
        'user_id',
        'created_by',
        'updated_by'
    ];

    public static function getTeachers(array $all): array
    {
        $users = Teacher::query()
            ->select('teachers.id', 'teachers.teacher_ref', 'teachers.email_address', 'teachers.phone_number', 'teachers_details.full_name', 'teachers_details.education_title', 'teachers_details.description', 'teachers.is_active', 'teachers.created_at')
            ->leftJoin('teachers_details', 'teachers.id', '=', 'teachers_details.teacher_id')
            ->where('teachers.is_active', 1)
            ->where('teachers_details.language_id', 1);

        if (!empty($all['keyword'])) {
            $users->where(function ($query) use ($all) {
                $query->where('teachers_details.full_name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if (!empty($all['reg_date'])) {
            $users->where('teachers.created_at', 'LIKE', '%' . $all['reg_date'] . '%');
        }

        $totalCount = $users->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $teachers = $users->orderBy('teachers.id', 'desc')
            ->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'teachers' => $teachers,
        ];
    }

    public static function getTeacher(array $all): array
    {
        $teacher_id = Teacher::where('teacher_ref', $all['ref'])->first()->id;
        $TeachersData = TeachersDetail::query()
            ->select('language_id', 'full_name', 'education_title', 'description')
            ->where('teacher_id', $teacher_id)
            ->get();

        $formattedData = [];

        foreach ($TeachersData as $teacher) {
            $languageId = $teacher->language_id;
            $formattedData[$languageId] = [
                'full_name' => $teacher->full_name,
                'education_title' => $teacher->education_title,
                'description' => $teacher->description,
                'course_descriptions' => self::getTeacherCourseDescriptionBaseOnLanguage($teacher_id,$languageId),
            ];
        }

        return $formattedData;
    }

    private static function getTeacherCourseDescriptionBaseOnLanguage($teacher_id,$languageId): array
    {
        $teachersCourseDescriptions = TeacherCourseDescription::query()
            ->select('course_id','language_id', 'description')
            ->where('teacher_id', $teacher_id)
            ->where('language_id', $languageId)
            ->where('is_active', true)
            ->get()
            ->toArray();

        foreach ($teachersCourseDescriptions as &$courseDescription) {
            $courseDescription['course_ref'] = Course::where('id', $courseDescription['course_id'])->first()->course_ref;
            unset($courseDescription['course_id']);
        }
        // remove to avoid potential issues
        unset($courseDescription);

        return $teachersCourseDescriptions;
    }

    public static function saveTeachers($all,$user_id)
    {
        try {
            DB::beginTransaction();
            $teacher = Teacher::create([
                'email_address' => $all['email'],
                'phone_number' => $all['phone_number'],
                'gender_id' => 1,
                'user_id' => $user_id,
                'created_by' => Auth::id(),
            ]);
            $teacher_id = $teacher->id;

            $languages = Language::all();

            foreach ($languages as $language) {
                $course_description = $all[$language->id]['course_description'];
                TeachersDetail::create([
                    'teacher_id' => $teacher_id,
                    'language_id' => $language->id,
                    'full_name' => $all[$language->id]['full_name'],
                    'education_title' => $all[$language->id]['education_title'],
                    'description' => $all[$language->id]['description'],
                    'created_by' => Auth::id(),
                ]);
                foreach ($course_description as $description){
                    TeacherCourseDescription::create([
                        'course_id' => Course::where('course_ref', $description['course_ref'])->first()->id,
                        'teacher_id' => $teacher_id,
                        'language_id' => $language->id,
                        'description' => $description['description'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            foreach ($all['courses'] as $course) {
                CoursesTeacher::create([
                    'teacher_id' => $teacher_id,
                    'course_id' => Course::where('course_ref', $course['course_ref'])->first()->id,
                    'created_by' => Auth::id(),
                ]);
            }
            DB::commit();
            return $teacher_id;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return throw $e;
        }
    }

    public static function updateTeachers(array $all): void
    {
        try {
            DB::beginTransaction();
            $teacher = Teacher::where('teacher_ref', $all['teacher_ref'])->first();
            $teacher->email_address = $all['email'];
            $teacher->phone_number = $all['phone_number'];
            $teacher->save();


            $user_id = $teacher->user_id;
            $user = User::where('id', $user_id)->first();
            $user->email = $all['email'];
            $user->phone_number = $all['phone_number'];
            $user->save();

            if($all['password'] != null){
                $user->password = bcrypt($all['password']);
                $user->save();
            }
            $languages = Language::all();

            foreach ($languages as $language) {
                $teacherDetails = TeachersDetail::where('teacher_id', $teacher->id)
                    ->where('language_id', $language->id)
                    ->first();
                $teacherDetails->full_name = $all[$language->id]['full_name'];
                $teacherDetails->education_title = $all[$language->id]['education_title'];
                $teacherDetails->description = $all[$language->id]['description'];
                $teacherDetails->save();
                self::updateTeacherCourseDescriptions($all[$language->id]['course_description'], $teacher->id, $language->id);
            }
            CoursesTeacher::where('teacher_id', $teacher->id)->update(['is_active' => false]);
            foreach ($all['courses'] as $course) {
                CoursesTeacher::updateOrCreate([
                    'teacher_id' => $teacher->id,
                    'course_id' => Course::where('course_ref', $course['course_ref'])->first()->id,
                ], [
                    'teacher_id' => $teacher->id,
                    'course_id' => Course::where('course_ref', $course['course_ref'])->first()->id,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }

    public static function updateTeacherCourseDescriptions($data,$teacher_id,$language_id): void
    {
        foreach ($data as $description){
            if($description['status_id'] == 1 || $description['status_id'] == 2) {
                TeacherCourseDescription::updateOrCreate([
                    'course_id' => Course::where('course_ref', $description['course_ref'])->first()->id,
                    'teacher_id' => $teacher_id,
                    'language_id' => $language_id,
                ], [
                    'description' => $description['description'],
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            } else {
                // status_id = 3
                $course_id = Course::where('course_ref', $description['delete_course_ref'])->first()->id;
                $course = TeacherCourseDescription::where('course_id', $course_id)
                    ->where('teacher_id', $teacher_id)
                    ->where('language_id', $language_id)
                    ->first();
                $course->is_active = false;
                $course->updated_by = Auth::id();
                $course->save();
            }
        }
    }

    public static function deleteTeachers($request): void
    {
        $user = Teacher::where('teacher_ref', $request->ref)->first();

        if ($user) {
            $user->is_active = 0;
            $user->updated_by = Auth::id();

            $user->save();
        }
    }

    public static function coursesTeachers($teacherId): array
    {
        return CoursesTeacher::query()
            ->select('courses.course_ref', 'courses_details.name')
            ->leftJoin('courses', 'courses.id', '=', 'courses_teachers.course_id')
            ->leftJoin('courses_details', 'courses_details.course_id', '=', 'courses.id')
            ->where('courses_teachers.teacher_id', $teacherId)
            ->where('courses_details.language_id', 1)
            ->where('courses_teachers.is_active', 1)
            ->get()
            ->toArray();
    }

    public static function getTeacherThumbnailUrl($mediaTypeID,$teacherID,$size)
    {
        return MediaThumbnail::select('media_thumbnails.thumb_url')
            ->leftJoin('media', 'media.id', '=', 'media_thumbnails.media_id')
            ->leftJoin('media_teachers', 'media_teachers.media_id', '=', 'media.id')
            ->where('media_teachers.media_type_id', $mediaTypeID)
            ->where('media_teachers.teacher_id', $teacherID)
            ->where('media_thumbnails.size', $size)
            ->first();
    }

    public static function getActiveTeachers() : array
    {
        return Teacher::select('teachers.teacher_ref','teachers_details.full_name')
            ->leftJoin('teachers_details', 'teachers.id', '=', 'teachers_details.teacher_id')
            ->where('teachers.is_active', 1)
            ->where('teachers_details.language_id', 1)
            ->get()
            ->toArray();

    }

    public static function teacherProfileData(array $all,$language_ref): \Illuminate\Database\Eloquent\Collection|array
    {
        $teacher = Teacher::where('teacher_ref', $all['ref'])->first();
//        $language_id = auth()->user()->language_id ?? 1; // Default to 1 if auth()->user()->language_id is null

        if ($language_ref != null) {
            $language_id = Language::where('language_ref', $language_ref)->first()->id;
        }
        else {
            $language_id = auth()->user()->language_id ?? 1; // Default to 1 if auth()->user()->language_id is null
        }

        $profile_data = TeachersDetail::query()
            ->select('teachers_details.full_name', 'teachers_details.education_title', 'teachers_details.description')
            ->where('teachers_details.teacher_id', $teacher->id)
            ->where('teachers_details.language_id', $language_id)
            ->first()
            ->toArray();

        $teacherCourses = CoursesDetail::query()
            ->select('courses.course_ref','courses.media_id', 'courses_details.name', 'courses_details.description', 'courses_teachers.course_id')
            ->leftJoin('courses', 'courses.id', '=', 'courses_details.course_id')
            ->leftJoin('courses_teachers', 'courses_teachers.course_id', '=', 'courses.id')
            ->where('courses_teachers.is_active', true)
            ->where('courses_teachers.teacher_id', $teacher->id)
            ->where('courses_details.language_id', $language_id)
            ->get();

        foreach ($teacherCourses as $course) {
            $video_count = Video::query()
                ->where('course_id', $course->course_id)
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->count();
            $course_price = Video::query()
                ->where('course_id', $course->course_id)
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->first();
            $course_duration = CoursesTeacher::query()
                ->select('course_duration')
                ->where('course_id', $course->course_id)
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->first();
            $subscribed_count = CoursesStudent::query()
                ->where('course_id', $course->course_id)
                ->where('teachers_id', $teacher->id)
                ->where('is_active', true)
                ->where('course_status_id',1)
                ->count();

            $course_img = $course->media_id != null ? json_decode(MediaThumbnail::getThumbUrl($course->media_id,200), true) : null ;
            $course->video_count = $video_count;
            $course->course_price = $course_price ? $course_price->price : 0;
            $course->course_duration = $course_duration ? $course_duration->course_duration : null;
            $course->subscribed_count = $subscribed_count;
            $course->course_img = $course_img ? $course_img['path'] : null;
            unset($course->course_id);
            unset($course->media_id);
        }

        // media types 1 = dp, 2 = cover, 3 = banner
        $profile_data['dp'] = self::getTeacherImages($teacher->id,200,1)->pluck('thumb_url')->first();
        $profile_data['cover'] = self::getTeacherImages($teacher->id,200,2)->pluck('thumb_url')->first();
        $profile_data['banners'] = self::getTeacherBanners($teacher->id,200,3);
        $profile_data['courses'] = $teacherCourses;
        return $profile_data;
    }

    public static function getTeacherImages($teacher_id, $size, $media_type_id): \Illuminate\Support\Collection
    {
        return DB::table('media_teachers')
            ->select('media_thumbnails.thumb_url')
            ->leftJoin('media', 'media.id', '=', 'media_teachers.media_id')
            ->leftJoin('media_thumbnails', 'media_thumbnails.media_id', '=', 'media.id')
            ->where('media_teachers.teacher_id', $teacher_id)
            ->where('media_teachers.is_active', 1)
            ->where('media_teachers.media_type_id', $media_type_id)
            ->where('media_thumbnails.size', $size)
            ->get();
    }

    public static function getTeacherBanners($teacher_id, $size, $media_type_id): \Illuminate\Support\Collection
    {
        $data = DB::table('media_teachers')
            ->select('media.type', 'media.url', 'media.id')
            ->leftJoin('media', 'media.id', '=', 'media_teachers.media_id')
            ->where('media_teachers.teacher_id', $teacher_id)
            ->where('media_teachers.is_active', 1)
            ->where('media_teachers.media_type_id', $media_type_id)
            ->get();

        foreach ($data as $item) {
            // Check if 'image' is included in $item->type
            if (str_contains($item->type, 'image')) {
                $thumbUrl = DB::table('media_thumbnails')
                    ->where('media_id', $item->id)
                    ->where('size', $size)
                    ->pluck('thumb_url')
                    ->first();
                $item->url = $thumbUrl;
            }
            unset($item->id);
        }
        return $data;
    }


    public static function getTeacherDetails($ref): array
    {
        $teacher_id = Teacher::where('teacher_ref', $ref)->first()->id;
        $TeachersData = TeachersDetail::query()
            ->select('language_id', 'full_name', 'education_title', 'description')
            ->where('teacher_id', $teacher_id)
            ->get();

        $formattedData = [];

        foreach ($TeachersData as $teacher) {
            $languageId = $teacher->language_id;
            $formattedData[$languageId] = [
                'full_name' => $teacher->full_name,
                'education_title' => $teacher->education_title,
                'description' => $teacher->description,
                'course_descriptions' => self::getTeacherCourseDescriptionBaseOnLanguage($teacher_id,$languageId),
            ];
        }

        return $formattedData;
    }



    public static function updateTeacher(array $all,$teacher_ref): void
    {
        try {
            DB::beginTransaction();
            $teacher = Teacher::where('teacher_ref', $teacher_ref)->first();
            $teacher->email_address = $all['email'];
            $teacher->phone_number = $all['phone_number'];
            $teacher->save();

            $user_id = $teacher->user_id;
            $user = User::where('id', $user_id)->first();
            $user->email = $all['email'];
            $user->phone_number = $all['phone_number'];
            $user->save();

            if($all['password'] != null){
                $user->password = bcrypt($all['password']);
                $user->save();
            }
            $languages = Language::all();

            foreach ($languages as $language) {
                $teacherDetails = TeachersDetail::where('teacher_id', $teacher->id)
                    ->where('language_id', $language->id)
                    ->first();
                $teacherDetails->full_name = $all[$language->id]['full_name'];
                $teacherDetails->education_title = $all[$language->id]['education_title'];
                $teacherDetails->description = $all[$language->id]['description'];
                $teacherDetails->save();
                self::updateTeacherCourseDescriptions($all[$language->id]['course_description'], $teacher->id, $language->id);
            }
            CoursesTeacher::where('teacher_id', $teacher->id)->update(['is_active' => false]);
            foreach ($all['courses'] as $course) {
                CoursesTeacher::updateOrCreate([
                    'teacher_id' => $teacher->id,
                    'course_id' => Course::where('course_ref', $course['course_ref'])->first()->id,
                ], [
                    'teacher_id' => $teacher->id,
                    'course_id' => Course::where('course_ref', $course['course_ref'])->first()->id,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }

}
