<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CoursesStudent;
use App\Models\CoursesTeacher;
use App\Models\MediaApprove;
use App\Models\MediaTeacher;
use App\Models\Payment;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Video;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TeachersController extends Controller
{
    public function getTeachers(Request $request): JsonResponse
    {
        try {
            $teachers = Teacher::getTeachers($request->all());
            foreach ($teachers['teachers'] as &$teacher){
                $profileURL = Teacher::getTeacherThumbnailUrl(1, $teacher['id'], 200);
                $teacher['profile_pic_url'] = optional($profileURL)->toArray()['thumb_url'] ?? null;
            }
            return $this->successReturn( $teachers, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], ['Failed to return data'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveTeachers(Request $request)
    {
        $form_data = json_decode($request['form'], true);
        $validator = ValidationService::saveTeacherValidator($form_data);

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $user_id = User::saveUserTeacher($form_data);
            $id = Teacher::saveTeachers($form_data,$user_id);

            $attachments = [
                'dp' => ['type_id' => 1, 'request_key' => 'dp_attachments', 'delete_key' => 'dp_delete_attachments'],
                'cover' => ['type_id' => 2, 'request_key' => 'cover_attachments', 'delete_key' => 'cover_delete_attachments'],
                'banner' => ['type_id' => 3, 'request_key' => 'banner_attachments', 'delete_key' => 'banner_delete_attachments'],
            ];

            $fileUploadService = new FileUploadService();

            foreach ($attachments as $attachmentType => $config) {
                $fileUploadService->removeFormMedia('media_teachers', json_decode($request[$config['delete_key']], true));

                if ($request->hasFile($config['request_key'])) {
                    $media_ids = $fileUploadService->store($request->file($config['request_key']));

                    foreach ($media_ids as $media_id) {
                        $fileUploadService->saveMediaToTable('media_teachers', [
                            'media_id' => $media_id,
                            'media_type_id' => $config['type_id'],
                            'teacher_id' => $id,
                        ]);
                    }
                }
            }
            return $this->successReturn([], 'New teacher added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], ['Teacher create failed.'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getTeacher(Request $request): JsonResponse
    {
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $teacher = Teacher::where('teacher_ref', $request->ref)->first();
            if (!$teacher) {
                return $this::errorReturn([], 'Teacher not found', ResponseAlias::HTTP_BAD_REQUEST);
            }

            $teacherData = new \stdClass();
            $teacherData->teacher = Teacher::getTeacher($request->all());
            $teacher_id = $teacher->id;
            $teacherData->courses = Teacher::coursesTeachers($teacher_id);
            $teacherData->dp_attachments = (new FileUploadService)->getMediaByTable('media_teachers',
                ['media_teachers.teacher_id' => $teacher_id],
                ['media_teachers.media_type_id' => 1]
            );

            $teacherData->cover_attachments = (new FileUploadService)->getMediaByTable('media_teachers',
                ['media_teachers.teacher_id' => $teacher_id],
                ['media_teachers.media_type_id' => 2]
            );

            $teacherData->banner_attachments = (new FileUploadService)->getMediaByTable('media_teachers',
                ['media_teachers.teacher_id' => $teacher_id],
                ['media_teachers.media_type_id' => 3]
            );
            return $this->successReturn($teacherData, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([$e], ['Failed to return data.'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateTeachers(Request $request): JsonResponse
    {
        $form_data = json_decode($request['form'], true);

        $validator = ValidationService::updateTeacherValidator($form_data);

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $teachers = Teacher::where('teacher_ref', $form_data['teacher_ref'])->first();
            Teacher::updateTeachers((array)$form_data);

            $attachments = [
                'dp' => ['type_id' => 1, 'request_key' => 'dp_attachments', 'delete_key' => 'dp_delete_attachments'],
                'cover' => ['type_id' => 2, 'request_key' => 'cover_attachments', 'delete_key' => 'cover_delete_attachments'],
                'banner' => ['type_id' => 3, 'request_key' => 'banner_attachments', 'delete_key' => 'banner_delete_attachments'],
            ];

            $fileUploadService = new FileUploadService();

            foreach ($attachments as $attachmentType => $config) {
                $fileUploadService->removeFormMedia('media_teachers', json_decode($request[$config['delete_key']], true));

                if ($request->hasFile($config['request_key'])) {
                    $media_ids = $fileUploadService->store($request->file($config['request_key']));

                    foreach ($media_ids as $media_id) {
                        $fileUploadService->saveMediaToTable('media_teachers', [
                            'media_id' => $media_id,
                            'media_type_id' => $config['type_id'],
                            'teacher_id' => $teachers->id
                        ]);
                    }
                }
            }

            return $this->successReturn([], 'Teacher updated successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], ['Teacher update failed.'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteTeachers(Request $request): JsonResponse
    {
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $teachers = Teacher::where('teacher_ref', $request->ref)->first();

            if (!$teachers) {
                return $this->errorReturn([], ['Teacher not found.'], ResponseAlias::HTTP_BAD_REQUEST);
            }

            $is_check_teacher = CoursesStudent::checkTeacherBuyCourse($teachers->id);

            if ($is_check_teacher){
                return $this->errorReturn([], ["This teacher's course already bought student."], ResponseAlias::HTTP_BAD_REQUEST);
            }
            Teacher::deleteTeachers($request);

            return $this->successReturn([], 'Teacher deleted successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([$e], ['Teacher delete failed.'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getTeacherCourses(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::teacherRefValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $courses = CoursesTeacher::getTeacherCourses($request->all());
            return $this->successReturn($courses, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        }catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([$e], ['Data return unsuccessful'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getTeacherCoursesNotContainVideos(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::teacherRefValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $courses = CoursesTeacher::getTeacherCoursesNotContainVideos($request->all());
            return $this->successReturn($courses, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        }catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([$e], ['Data return unsuccessful'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getActiveTeachers(): JsonResponse
    {
        try {
            $teachers = Teacher::getActiveTeachers();
            return $this->successReturn( $teachers, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        }catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([$e], ['Data return unsuccessful'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getCourseBaseTeachers(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::courseRefValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $courseBaseTeachers = CoursesTeacher::getCourseBaseTeachers($request->all());

            $courseBaseTeachersVideo = [];
            $courseID = Course::where('course_ref', $request['ref'])->first()->id;

            foreach ($courseBaseTeachers as $teacher){
                $IDs = [
                    'course_id' => $courseID,
                    'teacher_id' => $teacher['teacher_id'],
                ];

                $amount = Video::checkCourseVideoPrice($IDs);
                if ($amount){
                    array_push($courseBaseTeachersVideo, $teacher);
                }
            }

            return $this->successReturn( $courseBaseTeachersVideo , 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function coursesTeachers(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::courseRefValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $coursesTeachers = CoursesTeacher::getCoursesTeachers($request->all(),$request->header('language_ref'));
            return $this->successReturn( $coursesTeachers, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], ['Failed to return data'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function teacherVideos(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::courseTeacherRefsValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $teacherCourses = Video::teacherVideos($request->all());
            return $this->successReturn( $teacherCourses, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], ['Failed to return data'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function teacherProfileData(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::teacherRefValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $profileData = Teacher::teacherProfileData($request->all(),$request->header('language_ref'));
            return $this->successReturn( $profileData, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], ['Failed to return data'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function teacherAllVideos(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::courseTeacherLanguageRefsValidator($request->all());
            if ($validator->fails()) {
                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $teacherCourses = Video::teacherAllVideos($request->all(),$request->header('language_ref'));
            return $this->successReturn( $teacherCourses, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], ['Failed to return data'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getTeacherDetails(): JsonResponse
    {
        $user_id = auth()->user()->id;
        $teacher = Teacher::where('user_id', $user_id)->first();

        try {
            if (!$teacher) {
                return $this::errorReturn([], 'Teacher not found', ResponseAlias::HTTP_BAD_REQUEST);
            }

            $teacherData = new \stdClass();
            $teacherData->teacher = Teacher::getTeacherDetails($teacher->teacher_ref);
            $teacher_id = $teacher->id;
            $teacherData->details = $teacher;
            $teacherData->courses = Teacher::coursesTeachers($teacher_id);

            $teacherData->dp_image = (new FileUploadService)->getMediaUrl('media_teachers',
                    ['media_teachers.teacher_id' => $teacher_id],
                    ['media_teachers.media_type_id' => 1]
                )->url ?? null;
            $teacherData->dp_attachments = (new FileUploadService)->getMediaByTable('media_teachers',
                ['media_teachers.teacher_id' => $teacher_id],
                ['media_teachers.media_type_id' => 1]
            );

            $teacherData->dp_attachments_pending = (new FileUploadService)->getMediaUrlPending('media_approve',
                    ['media_approve.teacher_id' => $teacher_id],
                    ['media_approve.media_type_id' => 1],
                    ['media_approve.approve_status' => 0]
                )->url ?? null;

            $teacherData->dp_rejected_attachments_pending = (new FileUploadService)->getMediaUrlReject('media_approve',
                ['media_approve.teacher_id' => $teacher_id],
                ['media_approve.media_type_id' => 1],
                ['media_approve.approve_status' => 2]
            );

            $teacherData->cover_attachments = (new FileUploadService)->getMediaByTable('media_teachers',
                ['media_teachers.teacher_id' => $teacher_id],
                ['media_teachers.media_type_id' => 2]
            );

            $teacherData->cover_image = (new FileUploadService)->getMediaUrl('media_teachers',
                    ['media_teachers.teacher_id' => $teacher_id],
                    ['media_teachers.media_type_id' => 2]
                )->url ?? null;

            $teacherData->cover_attachments_pending = (new FileUploadService)->getMediaUrlPending('media_approve',
                    ['media_approve.teacher_id' => $teacher_id],
                    ['media_approve.media_type_id' => 2],
                    ['media_approve.approve_status' => 0]
                )->url ?? null;

            $teacherData->cover_rejected_attachments_pending = (new FileUploadService)->getMediaUrlReject('media_approve',
                    ['media_approve.teacher_id' => $teacher_id],
                    ['media_approve.media_type_id' => 2],
                    ['media_approve.approve_status' => 2]
                ) ?? null;

            $teacherData->banner_attachments = (new FileUploadService)->getMediaByTable('media_teachers',
                ['media_teachers.teacher_id' => $teacher_id],
                ['media_teachers.media_type_id' => 3]
            );

//            $teacherData->banner_images = (new FileUploadService)->getMediaUrls('media_teachers',
//                ['media_teachers.teacher_id' => $teacher_id],
//                ['media_teachers.media_type_id' => 3],
//            );

            $teacherData->banner_attachments_pending = (new FileUploadService)->getMediaUrls('media_approve',
                    ['media_approve.teacher_id' => $teacher_id],
                    ['media_approve.media_type_id' => 3],
                    ['media_approve.approve_status' => 0]
                ) ?? null;

            $teacherData->banner_rejected_attachments_pending = (new FileUploadService)->getMediaUrlRejectBanner('media_approve',
                    ['media_approve.teacher_id' => $teacher_id],
                    ['media_approve.media_type_id' => 3],
                    ['media_approve.approve_status' => 2]
                ) ?? null;

            return $this->successReturn($teacherData, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([$e], ['Failed to return data.'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateTeacher(Request $request): JsonResponse
    {
        $form_data = json_decode($request['form'], true);
        $user_id = auth()->user()->id;
        $teacher = Teacher::where('user_id', $user_id)->first();

        $pending_image_banner = json_decode($request['b_pending_images'], true);
//        $pending_image_dp = json_decode($request['dp_pending_images'], true);
//        $pending_image_cover = json_decode($request['c_pending_images'], true);

        if($pending_image_banner){
            foreach ($pending_image_banner as $pending_image){
                $media_approve = MediaApprove::where('media_approve_ref', $pending_image)->first();
                $media_approve->delete();
            }
        }
//        if($pending_image_dp){
//            foreach ($pending_image_dp as $pending_image){
//                $media_approve = MediaApprove::where('media_approve_ref', $pending_image)->first();
//                $media_approve->delete();
//            }
//        }
//        if($pending_image_cover){
//            foreach ($pending_image_cover as $pending_image){
//                $media_approve = MediaApprove::where('media_approve_ref', $pending_image)->first();
//                $media_approve->delete();
//            }
//        }
        $validator = ValidationService::updateTeacherProfileValidator($form_data);

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Teacher::updateTeacher((array)$form_data, $teacher->teacher_ref);

            $attachments = [
                'dp' => ['type_id' => 1, 'request_key' => 'dp_attachments', 'delete_key' => 'dp_delete_attachments'],
                'cover' => ['type_id' => 2, 'request_key' => 'cover_attachments', 'delete_key' => 'cover_delete_attachments'],
                'banner' => ['type_id' => 3, 'request_key' => 'banner_attachments', 'delete_key' => 'banner_delete_attachments'],
            ];

            $fileUploadService = new FileUploadService();

            foreach ($attachments as $attachmentType => $config) {
                $fileUploadService->removeFormMedia('media_teachers', json_decode($request[$config['delete_key']], true));

                if ($request->hasFile($config['request_key'])) {
                    $media_ids = $fileUploadService->store($request->file($config['request_key']));

                    if ($config['type_id'] == 1 || $config['type_id'] == 2) {
                        $fileUploadService->removeMediaByTable('media_approve', ['teacher_id' => $teacher->id, 'media_type_id' => $config['type_id']]);
                    }
                    if ($config['type_id'] == 3) {
                        $fileUploadService->removeMediaByTable('media_approve', ['teacher_id' => $teacher->id, 'media_type_id' => $config['type_id'], 'approve_status' => 2]);
                    }

                    foreach ($media_ids as $media_id) {
                        $fileUploadService->saveMediaToTable('media_approve', [
                            'media_id' => $media_id,
                            'media_type_id' => $config['type_id'],
                            'teacher_id' => $teacher->id,
                            'approve_status' => '0',
                            'is_active' => '1',
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            return $this->successReturn([], 'Teacher updated successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], ['Teacher update failed.'], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getMediaApproval(Request $request): JsonResponse
    {
        $validator = ValidationService::getTeacherValidator($request->all());

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $students = MediaApprove::getApproveMedia($request->all());
            return $this::successReturn( $students, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Failed to return data.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getMediaAdminApproval(Request $request): JsonResponse
    {
        try {
            $validator = ValidationService::MediaAdminApprovalValidator($request->all());
            if ($validator->fails()) {
                return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
            }
            $mediaApprove = MediaApprove::where('media_approve_ref', $request->media_approve_ref)->first();

//            $mediaTeacher = MediaTeacher::where('media_type_id', $mediaApprove->media_type_id)
//                ->where('teacher_id', $mediaApprove->teacher_id)
//                ->first();
//            if($mediaTeacher && $request->approve_status == 1 && ($mediaApprove->media_type_id == 1 || $mediaApprove->media_type_id == 2)){
//                $mediaTeacher->media_id = $mediaApprove->media_id;
//                $mediaTeacher->save();
//            }
            if ($request->approve_status == 1){
                //create new media teacher
                $mediaTeacher = new MediaTeacher();
                $mediaTeacher->media_id = $mediaApprove->media_id;
                $mediaTeacher->media_type_id = $mediaApprove->media_type_id;
                $mediaTeacher->teacher_id = $mediaApprove->teacher_id;
                $mediaTeacher->is_active = true;
                $mediaTeacher->save();
            }

            $mediaApprove->approve_status = $request->approve_status;
            $mediaApprove->comment = $request->comment;
            $mediaApprove->save();

            return $this::successReturn( [], 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Failed to return data.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
