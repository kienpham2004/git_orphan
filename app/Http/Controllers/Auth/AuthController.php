<?php

namespace App\Http\Controllers\Auth;

use App\Models\UserAccount;
use App\Original\Util\SessionUtil;
use App\Events\LoginedEvent;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Auth;
use Event;
use App\Lib\Util\Constants;
// 独自
use OhAccount;
use App\Http\Controllers\tInitSearch;
use Session;
use Log;

class AuthController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers;

    protected $redirectTo = '/login';

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard  $auth
     * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
     * @return void
     */
    public function __construct(Guard $auth, Registrar $registrar)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;
        
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function postLogin(LoginRequest $request)
    {
        \Log::useDailyFiles(storage_path().'/logs/access.log', 5, 'info');

        if (Auth::attempt(['user_login_id' => $request->id, 'password' => $request->password])) {
            Event::fire( new LoginedEvent( Auth::user() ) );
            $user = Auth::user();
            
            // ユーザー情報を登録(セッション)
            SessionUtil::putUser( new OhAccount( $user ) );
            // 初期値設定
            self::initValues();

            \Log::info($request->id. "のアカウントがログインできました" );
            return redirect()->intended('top');
        }
        
        \Log::info("IDまたはパスワードが間違っています" );
        return redirect('auth/login')->with('error', 'IDまたはパスワードが間違っています');
    }

    public function getLogout() {
        if(SessionUtil::getUser()) {
            $user_login_id = SessionUtil::getUser()->getUserLoginId();
            \Log::useDailyFiles(storage_path().'/logs/access.log', 5, 'info');
            \Log::info($user_login_id."のアカウントはログアウトした。" );
        }

        // ユーザー情報を削除(セッション)
        SessionUtil::removeUser();

        if( session('csrf_error' ) ) {
            session('error', 'セッションが切れました。<br />セキュリティのためログアウトしました。');
        }

        $this->auth->logout();

        Session::flush(); // removes all session data

        return redirect(property_exists( $this, 'redirectAfterLogout' ) ? $this->redirectAfterLogout : '/');
        //$this->getLogout();
    }

    /**
     * 初期値を設定
     */
    private function initValues(){
        // ユーザー権限一覧を取得する
        SessionUtil::put(Constants::SEC_ACCOUT_PERMISION, UserAccount::allStaffPermision() );

        // 検索データ範囲を取得する。
        $minTargetData = 0;
        $maxTargetData = 0;
        tInitSearch::getMaxMinData(Constants::TB_TARGET_CARS, Constants::TGC_INSPECTION_YM, $minTargetData, $maxTargetData);
        // 対象データのセッションに格納する
        SessionUtil::put(Constants::SEC_TARGET_DATA_MIN, $minTargetData);
        SessionUtil::put(Constants::SEC_TARGET_DATA_MAX, $maxTargetData);

        // 顧客データ範囲を取得する。
        $minCusData = 0;
        $maxCusData = 0;
        tInitSearch::getMaxMinData(Constants::TB_CUSTOMER, Constants::SYAKEN_NEXT_DATE, $minCusData, $maxCusData);
        // 対象データのセッションに格納する
        SessionUtil::put(Constants::SEC_CUSTOMER_DATA_MIN, $minCusData);
        SessionUtil::put(Constants::SEC_CUSTOMER_DATA_MAX, $maxCusData);

        // 任意保険終期
        $minInsEndData = 0;
        $maxInsEndDate = 0;
        tInitSearch::getMaxMinData(Constants::TB_TARGET_CARS, Constants::TGC_CUSTOMER_INSURANCE_END_DATE, $minInsEndData, $maxInsEndDate);
        // 対象データのセッションに格納する
        SessionUtil::put(Constants::SEC_INSURANCE_END_DATA_MIN, $minInsEndData);
        SessionUtil::put(Constants::SEC_INSURANCE_END_DATA_MAX, $maxInsEndDate);


//        // 保険データ範囲を取得する。
//        $minInsData = 0;
//        $maxInsData = 0;
//        tInitSearch::getMaxMinData(Constants::TB_INSURANCE, Constants::INSU_INSPECTION_TARGET_YM, $minInsData, $maxInsData);
//        // 対象データのセッションに格納する
//        SessionUtil::put(Constants::SEC_INSURANCE_TARGET_DATA_MIN, $minInsData);
//        SessionUtil::put(Constants::SEC_INSURANCE_TARGET_DATA_MAX, $maxInsData);
//
//        $minInsData = 0;
//        $maxInsData = 0;
//        tInitSearch::getMaxMinData(Constants::TB_INSURANCE, Constants::INSU_INSPECTION_YM, $minInsData, $maxInsData);
//        // 対象データのセッションに格納する
//        SessionUtil::put(Constants::SEC_INSURANCE_DATA_MIN, $minInsData);
//        SessionUtil::put(Constants::SEC_INSURANCE_DATA_MAX, $maxInsData);
    }
}
