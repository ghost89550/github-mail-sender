<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\UserRegister;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Swagger\Annotations as SWG;

/**
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="Github Mailer",
 *     version="1.0",
 *     description="An easy way to remind the github developer what is the weather right now xD",
 *     @SWG\Contact(
 *         name="Api Support",
 *         email="kefzce@gmail.com"
 *     )
 *   )
 * )
 */
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @SWG\Post(
     *      path="/api/auth/register",
     *      operationId="jwt.register",
     *      tags={"Auth"},
     *      summary="User register",
     *      description="Return currently creater user object",
     *      @SWG\Parameter(
     *          name="name",
     *          description="User name",
     *          required=true,
     *          type="string",
     *          in="query",
     *     ),
     *      @SWG\Parameter(
     *          name="email",
     *          description="User email",
     *          required=true,
     *          type="string",
     *          in="query",
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          description="User password",
     *          required=true,
     *          type="string",
     *          in="query",
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request")
     *     )
     *
     * Auth user via JWT and return access_token
     * @param Request $request
     * @return UserResource
     */
    public function register(Request $request): UserResource
    {
        $credentials = $request->only(['name', 'email', 'password']);

        $rules = [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|max:255',
            'name' => 'required|max:255',
        ];

        $validator = Validator::make($credentials, $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->getMessageBag()]);
        }

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $user = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);

        event(new UserRegister($user));

        return new UserResource($user);
    }

    /**
     * @SWG\Post(
     *      path="/api/auth/login",
     *      operationId="jwt.login",
     *      tags={"Auth"},
     *      summary="User login via jwt auth",
     *      description="Return access_token",
     *      @SWG\Parameter(
     *          name="email",
     *          description="User email",
     *          required=true,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          description="User password",
     *          required=true,
     *          type="string",
     *          in="query"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request")
     *     )
     *
     * Auth user via JWT and return access_token
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @SWG\Post(
     *      path="/api/auth/me",
     *      operationId="jwt.checkme",
     *      tags={"Auth"},
     *      summary="Return current login user object",
     *      description="Return currently auth user",
     *     @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=401, description="Unauthenticated")
     *     )
     *
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * @SWG\Post(
     *      path="/api/auth/logout",
     *      operationId="jwt.logout",
     *      tags={"Auth"},
     *      summary="Invalidate the token",
     *      description="Logout current authenticated user",
     *     @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request")
     *     )
     *
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @SWG\Post(
     *      path="/api/auth/refresh",
     *      operationId="jwt.refresh",
     *      tags={"Auth"},
     *      summary="Refresh token",
     *      description="Refresh token",
     *     @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request")
     *     )
     *
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}
