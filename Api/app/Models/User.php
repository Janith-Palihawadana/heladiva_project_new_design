<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @property int $id
 * @property string $user_ref
 * @property int $language_id
 * @property int $role_id
 * @property int $agency_id
 * @property int permission_id
 * @property string $email
 * @property string $full_name
 * @property string $phone_number
 * @property Carbon|null $otp_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property bool $is_active
 * @property bool $is_login
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $casts = [
        'role_id' => 'int',
        'is_active' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int',
        'agency_id' => 'int',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'user_ref',
        'role_id',
        'agency_id',
        'email',
        'phone_number',
        'full_name',
        'password',
        'remember_token',
        'is_active',
        'token',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public static function getUsers(array $all, $count): Collection|int|array
    {
        $Users = User::query()
            ->select('users.user_ref', 'users.email', 'users.phone_number', 'users.full_name', 'users.is_active','users.role_id')
            ->where('users.agency_id', $all['agency_id']);

        if (!empty($all['keyword'])) {
            $Users->where(function ($query) use ($all) {
                $query->where('users.full_name', 'LIKE', '%' . $all['keyword'] . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $all['keyword'] . '%')
                    ->orWhere('users.phone_number', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $Users->where('users.is_active', $all['is_active']);
        }

        if ($count) {
            return $Users->count();
        }

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;
        return $Users->orderBy('users.id', 'desc')
            ->offset($start_no)
            ->limit($page_size)
            ->get();
    }

    public static function saveUsers($request): void
    {
        $user = User::create([
            'language_id' => 1,
            'role_id' => 2,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'is_active' => $request->is_active,
            'created_by' => Auth::id(),
        ]);

        $user_id = $user->id;

        UserDetail::create([
            'user_id' => $user_id,
            'language_id' => 1,
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);
    }

    public static function updateUsers($request): void
    {
        $user = User::where('user_ref', $request->user_ref)->first();

        if ($user) {
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->is_active = $request->is_active;

            // Check if password is provided and update it
            if ($request->password !== null && $request->password !== '') {
                $user->password = Hash::make($request->password);
            }

            $user->updated_by = Auth::id();
            $user->save();
        }

        $user_id = $user->id;

        $user_details = UserDetail::where('user_id', $user_id)->first();

        if ($user_details) {
            $user_details->name = $request->name;
            $user_details->updated_by = Auth::id();
            $user_details->save();
        }
    }

    public static function deleteUsers($request): void
    {
        $user = User::where('user_ref', $request->ref)->first();

        if ($user) {
            $user->is_active = 0;
//            $user->email = 'inactive_' . uniqid() . '_' . $user->email;
            $user->updated_by = Auth::id();
            $user->save();
        }
    }

    public static function createUser($request)
    {
        DB::transaction(function () use ($request) {
            $language_id = Language::query()->where('language_ref', $request->language_ref)->first()->id;

            $user = User::create([
                'language_id' => $language_id,
                'role_id' => 4, // 4 id for student
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password)
            ]);

            $userId = $user->id;
            $student = Student::create([
                'user_id' => $userId,
                'city_id' => $request->city,
                'language_id' => $language_id,
                'full_name' => $request->full_name,
                'address' => $request->address,
            ]);

            //generate student id
            $StudentID = $student->id;
            $year = date("y");
            $student_id = $year . "KL" . str_pad($StudentID, 5, "0", STR_PAD_LEFT);

            $student_details = Student::where('id',$StudentID)->first();
            $student_details->student_id = $student_id;
            $student_details ->save();
        });

        return [
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => $request->password
        ];
    }

    public static function saveUser($all)
    {
        $user = User::create([
            'role_id' => 2,
            'email' => $all['email'],
            'full_name' => $all['full_name'],
            'phone_number' => $all['phone_number'],
            'password' => Hash::make($all['password']),
            'is_active' => $all['is_active'],
            'agency_id' => $all['agency_id'],
            'created_by' => Auth::id(),
        ]);
        return $user;
    }

    public static function updateUser($all){

        $user = User::where('user_ref', $all["user_ref"])->first();
        $user->email = $all['email'];
        $user->phone_number = $all['phone_number'];
        $user->is_active = $all['is_active'];
        $user->role_id = $all['role_id'];
        $user->save();

        if($all['password'] != null){
            $user->password = bcrypt($all['password']);
            $user->save();
        }
    }

    public static function getUserAgencyName($user_id): string
    {
        $agency_name = '';
        $user = User::query()->where('id', $user_id)->first();
        if ($user) {
            $agency = Agency::query()->where('id', $user->agency_id)->first();
            if ($agency) {
                $agency_name = $agency->name;
            }
        }
        return $agency_name;
    }

}
